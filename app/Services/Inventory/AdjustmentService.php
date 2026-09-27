<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvAdjustmentReason;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AdjustmentService
{
    /** Absolute value above this requires inv_erp_manage to approve. */
    public const APPROVAL_THRESHOLD = 10000.0;

    private StockEngine $stockEngine;

    public function __construct(StockEngine $stockEngine)
    {
        $this->stockEngine = $stockEngine;
    }

    /**
     * @param  array<int, array{item_id:int,store_id:int,direction:string,quantity:float|int|string,unit_cost?:float|int|string|null,notes?:string|null}>  $lines
     */
    public function createDraft(
        int $accountId,
        int $reasonId,
        string $documentDate,
        array $lines,
        ?string $notes = null,
        ?int $userId = null
    ): InvStockDocument {
        return DB::transaction(function () use ($accountId, $reasonId, $documentDate, $lines, $notes, $userId) {
            $reason = InvAdjustmentReason::forAccount($accountId)->where('active', true)->find($reasonId);
            if (! $reason) {
                throw InventoryException::invalidAdjustmentReason();
            }

            if ($lines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $stockLines = [];
            foreach ($lines as $line) {
                $direction = $line['direction'];
                if (! $reason->allowsDirection($direction)) {
                    throw new InventoryException("Reason \"{$reason->name}\" does not allow {$direction} adjustments.");
                }

                $qty = (float) $line['quantity'];
                if ($qty <= 0) {
                    throw InventoryException::invalidQuantity();
                }

                $unitCost = $line['unit_cost'] ?? null;
                if ($direction === InvStockDocumentLine::DIRECTION_OUT) {
                    $balance = InvStockBalance::where('item_id', $line['item_id'])
                        ->where('store_id', $line['store_id'])
                        ->first();
                    $available = $balance ? (float) $balance->quantity : 0.0;
                    if ($available + 0.00005 < $qty) {
                        throw InventoryException::insufficientStock(
                            '#'.$line['item_id'],
                            '#'.$line['store_id'],
                            $available,
                            $qty
                        );
                    }
                    $unitCost = $balance ? (float) $balance->avg_cost : 0.0;
                } elseif ($unitCost === null || $unitCost === '') {
                    throw InventoryException::unitCostRequired();
                }

                $stockLines[] = [
                    'item_id' => (int) $line['item_id'],
                    'store_id' => (int) $line['store_id'],
                    'direction' => $direction,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'notes' => $line['notes'] ?? null,
                ];
            }

            $document = $this->stockEngine->createDraft(
                $accountId,
                InvStockDocument::TYPE_ADJUSTMENT,
                $documentDate,
                $stockLines,
                $notes,
                $userId
            );

            $document->adjustment_reason_id = $reason->id;
            $document->save();

            return $document->fresh(['lines', 'adjustmentReason']);
        });
    }

    public function approve(InvStockDocument $document, ?int $userId = null, bool $elevated = false): InvStockDocument
    {
        return DB::transaction(function () use ($document, $userId, $elevated) {
            $document = InvStockDocument::query()->where('id', $document->id)->lockForUpdate()->firstOrFail();
            $document->load('lines');

            if ($document->document_type !== InvStockDocument::TYPE_ADJUSTMENT || ! $document->isDraft()) {
                throw InventoryException::adjustmentNotDraft($document->document_no);
            }

            $value = $document->absoluteValue();
            if ($value > self::APPROVAL_THRESHOLD && ! $elevated) {
                throw InventoryException::adjustmentApprovalRequired(self::APPROVAL_THRESHOLD);
            }

            $document->approved_at = now();
            $document->approved_by = $userId;
            $document->updated_by = $userId;
            $document->save();

            return $document->fresh(['lines', 'adjustmentReason']);
        });
    }

    public function post(InvStockDocument $document, ?int $userId = null): InvStockDocument
    {
        $document = $document->fresh('lines');

        if ($document->document_type !== InvStockDocument::TYPE_ADJUSTMENT || ! $document->isDraft()) {
            throw InventoryException::adjustmentNotDraft($document->document_no);
        }

        if (! $document->isApproved()) {
            throw InventoryException::adjustmentNotApproved($document->document_no);
        }

        return $this->stockEngine->post($document, $userId);
    }

    /**
     * Create count-variance draft from system vs counted quantities.
     *
     * @param  array<int, array{item_id:int,counted_qty:float|int|string}>  $counts
     */
    public function createFromCycleCount(
        int $accountId,
        int $storeId,
        string $documentDate,
        array $counts,
        ?int $userId = null
    ): InvStockDocument {
        $reason = InvAdjustmentReason::forAccount($accountId)
            ->where('code', 'count_variance')
            ->where('active', true)
            ->first();

        if (! $reason) {
            throw InventoryException::invalidAdjustmentReason();
        }

        $lines = [];
        foreach ($counts as $row) {
            $itemId = (int) $row['item_id'];
            $counted = (float) $row['counted_qty'];
            $balance = InvStockBalance::where('item_id', $itemId)->where('store_id', $storeId)->first();
            $system = $balance ? (float) $balance->quantity : 0.0;
            $diff = round($counted - $system, 4);

            if (abs($diff) < 0.00005) {
                continue;
            }

            if ($diff > 0) {
                $lines[] = [
                    'item_id' => $itemId,
                    'store_id' => $storeId,
                    'direction' => InvStockDocumentLine::DIRECTION_IN,
                    'quantity' => abs($diff),
                    'unit_cost' => $balance ? (float) $balance->avg_cost : 0.0,
                    'notes' => 'System '.$system.' → Counted '.$counted,
                ];
            } else {
                $lines[] = [
                    'item_id' => $itemId,
                    'store_id' => $storeId,
                    'direction' => InvStockDocumentLine::DIRECTION_OUT,
                    'quantity' => abs($diff),
                    'notes' => 'System '.$system.' → Counted '.$counted,
                ];
            }
        }

        if ($lines === []) {
            throw new InventoryException('No variances found — counted quantities match system.');
        }

        return $this->createDraft(
            $accountId,
            (int) $reason->id,
            $documentDate,
            $lines,
            'Cycle count store #'.$storeId,
            $userId
        );
    }

    public function needsElevatedApproval(InvStockDocument $document): bool
    {
        $document->loadMissing('lines');

        return $document->absoluteValue() > self::APPROVAL_THRESHOLD;
    }

    public function userCanApprove(InvStockDocument $document): bool
    {
        if (! Gate::allows('inv_adjust_manage') && ! Gate::allows('inv_erp_manage')) {
            return false;
        }

        if ($this->needsElevatedApproval($document)) {
            return Gate::allows('inv_erp_manage');
        }

        return Gate::allows('inv_adjust_manage') || Gate::allows('inv_erp_manage');
    }
}
