<?php

namespace App\Services\Inventory;

use App\Models\Inventory\InvDocumentSequence;
use App\Models\Inventory\InvStockDocument;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public const TYPE_PURCHASE_ORDER = 'purchase_order';

    /** @var array<string, string> */
    private const PREFIXES = [
        InvStockDocument::TYPE_OPENING => 'OPN',
        InvStockDocument::TYPE_ADJUSTMENT => 'ADJ',
        InvStockDocument::TYPE_PURCHASE_RECEIPT => 'GRN',
        InvStockDocument::TYPE_PURCHASE_RETURN => 'PRT',
        InvStockDocument::TYPE_TRANSFER => 'TRF',
        InvStockDocument::TYPE_SALE => 'SAL',
        InvStockDocument::TYPE_ISSUE => 'ISS',
        InvStockDocument::TYPE_SALE_RETURN => 'SRT',
        InvStockDocument::TYPE_REVERSAL => 'REV',
        self::TYPE_PURCHASE_ORDER => 'PO',
    ];

    public function next(int $accountId, string $documentType, ?int $year = null): string
    {
        $year = $year ?? (int) date('Y');
        $prefix = self::PREFIXES[$documentType] ?? strtoupper(substr($documentType, 0, 3));

        return DB::transaction(function () use ($accountId, $documentType, $year, $prefix) {
            $sequence = InvDocumentSequence::query()
                ->where('account_id', $accountId)
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = InvDocumentSequence::create([
                    'account_id' => $accountId,
                    'document_type' => $documentType,
                    'year' => $year,
                    'prefix' => $prefix,
                    'last_number' => 0,
                ]);

                $sequence = InvDocumentSequence::query()
                    ->where('id', $sequence->id)
                    ->lockForUpdate()
                    ->first();
            }

            $sequence->last_number = (int) $sequence->last_number + 1;
            $sequence->save();

            return sprintf('%s-%d-%04d', $sequence->prefix, $year, $sequence->last_number);
        });
    }
}
