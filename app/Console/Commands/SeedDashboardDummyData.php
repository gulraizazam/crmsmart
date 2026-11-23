<?php

namespace App\Console\Commands;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class SeedDashboardDummyData extends Command
{
    protected $signature = 'dashboard:dummy-data
        {--purge : Remove previously seeded dummy data and exit}
        {--days=150 : Days of history to generate, ending today}
        {--account=1 : Account to seed}
        {--force : Skip the confirmation prompt}';

    protected $description = 'Seed 5 dummy doctors with patients, leads, appointments, plans, invoices, payments and expenses so every dashboard chart has data';

    private const MANIFEST = 'dummy-dashboard-manifest.json';
    private const EMAIL_DOMAIN = 'dummy-dashboard.test';
    private const TAX_RATE = 5;

    // Child rows are deleted before their parents.
    private const PURGE_ORDER = [
        'activities', 'package_advances', 'invoice_details', 'invoices', 'package_services', 'package_bundles',
        'packages', 'appointments', 'leads', 'expenses', 'expense_categories', 'cash_pools',
        'doctor_has_locations', 'resources', 'users',
    ];

    private const FEMALE_NAMES = ['Ayesha', 'Fatima', 'Zainab', 'Maryam', 'Hira', 'Sana', 'Amna', 'Mahnoor', 'Iqra', 'Rabia', 'Sadia', 'Nimra', 'Areeba', 'Kiran', 'Mehwish', 'Saba', 'Anum', 'Laiba', 'Hafsa', 'Komal', 'Noor', 'Alina', 'Bushra', 'Sidra'];
    private const MALE_NAMES = ['Ali', 'Ahmed', 'Hassan', 'Usman', 'Bilal', 'Hamza', 'Faisal', 'Imran', 'Kashif', 'Saad', 'Zeeshan', 'Fahad', 'Asad', 'Shahzaib', 'Danish', 'Waqas', 'Junaid', 'Omer', 'Haris', 'Talha'];
    private const LAST_NAMES = ['Khan', 'Ahmed', 'Malik', 'Raza', 'Siddiqui', 'Qureshi', 'Butt', 'Chaudhry', 'Sheikh', 'Hussain', 'Iqbal', 'Javed', 'Akhtar', 'Mirza', 'Abbasi', 'Rana', 'Shah', 'Zafar', 'Anwar', 'Baig'];
    private const DOCTORS = [
        ['Dr. Ayesha Khan', 2], ['Dr. Bilal Ahmed', 1], ['Dr. Sana Malik', 2], ['Dr. Usman Raza', 1], ['Dr. Hina Siddiqui', 2],
    ];

    private int $accountId;
    private int $adminId;
    private string $adminName;
    private array $fdmIds = [];
    private string $passwordHash;
    private Carbon $now;
    private Carbon $today;
    private array $manifest = [];
    private int $phoneSeq = 0;
    private int $patientSeq = 0;

    private array $centres = [];
    private array $centreDoctors = [];
    private array $doctors = [];
    private array $categories = [];
    private int $allServicesId;
    private array $apptStatus = [];
    private array $leadStatus = [];
    private array $leadSources = [];
    private array $modes = [];
    private int $paidInvoiceStatusId;
    private int $practitionerTypeId;
    private int $patientTypeId;
    private int $doctorResourceTypeId;
    private array $cashInByCentre = [];
    private array $cashRefundByCentre = [];

    public function handle()
    {
        mt_srand(20261123);
        $this->accountId = (int) $this->option('account');
        $database = DB::connection()->getDatabaseName();

        if ($this->option('purge')) {
            if (! $this->option('force') && ! $this->confirm("Remove dummy dashboard data from database [{$database}]?")) {
                return self::FAILURE;
            }
            $this->purge();

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm("Insert dummy dashboard data into database [{$database}]? Data from a previous run is removed first.")) {
            return self::FAILURE;
        }

        $this->purge();
        $this->loadLookups();

        DB::transaction(function () {
            $this->createDoctors();
            $this->createPatientJourneys();
            $this->createExpensesAndPools();
        });

        file_put_contents(storage_path('app/'.self::MANIFEST), json_encode($this->manifest));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->printSummary();

        return self::SUCCESS;
    }

    private function purge(): void
    {
        $path = storage_path('app/'.self::MANIFEST);
        if (! file_exists($path)) {
            $this->line('No previous dummy data found.');

            return;
        }

        $manifest = json_decode(file_get_contents($path), true) ?: [];

        DB::transaction(function () use ($manifest) {
            if (! empty($manifest['model_has_roles'])) {
                DB::table('model_has_roles')->where('model_type', User::class)->whereIn('model_id', $manifest['model_has_roles'])->delete();
            }
            if (! empty($manifest['role_has_users'])) {
                DB::table('role_has_users')->whereIn('user_id', $manifest['role_has_users'])->delete();
            }
            foreach (self::PURGE_ORDER as $table) {
                $ids = $manifest[$table] ?? [];
                if ($table === 'activities' && $ids) {
                    $this->deleteDummyActivities($ids);
                    continue;
                }
                foreach (array_chunk($ids, 1000) as $chunk) {
                    DB::table($table)->whereIn('id', $chunk)->delete();
                }
            }
        });

        unlink($path);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $removed = array_sum(array_map('count', $manifest));
        $this->info("Removed {$removed} dummy rows.");

        $leftovers = DB::table('users')->where('email', 'like', '%@'.self::EMAIL_DOMAIN)->count();
        if ($leftovers > 0) {
            $this->warn("{$leftovers} users with @".self::EMAIL_DOMAIN.' emails were not in the manifest and were left untouched.');
        }
    }

    // activities has a tamper-evident delete trigger; the flag is raised only while removing rows this command inserted.
    private function deleteDummyActivities(array $ids): void
    {
        DB::statement('SET @activities_mutation_allowed = 1');
        try {
            foreach (array_chunk($ids, 1000) as $chunk) {
                DB::table('activities')->whereIn('id', $chunk)->delete();
            }
        } finally {
            DB::statement('SET @activities_mutation_allowed = 0');
        }
    }

