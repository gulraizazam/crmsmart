<?php

namespace App\Services\Inventory;

use App\Exceptions\InventoryException;
use App\Models\Inventory\InvStockBalance;
use App\Models\Inventory\InvStockDocument;
use App\Models\Inventory\InvStockDocumentLine;

class OutboundService
{
    private StockEngine $stockEngine;

    public function __construct(StockEngine $stockEngine)
    {
        $this->stockEngine = $stockEngine;
    }

    /**
     * Post an outbound (sale/issue) or sales-return document.
     *
     * @param  array<int, array{item_id:int,store_id:int,quantity:float|int|string}>  $lines
     */
    public function post(
        int $accountId,
        string $documentType,
        string $documentDate,
        array $lines,
        ?string $notes = null,
        ?int $userId = null
    ): InvStockDocument {
        if (! in_array($documentType, [
            InvStockDocument::TYPE_SALE,
            InvStockDocument::TYPE_ISSUE,
            InvStockDocument::TYPE_SALE_RETURN,
        ], true)) {
            throw new InventoryException('Invalid outbound document type.');
        }

        if ($lines === []) {
            throw InventoryException::documentHasNoLines();
        }

        $direction = $documentType === InvStockDocument::TYPE_SALE_RETURN
            ? InvStockDocumentLine::DIRECTION_IN
            : InvStockDocumentLine::DIRECTION_OUT;

        $stockLines = [];
        foreach ($lines as $line) {
            $qty = (float) $line['quantity'];
            if ($qty <= 0) {
                throw InventoryException::invalidQuantity();
            }

            if ($direction === InvStockDocumentLine::DIRECTION_OUT) {
                $this->assertAvailable(
                    (int) $line['item_id'],
                    (int) $line['store_id'],
                    $qty
                );
            }

            $entry = [
                'item_id' => (int) $line['item_id'],
                'store_id' => (int) $line['store_id'],
                'direction' => $direction,
                'quantity' => $qty,
            ];

            if ($direction === InvStockDocumentLine::DIRECTION_IN) {
                if (array_key_exists('unit_cost', $line) && $line['unit_cost'] !== null && $line['unit_cost'] !== '') {
                    $entry['unit_cost'] = (float) $line['unit_cost'];
                } else {
                    $balance = InvStockBalance::where('item_id', $line['item_id'])
                        ->where('store_id', $line['store_id'])
                        ->first();
                    $entry['unit_cost'] = $balance ? (float) $balance->avg_cost : 0.0;
                }
            }

            $stockLines[] = $entry;
        }

        $document = $this->stockEngine->createDraft(
            $accountId,
            $documentType,
            $documentDate,
            $stockLines,
            $notes,
            $userId
        );

        return $this->stockEngine->post($document, $userId);
    }

    private function assertAvailable(int $itemId, int $storeId, float $qty): void
    {
        $balance = InvStockBalance::where('item_id', $itemId)->where('store_id', $storeId)->first();
        $available = $balance ? (float) $balance->quantity : 0.0;

        if ($available + 0.00005 < $qty) {
            $itemName = optional(\App\Models\Inventory\InvItem::find($itemId))->name ?? '#'.$itemId;
            $storeName = optional(\App\Models\Inventory\InvStore::find($storeId))->name ?? '#'.$storeId;
            throw InventoryException::insufficientStock($itemName, $storeName, $available, $qty);
        }
    }
}
