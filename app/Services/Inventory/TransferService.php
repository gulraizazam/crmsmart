<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use App\Models\Inventory\InvTransfer;
use App\Models\Inventory\InvTransferLine;
use Illuminate\Support\Facades\DB;

class TransferService
{
    private DocumentNumberService $documentNumbers;
    private StockEngine $stockEngine;

    public function __construct(DocumentNumberService $documentNumbers, StockEngine $stockEngine)
    {
        $this->documentNumbers = $documentNumbers;
        $this->stockEngine = $stockEngine;
    }

    /**
     * @param  array<int, array{item_id:int,quantity:float|int|string,notes?:string|null}>  $lines
     */
    public function createDraft(
        int $accountId,
        int $fromStoreId,
        int $toStoreId,
        string $transferDate,
        array $lines,
        ?string $notes = null,
        ?int $userId = null
    ): InvTransfer {
        return DB::transaction(function () use ($accountId, $fromStoreId, $toStoreId, $transferDate, $lines, $notes, $userId) {
            if ($fromStoreId === $toStoreId) {
                throw InventoryException::sameStoreTransfer();
            }
            if ($lines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $transfer = InvTransfer::create([
                'account_id' => $accountId,
                'transfer_no' => $this->documentNumbers->next($accountId, InvStockDocument::TYPE_TRANSFER),
                'from_store_id' => $fromStoreId,
                'to_store_id' => $toStoreId,
                'status' => InvTransfer::STATUS_DRAFT,
                'transfer_date' => $transferDate,
                'notes' => $notes,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach (array_values($lines) as $index => $line) {
                InvTransferLine::create([
                    'transfer_id' => $transfer->id,
                    'line_no' => $index + 1,
                    'item_id' => $line['item_id'],
                    'quantity_requested' => $line['quantity'],
                    'quantity_dispatched' => 0,
                    'quantity_received' => 0,
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $transfer->fresh('lines');
        });
    }

    public function approve(InvTransfer $transfer, ?int $userId = null): InvTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer = InvTransfer::query()->where('id', $transfer->id)->lockForUpdate()->firstOrFail();

            if (! $transfer->isDraft()) {
                throw InventoryException::transferNotDraft($transfer->transfer_no);
            }
            if ($transfer->lines()->count() === 0) {
                throw InventoryException::documentHasNoLines();
            }

            $transfer->status = InvTransfer::STATUS_APPROVED;
            $transfer->approved_at = now();
            $transfer->approved_by = $userId;
            $transfer->updated_by = $userId;
            $transfer->save();

            return $transfer->fresh('lines');
        });
    }

    public function dispatch(InvTransfer $transfer, ?string $documentDate = null, ?int $userId = null): InvTransfer
    {
        return DB::transaction(function () use ($transfer, $documentDate, $userId) {
            $transfer = InvTransfer::query()->where('id', $transfer->id)->lockForUpdate()->firstOrFail();
            $transfer->load(['lines.item', 'fromStore']);

            if (! $transfer->canDispatch()) {
                throw InventoryException::transferNotDispatchable($transfer->transfer_no);
            }

            $stockLines = [];
            foreach ($transfer->lines as $line) {
                $qty = (float) $line->quantity_requested;
                if ($qty <= 0) {
                    throw InventoryException::invalidQuantity();
                }

                $balance = InvStockBalance::where('item_id', $line->item_id)
                    ->where('store_id', $transfer->from_store_id)
                    ->first();
                $avg = $balance ? (float) $balance->avg_cost : 0.0;

                $stockLines[] = [
                    'item_id' => (int) $line->item_id,
                    'store_id' => (int) $transfer->from_store_id,
                    'direction' => InvStockDocumentLine::DIRECTION_OUT,
                    'quantity' => $qty,
                    'unit_cost' => $avg,
                    'transfer_line_id' => $line->id,
                ];
            }

            $document = $this->stockEngine->createDraft(
                (int) $transfer->account_id,
                InvStockDocument::TYPE_TRANSFER,
                $documentDate ?: ($transfer->transfer_date ? $transfer->transfer_date->toDateString() : date('Y-m-d')),
                $stockLines,
                'Dispatch '.$transfer->transfer_no,
                $userId,
                [
                    'reference_type' => InvTransfer::REF_TYPE,
                    'reference_id' => $transfer->id,
                ]
            );

            $posted = $this->stockEngine->post($document, $userId);
            $posted->load('lines');

            foreach ($posted->lines as $docLine) {
                if (! $docLine->transfer_line_id) {
                    continue;
                }
                $tLine = InvTransferLine::query()
                    ->where('id', $docLine->transfer_line_id)
                    ->lockForUpdate()
                    ->first();
                if ($tLine) {
                    $tLine->quantity_dispatched = round((float) $docLine->quantity, 4);
                    $tLine->unit_cost = $docLine->unit_cost;
                    $tLine->save();
                }
            }

            $transfer->status = InvTransfer::STATUS_IN_TRANSIT;
            $transfer->dispatched_at = now();
            $transfer->dispatched_by = $userId;
            $transfer->dispatch_document_id = $posted->id;
            $transfer->updated_by = $userId;
            $transfer->save();

            return $transfer->fresh(['lines', 'dispatchDocument']);
        });
    }

    /**
     * @param  array<int, array{transfer_line_id:int,quantity:float|int|string}>  $receiveLines
     */
    public function receive(
        InvTransfer $transfer,
        string $documentDate,
        array $receiveLines,
        ?string $notes = null,
        ?int $userId = null
    ): InvStockDocument {
        return DB::transaction(function () use ($transfer, $documentDate, $receiveLines, $notes, $userId) {
            $transfer = InvTransfer::query()->where('id', $transfer->id)->lockForUpdate()->firstOrFail();
            $transfer->load('lines.item');

            if (! $transfer->canReceive()) {
                throw InventoryException::transferNotReceivable($transfer->transfer_no);
            }
            if ($receiveLines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $linesById = $transfer->lines->keyBy('id');
            $stockLines = [];

            foreach ($receiveLines as $row) {
                $lineId = (int) $row['transfer_line_id'];
                /** @var InvTransferLine|null $tLine */
                $tLine = $linesById->get($lineId);
                if (! $tLine) {
                    throw new InventoryException('Invalid transfer line.');
                }

                $qty = (float) $row['quantity'];
                if ($qty <= 0) {
                    throw InventoryException::invalidQuantity();
                }

                $remaining = $tLine->remainingToReceive();
                if ($qty > $remaining + 0.00005) {
                    $itemName = optional($tLine->item)->name ?? ('#'.$tLine->item_id);
                    throw InventoryException::overReceive($itemName, $remaining, $qty);
                }

                $stockLines[] = [
                    'item_id' => (int) $tLine->item_id,
                    'store_id' => (int) $transfer->to_store_id,
                    'direction' => InvStockDocumentLine::DIRECTION_IN,
                    'quantity' => $qty,
                    'unit_cost' => (float) ($tLine->unit_cost ?? 0),
                    'transfer_line_id' => $tLine->id,
                ];
            }

            $document = $this->stockEngine->createDraft(
                (int) $transfer->account_id,
                InvStockDocument::TYPE_TRANSFER,
                $documentDate,
                $stockLines,
                $notes ?: ('Receive '.$transfer->transfer_no),
                $userId,
                [
                    'reference_type' => InvTransfer::REF_TYPE,
                    'reference_id' => $transfer->id,
                ]
            );

            $posted = $this->stockEngine->post($document, $userId);

            foreach ($receiveLines as $row) {
                $tLine = InvTransferLine::query()
                    ->where('id', (int) $row['transfer_line_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $tLine->quantity_received = round((float) $tLine->quantity_received + (float) $row['quantity'], 4);
                $tLine->save();
            }

            $freshLines = InvTransferLine::where('transfer_id', $transfer->id)->get();
            $allDone = $freshLines->every(function (InvTransferLine $line) {
                return $line->remainingToReceive() <= 0.00005;
            });

            if ($allDone) {
                $transfer->status = InvTransfer::STATUS_COMPLETED;
                $transfer->received_at = now();
                $transfer->received_by = $userId;
            }
            $transfer->updated_by = $userId;
            $transfer->save();

            return $posted;
        });
    }

    public function cancel(InvTransfer $transfer, ?int $userId = null): InvTransfer
    {
        return DB::transaction(function () use ($transfer, $userId) {
            $transfer = InvTransfer::query()->where('id', $transfer->id)->lockForUpdate()->firstOrFail();
            $transfer->load('lines');

            if (in_array($transfer->status, [InvTransfer::STATUS_COMPLETED, InvTransfer::STATUS_CANCELLED], true)) {
                throw new InventoryException("Transfer {$transfer->transfer_no} cannot be cancelled.");
            }

            if ($transfer->isInTransit()) {
                $received = (float) $transfer->lines->sum('quantity_received');
                if ($received > 0.00005) {
                    throw new InventoryException("Transfer {$transfer->transfer_no} has receipts and cannot be cancelled.");
                }
                if ($transfer->dispatch_document_id) {
                    $doc = InvStockDocument::find($transfer->dispatch_document_id);
                    if ($doc && $doc->isPosted()) {
                        $this->stockEngine->reverse($doc, 'Cancel transfer '.$transfer->transfer_no, $userId);
                    }
                }
            }

            $transfer->status = InvTransfer::STATUS_CANCELLED;
            $transfer->updated_by = $userId;
            $transfer->save();

            return $transfer;
        });
    }
}
