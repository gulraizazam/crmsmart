<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvPurchaseOrder;
use App\Models\Inventory\InvPurchaseOrderLine;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    private DocumentNumberService $documentNumbers;
    private StockEngine $stockEngine;

    public function __construct(DocumentNumberService $documentNumbers, StockEngine $stockEngine)
    {
        $this->documentNumbers = $documentNumbers;
        $this->stockEngine = $stockEngine;
    }

    /**
     * @param  array<int, array{item_id:int,store_id:int,quantity:float|int|string,unit_cost:float|int|string,notes?:string|null}>  $lines
     */
    public function createDraftPo(
        int $accountId,
        int $supplierId,
        string $orderDate,
        array $lines,
        ?string $expectedDate = null,
        ?string $notes = null,
        ?int $userId = null
    ): InvPurchaseOrder {
        return DB::transaction(function () use ($accountId, $supplierId, $orderDate, $lines, $expectedDate, $notes, $userId) {
            if ($lines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $po = InvPurchaseOrder::create([
                'account_id' => $accountId,
                'po_number' => $this->documentNumbers->next($accountId, DocumentNumberService::TYPE_PURCHASE_ORDER),
                'supplier_id' => $supplierId,
                'status' => InvPurchaseOrder::STATUS_DRAFT,
                'order_date' => $orderDate,
                'expected_date' => $expectedDate,
                'notes' => $notes,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach (array_values($lines) as $index => $line) {
                InvPurchaseOrderLine::create([
                    'purchase_order_id' => $po->id,
                    'line_no' => $index + 1,
                    'item_id' => $line['item_id'],
                    'store_id' => $line['store_id'],
                    'quantity_ordered' => $line['quantity'],
                    'quantity_received' => 0,
                    'unit_cost' => $line['unit_cost'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $po->fresh('lines');
        });
    }

    public function approve(InvPurchaseOrder $po, ?int $userId = null): InvPurchaseOrder
    {
        return DB::transaction(function () use ($po, $userId) {
            $po = InvPurchaseOrder::query()->where('id', $po->id)->lockForUpdate()->firstOrFail();

            if (! $po->isDraft()) {
                throw InventoryException::purchaseOrderNotDraft($po->po_number);
            }

            if ($po->lines()->count() === 0) {
                throw InventoryException::documentHasNoLines();
            }

            $po->status = InvPurchaseOrder::STATUS_ORDERED;
            $po->approved_at = now();
            $po->approved_by = $userId;
            $po->updated_by = $userId;
            $po->save();

            return $po->fresh('lines');
        });
    }

    public function cancel(InvPurchaseOrder $po, ?int $userId = null): InvPurchaseOrder
    {
        return DB::transaction(function () use ($po, $userId) {
            $po = InvPurchaseOrder::query()->where('id', $po->id)->lockForUpdate()->firstOrFail();

            if (! in_array($po->status, [InvPurchaseOrder::STATUS_DRAFT, InvPurchaseOrder::STATUS_ORDERED], true)) {
                throw new InventoryException("Purchase order {$po->po_number} cannot be cancelled.");
            }

            if ((float) $po->lines()->sum('quantity_received') > 0) {
                throw new InventoryException("Purchase order {$po->po_number} has receipts and cannot be cancelled.");
            }

            $po->status = InvPurchaseOrder::STATUS_CANCELLED;
            $po->updated_by = $userId;
            $po->save();

            return $po;
        });
    }

    /**
     * Receive against an ordered PO (partial OK). Posts GRN immediately.
     *
     * @param  array<int, array{purchase_order_line_id:int,store_id:int,quantity:float|int|string,unit_cost?:float|int|string|null}>  $receiveLines
     */
    public function receive(
        InvPurchaseOrder $po,
        string $documentDate,
        array $receiveLines,
        ?string $notes = null,
        ?int $userId = null
    ): InvStockDocument {
        return DB::transaction(function () use ($po, $documentDate, $receiveLines, $notes, $userId) {
            $po = InvPurchaseOrder::query()->where('id', $po->id)->lockForUpdate()->firstOrFail();
            $po->load('lines.item');

            if (! $po->canReceive()) {
                throw InventoryException::purchaseOrderNotReceivable($po->po_number);
            }

            if ($receiveLines === []) {
                throw InventoryException::documentHasNoLines();
            }

            $linesById = $po->lines->keyBy('id');
            $stockLines = [];

            foreach ($receiveLines as $row) {
                $poLineId = (int) $row['purchase_order_line_id'];
                /** @var InvPurchaseOrderLine|null $poLine */
                $poLine = $linesById->get($poLineId);
                if (! $poLine) {
                    throw new InventoryException('Invalid purchase order line.');
                }

                $qty = (float) $row['quantity'];
                if ($qty <= 0) {
                    throw InventoryException::invalidQuantity();
                }

                $remaining = $poLine->remainingQty();
                if ($qty > $remaining + 0.00005) {
                    $itemName = optional($poLine->item)->name ?? ('#'.$poLine->item_id);
                    throw InventoryException::overReceive($itemName, $remaining, $qty);
                }

                $unitCost = array_key_exists('unit_cost', $row) && $row['unit_cost'] !== null && $row['unit_cost'] !== ''
                    ? (float) $row['unit_cost']
                    : (float) $poLine->unit_cost;

                $stockLines[] = [
                    'item_id' => (int) $poLine->item_id,
                    'store_id' => (int) $row['store_id'],
                    'direction' => InvStockDocumentLine::DIRECTION_IN,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'purchase_order_line_id' => $poLine->id,
                ];
            }

            $document = $this->stockEngine->createDraft(
                (int) $po->account_id,
                InvStockDocument::TYPE_PURCHASE_RECEIPT,
                $documentDate,
                $stockLines,
                $notes ?: ('GRN for '.$po->po_number),
                $userId,
                [
                    'reference_type' => InvPurchaseOrder::REF_TYPE,
                    'reference_id' => $po->id,
                ]
            );

            $posted = $this->stockEngine->post($document, $userId);

            foreach ($receiveLines as $row) {
                $poLine = InvPurchaseOrderLine::query()
                    ->where('id', (int) $row['purchase_order_line_id'])
                    ->lockForUpdate()
                    ->firstOrFail();
                $poLine->quantity_received = round((float) $poLine->quantity_received + (float) $row['quantity'], 4);
                $poLine->save();
            }

            $freshLines = InvPurchaseOrderLine::where('purchase_order_id', $po->id)->get();
            $allDone = $freshLines->every(function (InvPurchaseOrderLine $line) {
                return $line->remainingQty() <= 0.00005;
            });

            if ($allDone) {
                $po->status = InvPurchaseOrder::STATUS_CLOSED;
                $po->updated_by = $userId;
                $po->save();
            }

            return $posted;
        });
    }
}