    private function loadLookups(): void
    {
        // Treat today as a finished clinic day so the "Today" charts and activity feed have data whenever you test.
        $this->now = Carbon::now()->max(Carbon::today()->setTime(20, 0));
        $this->today = Carbon::today();
        $this->passwordHash = Hash::make('Dummy@12345');
        $this->practitionerTypeId = (int) config('constants.practitioner_id');
        $this->patientTypeId = (int) config('constants.patient_id');

        $admin = User::where('account_id', $this->accountId)->where('active', 1)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super-Admin', 'Administrator']);
            })
            ->orderBy('id')
            ->first();
        if (! $admin) {
            throw new \RuntimeException('No active Super-Admin or Administrator user found for this account.');
        }
        $this->adminId = (int) $admin->id;
        $this->adminName = $admin->name;

        $fdmRoleId = DB::table('roles')->where('name', 'FDM')->value('id');
        $this->fdmIds = $fdmRoleId
            ? DB::table('role_has_users')->where('role_id', $fdmRoleId)->pluck('user_id')->map('intval')->all()
            : [];

        $this->doctorResourceTypeId = (int) DB::table('resource_types')->whereRaw('LOWER(name) = ?', ['doctor'])->value('id');
        $this->paidInvoiceStatusId = (int) DB::table('invoice_statuses')->where('slug', 'paid')->value('id');
        if (! $this->doctorResourceTypeId || ! $this->paidInvoiceStatusId) {
            throw new \RuntimeException('Doctor resource type or paid invoice status is missing.');
        }

        $this->loadAppointmentStatuses();
        $this->loadLeadLookups();
        $this->loadPaymentModes();
        $this->loadCentres();
        $this->loadServiceCategories();
    }

    private function loadAppointmentStatuses(): void
    {
        $statuses = DB::table('appointment_statuses')->where('account_id', $this->accountId)->get();
        $find = function (callable $match, string $label) use ($statuses) {
            $row = $statuses->first($match);
            if (! $row) {
                throw new \RuntimeException("Appointment status '{$label}' not found.");
            }

            return (int) $row->id;
        };
        $isParent = function ($s) {
            return empty($s->parent_id);
        };

        $this->apptStatus = [
            'pending' => $find(function ($s) use ($isParent) {
                return $isParent($s) && strcasecmp($s->name, 'Pending') === 0;
            }, 'Pending'),
            'arrived' => $find(function ($s) {
                return (int) $s->is_arrived === 1;
            }, 'Arrived'),
            'converted' => $find(function ($s) {
                return (int) $s->is_converted === 1;
            }, 'Converted'),
            'noshow' => $find(function ($s) use ($isParent) {
                return $isParent($s) && stripos($s->name, 'show') !== false;
            }, "Didn't show up"),
            'cancelled' => $find(function ($s) use ($isParent) {
                return $isParent($s) && stripos($s->name, 'cancel') !== false;
            }, 'Cancelled'),
            'not_interested' => $find(function ($s) use ($isParent) {
                return $isParent($s) && (int) $s->active === 1 && stripos($s->name, 'not interested') !== false;
            }, 'Not interested'),
        ];
    }

    private function loadLeadLookups(): void
    {
        $statuses = DB::table('lead_statuses')->where('active', 1)->orderBy('id')->get(['id', 'name']);
        $byName = function (string $name) use ($statuses) {
            $row = $statuses->first(function ($s) use ($name) {
                return strcasecmp(trim($s->name), $name) === 0;
            });

            return $row ? (int) $row->id : null;
        };
        $open = $byName('Open');
        if (! $open) {
            throw new \RuntimeException("Lead status 'Open' not found.");
        }
        foreach (['open' => 'Open', 'booked' => 'Booked', 'arrived' => 'Arrived', 'converted' => 'Converted', 'not_interested' => 'Not Interested', 'call_again' => 'Call Again', 'junk' => 'Junk'] as $key => $name) {
            $this->leadStatus[$key] = $byName($name) ?: $open;
        }

        $weights = ['social' => 30, 'whatsapp' => 15, 'google' => 15, 'website' => 12, 'referral' => 12, 'radio' => 4, 'dawn' => 4, 'other' => 8];
        $seen = [];
        foreach (DB::table('lead_sources')->where('active', 1)->orderBy('id')->get(['id', 'name']) as $source) {
            $key = strtolower(trim($source->name));
            if (isset($seen[$key]) || Str::startsWith($key, 'new patient')) {
                continue;
            }
            $seen[$key] = true;
            $weight = 5;
            foreach ($weights as $needle => $w) {
                if (strpos($key, $needle) !== false) {
                    $weight = $w;
                    break;
                }
            }
            $this->leadSources[(int) $source->id] = $weight;
        }
        if (! $this->leadSources) {
            throw new \RuntimeException('No active lead sources found.');
        }
    }

    private function loadPaymentModes(): void
    {
        $modes = DB::table('payment_modes')->get(['id', 'name']);
        foreach (['cash' => 'Cash', 'card' => 'Card', 'bank' => 'Bank/Wire Transfer', 'settle' => 'Settle Amount'] as $key => $name) {
            $id = $modes->first(function ($m) use ($name) {
                return strcasecmp($m->name, $name) === 0;
            });
            if (! $id) {
                throw new \RuntimeException("Payment mode '{$name}' not found.");
            }
            $this->modes[$key] = (int) $id->id;
        }
    }

    private function loadCentres(): void
    {
        $locations = DB::table('locations')
            ->where('account_id', $this->accountId)
            ->where('active', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNotNull('ntn')->orWhereNotNull('stn');
            })
            ->where('name', 'not like', 'All %')
            ->orderBy('id')
            ->get(['id', 'name', 'city_id', 'region_id']);

        // Round-robin by city so the centre charts show several cities.
        $byCity = $locations->groupBy('city_id');
        $picked = [];
        while (count($picked) < 6 && $byCity->isNotEmpty()) {
            foreach ($byCity as $cityId => $rows) {
                if (count($picked) >= 6) {
                    break;
                }
                $picked[] = $rows->shift();
                if ($rows->isEmpty()) {
                    $byCity->forget($cityId);
                }
            }
        }
        if (! $picked) {
            throw new \RuntimeException('Need at least one active centre with NTN/STN set.');
        }

        $cityNames = DB::table('cities')->whereIn('id', collect($picked)->pluck('city_id'))->pluck('name', 'id');
        foreach ($picked as $i => $loc) {
            $this->centres[(int) $loc->id] = [
                'id' => (int) $loc->id,
                'name' => $loc->name,
                'city_id' => (int) $loc->city_id,
                'region_id' => (int) $loc->region_id,
                'label' => ($cityNames[$loc->city_id] ?? '').'-'.$loc->name,
                'weight' => [30, 24, 16, 12, 10, 8][$i] ?? 8,
            ];
        }
    }

    private function loadServiceCategories(): void
    {
        $children = DB::table('services')
            ->where('account_id', $this->accountId)
            ->where('active', 1)
            ->where('end_node', 1)
            ->where('parent_id', '>', 0)
            ->where('price', '>', 0)
            ->whereNull('deleted_at')
            ->get(['id', 'name', 'parent_id', 'price']);

        $parents = DB::table('services')
            ->whereIn('id', $children->pluck('parent_id')->unique())
            ->where('active', 1)
            ->whereNull('deleted_at')
            ->get(['id', 'name']);

        $weights = ['laser hair' => 18, 'skin' => 14, 'facial' => 12, 'body' => 10, 'invasive' => 8, 'hair loss' => 8, 'chemical' => 8, 'pico' => 7, 'ultraformer' => 6, 'meso' => 6, 'weight' => 6, 'infini' => 5];
        foreach ($parents as $parent) {
            $name = strtolower($parent->name);
            if ($name === 'all services' || strpos($name, 'refund') !== false || strpos($name, 'settle') !== false) {
                continue;
            }
            $weight = 4;
            foreach ($weights as $needle => $w) {
                if (strpos($name, $needle) !== false) {
                    $weight = $w;
                    break;
                }
            }
            $this->categories[(int) $parent->id] = [
                'id' => (int) $parent->id,
                'name' => $parent->name,
                'weight' => $weight,
                'children' => $children->where('parent_id', $parent->id)->map(function ($c) {
                    return ['id' => (int) $c->id, 'name' => $c->name, 'price' => (float) $c->price];
                })->values()->all(),
            ];
        }
        if (! $this->categories) {
            throw new \RuntimeException('No priced active services found.');
        }

        $this->allServicesId = (int) (DB::table('services')->where('name', 'All Services')->whereNull('deleted_at')->value('id')
            ?: array_key_first($this->categories));
    }

    private function createDoctors(): void
    {
        $roleRow = DB::table('model_has_roles')
            ->join('users', 'users.id', '=', 'model_has_roles.model_id')
            ->where('model_has_roles.model_type', User::class)
            ->where('users.user_type_id', $this->practitionerTypeId)
            ->where('users.resource_type_id', $this->doctorResourceTypeId)
            ->groupBy('model_has_roles.role_id')
            ->orderByRaw('COUNT(*) DESC')
            ->select('model_has_roles.role_id')
            ->first();
        $roleId = $roleRow ? (int) $roleRow->role_id : (int) DB::table('roles')->whereIn('name', ['Aesthetic Doctor', 'Consultant'])->value('id');

        $centreIds = array_keys($this->centres);
        $count = count($centreIds);
        $joinedAt = $this->today->copy()->subDays((int) $this->option('days') + 30)->setTime(10, 0)->toDateTimeString();

        foreach (self::DOCTORS as $i => [$name, $gender]) {
            $userId = $this->insert('users', [
                'name' => $name,
                'email' => 'dr.'.Str::slug(str_replace('Dr. ', '', $name), '.').'@'.self::EMAIL_DOMAIN,
                'phone' => $this->nextPhone(),
                'gender' => $gender,
                'user_type_id' => $this->practitionerTypeId,
                'resource_type_id' => $this->doctorResourceTypeId,
                'can_perform_consultation' => 1,
                'account_id' => $this->accountId,
                'active' => 1,
                'password' => $this->passwordHash,
                'created_by' => $this->adminId,
                'created_at' => $joinedAt,
                'updated_at' => $joinedAt,
            ]);

            if ($roleId) {
                DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => User::class, 'model_id' => $userId]);
                DB::table('role_has_users')->insert(['role_id' => $roleId, 'user_id' => $userId]);
                $this->manifest['model_has_roles'][] = $userId;
                $this->manifest['role_has_users'][] = $userId;
            }

            $resourceId = $this->insert('resources', [
                'name' => $name,
                'resource_type_id' => $this->doctorResourceTypeId,
                'external_id' => $userId,
                'account_id' => $this->accountId,
                'active' => 1,
                'created_at' => $joinedAt,
                'updated_at' => $joinedAt,
            ]);

            // Every centre gets at least one doctor; the last doctor covers three centres.
            $assigned = [$centreIds[$i % $count], $centreIds[($i + 1) % $count]];
            if ($i === count(self::DOCTORS) - 1) {
                $assigned[] = $centreIds[($i + 2) % $count];
            }
            $assigned = array_values(array_unique($assigned));

            foreach ($assigned as $centreId) {
                $this->insert('doctor_has_locations', [
                    'user_id' => $userId,
                    'location_id' => $centreId,
                    'service_id' => $this->allServicesId,
                    'end_node' => 1,
                    'is_allocated' => 1,
                    'created_at' => $joinedAt,
                    'updated_at' => $joinedAt,
                ]);
                $this->centreDoctors[$centreId][] = $userId;
            }

            $this->doctors[$userId] = ['id' => $userId, 'name' => $name, 'resource_id' => $resourceId];
        }
    }

    private function createPatientJourneys(): void
    {
        $days = (int) $this->option('days');
        $start = $this->today->copy()->subDays($days);

        for ($day = $start->copy(); $day->lte($this->today); $day->addDay()) {
            // Fewer leads on Sundays and a gentle upward trend over the period.
            $leads = $day->isSunday() ? mt_rand(2, 4) : mt_rand(6, 11);
            $leads += (int) round(($start->diffInDays($day) / max(1, $days)) * 3);

            for ($i = 0; $i < $leads; $i++) {
                $this->createLeadJourney($day->copy());
            }
        }
    }

    private function createLeadJourney(Carbon $day): void
    {
        $createdAt = $this->randomMomentOn($day, 9 * 60, 21 * 60);
        if (! $createdAt) {
            return;
        }

        $centre = $this->centres[$this->weighted(array_column($this->centres, 'weight', 'id'))];
        $category = $this->categories[$this->weighted(array_column($this->categories, 'weight', 'id'))];
        $gender = $this->weighted([2 => 65, 1 => 32, 0 => 3]) ?: null;
        $name = $this->personName($gender);
        $phone = $this->nextPhone();
        $this->patientSeq++;

        $patientId = $this->insert('users', [
            'name' => $name,
            'email' => 'patient'.$this->patientSeq.'@'.self::EMAIL_DOMAIN,
            'phone' => $phone,
            'gender' => $gender,
            'dob' => mt_rand(1, 100) <= 70 ? $day->copy()->subYears(mt_rand(18, 55))->subDays(mt_rand(0, 364))->toDateString() : null,
            'user_type_id' => $this->patientTypeId,
            'account_id' => $this->accountId,
            'active' => 1,
            'password' => $this->passwordHash,
            'created_by' => $this->adminId,
            'created_at' => $createdAt->toDateTimeString(),
            'updated_at' => $createdAt->toDateTimeString(),
        ]);

        $booked = mt_rand(1, 100) <= 65;
        $leadStatus = $booked ? null : $this->leadStatus[$this->weighted(['open' => 40, 'call_again' => 25, 'not_interested' => 20, 'junk' => 15])];

        $leadId = $this->insert('leads', array_filter([
            'active' => 1,
            'msg_count' => 0,
            'patient_id' => $patientId,
            'region_id' => $centre['region_id'],
            'city_id' => $centre['city_id'],
            'location_id' => $centre['id'],
            'lead_source_id' => $this->weighted($this->leadSources),
            'lead_status_id' => $leadStatus ?: $this->leadStatus['booked'],
            'service_id' => $category['id'],
            'created_by' => $this->adminId,
            'updated_by' => $this->adminId,
            'account_id' => $this->accountId,
            'name' => $name,
            'email' => 'patient'.$this->patientSeq.'@'.self::EMAIL_DOMAIN,
            'phone' => $phone,
            'gender' => $gender,
            'created_at' => $createdAt->toDateTimeString(),
            'updated_at' => $createdAt->toDateTimeString(),
        ], function ($value) {
            return $value !== null;
        }));

        if (! $booked) {
            return;
        }

        $patient = ['id' => $patientId, 'name' => $name, 'lead_id' => $leadId];
        $outcome = $this->createConsultation($patient, $centre, $category, $day, $createdAt);

        $leadStatusByOutcome = [
            'pending' => 'booked',
            'arrived' => 'arrived',
            'converted' => 'converted',
            'noshow' => mt_rand(0, 1) ? 'booked' : 'call_again',
            'cancelled' => mt_rand(0, 1) ? 'not_interested' : 'call_again',
            'not_interested' => 'not_interested',
        ];
        DB::table('leads')->where('id', $leadId)->update([
            'lead_status_id' => $this->leadStatus[$leadStatusByOutcome[$outcome]],
            'converted_by' => $outcome === 'converted' ? $this->adminId : null,
        ]);
    }

    private function createConsultation(array $patient, array $centre, array $category, Carbon $leadDay, Carbon $leadCreatedAt): string
    {
        $offset = $this->weighted([0 => 15, 1 => 25, 2 => 20, 3 => 15, 4 => 10, 5 => 8, 6 => 7]);
        $bookedAt = $this->capToNow($leadCreatedAt->copy()->addMinutes(mt_rand(5, 90)));
        $date = $leadDay->copy()->addDays($offset);
        $slot = $this->slotOn($date, $offset === 0 ? $bookedAt->copy()->addMinutes(30) : null);
        if (! $slot) {
            $date->addDay();
            $slot = $this->slotOn($date, null);
        }

        $outcome = 'pending';
        if ($date->lt($this->today)) {
            $outcome = $this->weighted(['arrived' => 34, 'converted' => 30, 'noshow' => 18, 'cancelled' => 9, 'not_interested' => 4, 'pending' => 5]);
        } elseif ($date->eq($this->today)) {
            $outcome = $this->weighted(['arrived' => 34, 'converted' => 28, 'pending' => 20, 'noshow' => 10, 'cancelled' => 8]);
        }

        $arrivedAt = null;
        $convertedAt = null;
        if (in_array($outcome, ['arrived', 'converted'], true)) {
            $arrivedAt = $slot->copy()->addMinutes(mt_rand(0, 15));
            if ($arrivedAt->gt($this->now)) {
                $outcome = 'pending';
                $arrivedAt = null;
            } elseif ($outcome === 'converted') {
                $convertedAt = $this->capToNow($arrivedAt->copy()->addMinutes(mt_rand(15, 45)));
            }
        }

        $doctorId = $this->centreDoctors[$centre['id']][array_rand($this->centreDoctors[$centre['id']])];
        $createdBy = ($this->fdmIds && mt_rand(1, 100) <= 30) ? $this->fdmIds[array_rand($this->fdmIds)] : $this->adminId;
        $statusId = $this->apptStatus[$outcome];
        $updatedAt = $convertedAt ?: ($arrivedAt ?: $bookedAt);

        $appointmentId = $this->insertAppointment([
            'patient' => $patient,
            'centre' => $centre,
            'doctor_id' => $doctorId,
            'service_id' => $category['id'],
            'type' => 1,
            'slot' => $slot,
            'status_id' => $statusId,
            'arrived_at' => $arrivedAt,
            'converted_at' => $convertedAt,
            'created_by' => $createdBy,
            'created_at' => $bookedAt,
            'updated_at' => $updatedAt,
        ]);

        if (! $arrivedAt) {
            return $outcome;
        }

        $fee = $this->weighted([0 => 15, 1500 => 25, 2000 => 30, 2500 => 20, 3000 => 10]);
        $invoiceId = $this->insertInvoice($patient['id'], $appointmentId, $centre['id'], $doctorId, $fee, $category['id'], $arrivedAt, null, null);

        if ($fee > 0) {
            $mode = $this->weighted(['cash' => 50, 'card' => 38, 'bank' => 12]);
            $this->insertPayment([
                'cash_flow' => 'in', 'amount' => $fee, 'mode' => $mode, 'patient_id' => $patient['id'], 'centre_id' => $centre['id'],
                'appointment_id' => $appointmentId, 'appointment_type_id' => 1, 'invoice_id' => $invoiceId, 'at' => $arrivedAt,
            ]);
            $this->insertSettlement($fee, $patient['id'], $centre['id'], $appointmentId, 1, $invoiceId, null, $arrivedAt);
            $this->insertActivity([
                'action' => 'received',
                'activity_type' => 'invoice_created',
                'appointment_type' => $category['name'].' Consultation',
                'appointment_id' => $appointmentId,
                'invoice_id' => $invoiceId,
                'patient' => $patient,
                'centre' => $centre,
                'amount' => $fee,
                'at' => $arrivedAt,
                'description' => '<span class="highlight">'.e($this->adminName).'</span> created invoice <span class="highlight-green">Rs. '.number_format($fee).'</span> for <span class="highlight-orange">'.e($category['name']).' Consultation</span> in <span class="highlight">'.e($centre['label']).'</span> on '.$arrivedAt->format('M j, Y'),
            ]);
        }

        if ($convertedAt) {
            $this->createPlan($patient, $centre, $category, $doctorId, $appointmentId, $convertedAt);
        }

        return $outcome;
    }

    private function createPlan(array $patient, array $centre, array $category, int $doctorId, int $consultationId, Carbon $convertedAt): void
    {
        $children = $category['children'];
        shuffle($children);
        $services = array_slice($children, 0, (count($children) > 1 && mt_rand(1, 100) <= 25) ? 2 : 1);
        $discount = $this->weighted([0 => 50, 5 => 20, 10 => 20, 15 => 10]);
        $randomId = md5(uniqid((string) mt_rand(), true));
        $at = $convertedAt->toDateTimeString();

        $packageId = $this->insert('packages', [
            'random_id' => $randomId,
            'name' => '',
            'plan_type' => 'plan',
            'plan_name' => $services[0]['name'],
            'sessioncount' => '0',
            'total_price' => 0,
            'total_price_bk' => 0,
            'is_refund' => 0,
            'is_exclusive' => 0,
            'account_id' => $this->accountId,
            'patient_id' => $patient['id'],
            'location_id' => $centre['id'],
            'appointment_id' => $consultationId,
            'active' => 1,
            'is_setteled' => 0,
            'created_at' => $at,
            'updated_at' => $at,
        ]);

        $sessions = [];
        $total = 0.0;
        foreach ($services as $service) {
            $qty = $this->sessionsFor($category['name']);
            $perSession = round($service['price'] * (100 - $discount) / 100);
            $net = $perSession * $qty;
            $total += $net;

            $bundleId = $this->insert('package_bundles', [
                'random_id' => $randomId,
                'is_allocate' => 1,
                'qty' => $qty,
                'discount_name' => '-',
                'discount_price' => 0,
                'service_price' => $service['price'],
                'net_amount' => $net,
                'tax_exclusive_net_amount' => round($net / (1 + self::TAX_RATE / 100)),
                'tax_percenatage' => self::TAX_RATE,
                'tax_price' => $net - round($net / (1 + self::TAX_RATE / 100)),
                'tax_including_price' => $net,
                'location_id' => $centre['id'],
                'package_id' => $packageId,
                'active' => 1,
                'orignal_price' => $service['price'],
                'created_at' => $at,
                'updated_at' => $at,
            ]);

            for ($s = 0; $s < $qty; $s++) {
                $packageServiceId = $this->insert('package_services', [
                    'random_id' => $randomId,
                    'package_id' => $packageId,
                    'package_bundle_id' => $bundleId,
                    'service_id' => $service['id'],
                    'is_consumed' => 0,
                    'orignal_price' => $service['price'],
                    'price' => $perSession,
                    'actual_price' => $perSession,
                    'is_exclusive' => 0,
                    'tax_exclusive_price' => round($perSession / (1 + self::TAX_RATE / 100), 2),
                    'tax_percenatage' => self::TAX_RATE,
                    'tax_price' => round($perSession - $perSession / (1 + self::TAX_RATE / 100), 2),
                    'tax_including_price' => $perSession,
                    'sold_by' => $doctorId,
                    'consumption_order' => $s,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]);
                $sessions[] = ['id' => $packageServiceId, 'service' => $service, 'price' => $perSession];
            }
        }

        DB::table('packages')->where('id', $packageId)->update([
            'name' => (string) $packageId,
            'total_price' => $total,
            'sessioncount' => (string) count($sessions),
        ]);

        $plan = ['id' => $packageId, 'total' => $total, 'paid' => 0.0, 'consumed' => 0.0, 'consultation_id' => $consultationId];
        $upfront = min($total, $this->roundUp($total * $this->weighted([100 => 35, 75 => 15, 50 => 30, 30 => 20]) / 100));
        $this->payPlan($plan, $patient, $centre, $upfront, $convertedAt->copy()->addMinutes(2));

        $this->scheduleTreatments($plan, $patient, $centre, $doctorId, $sessions, $convertedAt);

        if ($plan['paid'] > 0 && mt_rand(1, 100) <= 5) {
            $refundAt = $convertedAt->copy()->addDays(mt_rand(10, 45))->setTime(mt_rand(12, 17), mt_rand(0, 59));
            $amount = floor($plan['paid'] * mt_rand(20, 50) / 100 / 500) * 500;
            if ($amount >= 500 && $refundAt->lte($this->now)) {
                $mode = $this->weighted(['cash' => 60, 'bank' => 40]);
                $this->insertPayment([
                    'cash_flow' => 'out', 'is_refund' => 1, 'amount' => $amount, 'mode' => $mode, 'patient_id' => $patient['id'],
                    'centre_id' => $centre['id'], 'package_id' => $packageId, 'at' => $refundAt,
                ]);
                $this->insertActivity([
                    'action' => 'refunded',
                    'activity_type' => 'refund_made',
                    'appointment_type' => 'Plan',
                    'plan_id' => $packageId,
                    'patient' => $patient,
                    'centre' => $centre,
                    'amount' => $amount,
                    'at' => $refundAt,
                    'description' => '<span class="highlight">'.e($this->adminName).'</span> refunded <span class="highlight-green">Rs. '.number_format($amount).'</span> to <span class="highlight-orange">'.e($patient['name']).'</span> for <span class="highlight-purple">Plan #'.sprintf('%05d', $packageId).'</span> in <span class="highlight">'.e($centre['name']).'</span> on <span class="highlight-purple">'.$refundAt->format('M j, Y').'</span>',
                ]);
            }
        }
    }

    private function scheduleTreatments(array &$plan, array $patient, array $centre, int $doctorId, array $sessions, Carbon $convertedAt): void
    {
        $cursor = $convertedAt->copy();
        $horizon = $this->today->copy()->addDays(21);

        foreach ($sessions as $index => $session) {
            $gap = $index === 0 ? (mt_rand(1, 100) <= 25 ? 0 : mt_rand(7, 21)) : mt_rand(14, 35);
            $date = $cursor->copy()->startOfDay()->addDays($gap);
            if ($date->gt($horizon)) {
                return;
            }
            $slot = $this->slotOn($date, $gap === 0 ? $cursor->copy()->addMinutes(30) : null);
            if (! $slot) {
                $date->addDay();
                $slot = $this->slotOn($date, null);
            }

            $arrivedAt = null;
            $outcome = 'pending';
            if ($date->lt($this->today)) {
                $outcome = $this->weighted(['arrived' => 85, 'noshow' => 10, 'cancelled' => 5]);
            } elseif ($date->eq($this->today)) {
                $outcome = $this->weighted(['arrived' => 75, 'pending' => 15, 'noshow' => 10]);
            }
            if ($outcome === 'arrived') {
                $arrivedAt = $slot->copy()->addMinutes(mt_rand(0, 10));
                if ($arrivedAt->gt($this->now)) {
                    $outcome = 'pending';
                    $arrivedAt = null;
                }
            }

            $bookedAt = $this->capToNow($cursor->copy()->addMinutes(mt_rand(5, 30)));
            $appointmentId = $this->insertAppointment([
                'patient' => $patient,
                'centre' => $centre,
                'doctor_id' => $doctorId,
                'service_id' => $session['service']['id'],
                'type' => 2,
                'slot' => $slot,
                'status_id' => $this->apptStatus[$outcome],
                'arrived_at' => $arrivedAt,
                'converted_at' => null,
                'created_by' => $this->adminId,
                'created_at' => $bookedAt,
                'updated_at' => $arrivedAt ?: $bookedAt,
            ]);

            if ($arrivedAt) {
                $this->consumeSession($plan, $patient, $centre, $doctorId, $session, $appointmentId, $arrivedAt);
                $cursor = $arrivedAt;
            } else {
                $cursor = $slot;
            }
        }
    }

    private function consumeSession(array &$plan, array $patient, array $centre, int $doctorId, array $session, int $appointmentId, Carbon $arrivedAt): void
    {
        $price = $session['price'];
        $plan['consumed'] += $price;

        if ($plan['paid'] < $plan['consumed']) {
            $remaining = $plan['total'] - $plan['paid'];
            $amount = min($remaining, $this->roundUp(max($plan['consumed'] - $plan['paid'], $remaining * mt_rand(30, 100) / 100)));
            $this->payPlan($plan, $patient, $centre, $amount, $arrivedAt->copy()->subMinutes(2));
        }

        $invoiceId = $this->insertInvoice($patient['id'], $appointmentId, $centre['id'], $doctorId, $price, $session['service']['id'], $arrivedAt, $plan['id'], $session['id']);
        $this->insertSettlement($price, $patient['id'], $centre['id'], $appointmentId, 2, $invoiceId, $plan['id'], $arrivedAt);

        DB::table('package_services')->where('id', $session['id'])->update([
            'is_consumed' => 1,
            'consumed_at' => $arrivedAt->toDateTimeString(),
            'updated_at' => $arrivedAt->toDateTimeString(),
        ]);

        $serviceName = $session['service']['name'];
        $this->insertActivity([
            'action' => 'consumed',
            'activity_type' => 'Treatment',
            'appointment_type' => $serviceName.' Treatment',
            'appointment_id' => $appointmentId,
            'invoice_id' => $invoiceId,
            'service_id' => $session['service']['id'],
            'service' => $serviceName,
            'schedule_date' => $arrivedAt->toDateString(),
            'patient' => $patient,
            'centre' => $centre,
            'amount' => $price,
            'at' => $arrivedAt,
            'description' => '<span class="highlight">'.e($this->adminName).'</span> consumed <span class="highlight-green">Rs. '.number_format($price).'</span> from <span class="highlight-orange">'.e($patient['name']).'</span> for <span class="highlight-orange">'.e($serviceName).'</span> Treatment at <span class="highlight">'.e($centre['name']).'</span> on <span class="highlight-purple">'.$arrivedAt->format('M j, Y').'</span>',
        ]);
    }

    private function payPlan(array &$plan, array $patient, array $centre, float $amount, Carbon $at): void
    {
        if ($amount <= 0) {
            return;
        }
        $at = $this->capToNow($at);
        $mode = $this->weighted(['cash' => 40, 'card' => 45, 'bank' => 15]);
        $this->insertPayment([
            'cash_flow' => 'in', 'amount' => $amount, 'mode' => $mode, 'patient_id' => $patient['id'], 'centre_id' => $centre['id'],
            'appointment_id' => $plan['consultation_id'], 'package_id' => $plan['id'], 'at' => $at,
        ]);
        $plan['paid'] += $amount;

        $this->insertActivity([
            'action' => 'received',
            'activity_type' => 'payment_received',
            'appointment_type' => 'Plan',
            'plan_id' => $plan['id'],
            'patient' => $patient,
            'centre' => $centre,
            'amount' => $amount,
            'at' => $at,
            'description' => '<span class="highlight">'.e($this->adminName).'</span> received payment Rs. <span class="highlight-green">'.number_format($amount).'</span> from <span class="highlight-orange">'.e($patient['name']).'</span> for <span class="highlight-purple">Plan Id: '.$plan['id'].'</span> in <span class="highlight">'.e($centre['label']).'</span> ',
        ]);
    }

    private function createExpensesAndPools(): void
    {
        $categoryIds = [];
        foreach (['Rent', 'Salaries', 'Utilities', 'Marketing', 'Medical Supplies', 'Maintenance', 'Miscellaneous'] as $order => $name) {
            $existing = DB::table('expense_categories')->where('account_id', $this->accountId)->where('name', $name)->whereNull('deleted_at')->value('id');
            $categoryIds[$name] = $existing ?: $this->insert('expense_categories', [
                'account_id' => $this->accountId,
                'name' => $name,
                'vendor_emphasis' => 0,
                'is_active' => 1,
                'sort_order' => $order + 10,
                'created_at' => $this->now->toDateTimeString(),
                'updated_at' => $this->now->toDateTimeString(),
            ]);
        }

        $pools = [];
        foreach ($this->centres as $centreId => $centre) {
            $existing = DB::table('cash_pools')->where('account_id', $this->accountId)->where('type', 'branch_cash')
                ->where('location_id', $centreId)->whereNull('deleted_at')->value('id');
            $pools[$centreId] = [
                'id' => $existing ?: $this->insert('cash_pools', [
                    'account_id' => $this->accountId,
                    'type' => 'branch_cash',
                    'location_id' => $centreId,
                    'name' => $centre['name'].' Cash',
                    'opening_balance' => 0,
                    'cached_balance' => 0,
                    'is_active' => 1,
                    'opening_balance_frozen' => 0,
                    'created_at' => $this->now->toDateTimeString(),
                    'updated_at' => $this->now->toDateTimeString(),
                ]),
                'owned' => ! $existing,
                'spent' => 0.0,
            ];
        }

        $start = $this->today->copy()->subDays((int) $this->option('days'));
        for ($day = $start->copy(); $day->lte($this->today); $day->addDay()) {
            foreach ($this->centres as $centreId => $centre) {
                $planned = [];
                if ($day->day === 1) {
                    $planned[] = ['Rent', mt_rand(60, 120) * 1000, 'bank', 'Monthly rent'];
                }
                if ($day->day === 10) {
                    $planned[] = ['Utilities', mt_rand(15, 40) * 1000, 'cash', 'Electricity, gas and water bills'];
                }
                if ($day->day === 28) {
                    $planned[] = ['Salaries', mt_rand(120, 220) * 1000, 'bank', 'Staff salaries'];
                }
                if (($day->isTuesday() || $day->isFriday()) && mt_rand(1, 100) <= 70) {
                    $planned[] = ['Medical Supplies', mt_rand(3, 25) * 1000, 'cash', 'Consumables and medical supplies'];
                }
                if (mt_rand(1, 100) <= 6) {
                    $planned[] = ['Maintenance', mt_rand(2, 15) * 1000, 'cash', 'Equipment and premises maintenance'];
                }
                if (mt_rand(1, 100) <= 35) {
                    $planned[] = ['Miscellaneous', mt_rand(1, 10) * 500, 'cash', 'Petty cash - pantry and courier'];
                }
                if ($day->isMonday() && mt_rand(1, count($this->centres)) === 1) {
                    $planned[] = ['Marketing', mt_rand(10, 60) * 1000, 'bank', 'Social media campaign'];
                }

                foreach ($planned as [$categoryName, $amount, $method, $label]) {
                    $this->insertExpense($pools[$centreId], $categoryIds[$categoryName], $centre, $day, $amount, $method, $label.' - '.$centre['name']);
                }
            }
        }

        foreach ($this->centres as $centreId => $centre) {
            $pool = $pools[$centreId];
            if (! $pool['owned']) {
                continue;
            }
            $net = ($this->cashInByCentre[$centreId] ?? 0) - ($this->cashRefundByCentre[$centreId] ?? 0) - $pool['spent'];
            $opening = max(500000, 100000 - $net) + mt_rand(0, 8) * 50000;
            DB::table('cash_pools')->where('id', $pool['id'])->update([
                'opening_balance' => $opening,
                'cached_balance' => round($opening + $net, 2),
            ]);
        }
    }

    private function insertExpense(array &$pool, int $categoryId, array $centre, Carbon $day, int $amount, string $method, string $description): void
    {
        $recent = $day->diffInDays($this->today) <= 7;
        $status = $recent
            ? $this->weighted(['approved' => 70, 'pending' => 25, 'rejected' => 5])
            : $this->weighted(['approved' => 92, 'rejected' => 8]);
        $at = $this->randomMomentOn($day, 10 * 60, 18 * 60) ?: $this->now->copy();

        $this->insert('expenses', [
            'account_id' => $this->accountId,
            'expense_date' => $day->toDateString(),
            'amount' => $amount,
            'category_id' => $categoryId,
            'paid_from_pool_id' => $pool['id'],
            'for_branch_id' => $centre['id'],
            'payment_method_id' => $this->modes[$method],
            'description' => $description,
            'status' => $status,
            'verified_by' => $status === 'pending' ? null : $this->adminId,
            'rejection_reason' => $status === 'rejected' ? 'Receipt missing' : null,
            'is_flagged' => 0,
            'created_by' => $this->adminId,
            'is_for_general' => 0,
            'created_at' => $at->toDateTimeString(),
            'updated_at' => $at->toDateTimeString(),
            'system_created_at' => $at->toDateTimeString(),
        ]);

        if ($status !== 'rejected') {
            $pool['spent'] += $amount;
        }
    }

    private function insertAppointment(array $a): int
    {
        $slot = $a['slot'];

        return $this->insert('appointments', [
            'name' => $a['patient']['name'],
            'scheduled_date' => $slot->toDateString(),
            'scheduled_time' => $slot->format('H:i:s'),
            'scheduled_at_count' => 0,
            'first_scheduled_date' => $slot->toDateString(),
            'first_scheduled_time' => $slot->format('H:i:s'),
            'first_scheduled_count' => 1,
            'rescheduled_count' => 0,
            'send_message' => 1,
            'lead_id' => $a['patient']['lead_id'],
            'patient_id' => $a['patient']['id'],
            'doctor_id' => $a['doctor_id'],
            'region_id' => $a['centre']['region_id'],
            'city_id' => $a['centre']['city_id'],
            'location_id' => $a['centre']['id'],
            'base_appointment_status_id' => $a['status_id'],
            'appointment_status_id' => $a['status_id'],
            'appointment_status_allow_message' => 0,
            'service_id' => $a['service_id'],
            'resource_id' => $this->doctors[$a['doctor_id']]['resource_id'],
            'arrived_at' => $a['arrived_at'] ? $a['arrived_at']->toDateTimeString() : null,
            'converted_at' => $a['converted_at'] ? $a['converted_at']->toDateTimeString() : null,
            'appointment_type_id' => $a['type'],
            'created_by' => $a['created_by'],
            'updated_by' => $a['created_by'],
            'active' => 1,
            'msg_count' => 0,
            'counter' => 0,
            'consultancy_type' => $a['type'] === 1 ? 'in_person' : null,
            'account_id' => $this->accountId,
            'created_at' => $a['created_at']->toDateTimeString(),
            'updated_at' => $a['updated_at']->toDateTimeString(),
        ]);
    }

    private function insertInvoice(int $patientId, int $appointmentId, int $centreId, int $doctorId, float $price, int $serviceId, Carbon $at, ?int $packageId, ?int $packageServiceId): int
    {
        $time = $at->toDateTimeString();
        $taxExclusive = round($price / (1 + self::TAX_RATE / 100), 2);

        $invoiceId = $this->insert('invoices', [
            'total_price' => $price,
            'account_id' => $this->accountId,
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'invoice_status_id' => $this->paidInvoiceStatusId,
            'created_by' => $this->adminId,
            'location_id' => $centreId,
            'doctor_id' => $doctorId,
            'active' => 1,
            'is_exclusive' => 0,
            'package_id' => $packageId,
            'is_settlement' => 0,
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        $this->insert('invoice_details', [
            'qty' => 1,
            'service_price' => $price,
            'tax_exclusive_serviceprice' => $taxExclusive,
            'tax_percenatage' => self::TAX_RATE,
            'tax_price' => round($price - $taxExclusive, 2),
            'tax_including_price' => $price,
            'is_exclusive' => 0,
            'net_amount' => $price,
            'service_id' => $serviceId,
            'package_id' => $packageId,
            'invoice_id' => $invoiceId,
            'package_service_id' => $packageServiceId,
            'active' => 1,
            'is_settlement' => 0,
            'created_at' => $time,
            'updated_at' => $time,
        ]);

        return $invoiceId;
    }

    private function insertPayment(array $p): void
    {
        $time = $p['at']->toDateTimeString();
        $isRefund = (int) ($p['is_refund'] ?? 0);

        $this->insert('package_advances', [
            'cash_flow' => $p['cash_flow'],
            'cash_amount' => $p['amount'],
            'active' => 1,
            'is_refund' => $isRefund,
            'is_adjustment' => 0,
            'is_tax' => (int) ($p['is_tax'] ?? 0),
            'is_cancel' => 0,
            'patient_id' => $p['patient_id'],
            'payment_mode_id' => $this->modes[$p['mode']],
            'account_id' => $this->accountId,
            'appointment_type_id' => $p['appointment_type_id'] ?? null,
            'appointment_id' => $p['appointment_id'] ?? null,
            'location_id' => $p['centre_id'],
            'created_by' => $this->adminId,
            'updated_by' => $this->adminId,
            'package_id' => $p['package_id'] ?? null,
            'invoice_id' => $p['invoice_id'] ?? null,
            'is_setteled' => 0,
            'created_at' => $time,
            'updated_at' => $time,
            'system_created_at' => $time,
        ]);

        if ($p['mode'] === 'cash' && $p['cash_flow'] === 'in') {
            $this->cashInByCentre[$p['centre_id']] = ($this->cashInByCentre[$p['centre_id']] ?? 0) + $p['amount'];
        }
        if ($p['mode'] === 'cash' && $isRefund) {
            $this->cashRefundByCentre[$p['centre_id']] = ($this->cashRefundByCentre[$p['centre_id']] ?? 0) + $p['amount'];
        }
    }

    // Mirrors the two settle-mode "out" rows (net of tax, then tax) written when an invoice is raised.
    private function insertSettlement(float $price, int $patientId, int $centreId, int $appointmentId, int $typeId, int $invoiceId, ?int $packageId, Carbon $at): void
    {
        $tax = round($price - $price / (1 + self::TAX_RATE / 100), 2);
        foreach ([[$price - $tax, 0], [$tax, 1]] as [$amount, $isTax]) {
            $this->insertPayment([
                'cash_flow' => 'out', 'amount' => $amount, 'mode' => 'settle', 'is_tax' => $isTax, 'patient_id' => $patientId,
                'centre_id' => $centreId, 'appointment_id' => $appointmentId, 'appointment_type_id' => $typeId,
                'invoice_id' => $invoiceId, 'package_id' => $packageId, 'at' => $at,
            ]);
        }
    }

    private function insertActivity(array $a): void
    {
        $time = $a['at']->toDateTimeString();
        $this->insert('activities', [
            'plan_id' => $a['plan_id'] ?? null,
            'planId' => $a['plan_id'] ?? null,
            'appointment_id' => $a['appointment_id'] ?? null,
            'action' => $a['action'],
            'service' => $a['service'] ?? null,
            'service_id' => $a['service_id'] ?? null,
            'appointment_type' => $a['appointment_type'],
            'activity_type' => $a['activity_type'],
            'schedule_date' => $a['schedule_date'] ?? null,
            'patient' => $a['patient']['name'],
            'patient_id' => $a['patient']['id'],
            'created_by' => (string) $this->adminId,
            'invoice_id' => $a['invoice_id'] ?? null,
            'amount' => $a['amount'],
            'location' => $a['centre']['name'],
            'centre_id' => $a['centre']['id'],
            'description' => $a['description'],
            'account_id' => $this->accountId,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
    }

    private function insert(string $table, array $row): int
    {
        $id = (int) DB::table($table)->insertGetId($row);
        $this->manifest[$table][] = $id;

        return $id;
    }

    private function sessionsFor(string $categoryName): int
    {
        $name = strtolower($categoryName);
        if (strpos($name, 'laser') !== false) {
            return mt_rand(4, 8);
        }
        if (strpos($name, 'facial') !== false || strpos($name, 'peel') !== false || strpos($name, 'hair loss') !== false) {
            return mt_rand(3, 6);
        }

        return mt_rand(1, 4);
    }

    private function slotOn(Carbon $date, ?Carbon $notBefore): ?Carbon
    {
        $slots = [];
        for ($minute = 10 * 60; $minute <= 19 * 60 + 45; $minute += 15) {
            $slot = $date->copy()->startOfDay()->addMinutes($minute);
            if (! $notBefore || $slot->gte($notBefore)) {
                $slots[] = $slot;
            }
        }

        return $slots ? $slots[array_rand($slots)] : null;
    }

    private function randomMomentOn(Carbon $day, int $fromMinute, int $toMinute): ?Carbon
    {
        if ($day->isSameDay($this->now)) {
            $toMinute = min($toMinute, $this->now->hour * 60 + $this->now->minute - 1);
        }
        if ($toMinute < $fromMinute) {
            return null;
        }

        return $day->copy()->startOfDay()->addMinutes(mt_rand($fromMinute, $toMinute))->addSeconds(mt_rand(0, 59));
    }

    private function capToNow(Carbon $moment): Carbon
    {
        return $moment->gt($this->now) ? $this->now->copy() : $moment;
    }

    private function roundUp(float $amount): float
    {
        return ceil($amount / 500) * 500;
    }

    private function personName(?int $gender): string
    {
        $first = $gender === 1 ? self::MALE_NAMES : self::FEMALE_NAMES;

        return $first[array_rand($first)].' '.self::LAST_NAMES[array_rand(self::LAST_NAMES)];
    }

    private function nextPhone(): string
    {
        $this->phoneSeq++;

        return '399'.str_pad((string) $this->phoneSeq, 7, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int|string, int>  $weights
     * @return int|string
     */
    private function weighted(array $weights)
    {
        $roll = mt_rand(1, array_sum($weights));
        foreach ($weights as $key => $weight) {
            $roll -= $weight;
            if ($roll <= 0) {
                return $key;
            }
        }

        return array_key_first($weights);
    }

    private function printSummary(): void
    {
        $rows = [];
        foreach ($this->manifest as $table => $ids) {
            $rows[] = [$table, count($ids)];
        }
        $this->table(['Table', 'Rows created'], $rows);
        $this->info('Doctors: '.implode(', ', array_column($this->doctors, 'name')));
        $this->info('Centres: '.implode(', ', array_column($this->centres, 'name')));
        $this->line('Remove everything again with: php artisan dashboard:dummy-data --purge');
    }
}
