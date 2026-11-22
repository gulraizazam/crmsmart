<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\HR;

use App\Helpers\Filters;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use App\Models\Designation;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Services\HR\HrNotificationService;
use App\Services\HR\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class LeaveApplicationController extends Controller
{
    use ApiResponse;

    protected const FILTER_KEY = 'hr_leave_applications';

    public function __construct(
        protected readonly LeaveService $leaveService,
        protected readonly HrNotificationService $notificationService,
    ) {}

    public function index(): View
    {
        $accountId = Auth::user()->account_id;

        $leaveTypes = LeaveType::active()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $designations = Designation::active()
            ->where('account_id', $accountId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $locations = \App\Models\Locations::where('account_id', $accountId)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.hr.leave.applications.index', compact('leaveTypes', 'designations', 'locations'));
    }

    public function datatable(Request $request): JsonResponse
    {
        try {
            $accountId = Auth::user()->account_id;
            $userId = Auth::id();
            $filters = getFilters($request->all());
            $applyFilter = checkFilters($filters, self::FILTER_KEY);

            $query = LeaveApplication::query()
                ->select([
                    'leave_applications.*',
                    'users.name as employee_name',
                    'leave_types.name as leave_type_name',
                    'designations.name as designation_name',
                ])
                ->join('users', 'leave_applications.user_id', '=', 'users.id')
                ->join('leave_types', 'leave_applications.leave_type_id', '=', 'leave_types.id')
                ->leftJoin('employee_details', 'users.id', '=', 'employee_details.user_id')
                ->leftJoin('designations', 'employee_details.designation_id', '=', 'designations.id')
                ->where('leave_applications.account_id', $accountId)
                ->whereNull('leave_applications.deleted_at');

            if (hasFilter($filters, 'status')) {
                $query->where('leave_applications.status', $filters['status']);
                Filters::put($userId, self::FILTER_KEY, 'status', $filters['status']);
            } elseif ($applyFilter) {
                Filters::forget($userId, self::FILTER_KEY, 'status');
            } else {
                $stored = Filters::get($userId, self::FILTER_KEY, 'status');
                if ($stored) {
                    $query->where('leave_applications.status', $stored);
                }
            }

            if (hasFilter($filters, 'leave_type_id')) {
                $query->where('leave_applications.leave_type_id', $filters['leave_type_id']);
                Filters::put($userId, self::FILTER_KEY, 'leave_type_id', $filters['leave_type_id']);
            } elseif ($applyFilter) {
                Filters::forget($userId, self::FILTER_KEY, 'leave_type_id');
            }

            if (hasFilter($filters, 'employee_name')) {
                $query->where('users.name', 'like', '%' . $filters['employee_name'] . '%');
                Filters::put($userId, self::FILTER_KEY, 'employee_name', $filters['employee_name']);
            } elseif ($applyFilter) {
                Filters::forget($userId, self::FILTER_KEY, 'employee_name');
            }

            if (hasFilter($filters, 'date_from') && hasFilter($filters, 'date_to')) {
                $query->whereBetween('leave_applications.start_date', [$filters['date_from'], $filters['date_to']]);
                Filters::put($userId, self::FILTER_KEY, 'date_from', $filters['date_from']);
                Filters::put($userId, self::FILTER_KEY, 'date_to', $filters['date_to']);
            } elseif ($applyFilter) {
                Filters::forget($userId, self::FILTER_KEY, 'date_from');
                Filters::forget($userId, self::FILTER_KEY, 'date_to');
            }

            $total = (clone $query)->count();

            [$orderBy, $order] = getSortBy($request);
            [$displayLength, $displayStart, $pages, $page] = getPaginationElement($request, $total);

            $sortColumn = match ($orderBy) {
                'name', 'employee_name' => 'users.name',
                'leave_type' => 'leave_types.name',
                'designation' => 'designations.name',
                'status' => 'leave_applications.status',
                'start_date' => 'leave_applications.start_date',
                'end_date' => 'leave_applications.end_date',
                'total_days' => 'leave_applications.total_days',
                default => 'leave_applications.created_at',
            };

            $records = $query
                ->limit($displayLength)
                ->offset($displayStart)
                ->orderBy($sortColumn, $order ?: 'desc')
                ->get();

            $data = $records->map(function ($app) {
                $hoursDisplay = null;
                $durationValue = $app->duration_type?->value;

                if ($durationValue === 'custom' && $app->custom_hours !== null) {
                    $hoursDisplay = rtrim(rtrim((string) $app->custom_hours, '0'), '.') . ' hrs';
                } elseif (in_array($durationValue, ['half', 'short'], true) && $app->shift_hours_snapshot) {
                    $hoursDisplay = rtrim(rtrim(number_format((float) $app->total_days * (float) $app->shift_hours_snapshot, 2), '0'), '.') . ' hrs';
                }

                return [
                    'id' => $app->id,
                    'employee_name' => $app->employee_name,
                    'leave_type' => $app->leave_type_name,
                    'start_date' => \Carbon\Carbon::parse($app->start_date)->format('d M Y'),
                    'end_date' => \Carbon\Carbon::parse($app->end_date)->format('d M Y'),
                    'total_days' => (float) $app->total_days,
                    'hours_display' => $hoursDisplay,
                    'duration_type' => $app->duration_type?->label(),
                    'status' => $app->status,
                    'applied_on' => $app->created_at->format('d M Y'),
                    'designation' => $app->designation_name ?? '—',
                ];
            });

            return response()->json([
                'data' => $data,
                'permissions' => [
                    'approve' => Gate::allows('hr_leave_approve'),
                ],
                'active_filters' => Filters::all($userId, self::FILTER_KEY),
                'filter_values' => [
                    'statuses' => ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'],
                ],
                'meta' => [
                    'field' => $orderBy,
                    'page' => $page,
                    'pages' => $pages,
                    'perpage' => $displayLength,
                    'total' => $total,
                    'sort' => $order,
                ],
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@datatable');
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $accountId = $user->account_id;

            $validated = $request->validate([
                'leave_type_id' => ['required', "exists:leave_types,id,account_id,{$accountId}"],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date', 'after_or_equal:start_date'],
                'duration_type' => ['required', 'in:full,half,short,custom'],
                'custom_hours' => ['nullable', 'required_if:duration_type,custom', 'numeric', 'gt:0'],
                'reason' => ['required', 'string', 'max:1000'],
            ]);

            // Half/Short/Custom are single-day — pin end_date to start_date.
            if (in_array($validated['duration_type'], ['half', 'short', 'custom'], true)) {
                $validated['end_date'] = $validated['start_date'];
            }

            if ($validated['duration_type'] === 'custom') {
                $shiftHours = $this->leaveService->resolveShiftHours($user->id);
                if ($shiftHours === null) {
                    return $this->errorResponse('By-hours leave requires a shift duration on the employee profile.');
                }
                if ((float) $validated['custom_hours'] >= $shiftHours) {
                    return $this->errorResponse('Hours must be less than shift hours (' . $shiftHours . '). Use Full Day instead.');
                }
            }

            // Check overlap
            if ($this->leaveService->hasOverlap($user->id, $validated['start_date'], $validated['end_date'])) {
                return $this->errorResponse('You already have a leave application for these dates.');
            }

            $application = $this->leaveService->apply($validated, $user->id, $user->account_id);

            try {
                $this->notificationService->onLeaveSubmitted($application);
            } catch (\Exception) {
                // Notification failure should not block the operation
            }

            // Return balance warning if applicable
            $warning = $this->leaveService->getBalanceWarning(
                $user->id,
                (int) $validated['leave_type_id'],
                (float) $application->total_days
            );

            $message = 'Leave application submitted successfully.';
            if ($warning) {
                $message .= ' Warning: ' . $warning;
            }

            return $this->successResponse($message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@store');
        }
    }

    public function approve(Request $request, LeaveApplication $leaveApplication): JsonResponse
    {
        try {
            if ($leaveApplication->status->value !== 'pending') {
                return $this->errorResponse('Only pending applications can be approved.');
            }

            $this->leaveService->approve(
                $leaveApplication,
                $request->input('review_notes'),
                Auth::id()
            );

            try {
                $this->notificationService->onLeaveApproved($leaveApplication);
            } catch (\Exception) {
                // Notification failure should not block the operation
            }

            return $this->successResponse('Leave application approved.');
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@approve');
        }
    }

    public function reject(Request $request, LeaveApplication $leaveApplication): JsonResponse
    {
        try {
            if ($leaveApplication->status->value !== 'pending') {
                return $this->errorResponse('Only pending applications can be rejected.');
            }

            $this->leaveService->reject(
                $leaveApplication,
                $request->input('review_notes'),
                Auth::id()
            );

            try {
                $this->notificationService->onLeaveRejected($leaveApplication);
            } catch (\Exception) {
                // Notification failure should not block the operation
            }

            return $this->successResponse('Leave application rejected.');
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@reject');
        }
    }

    public function cancel(Request $request, LeaveApplication $leaveApplication): JsonResponse
    {
        try {
            $status = $leaveApplication->status->value;

            if ($status === 'approved') {
                if (!Gate::allows('hr_leave_approve')) {
                    return $this->errorResponse('Only HR managers can cancel approved leaves.', 403);
                }
            } elseif ($status === 'pending') {
                $isOwner = (int) $leaveApplication->user_id === (int) Auth::id();
                if (!$isOwner && !Gate::allows('hr_leave_approve')) {
                    return $this->errorResponse('You can only cancel your own leave applications.', 403);
                }
            } else {
                return $this->errorResponse('Only pending or approved applications can be cancelled.');
            }

            $this->leaveService->cancel($leaveApplication);

            try {
                $this->notificationService->onLeaveCancelled($leaveApplication);
            } catch (\Exception) {
                // Notification failure should not block the operation
            }

            $msg = $status === 'approved'
                ? 'Approved leave cancelled and balance restored.'
                : 'Leave application cancelled.';

            return $this->successResponse($msg);
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@cancel');
        }
    }

    public function calendar(): View
    {
        return view('admin.hr.leave.calendar');
    }

    public function calendarData(Request $request): JsonResponse
    {
        try {
            $accountId = Auth::user()->account_id;
            $month = $request->get('month', now()->month);
            $year = $request->get('year', now()->year);

            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $applications = LeaveApplication::with(['user', 'leaveType'])
                ->where('account_id', $accountId)
                ->whereIn('status', ['pending', 'approved'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('start_date', [$startDate, $endDate])
                      ->orWhereBetween('end_date', [$startDate, $endDate])
                      ->orWhere(function ($q2) use ($startDate, $endDate) {
                          $q2->where('start_date', '<=', $startDate)
                             ->where('end_date', '>=', $endDate);
                      });
                })
                ->get();

            $items = $applications->map(fn (LeaveApplication $app) => [
                'id' => $app->id,
                'group' => $app->user_id,
                'content' => $app->leaveType->name . ' (' . $app->total_days . 'd)',
                'start' => $app->start_date->format('Y-m-d'),
                'end' => $app->end_date->addDay()->format('Y-m-d'),
                'className' => $app->status->value === 'approved' ? 'bg-success' : 'bg-warning',
                'title' => $app->user->name . ': ' . $app->leaveType->name . ' (' . $app->status->value . ')',
            ]);

            $groups = $applications->pluck('user')->unique('id')->map(fn ($u) => [
                'id' => $u->id,
                'content' => $u->name,
            ])->values();

            return $this->successResponse('OK', [
                'items' => $items,
                'groups' => $groups,
            ]);
        } catch (\Exception $e) {
            return $this->handleException($e, 'LeaveApplicationController@calendarData');
        }
    }
}
