<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvPeriodLock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PeriodLockService
{
    public function assertNotLocked(int $accountId, $date): void
    {
        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
        if ($this->isLocked($accountId, (int) $carbon->year, (int) $carbon->month)) {
            throw InventoryException::periodLocked(
                sprintf('%04d-%02d', $carbon->year, $carbon->month)
            );
        }
    }

    public function isLocked(int $accountId, int $year, int $month): bool
    {
        $lock = InvPeriodLock::forAccount($accountId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->first();

        return $lock ? $lock->isLocked() : false;
    }

    public function lock(int $accountId, int $year, int $month, ?int $userId = null, ?string $notes = null): InvPeriodLock
    {
        return DB::transaction(function () use ($accountId, $year, $month, $userId, $notes) {
            $lock = InvPeriodLock::forAccount($accountId)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->lockForUpdate()
                ->first();

            if (! $lock) {
                $lock = InvPeriodLock::create([
                    'account_id' => $accountId,
                    'period_year' => $year,
                    'period_month' => $month,
                ]);
                $lock = InvPeriodLock::query()->where('id', $lock->id)->lockForUpdate()->firstOrFail();
            }

            if ($lock->isLocked()) {
                throw new InventoryException('Period '.$lock->label().' is already locked.');
            }

            $lock->locked_at = now();
            $lock->locked_by = $userId;
            $lock->unlocked_at = null;
            $lock->unlocked_by = null;
            $lock->notes = $notes;
            $lock->save();

            return $lock;
        });
    }

    public function unlock(int $accountId, int $year, int $month, ?int $userId = null, ?string $notes = null): InvPeriodLock
    {
        return DB::transaction(function () use ($accountId, $year, $month, $userId, $notes) {
            $lock = InvPeriodLock::forAccount($accountId)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->lockForUpdate()
                ->first();

            if (! $lock || ! $lock->isLocked()) {
                throw new InventoryException('Period is not locked.');
            }

            $lock->unlocked_at = now();
            $lock->unlocked_by = $userId;
            if ($notes) {
                $lock->notes = trim(($lock->notes ? $lock->notes.' | ' : '').$notes);
            }
            $lock->save();

            return $lock;
        });
    }
}
