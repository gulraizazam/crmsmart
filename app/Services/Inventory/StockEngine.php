<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use App\Models\Inventory\InvStockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Phase 0 Stock Engine.
 * Only posted documents change stock. Quantity is never edited directly.
 */
class StockEngine
{
    private DocumentNumberService $documentNumbers;
    private PeriodLockService $periodLocks;

    /** When true, OUT cannot exceed available qty. */
    private bool $blockNegativeStock = true;

    public function __construct(DocumentNumberService $documentNumbers, PeriodLockService $periodLocks)
    {
        $this->documentNumbers = $documentNumbers;
        $this->periodLocks = $periodLocks;
    }

    public function allowNegativeStock(bool $allow = true): self
    {
        $this->blockNegativeStock = ! $allow;

        return $this;
    }

    /**
     * Post a draft document: write immutable movements and update balances.
     */
    public function post(InvStockDocument $document, ?int $userId = null): InvStockDocument
    {
        return DB::transaction(function () use ($document, $userId) {
            /** @var InvStockDocument $document */
            $document = InvStockDocument::query()
                ->where('id', $document->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $document->isDraft()) {
                throw InventoryException::documentNotDraft($document->document_no);
            }

            $document->load(['lines.item', 'lines.store']);

            if ($document->lines->isEmpty()) {
                throw InventoryException::documentHasNoLines();
            }

            $this->periodLocks->assertNotLocked((int) $document->account_id, $document->document_date);

            $movedAt = Carbon::parse($document->document_date)->endOfDay();
            if ($movedAt->isFuture()) {
                $movedAt = now();
            }

            foreach ($document->lines as $line) {
                $this->assertLineValid($line);
                $this->applyLine($document, $line, $movedAt, $userId);
            }

            $document->status = InvStockDocument::STATUS_POSTED;
            $document->posted_at = now();
            $document->posted_by = $userId;
            $document->updated_by = $userId;
            $document->save();

            return $document->fresh(['lines', 'movements']);
        });
    }

