<?php

namespace App\Services\Lead;

use App\Models\Leads;
use App\Models\LeadDepartment;
use App\Models\RoleHasUsers;
use App\Models\Activity;
use App\Models\Cities;
use App\Models\Services;
use App\Models\Locations;
use App\Models\LeadSources;
use App\Models\LeadStatuses;
use App\Models\LeadComments;
use App\Models\LeadsServices;
use App\Models\User;
use App\Models\Regions;
use App\Models\Settings;
use App\Models\Patients;
use App\Models\AuditTrails;
use App\Models\AppointmentStatuses;
use App\Models\Telecomprovidernumber;
use App\Helpers\ACL;
use App\Helpers\Filters;
use App\Helpers\ActivityLogger;
use App\Helpers\GeneralFunctions;
use App\Helpers\Widgets\LocationsWidget;
use App\Exceptions\LeadException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LeadService
{
    protected int $cacheTtl = 3600; // 1 hour cache
    
    // Cached lookup data for batch operations
    protected ?array $lookupCache = null;

    /**
     * Get paginated leads for datatable (OPTIMIZED)
     * Uses single query approach with deferred count for better performance
     */
    public function getDatatableData(array $filters, ?string $leadType = null): array
    {
        $userId = Auth::id();
        $accountId = Auth::user()->account_id;
        $filename = $leadType ? 'junk_leads' : 'leads';

        $whereConditions = $this->buildWhereConditions($filters, $filename, $userId);
        $serviceConditions = $this->buildServiceConditions($filters, $filename, $userId);
        [$orderBy, $order] = $this->getOrderParams($filters, $filename, $userId);

        $junkStatus = $this->getJunkLeadStatus($accountId);
        $junkStatusId = $junkStatus->id ?? 0;
        $userCities = ACL::getUserCities();

        // Build optimized count query (no eager loading, minimal columns)
        $countQuery = $this->buildCountQuery($whereConditions, $serviceConditions, $leadType, $junkStatusId, $userCities);
        $totalRecords = $countQuery->count();

        // Build result query with optimized eager loading
        $resultQuery = $this->buildOptimizedResultQuery($whereConditions, $serviceConditions, $leadType, $junkStatusId, $userCities);

        return [
            'total' => $totalRecords,
            'query' => $resultQuery,
            'orderBy' => $orderBy,
            'order' => $order,
        ];
    }

    /**
     * Parent lead statuses for Kanban columns (junk excluded on the main board).
     */
    public function getKanbanColumns(?string $leadType = null): array
    {
        $accountId = Auth::user()->account_id;
        $junkStatus = $this->getJunkLeadStatus($accountId);

        $parents = LeadStatuses::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->where(function ($query) {
                $query->where('parent_id', 0)->orWhereNull('parent_id');
            })
            ->orderBy('sort_no', 'asc');

        if ($leadType) {
            $parents->where('id', $junkStatus->id ?? 0);
        } elseif ($junkStatus) {
            $parents->where('id', '!=', $junkStatus->id);
        }

        $parents = $parents->get(['id', 'name', 'sort_no', 'is_default', 'is_converted', 'is_arrived', 'is_junk']);

        $children = LeadStatuses::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->where('parent_id', '>', 0)
            ->get(['id', 'parent_id', 'name'])
            ->groupBy('parent_id');

        return $parents->map(function (LeadStatuses $parent) use ($children) {
            $kids = $children->get($parent->id, collect());

            return [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'is_default' => (int) $parent->is_default,
                'is_converted' => (int) $parent->is_converted,
                'is_arrived' => (int) $parent->is_arrived,
                'is_junk' => (int) $parent->is_junk,
                'status_ids' => array_values(array_merge(
                    [(int) $parent->id],
                    $kids->pluck('id')->map(fn ($id) => (int) $id)->all()
                )),
            ];
        })->values()->all();
    }

    /**
     * Roll-up lead counts per Kanban column.
     */
    public function countKanbanColumns(array $filters, ?string $leadType, array $columns): array
    {
        $rows = $this->kanbanStatusCountMap($filters, $leadType);
        $counts = [];

        foreach ($columns as $column) {
            $sum = 0;
            foreach ($column['status_ids'] as $statusId) {
                $sum += (int) ($rows[$statusId] ?? 0);
            }
            $counts[(int) $column['id']] = $sum;
        }

        return $counts;
    }

    /**
     * Query + total for one Kanban column (parent status + its children).
     */
    public function getKanbanColumnQuery(array $filters, ?string $leadType, array $statusIds): array
    {
        $userId = Auth::id();
        $filename = $leadType ? 'junk_leads' : 'leads';

        $whereConditions = $this->buildWhereConditions($filters, $filename, $userId);
        $serviceConditions = $this->buildServiceConditions($filters, $filename, $userId);
        $junkStatus = $this->getJunkLeadStatus(Auth::user()->account_id);
        $junkStatusId = $junkStatus->id ?? 0;
        $userCities = ACL::getUserCities();
        $statusIds = array_values(array_filter(array_map('intval', $statusIds)));

        $countQuery = $this->buildCountQuery($whereConditions, $serviceConditions, $leadType, $junkStatusId, $userCities)
            ->whereIn('leads.lead_status_id', $statusIds);

        $resultQuery = $this->buildOptimizedResultQuery($whereConditions, $serviceConditions, $leadType, $junkStatusId, $userCities)
            ->whereIn('leads.lead_status_id', $statusIds);

        return [
            'total' => (int) $countQuery->count(),
            'query' => $resultQuery,
        ];
    }

    /**
     * @return array<int, int>
     */
    protected function kanbanStatusCountMap(array $filters, ?string $leadType): array
    {
        $userId = Auth::id();
        $filename = $leadType ? 'junk_leads' : 'leads';

        $whereConditions = $this->buildWhereConditions($filters, $filename, $userId);
        $serviceConditions = $this->buildServiceConditions($filters, $filename, $userId);
        $junkStatus = $this->getJunkLeadStatus(Auth::user()->account_id);
        $junkStatusId = $junkStatus->id ?? 0;
        $userCities = ACL::getUserCities();

        $query = $this->buildCountQuery($whereConditions, $serviceConditions, $leadType, $junkStatusId, $userCities);
        $query->getQuery()->columns = null;
        $rows = $query
            ->selectRaw('leads.lead_status_id, COUNT(leads.id) as total')
            ->groupBy('leads.lead_status_id')
            ->get();

        $map = [];
        foreach ($rows as $row) {
            $map[(int) $row->lead_status_id] = (int) $row->total;
        }

        return $map;
    }

    /**
     * Build lightweight count query (no eager loading)
     */
    protected function buildCountQuery(array $where, array $whereService, ?string $leadType, int $junkStatusId, array $userCities): \Illuminate\Database\Eloquent\Builder
    {
        $query = Leads::where(function ($q) use ($userCities) {
            $q->whereIn('leads.city_id', $userCities)
              ->orWhereNull('leads.city_id');
        });

        if (!empty($where)) {
            $query->where($where);
        }

        // Use EXISTS subquery for service filter (faster than whereHas for counts)
        if (!empty($whereService)) {
            $query->whereExists(function ($subquery) use ($whereService) {
                $subquery->select(DB::raw(1))
                    ->from('leads_services')
                    ->whereColumn('leads_services.lead_id', 'leads.id')
                    ->where('leads_services.status', 1)
                    ->where($whereService);
            });
        }

        // Filter by junk status
        if ($leadType) {
            $query->where('leads.lead_status_id', $junkStatusId);
        } else {
            $query->where('leads.lead_status_id', '!=', $junkStatusId);
        }

        return $query;
    }

    /**
     * Build optimized result query with selective eager loading
     */
    protected function buildOptimizedResultQuery(array $where, array $whereService, ?string $leadType, int $junkStatusId, array $userCities): \Illuminate\Database\Eloquent\Builder
    {
        // Only eager load what's needed, with minimal columns
        $query = Leads::with([
            'lead_service' => function ($q) {
                $q->select('id', 'lead_id', 'service_id', 'child_service_id', 'status')
                  ->with([
                      'service:id,name',
                      'childservice:id,name',
                  ]);
            },
            'city:id,name',
            'towns:id,name',
            'department:id,name',
            'assignedTo:id,name',
        ])->where(function ($q) use ($userCities) {
            $q->whereIn('leads.city_id', $userCities)
              ->orWhereNull('leads.city_id');
        });

        if (!empty($where)) {
            $query->where($where);
        }

        // Use EXISTS for better performance on large datasets
        if (!empty($whereService)) {
            $query->whereExists(function ($subquery) use ($whereService) {
                $subquery->select(DB::raw(1))
                    ->from('leads_services')
                    ->whereColumn('leads_services.lead_id', 'leads.id')
                    ->where('leads_services.status', 1)
                    ->where($whereService);
            });
        }

        // Filter by junk status
        if ($leadType) {
            $query->where('leads.lead_status_id', $junkStatusId);
        } else {
            $query->where('leads.lead_status_id', '!=', $junkStatusId);
        }

        return $query;
    }

    /**
     * Create a new lead
     */
    public function createLead(array $data): Leads
    {
        return DB::transaction(function () use ($data) {
            $accountId = Auth::user()->account_id;
            $userId = Auth::id();

            // Clean phone number
            $data['phone'] = GeneralFunctions::cleanNumber($data['phone']);

            // Check for existing lead
            $existingLead = Leads::where('phone', $data['phone'])
                ->where('account_id', $accountId)
                ->first();

            if ($data['new_lead'] ?? false) {
                if ($existingLead) {
                    throw LeadException::phoneAlreadyExists($data['phone']);
                }
                return $this->createNewLead($data, $accountId, $userId);
            }

            return $this->updateExistingLead($existingLead, $data, $accountId, $userId);
        });
    }

    /**
     * Create new lead record
     */
    protected function createNewLead(array $data, int $accountId, int $userId): Leads
    {
        // Set default lead status if not provided
        if (empty($data['lead_status_id'])) {
            $defaultStatus = $this->getDefaultLeadStatus($accountId);
            $data['lead_status_id'] = $defaultStatus?->id;
        }

        // Set region from city
        if (!empty($data['city_id'])) {
            $data['region_id'] = $this->getRegionFromCity($data['city_id']);
        }

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['converted_by'] = $userId;
        $data['account_id'] = $accountId;
        $data['created_at'] = Carbon::now();

        $lead = Leads::create($data);

        // Create lead service
        $this->createLeadService($lead->id, $data, $accountId);

        // Log activity
        $this->logLeadActivity($lead, $data);

        return $lead;
    }

    /**
     * Update existing lead
     */
    protected function updateExistingLead(?Leads $existingLead, array $data, int $accountId, int $userId): Leads
    {
        $defaultStatus = $this->getDefaultLeadStatus($accountId);

        if ($existingLead) {
            $data['lead_status_id'] = $defaultStatus?->id ?? $existingLead->lead_status_id;
            $data['updated_by'] = $userId;
            $data['updated_at'] = Carbon::now();

            $existingLead->update($data);
            $lead = $existingLead;
        } else {
            $data['lead_status_id'] = $defaultStatus?->id;
            $lead = $this->createNewLead($data, $accountId, $userId);
        }

        // Create new lead service entry
        $this->createLeadService($lead->id, $data, $accountId);

        // Log activity
        $this->logLeadActivity($lead, $data);

        return $lead;
    }

    /**
     * Log lead activity
     */
    protected function logLeadActivity(Leads $lead, array $data): void
    {
        $location = null;
        $service = null;

        // Get location if available
        if (isset($data['location_id'])) {
            $location = Locations::with('city')->find($data['location_id']);
        } elseif ($lead->location_id) {
            $location = Locations::with('city')->find($lead->location_id);
        }

        // Get service if available
        if (isset($data['service_id'])) {
            $service = Services::find($data['service_id']);
        } elseif ($lead->service_id) {
            $service = Services::find($lead->service_id);
        }

        // Log the lead creation activity
        ActivityLogger::logLeadCreated($lead, $location, $service);
    }

    /**
     * Update lead
     */
    public function updateLead($id, array $data): Leads
    {
        return DB::transaction(function () use ($id, $data) {
            $lead = Leads::findOrFail($id);

            // Check if status change is allowed
            if (isset($data['lead_status_id']) && $data['lead_status_id'] != $lead->lead_status_id) {
                $this->validateStatusChange($lead);
            }

            // Handle service updates
            if (!empty($data['service_id'])) {
                $this->updateLeadServices($id, $data);
            }

            $data['updated_at'] = Carbon::now();
            $data['updated_by'] = Auth::id();
            $data['account_id'] = Auth::user()->account_id;

            $before = $lead->replicate();
            $before->setRelation('city', $lead->city()->first());
            $before->setRelation('towns', $lead->towns()->first());
            $before->setRelation('lead_status', $lead->lead_status()->first());
            if (Schema::hasColumn('leads', 'assigned_to')) {
                $before->setRelation('assignedTo', $lead->assignedTo()->first());
            }
            if (Schema::hasColumn('leads', 'department_id')) {
                $before->setRelation('department', $lead->department()->first());
            }
            $lead->update($data);
            $fresh = $lead->fresh();
            $this->logTrackedLeadChanges($before, $fresh);

            // Update patient name if phone matches
            if (!empty($data['phone']) && !empty($data['name'])) {
                GeneralFunctions::patientNameUpdate($data['phone'], $data['name']);
            }

            return $fresh;
        });
    }

    /**
     * Validate status change is allowed
     */
    protected function validateStatusChange(Leads $lead): void
    {
        if (!$lead->lead_status_id) {
            return;
        }

        $currentStatus = LeadStatuses::find($lead->lead_status_id);
        if ($currentStatus && ($currentStatus->is_arrived || $currentStatus->is_converted)) {
            throw LeadException::statusChangeNotAllowed($currentStatus->name);
        }
    }

    /**
     * Update lead services
     */
    protected function updateLeadServices(int $leadId, array $data): void
    {
        // Delete old services for this service type
        if (!empty($data['old_service'])) {
            LeadsServices::where([
                'lead_id' => $leadId,
                'service_id' => $data['old_service'],
                'consultancy_id' => null,
            ])->delete();
        }

        $childServices = $data['child_service_id'] ?? [];

        if (!empty($childServices)) {
            foreach ($childServices as $childServiceId) {
                $leadService = LeadsServices::updateOrCreate([
                    'lead_id' => $leadId,
                    'service_id' => $data['service_id'],
                    'child_service_id' => $childServiceId,
                    'consultancy_id' => null,
                ], [
                    'status' => 1,
                ]);

                // Deactivate other services
                LeadsServices::where('lead_id', $leadId)
                    ->where('id', '!=', $leadService->id)
                    ->update(['status' => 0]);
            }
        } else {
            $leadService = LeadsServices::updateOrCreate([
                'lead_id' => $leadId,
                'service_id' => $data['service_id'],
                'consultancy_id' => null,
            ], [
                'status' => 1,
            ]);

            LeadsServices::where('lead_id', $leadId)
                ->where('id', '!=', $leadService->id)
                ->update(['status' => 0]);
        }
    }

    /**
     * Delete lead
     */
    public function deleteLead($id): bool
    {
        $lead = Leads::findOrFail($id);
        return $lead->delete();
    }

    /**
     * Bulk delete leads
     */
    public function bulkDelete(array $ids): int
    {
        return Leads::whereIn('id', $ids)->delete();
    }

    /**
     * Update lead status
     */
    public function updateLeadStatus($leadId, array $data): Leads
    {
        return DB::transaction(function () use ($leadId, $data) {
            $lead = Leads::findOrFail($leadId);

            // Validate status change
            $this->validateStatusChange($lead);

            $statusId = $data['lead_status_chalid_id'] ?? $data['lead_status_parent_id'];
            $previousStatus = optional(LeadStatuses::find($lead->lead_status_id))->name ?: '—';

            $lead->update([
                'lead_status_id' => $statusId,
                'converted_by' => Auth::id(),
            ]);

            $fresh = $lead->fresh();
            $newStatus = optional(LeadStatuses::find($statusId))->name ?: '—';
            ActivityLogger::logLeadChange(
                $fresh,
                'Status updated',
                'lead_status_changed',
                $previousStatus,
                $newStatus
            );

            // Add comment if provided
            $comment = $data['comment1'] ?? $data['comment2'] ?? null;
            if ($comment) {
                LeadComments::create([
                    'lead_id' => $leadId,
                    'comment' => $comment,
                    'created_by' => Auth::id(),
                ]);
            }

            return $lead->fresh();
        });
    }

    /**
     * Toggle lead active status
     */
    public function toggleStatus($id, $status): Leads
    {
        $lead = Leads::findOrFail($id);
        $previous = $lead->active ? 'Active' : 'Inactive';
        $lead->update(['active' => $status]);
        $new = $status ? 'Active' : 'Inactive';
        if ($previous !== $new) {
            ActivityLogger::logLeadChange($lead->fresh(), 'Active flag changed', 'lead_active_changed', $previous, $new);
        }
        return $lead;
    }

    /**
     * Get lead detail with all relations
     */
    public function getLeadDetail($id): ?Leads
    {
        return Leads::with([
            'lead_comments.user:id,name',
            'towns:id,name',
            'city:id,name',
            'lead_source:id,name',
            'lead_status:id,name,parent_id',
            'department:id,name',
            'assignedTo:id,name',
            'lead_service.service:id,name',
            'lead_service.childservice:id,name',
        ])->find($id);
    }

    /**
     * Get lead for editing
     */
    public function getLeadForEdit($id): ?Leads
    {
        return Leads::with('lead_service')->where([
            'id' => $id,
            'account_id' => Auth::user()->account_id,
        ])->first();
    }

    /**
     * Create lead service entry
     */
    public function createLeadService($leadId, array $data, $accountId): LeadsServices
    {
        $openStatus = $this->getDefaultLeadStatus($accountId);
        $metaLeadId = !empty($data['meta_lead_id']) ? trim($data['meta_lead_id']) : null;

        $leadService = LeadsServices::create([
            'lead_id' => $leadId,
            'service_id' => $data['service_id'] ?? null,
            'child_service_id' => $data['child_service_id'] ?? null,
            'meta_lead_id' => $metaLeadId,
            'status' => 1,
            'lead_status_id' => $openStatus?->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Deactivate previous services
        LeadsServices::where('lead_id', $leadId)
            ->where('id', '!=', $leadService->id)
            ->update(['status' => 0]);

        return $leadService;
    }

    /**
     * Import leads from file (OPTIMIZED - Batch Processing)
     * Uses bulk inserts/updates to minimize database queries
     */
    public function importLeads(array $rows, array $options): array
    {
        $accountId = Auth::user()->account_id;
        $userId = Auth::id();
        $now = Carbon::now();

        // Pre-load all lookup data
        $lookupData = $this->loadImportLookupData($accountId);
        $defaultStatusId = $this->getDefaultLeadStatus($accountId)?->id;

        $stats = [
            'created' => 0,
            'updated' => 0,
            'invalid_phones' => [],
            'invalid_services' => [],
        ];

        // Step 1: Extract and validate all phones upfront
        $validRows = [];
        $phoneToRowMap = [];
        
        foreach ($rows as $row) {
            $phone = GeneralFunctions::cleanNumber($row['phone'] ?? '');
            
            if (strlen($phone) < 10 || strlen($phone) > 12) {
                $stats['invalid_phones'][] = $row['phone'] ?? '';
                continue;
            }

            // Validate service
            $serviceKey = strtolower(trim($row['service'] ?? ''));
            $serviceData = $lookupData['services'][$serviceKey] ?? null;
            
            if (!$serviceData && !empty($serviceKey)) {
                if (!in_array($row['service'], $stats['invalid_services'])) {
                    $stats['invalid_services'][] = $row['service'];
                }
                continue;
            }

            if (!$serviceData) {
                continue; // Skip rows without service
            }

            $row['_phone_clean'] = $phone;
            $row['_service_id'] = $serviceData['id'];
            $row['_child_service_id'] = null;
            
            if (!empty($row['treatment'])) {
                $treatmentKey = strtolower(trim($row['treatment']));
                $row['_child_service_id'] = $lookupData['child_services'][$serviceData['id']][$treatmentKey] ?? null;
            }

            $validRows[] = $row;
            $phoneToRowMap[$phone] = $row;
        }

        if (empty($validRows)) {
            return $stats;
        }

        // Step 2: Get all existing leads in ONE query
        $allPhones = array_keys($phoneToRowMap);
        $existingLeads = Leads::whereIn('phone', $allPhones)
            ->where('account_id', $accountId)
            ->get()
            ->keyBy('phone');

        // Step 3: Separate into creates and updates
        $leadsToCreate = [];
        $leadsToUpdate = [];
        $phoneToLeadId = [];

        foreach ($validRows as $row) {
            $phone = $row['_phone_clean'];
            $cityKey = strtolower(trim($row['city'] ?? ''));
            $leadStatusKey = strtolower(trim($row['lead_status'] ?? ''));
            $leadStatusId = $lookupData['lead_statuses'][$leadStatusKey] ?? $defaultStatusId;

            $leadData = [
                'name' => $row['full_name'] ?? '',
                'email' => $row['email'] ?? null,
                'phone' => $phone,
                'gender' => $this->parseGender($row['gender'] ?? ''),
                'city_id' => $lookupData['cities'][$cityKey] ?? null,
                'region_id' => $lookupData['regions'][$cityKey] ?? null,
                'lead_source_id' => $lookupData['lead_sources'][strtolower(trim($row['lead_source'] ?? ''))] ?? Config::get('constants.lead_source_social_media'),
                'location_id' => $lookupData['locations'][strtolower(trim($row['centre'] ?? ''))] ?? null,
                'meta_lead_id' => !empty($row['meta_lead_id']) ? trim($row['meta_lead_id']) : null,
                'account_id' => $accountId,
                'updated_by' => $userId,
                'converted_by' => $userId,
                'updated_at' => $now,
                'active' => 1,
            ];

            if (isset($existingLeads[$phone])) {
                // Update existing
                $existingLead = $existingLeads[$phone];
                $phoneToLeadId[$phone] = $existingLead->id;
                
                if ($options['update_records']) {
                    if (!$options['skip_lead_statuses']) {
                        $leadData['lead_status_id'] = $leadStatusId;
                    }
                    $leadsToUpdate[$existingLead->id] = $leadData;
                    $stats['updated']++;
                } else {
                    // Only update minimal fields
                    $leadsToUpdate[$existingLead->id] = [
                        'updated_by' => $userId,
                        'converted_by' => $userId,
                        'updated_at' => $now,
                        'location_id' => $leadData['location_id'],
                        'meta_lead_id' => $leadData['meta_lead_id'],
                    ];
                    if (!$options['skip_lead_statuses']) {
                        $leadsToUpdate[$existingLead->id]['lead_status_id'] = $leadStatusId;
                    }
                    $stats['updated']++;
                }
            } else {
                // Create new
                $leadData['lead_status_id'] = $leadStatusId;
                $leadData['created_by'] = $userId;
                $leadData['created_at'] = $now;
                $leadsToCreate[$phone] = $leadData;
                $stats['created']++;
            }
        }

        // Pre-load services and locations for activity logging
        $servicesLookup = Services::whereIn('id', array_unique(array_column($validRows, '_service_id')))
            ->pluck('name', 'id')
            ->toArray();
        $locationsLookup = Locations::with('city')
            ->whereIn('id', array_filter(array_unique(array_map(fn($r) => $lookupData['locations'][strtolower(trim($r['centre'] ?? ''))] ?? null, $validRows))))
            ->get()
            ->keyBy('id');

        // Step 4: Execute in transaction with batch operations
        return DB::transaction(function () use ($leadsToCreate, $leadsToUpdate, $validRows, $phoneToLeadId, $defaultStatusId, $now, $stats, $accountId, $userId, $servicesLookup, $locationsLookup, $lookupData) {
            
            // Batch insert new leads
            if (!empty($leadsToCreate)) {
                // Insert in chunks of 500
                foreach (array_chunk($leadsToCreate, 500, true) as $chunk) {
                    Leads::insert(array_values($chunk));
                }
                
                // Get IDs of newly created leads
                $newPhones = array_keys($leadsToCreate);
                $newLeads = Leads::whereIn('phone', $newPhones)->pluck('id', 'phone');
                foreach ($newLeads as $phone => $id) {
                    $phoneToLeadId[$phone] = $id;
                }
            }

            // Batch update existing leads
            if (!empty($leadsToUpdate)) {
                foreach ($leadsToUpdate as $leadId => $data) {
                    Leads::where('id', $leadId)->update($data);
                }
            }

            // Step 5: Batch create lead services
            $leadServicesToCreate = [];
            $leadIdsToDeactivate = [];

            foreach ($validRows as $row) {
                $phone = $row['_phone_clean'];
                $leadId = $phoneToLeadId[$phone] ?? null;
                
                if (!$leadId) continue;

                $leadIdsToDeactivate[] = $leadId;
                $leadServicesToCreate[] = [
                    'lead_id' => $leadId,
                    'service_id' => $row['_service_id'],
                    'child_service_id' => $row['_child_service_id'],
                    'meta_lead_id' => !empty($row['meta_lead_id']) ? trim($row['meta_lead_id']) : null,
                    'status' => 1,
                    'lead_status_id' => $defaultStatusId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Deactivate old services in ONE query
            if (!empty($leadIdsToDeactivate)) {
                LeadsServices::whereIn('lead_id', $leadIdsToDeactivate)
                    ->update(['status' => 0]);
            }

            // Insert new services in chunks
            if (!empty($leadServicesToCreate)) {
                foreach (array_chunk($leadServicesToCreate, 500) as $chunk) {
                    LeadsServices::insert($chunk);
                }
            }

            // Step 6: Batch create activity logs
            $activitiesToCreate = [];
            $creatorName = Auth::user()->name ?? 'System';

            foreach ($validRows as $row) {
                $phone = $row['_phone_clean'];
                $leadId = $phoneToLeadId[$phone] ?? null;
                
                if (!$leadId) continue;

                $serviceName = $servicesLookup[$row['_service_id']] ?? '';
                $patientName = $row['full_name'] ?? 'Unknown';
                $locationId = $lookupData['locations'][strtolower(trim($row['centre'] ?? ''))] ?? null;
                $locationName = '';
                
                if ($locationId && isset($locationsLookup[$locationId])) {
                    $loc = $locationsLookup[$locationId];
                    $locationName = ($loc->city->name ?? '') . '-' . ($loc->name ?? '');
                }

                $description = '<span class="highlight">' . $creatorName . '</span> created a <span class="highlight-orange">' . ($serviceName ?: 'Service') . '</span> lead for <span class="highlight-orange">' . $patientName . '</span>' . ($locationName ? ' in <span class="highlight">' . $locationName . '</span>' : '');

                $sourceName = $row['lead_source'] ?? '—';
                $statusName = $row['lead_status'] ?? 'Open';
                $activityRow = [
                    'account_id' => $accountId,
                    'action' => 'Lead created',
                    'activity_type' => 'lead_created',
                    'description' => $description,
                    'patient' => $patientName,
                    'patient_id' => null,
                    'lead_id' => $leadId,
                    'lead_status' => $statusName ?: 'Open',
                    'lead_status_id' => $defaultStatusId,
                    'service' => $serviceName,
                    'service_id' => $row['_service_id'],
                    'location' => $locationName,
                    'centre_id' => $locationId,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('activities', 'previous_state')) {
                    $activityRow['previous_state'] = $sourceName ?: '—';
                    $activityRow['new_state'] = $statusName ?: 'Open';
                }
                $activitiesToCreate[] = $activityRow;
            }

            // Insert activities in chunks
            if (!empty($activitiesToCreate)) {
                foreach (array_chunk($activitiesToCreate, 500) as $chunk) {
                    \App\Models\Activity::insert($chunk);
                }
            }

            return $stats;
        });
    }

    /**
     * Load all lookup data for import
     */
    protected function loadImportLookupData(int $accountId): array
    {
        return Cache::remember("lead_import_lookup_{$accountId}", 300, function () use ($accountId) {
            $cities = Cities::where('account_id', $accountId)->get();
            $citiesCache = [];
            $regionsCache = [];
            foreach ($cities as $city) {
                $key = strtolower(trim($city->name));
                $citiesCache[$key] = $city->id;
                $regionsCache[$key] = $city->region_id;
            }

            $leadSources = LeadSources::where('account_id', $accountId)
                ->pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
                ->toArray();

            $leadStatuses = LeadStatuses::where('account_id', $accountId)
                ->pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
                ->toArray();

            $services = Services::where('account_id', $accountId)->get();
            $servicesCache = [];
            $childServicesCache = [];
            foreach ($services as $service) {
                $key = strtolower(trim($service->name));
                $servicesCache[$key] = [
                    'id' => $service->id,
                    'parent_id' => $service->parent_id,
                ];
                if ($service->parent_id) {
                    $childServicesCache[$service->parent_id][$key] = $service->id;
                }
            }

            $locations = Locations::where('account_id', $accountId)
                ->pluck('id', 'name')
                ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
                ->toArray();

            return [
                'cities' => $citiesCache,
                'regions' => $regionsCache,
                'lead_sources' => $leadSources,
                'lead_statuses' => $leadStatuses,
                'services' => $servicesCache,
                'child_services' => $childServicesCache,
                'locations' => $locations,
            ];
        });
    }

    /**
     * Parse gender from string
     */
    protected function parseGender(string $gender): int
    {
        $gender = strtolower(trim($gender));
        return $gender === 'female' ? 2 : 1;
    }

    /**
     * Get cached lookup data for forms
     */
    public function getFormLookupData(): array
    {
        $accountId = Auth::user()->account_id;

        return Cache::remember("lead_form_lookup_{$accountId}", $this->cacheTtl, function () use ($accountId) {
            return [
                'cities' => Cities::getActiveSortedFeatured(ACL::getUserCities()),
                'lead_sources' => LeadSources::getActiveSorted(),
                'lead_statuses' => LeadStatuses::getLeadStatuses(),
                'services' => Services::where([
                    'slug' => 'custom',
                    'parent_id' => 0,
                    'active' => 1,
                ])->pluck('name', 'id'),
                'gender' => Config::get('constants.gender_array'),
            ];
        });
    }

    /**
     * Get filter data for datatable
     */
    public function getFilterData(string $filename): array
    {
        $accountId = Auth::user()->account_id;
        $userId = Auth::id();

        $cacheKey = "lead_filters_{$accountId}";

        $filterValues = Cache::remember($cacheKey, $this->cacheTtl, function () use ($accountId) {
            $junkStatus = $this->getJunkLeadStatus($accountId);

            return [
                'cities' => Cities::getActiveSortedFeatured(ACL::getUserCities()),
                'locations' => Locations::getActiveRecordsByCity('', ACL::getUserCentres(), $accountId)->pluck('name', 'id'),
                'regions' => \App\Models\Regions::getActiveSorted(ACL::getUserRegions()),
                'users' => \App\Models\User::getAllActiveRecords($accountId)->pluck('name', 'id'),
                'lead_statuses' => $junkStatus 
                    ? LeadStatuses::getLeadStatuses($junkStatus->id)
                    : LeadStatuses::getLeadStatuses(),
                'Services' => Services::where([
                    'slug' => 'custom',
                    'parent_id' => 0,
                    'active' => 1,
                ])->pluck('name', 'id'),
            ];
        });

        $activeFilters = Filters::all($userId, $filename);
        $junkStatus = $this->getJunkLeadStatus($accountId);
        $filterValues['lead_statuses'] = $junkStatus
            ? LeadStatuses::getLeadStatuses($junkStatus->id)
            : LeadStatuses::getLeadStatuses();
        $filterValues['leadServices'] = Filters::get($userId, 'leads', 'service_id');
        $filterValues['departments'] = $this->departmentsLookup($accountId);
        $filterValues['csr_users'] = $this->getCsrUsers($accountId);

        return [
            'filter_values' => $filterValues,
            'active_filters' => $activeFilters,
        ];
    }

    /**
     * Get default lead status
     */
    public function getDefaultLeadStatus(int $accountId): ?LeadStatuses
    {
        return Cache::remember("default_lead_status_{$accountId}", $this->cacheTtl, function () use ($accountId) {
            return LeadStatuses::where([
                'account_id' => $accountId,
                'is_default' => 1,
            ])->first();
        });
    }

    /**
     * Get junk lead status
     */
    public function getJunkLeadStatus(int $accountId): ?LeadStatuses
    {
        return Cache::remember("junk_lead_status_{$accountId}", $this->cacheTtl, function () use ($accountId) {
            return LeadStatuses::where([
                'account_id' => $accountId,
                'is_junk' => 1,
            ])->first();
        });
    }

    /**
     * Get converted lead status
     */
    public function getConvertedLeadStatus(int $accountId): ?LeadStatuses
    {
        return Cache::remember("converted_lead_status_{$accountId}", $this->cacheTtl, function () use ($accountId) {
            return LeadStatuses::where([
                'account_id' => $accountId,
                'is_converted' => 1,
            ])->first();
        });
    }

    /**
     * Get region from city
     */
    protected function getRegionFromCity(int $cityId): ?int
    {
        $city = Cities::find($cityId);
        return $city?->region_id;
    }

    /**
     * Build where conditions from filters
     */
    protected function buildWhereConditions(array $filters, string $filename, int $userId): array
    {
        $where = [];
        $applyFilter = checkFilters($filters, $filename);

        $filterMappings = [
            'lead_id' => ['id', '='],
            'name' => ['name', 'like', '%', '%'],
            'phone' => ['phone', 'like', '%', '%', true],
            'city_id' => ['city_id', '='],
            'location_id' => ['leads.location_id', '='],
            'gender_id' => ['gender', '='],
            'region_id' => ['region_id', '='],
            'lead_status_id' => ['lead_status_id', '='],
            'created_by' => ['leads.created_by', '='],
            'department_id' => ['leads.department_id', '='],
            'assigned_to' => ['leads.assigned_to', '='],
        ];

        foreach ($filterMappings as $filterKey => $mapping) {
            $where = $this->applyFilter($where, $filters, $filterKey, $mapping, $filename, $userId, $applyFilter);
        }

        // Handle date range
        if (hasFilter($filters, 'created_at')) {
            $dateRange = explode(' - ', $filters['created_at']);
            $startDate = date('Y-m-d H:i:s', strtotime($dateRange[0]));
            $endDate = (new \DateTime($dateRange[1]))->setTime(23, 59, 0)->format('Y-m-d H:i:s');
            $where[] = ['leads.created_at', '>=', $startDate];
            $where[] = ['leads.created_at', '<=', $endDate];
            Filters::put($userId, $filename, 'created_at', $filters['created_at']);
        } elseif ($applyFilter) {
            Filters::forget($userId, $filename, 'created_at');
        }

        return $where;
    }

    /**
     * Apply single filter
     */
    protected function applyFilter(array $where, array $filters, string $key, array $mapping, string $filename, int $userId, bool $applyFilter): array
    {
        $column = $mapping[0];
        $operator = $mapping[1];
        $prefix = $mapping[2] ?? '';
        $suffix = $mapping[3] ?? '';
        $cleanNumber = $mapping[4] ?? false;

        if (hasFilter($filters, $key)) {
            $value = $cleanNumber ? GeneralFunctions::cleanNumber($filters[$key]) : $filters[$key];
            $where[] = [$column, $operator, $prefix . $value . $suffix];
            Filters::put($userId, $filename, $key, $value);
        } elseif ($applyFilter) {
            Filters::forget($userId, $filename, $key);
        } elseif (Filters::get($userId, $filename, $key)) {
            $value = Filters::get($userId, $filename, $key);
            if ($cleanNumber) {
                $value = GeneralFunctions::cleanNumber($value);
            }
            $where[] = [$column, $operator, $prefix . $value . $suffix];
        }

        return $where;
    }

    /**
     * Build service conditions from filters
     */
    protected function buildServiceConditions(array $filters, string $filename, int $userId): array
    {
        $where = [];
        $applyFilter = checkFilters($filters, $filename);

        if (hasFilter($filters, 'service_id')) {
            $where[] = ['service_id', '=', $filters['service_id']];
            Filters::put($userId, $filename, 'service_id', $filters['service_id']);
        } elseif ($applyFilter) {
            Filters::forget($userId, $filename, 'service_id');
        } elseif (Filters::get($userId, $filename, 'service_id')) {
            $where[] = ['service_id', '=', Filters::get($userId, $filename, 'service_id')];
        }

        return $where;
    }

    /**
     * Get order parameters
     */
    protected function getOrderParams(array $filters, string $filename, int $userId): array
    {
        if (isset($filters['sort'])) {
            [$orderBy, $order] = getSortBy(['sort' => $filters['sort']], 'leads.created_at', 'DESC');
            Filters::put($userId, $filename, 'order_by', $orderBy);
            Filters::put($userId, $filename, 'order', $order);
        } else {
            $orderBy = Filters::get($userId, $filename, 'order_by') ?: 'leads.created_at';
            $order = Filters::get($userId, $filename, 'order') ?: 'desc';

            if ($orderBy === 'created_at') {
                $orderBy = 'leads.created_at';
            }

            Filters::put($userId, $filename, 'order_by', $orderBy);
            Filters::put($userId, $filename, 'order', $order);
        }

        return [$orderBy, $order];
    }

    /**
     * Clear lead-related caches
     */
    public function clearCache(): void
    {
        $accountId = Auth::user()->account_id;
        Cache::forget("lead_form_lookup_{$accountId}");
        Cache::forget("lead_filters_{$accountId}");
        Cache::forget("default_lead_status_{$accountId}");
        Cache::forget("junk_lead_status_{$accountId}");
        Cache::forget("converted_lead_status_{$accountId}");
        Cache::forget("lead_import_lookup_{$accountId}");
    }

    /**
     * Get child services for a parent service
     */
    public function getChildServices($serviceId): \Illuminate\Support\Collection
    {
        return Services::where([
            'parent_id' => $serviceId,
            'active' => 1,
        ])->pluck('name', 'id');
    }

    /**
     * Add comment to lead
     */
    public function addComment($leadId, string $comment): LeadComments
    {
        $row = LeadComments::create([
            'lead_id' => $leadId,
            'comment' => $comment,
            'created_by' => Auth::id(),
        ]);

        $lead = Leads::find($leadId);
        if ($lead) {
            ActivityLogger::logLeadChange($lead, 'Comment added', 'lead_commented', '—', $comment);
        }

        return $row;
    }

    protected function logTrackedLeadChanges(Leads $before, Leads $after): void
    {
        $after->loadMissing(['city:id,name', 'towns:id,name', 'lead_status:id,name', 'assignedTo:id,name', 'department:id,name']);

        if ((int) $before->lead_status_id !== (int) $after->lead_status_id) {
            ActivityLogger::logLeadChange(
                $after,
                'Status updated',
                'lead_status_changed',
                $before->lead_status->name ?? '—',
                $after->lead_status->name ?? '—'
            );
        }

        if ((int) $before->city_id !== (int) $after->city_id) {
            ActivityLogger::logLeadChange(
                $after,
                'City updated',
                'lead_city_changed',
                $before->city->name ?? '—',
                $after->city->name ?? '—'
            );
        }

        if ((int) $before->location_id !== (int) $after->location_id) {
            ActivityLogger::logLeadChange(
                $after,
                'Location updated',
                'lead_location_changed',
                $before->towns->name ?? '—',
                $after->towns->name ?? '—'
            );
        }

        if (Schema::hasColumn('leads', 'assigned_to') && (int) ($before->assigned_to ?? 0) !== (int) ($after->assigned_to ?? 0)) {
            ActivityLogger::logLeadChange(
                $after,
                'Assignee changed',
                'lead_assignee_changed',
                $before->assignedTo->name ?? '—',
                $after->assignedTo->name ?? '—'
            );
        }
    }

    public function assignLead(int $leadId, int $userId): Leads
    {
        if (! $this->isCsrUser($userId)) {
            throw new LeadException('Selected user is not a CSR.');
        }

        $lead = Leads::findOrFail($leadId);
        $previous = optional($lead->assignedTo)->name ?: '—';
        $lead->update([
            'assigned_to' => $userId,
            'updated_by' => Auth::id(),
            'updated_at' => Carbon::now(),
        ]);
        $fresh = $lead->fresh(['assignedTo:id,name']);
        $new = optional($fresh->assignedTo)->name ?: '—';
        if ($previous !== $new) {
            ActivityLogger::logLeadChange($fresh, 'Assignee changed', 'lead_assignee_changed', $previous, $new);
        }

        return $fresh;
    }

    public function getCsrUsers(?int $accountId = null): array
    {
        $accountId = $accountId ?? Auth::user()->account_id;
        $userIds = $this->csrUserIds();
        if ($userIds === []) {
            return [];
        }

        return User::query()
            ->where('account_id', $accountId)
            ->where('active', 1)
            ->whereIn('id', $userIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function isCsrUser(int $userId): bool
    {
        return in_array($userId, $this->csrUserIds(), true);
    }

    protected function csrUserIds(): array
    {
        $roleIds = DB::table('roles')->whereIn('name', ['CSR'])->pluck('id');
        if ($roleIds->isEmpty()) {
            return [];
        }

        $userIds = RoleHasUsers::withTrashed()->whereIn('role_id', $roleIds)->pluck('user_id');
        if (Schema::hasTable('model_has_roles')) {
            $userIds = $userIds->merge(
                DB::table('model_has_roles')->whereIn('role_id', $roleIds)->pluck('model_id')
            );
        }

        return $userIds->filter()->unique()->map(fn ($id) => (int) $id)->values()->all();
    }

    public function getDepartmentsForLocation(?int $locationId = null, ?int $accountId = null)
    {
        $accountId = $accountId ?? Auth::user()->account_id;
        $query = LeadDepartment::forAccount($accountId)->where('active', 1)->orderBy('sort_order')->orderBy('name');

        if ($locationId) {
            $forLocation = (clone $query)->whereHas('locations', function ($q) use ($locationId) {
                $q->where('locations.id', $locationId);
            })->get();
            if ($forLocation->isNotEmpty()) {
                return $forLocation;
            }
        }

        return $query->get();
    }

    public function departmentsLookup(?int $accountId = null): array
    {
        return LeadDepartment::getActiveForAccount($accountId)->pluck('name', 'id')->toArray();
    }

    public function getLeadActivities(int $leadId): array
    {
        $lead = Leads::with(['lead_source:id,name', 'lead_status:id,name'])->find($leadId);

        $rows = Activity::query()
            ->with(['user:id,name'])
            ->where('lead_id', $leadId)
            ->whereIn('activity_type', [
                'lead_created',
                'lead_status_changed',
                'lead_booked',
                'lead_arrived',
                'lead_converted',
                'lead_removed_from_junk',
                'lead_commented',
                'lead_assignee_changed',
                'lead_city_changed',
                'lead_location_changed',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $items[] = $this->formatLeadActivity($row, $lead);
        }

        return $items;
    }

    protected function formatLeadActivity(Activity $row, ?Leads $lead = null): array
    {
        $actor = $row->user->name ?? 'System';
        $previous = $row->previous_state ?? null;
        $new = $row->new_state ?? null;
        $type = $row->activity_type ?: 'lead_activity';
        $at = $row->created_at ? Carbon::parse($row->created_at)->format('M j, Y g:i A') : '—';

        if (($previous === null || $previous === '') && ($new === null || $new === '')) {
            [$previous, $new] = $this->inferredLeadActivityStates($row);
        }

        $previous = $previous ?: '—';
        $new = $new ?: '—';
        $action = $this->leadActivityTitle($type, $row->action);
        $fields = $this->leadActivityFields($type, $at, $actor, $previous, $new, $row, $lead);

        return [
            'id' => $row->id,
            'type' => $type,
            'action' => $action,
            'fields' => $fields,
            'actor' => $actor,
            'at' => $at,
            'at_raw' => $row->created_at,
        ];
    }

    protected function leadActivityTitle(string $type, ?string $action): string
    {
        switch ($type) {
            case 'lead_created':
                return 'Lead created';
            case 'lead_status_changed':
            case 'lead_booked':
            case 'lead_arrived':
            case 'lead_converted':
            case 'lead_removed_from_junk':
                return 'Status updated';
            case 'lead_commented':
                return 'Comment added';
            case 'lead_assignee_changed':
                return 'Assignee changed';
            case 'lead_city_changed':
                return 'City updated';
            case 'lead_location_changed':
                return 'Location updated';
            default:
                return $action ?: 'Activity';
        }
    }

    protected function leadActivityFields(
        string $type,
        string $at,
        string $actor,
        string $previous,
        string $new,
        Activity $row,
        ?Leads $lead = null
    ): array {
        if ($type === 'lead_created') {
            $source = ($previous !== '—' && $previous !== '' && !str_starts_with($previous, 'New lead'))
                ? $previous
                : ($lead->lead_source->name ?? '—');
            $status = $row->lead_status ?: ($new !== '—' && !str_starts_with((string) $new, 'New lead') ? $new : ($lead->lead_status->name ?? 'Open'));

            return [
                ['label' => 'Date & time', 'value' => $at],
                ['label' => 'Lead source', 'value' => $source ?: '—'],
                ['label' => 'Lead status', 'value' => $status ?: '—'],
                ['label' => 'Created by', 'value' => $actor],
            ];
        }

        if (in_array($type, ['lead_status_changed', 'lead_booked', 'lead_arrived', 'lead_converted', 'lead_removed_from_junk'], true)) {
            $fields = [
                ['label' => 'Date & time', 'value' => $at],
                ['label' => 'Previous status', 'value' => $previous],
                ['label' => 'New status', 'value' => $new],
                ['label' => 'Updated by', 'value' => $actor],
            ];
            if ($type === 'lead_booked') {
                $fields[] = ['label' => 'Via', 'value' => 'Appointment booked'];
            }

            return $fields;
        }

        if ($type === 'lead_commented') {
            $comment = $new !== '—' ? $new : trim(html_entity_decode(strip_tags((string) $row->description)));

            return [
                ['label' => 'Date & time', 'value' => $at],
                ['label' => 'Comment', 'value' => $comment ?: '—'],
                ['label' => 'Commented by', 'value' => $actor],
            ];
        }

        if ($type === 'lead_assignee_changed') {
            return [
                ['label' => 'Date & time', 'value' => $at],
                ['label' => 'Previous assignee', 'value' => $previous],
                ['label' => 'New assignee', 'value' => $new],
                ['label' => 'Updated by', 'value' => $actor],
            ];
        }

        $subject = $type === 'lead_location_changed' ? 'location' : 'city';

        return [
            ['label' => 'Date & time', 'value' => $at],
            ['label' => 'Previous '.$subject, 'value' => $previous],
            ['label' => 'New '.$subject, 'value' => $new],
            ['label' => 'Updated by', 'value' => $actor],
        ];
    }

    protected function inferredLeadActivityStates(Activity $row): array
    {
        $type = $row->activity_type ?? '';
        $status = $row->lead_status ?: null;

        switch ($type) {
            case 'lead_created':
                return ['—', $status ?: 'New lead'];
            case 'lead_booked':
                return ['Open', 'Booked'];
            case 'lead_arrived':
                return ['Booked', 'Arrived'];
            case 'lead_converted':
                return ['Arrived', 'Converted'];
            case 'lead_removed_from_junk':
                return ['Junk', $status ?: 'Open'];
            case 'lead_commented':
                return ['—', trim(html_entity_decode(strip_tags((string) $row->description))) ?: 'Comment added'];
            default:
                return ['—', $status ?: '—'];
        }
    }

    /**
     * Get lead statuses with children
     */
    public function getLeadStatusesWithChildren($leadId): array
    {
        $lead = Leads::find($leadId);
        if (!$lead) {
            throw LeadException::notFound($leadId);
        }

        $leadStatus = LeadStatuses::find($lead->lead_status_id);
        $parentStatuses = LeadStatuses::getLeadStatuses();
        $comments = LeadComments::where('lead_id', $leadId)->get();

        if ($leadStatus->parent_id == 0) {
            $parentStatus = $leadStatus;
            $childStatus = null;
        } else {
            $childStatus = $leadStatus;
            $parentStatus = LeadStatuses::find($leadStatus->parent_id);
        }

        $childStatuses = LeadStatuses::where('parent_id', $parentStatus->id)->get();

        return [
            'lead' => $lead,
            'lead_statuses_Pdata' => $parentStatuses,
            'lead_statuses_Cdata' => $childStatuses->isEmpty() ? 'nothing' : $childStatuses,
            'lead_status_parent' => $parentStatus,
            'lead_status_chalid' => $childStatus ?? 'null',
            'lead_status_comment' => $comments,
        ];
    }

    // =========================================================================
    // BUSINESS LOGIC MOVED FROM LEADS MODEL
    // =========================================================================

    /**
     * Search leads by phone (optimized)
     * Moved from Leads::getLeadPhoneAjax
     */
    public function searchLeadsByPhone(string $phone, int $accountId): Collection
    {
        return Leads::where([
            ['active', '=', 1],
            ['account_id', '=', $accountId],
            ['phone', 'LIKE', "%{$phone}%"],
        ])
        ->select('name', 'id', 'phone')
        ->limit(50)
        ->get();
    }

    /**
     * Search leads by ID or name (optimized)
     * Moved from Leads::getLeadidAjax
     */
    public function searchLeadsById(string $search, int $accountId): Collection
    {
        // First try exact ID match
        if (is_numeric($search)) {
            $leads = Leads::where([
                'active' => 1,
                'account_id' => $accountId,
                'id' => $search,
            ])->select('name', 'id', 'phone')->get();

            if ($leads->isNotEmpty()) {
                return $leads;
            }
        }

        // Search by name or phone
        $searchTerm = GeneralFunctions::patientSearch($search);
        $phoneNumeric = GeneralFunctions::clearnString($search);

        $query = Leads::where(['active' => 1, 'account_id' => $accountId]);

        if (is_numeric($phoneNumeric)) {
            $phone = GeneralFunctions::cleanNumber($search);
            $query->where('phone', 'LIKE', "%{$phone}%");
        } else {
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        return $query->select('name', 'id', 'phone')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->unique('phone');
    }

    /**
     * Search leads by ID or name (alias for searchLeadsById)
     * Used by API endpoints for lead search functionality
     */
    public function searchLeads(string $search, int $accountId): Collection
    {
        return $this->searchLeadsById($search, $accountId);
    }

    /**
     * Prepare SMS content for delivery
     * Moved from Leads::prepareSMSContent
     */
    public function prepareSMSContent($leadId, string $smsContent): string
    {
        if (!$leadId) {
            return $smsContent;
        }

        // Load global setting for head office
        $setting = Settings::find(5);
        if ($setting) {
            $smsContent = str_replace('##head_office_phone##', $setting->data, $smsContent);
        }

        $lead = Leads::with(['city', 'lead_source', 'lead_status'])->find($leadId);
        if (!$lead) {
            return $smsContent;
        }

        $patient = Patients::find($lead->patient_id);
        if ($patient) {
            $smsContent = str_replace('##full_name##', $patient->full_name ?? '', $smsContent);
            $smsContent = str_replace('##email##', $patient->email ?? '', $smsContent);
            $smsContent = str_replace('##phone##', $patient->phone ?? '', $smsContent);
            $smsContent = str_replace('##gender##', Config::get('constants.gender_array')[$patient->gender] ?? '', $smsContent);
        }

        if ($lead->city) {
            $smsContent = str_replace('##city_name##', $lead->city->name, $smsContent);
        }

        if ($lead->lead_source) {
            $smsContent = str_replace('##lead_source_name##', $lead->lead_source->name, $smsContent);
        }

        if ($lead->lead_status) {
            $smsContent = str_replace('##lead_status_name##', $lead->lead_status->name, $smsContent);
        }

        return $smsContent;
    }

    /**
     * Create lead record with audit trail
     * Moved from Leads::createRecord
     */
    public function createLeadRecord(array $data, ?string $status = null): Leads
    {
        return DB::transaction(function () use ($data, $status) {
            $accountId = Auth::user()->account_id;

            if ($status === 'Appointment') {
                $data['service_id'] = $data['base_service_id'] ?? null;
                $record = Leads::updateOrCreate([
                    'phone' => $data['phone'],
                    'account_id' => $accountId,
                ], $data);

                $data['lead_id'] = $record->id;
                LeadsServices::create($data);

                AuditTrails::addEventLogger('leads', 'create', $data, Leads::getFillableFields(), $record);
                return $record;
            }

            // Set region from city
            if (!empty($data['city_id'])) {
                $city = Cities::find($data['city_id']);
                $data['region_id'] = $city?->region_id;
            }

            $existingLead = Leads::where([
                'phone' => $data['phone'],
                'account_id' => $accountId,
            ])->first();

            if (!$existingLead) {
                $record = Leads::create($data);
            } else {
                $openStatus = $this->getDefaultLeadStatus($accountId);
                if ($openStatus) {
                    $existingLead->lead_status_id = $openStatus->id;
                }
                $existingLead->created_at = Carbon::now();
                $existingLead->save();
                $record = $existingLead;
                $data['lead_id'] = $record->id;
            }

            AuditTrails::addEventLogger('leads', 'create', $data, Leads::getFillableFields(), $record);
            return $record;
        });
    }

    /**
     * Update lead record with audit trail
     * Moved from Leads::updateRecord
     */
    public function updateLeadRecord($id, array $data, bool $isAppointment = false): ?Leads
    {
        return DB::transaction(function () use ($id, $data, $isAppointment) {
            $record = Leads::find($id);
            if (!$record) {
                return null;
            }

            $oldData = $isAppointment ? $record->toArray() : [];

            // Set region from city
            if (!empty($data['city_id'])) {
                $city = Cities::find($data['city_id']);
                $data['region_id'] = $city?->region_id;
            }

            $data['updated_at'] = Carbon::now();
            $record->update($data);

            AuditTrails::editEventLogger('leads', 'Edit', $data, Leads::getFillableFields(), $oldData, $record);
            return $record;
        });
    }

    /**
     * Get lead report data (optimized)
     * Moved from Leads::getLeadReport
     */
    public function getLeadReport(array $filters): Collection
    {
        $query = $this->buildReportBaseQuery($filters);
        
        // Apply additional filters
        $this->applyReportFilters($query, $filters);

        // Age group filter
        if (!empty($filters['age_group_range'])) {
            $ageRange = explode(':', $filters['age_group_range']);
            $from = Carbon::now()->subYears((int) $ageRange[1])->toDateString();
            $to = Carbon::now()->subYears((int) $ageRange[0])->toDateString();
            $query->whereBetween('users.dob', [$from, $to]);
        }

        // Telecom provider filter
        if (!empty($filters['telecomprovider_id'])) {
            $providers = Telecomprovidernumber::whereIn('id', $filters['telecomprovider_id'])->get();
            $prefixes = $providers->pluck('pre_fix')->map(fn($p) => ltrim($p, '0'))->toArray();
            
            if (!empty($prefixes)) {
                $query->where(function ($q) use ($prefixes) {
                    foreach ($prefixes as $i => $prefix) {
                        if ($i === 0) {
                            $q->where('users.phone', 'like', $prefix . '%');
                        } else {
                            $q->orWhere('users.phone', 'like', $prefix . '%');
                        }
                    }
                });
            }
        }

        return $query->select([
            '*',
            'leads.created_by as lead_created_by',
            'leads.id as lead_id',
            'leads.created_at as lead_created_at',
            'users.id as PatientId',
        ])->get();
    }

    /**
     * Get marketing report data (optimized)
     * Moved from Leads::getMarketingReport
     */
    public function getMarketingReport(array $filters): Collection
    {
        $accountId = Auth::user()->account_id;
        $junkStatus = $this->getJunkLeadStatus($accountId);
        $junkStatusId = $junkStatus?->id ?? Config::get('constants.lead_status_junk');

        $query = $this->buildReportBaseQuery($filters, 'users.created_at');
        $query->whereNotIn('leads.lead_status_id', [$junkStatusId]);

        $this->applyReportFilters($query, $filters);

        return $query->select([
            '*',
            'leads.created_by as lead_created_by',
            'leads.id as lead_id',
            'leads.created_at as lead_created_at',
            'users.id as PatientId',
        ])->get();
    }

    /**
     * Get lead summary report (optimized)
     * Moved from Leads::getLeadSummaryReport
     */
    public function getLeadSummaryReport(array $filters): Collection
    {
        $query = $this->buildReportBaseQuery($filters);

        if (!empty($filters['region_id'])) {
            $query->where('leads.region_id', $filters['region_id']);
        }
        if (!empty($filters['city_id'])) {
            $query->where('leads.city_id', $filters['city_id']);
        }

        return $query->select([
            '*',
            'leads.created_by as lead_created_by',
            'leads.id as lead_id',
            'leads.created_at as lead_created_at',
            'users.id as PatientId',
        ])->get();
    }

    /**
     * Get NOW report (optimized)
     * Moved from Leads::getNowReport
     */
    public function getNowReport(array $filters, int $accountId): Collection
    {
        [$startDate, $endDate] = $this->parseDateRange($filters['date_range'] ?? null);

        $junkStatus = LeadStatuses::where('is_junk', 1)->first();
        $arrived = AppointmentStatuses::where('is_arrived', 1)->first();
        $pending = AppointmentStatuses::where('is_default', 1)->first();

        $appointments = DB::table('leads')
            ->join('appointments', 'leads.id', '=', 'appointments.lead_id')
            ->where('leads.lead_status_id', '!=', $junkStatus?->id ?? 0)
            ->where('appointments.base_appointment_status_id', Config::get('constants.appointment_status_not_show'))
            ->whereDate('appointments.created_at', '>=', $startDate)
            ->whereDate('appointments.created_at', '<=', $endDate)
            ->select('appointments.*', DB::raw('MAX(appointments.created_at) as max_created_at'))
            ->groupBy('appointments.patient_id', 'appointments.service_id')
            ->orderBy('appointments.created_at', 'DESC')
            ->get();

        // Pre-load services for performance
        $services = Services::where('account_id', $accountId)
            ->select('id', 'parent_id', 'slug', 'end_node')
            ->get()
            ->keyBy('id');

        // Filter out appointments with follow-ups
        return $appointments->filter(function ($appointment) use ($junkStatus, $arrived, $pending, $endDate, $services) {
            $rootService = LocationsWidget::findRoot($appointment->service_id, $services);

            $hasFollowUp = DB::table('leads')
                ->join('appointments', 'leads.id', '=', 'appointments.lead_id')
                ->where('leads.lead_status_id', '!=', $junkStatus?->id ?? 0)
                ->where('appointments.patient_id', $appointment->patient_id)
                ->whereIn('appointments.base_appointment_status_id', [$arrived?->id, $pending?->id])
                ->whereDate('appointments.created_at', '>', $endDate)
                ->exists();

            if (!$hasFollowUp) {
                return true;
            }

            // Check if follow-up is for same service
            $followUps = DB::table('appointments')
                ->where('patient_id', $appointment->patient_id)
                ->whereDate('created_at', '>', $endDate)
                ->pluck('service_id');

            foreach ($followUps as $serviceId) {
                if (LocationsWidget::findRoot($serviceId, $services) === $rootService) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Build base query for reports
     */
    protected function buildReportBaseQuery(array $filters, string $dateColumn = 'leads.created_at'): \Illuminate\Database\Eloquent\Builder
    {
        [$startDate, $endDate] = $this->parseDateRange($filters['date_range'] ?? null);

        return Leads::join('users', 'users.id', '=', 'leads.patient_id')
            ->where('users.user_type_id', Config::get('constants.patient_id'))
            ->where(function ($query) {
                $query->whereIn('leads.city_id', ACL::getUserCities())
                    ->orWhereNull('leads.city_id');
            })
            ->when($startDate, fn($q) => $q->whereDate($dateColumn, '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate($dateColumn, '<=', $endDate));
    }

    /**
     * Apply common report filters
     */
    protected function applyReportFilters($query, array $filters): void
    {
        $filterMap = [
            'cnic' => 'users.cnic',
            'dob' => 'users.dob',
            'patient_id' => 'users.id',
            'gender_id' => 'users.gender',
            'region_id' => 'leads.region_id',
            'city_id' => 'leads.city_id',
            'lead_status_id' => 'leads.lead_status_id',
            'service_id' => 'leads.service_id',
            'user_id' => 'leads.created_by',
            'town_id' => 'leads.town_id',
            'referred_id' => 'users.referred_by',
        ];

        foreach ($filterMap as $key => $column) {
            if (!empty($filters[$key])) {
                $query->where($column, $filters[$key]);
            }
        }

        // Like filters
        if (!empty($filters['email'])) {
            $query->where('users.email', 'like', '%' . $filters['email'] . '%');
        }
        if (!empty($filters['phone'])) {
            $query->where('users.phone', 'like', '%' . GeneralFunctions::cleanNumber($filters['phone']) . '%');
        }
    }

    /**
     * Parse date range string
     */
    protected function parseDateRange(?string $dateRange): array
    {
        if (!$dateRange) {
            return [null, null];
        }

        $parts = explode(' - ', $dateRange);
        return [
            date('Y-m-d', strtotime($parts[0])),
            date('Y-m-d', strtotime($parts[1] ?? $parts[0])),
        ];
    }

    /**
     * Send SMS to lead
     */
    public function sendSMS(int $leadId, string $phone): array
    {
        // Currently disabled - returns success
        // To enable, uncomment the implementation below
        return ['status' => true];

        /*
        // SMS implementation when enabled:
        $SMSTemplate = \App\Models\SMSTemplates::findOrFail(2);
        $preparedText = $this->prepareSMSContent($leadId, $SMSTemplate->content);
        $Settings = Settings::getAllRecordsDictionary(Auth::user()->account_id);
        
        $SMSObj = [
            'username' => $Settings[1]->data,
            'password' => $Settings[2]->data,
            'to' => GeneralFunctions::prepareNumber(GeneralFunctions::cleanNumber($phone)),
            'text' => $preparedText,
            'mask' => $Settings[3]->data,
            'test_mode' => $Settings[4]->data,
        ];
        
        $response = \App\Helpers\TelenorSMSAPI::SendSMS($SMSObj);
        
        \App\Models\SMSLogs::create(array_merge($SMSObj, $response, [
            'lead_id' => $leadId,
            'created_by' => Auth::id(),
        ]));
        
        return $response;
        */
    }
}