    /**
     * Reverse a posted document by creating and posting a reversing document.
     */
    public function reverse(InvStockDocument $document, ?string $reason = null, ?int $userId = null): InvStockDocument
    {
        return DB::transaction(function () use ($document, $reason, $userId) {
            /** @var InvStockDocument $document */
            $document = InvStockDocument::query()
                ->where('id', $document->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! $document->isPosted()) {
                throw InventoryException::documentNotPosted($document->document_no);
            }

            $this->periodLocks->assertNotLocked((int) $document->account_id, $document->document_date);
            $this->periodLocks->assertNotLocked((int) $document->account_id, now());

            $document->load('lines');

            $reversal = InvStockDocument::create([
                'account_id' => $document->account_id,
                'document_no' => $this->documentNumbers->next(
                    (int) $document->account_id,
                    InvStockDocument::TYPE_REVERSAL
                ),
                'document_type' => InvStockDocument::TYPE_REVERSAL,
                'status' => InvStockDocument::STATUS_DRAFT,
                'document_date' => now()->toDateString(),
                'notes' => trim(($reason ? $reason.' | ' : '').'Reversal of '.$document->document_no),
                'reversal_of_id' => $document->id,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($document->lines as $line) {
                InvStockDocumentLine::create([
                    'document_id' => $reversal->id,
                    'line_no' => $line->line_no,
                    'item_id' => $line->item_id,
                    'store_id' => $line->store_id,
                    'direction' => $line->direction === InvStockDocumentLine::DIRECTION_IN
                        ? InvStockDocumentLine::DIRECTION_OUT
                        : InvStockDocumentLine::DIRECTION_IN,
                    'quantity' => $line->quantity,
                    'unit_cost' => $line->unit_cost,
                    'notes' => $line->notes,
                ]);
            }

            $postedReversal = $this->post($reversal->fresh('lines'), $userId);

            $document->status = InvStockDocument::STATUS_REVERSED;
            $document->reversed_at = now();
            $document->reversed_by = $userId;
            $document->updated_by = $userId;
            $document->save();

            return $postedReversal;
        });
    }

    /**
     * Helper: create a draft document with lines (used by later phases / tests).
     *
     * @param  array<int, array{item_id:int,store_id:int,direction:string,quantity:float|int|string,unit_cost?:float|int|string|null,notes?:string|null}>  $lines
     */
    /**
     * @param  array{reference_type?:string|null,reference_id?:int|null}|null  $meta
     */
    public function createDraft(
        int $accountId,
        string $documentType,
        string $documentDate,
        array $lines,
        ?string $notes = null,
        ?int $userId = null,
        ?array $meta = null
    ): InvStockDocument {
        return DB::transaction(function () use ($accountId, $documentType, $documentDate, $lines, $notes, $userId, $meta) {
            if ($lines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $document = InvStockDocument::create([
                'account_id' => $accountId,
                'document_no' => $this->documentNumbers->next($accountId, $documentType),
                'document_type' => $documentType,
                'status' => InvStockDocument::STATUS_DRAFT,
                'document_date' => $documentDate,
                'notes' => $notes,
                'reference_type' => $meta['reference_type'] ?? null,
                'reference_id' => $meta['reference_id'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach (array_values($lines) as $index => $line) {
                InvStockDocumentLine::create([
                    'document_id' => $document->id,
                    'line_no' => $index + 1,
                    'item_id' => $line['item_id'],
                    'store_id' => $line['store_id'],
                    'direction' => $line['direction'],
                    'quantity' => $line['quantity'],
                    'unit_cost' => $line['unit_cost'] ?? null,
                    'notes' => $line['notes'] ?? null,
                    'purchase_order_line_id' => $line['purchase_order_line_id'] ?? null,
                    'transfer_line_id' => $line['transfer_line_id'] ?? null,
                ]);
            }

            return $document->fresh('lines');
        });
    }

    private function assertLineValid(InvStockDocumentLine $line): void
    {
        if ((float) $line->quantity <= 0) {
            throw InventoryException::invalidQuantity();
        }

        if (! in_array($line->direction, [
            InvStockDocumentLine::DIRECTION_IN,
            InvStockDocumentLine::DIRECTION_OUT,
        ], true)) {
            throw InventoryException::invalidDirection((string) $line->direction);
        }

        if ($line->direction === InvStockDocumentLine::DIRECTION_IN && $line->unit_cost === null) {
            throw InventoryException::unitCostRequired();
        }
    }

    private function applyLine(
        InvStockDocument $document,
        InvStockDocumentLine $line,
        Carbon $movedAt,
        ?int $userId
    ): void {
        $balance = InvStockBalance::query()
            ->where('item_id', $line->item_id)
            ->where('store_id', $line->store_id)
            ->lockForUpdate()
            ->first();

        if (! $balance) {
            $balance = InvStockBalance::create([
                'account_id' => $document->account_id,
                'item_id' => $line->item_id,
                'store_id' => $line->store_id,
                'quantity' => 0,
                'avg_cost' => 0,
            ]);

            $balance = InvStockBalance::query()
                ->where('id', $balance->id)
                ->lockForUpdate()
                ->firstOrFail();
        }

        $qty = (float) $line->quantity;
        $oldQty = (float) $balance->quantity;
        $oldAvg = (float) $balance->avg_cost;

        if ($line->direction === InvStockDocumentLine::DIRECTION_IN) {
            $unitCost = (float) $line->unit_cost;
            $newQty = $oldQty + $qty;
            $newAvg = $newQty > 0
                ? (($oldQty * $oldAvg) + ($qty * $unitCost)) / $newQty
                : 0.0;
        } else {
            if ($this->blockNegativeStock && $oldQty + 0.00005 < $qty) {
                $itemName = optional($line->item)->name ?? ('#'.$line->item_id);
                $storeName = optional($line->store)->name ?? ('#'.$line->store_id);
                throw InventoryException::insufficientStock($itemName, $storeName, $oldQty, $qty);
            }

            $unitCost = $oldAvg;
            $newQty = $oldQty - $qty;
            $newAvg = $newQty > 0 ? $oldAvg : 0.0;

            // Keep cost on the line for COGS traceability.
            if ($line->unit_cost === null) {
                $line->unit_cost = round($unitCost, 4);
                $line->save();
            }
        }

        $balance->quantity = round($newQty, 4);
        $balance->avg_cost = round($newAvg, 4);
        $balance->save();

        InvStockMovement::create([
            'account_id' => $document->account_id,
            'document_id' => $document->id,
            'document_line_id' => $line->id,
            'item_id' => $line->item_id,
            'store_id' => $line->store_id,
            'direction' => $line->direction,
            'quantity' => round($qty, 4),
            'unit_cost' => round($unitCost, 4),
            'balance_qty_after' => $balance->quantity,
            'avg_cost_after' => $balance->avg_cost,
            'moved_at' => $movedAt,
            'created_by' => $userId,
        ]);
    }
}
