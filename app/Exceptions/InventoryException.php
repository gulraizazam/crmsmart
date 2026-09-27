<?php

namespace App\Exceptions;

use Exception;

class InventoryException extends Exception
{
    public static function documentNotDraft(string $documentNo): self
    {
        return new self("Document {$documentNo} is not in draft status.");
    }

    public static function documentNotPosted(string $documentNo): self
    {
        return new self("Document {$documentNo} is not posted.");
    }

    public static function documentHasNoLines(): self
    {
        return new self('Document has no lines to post.');
    }

    public static function insufficientStock(string $itemName, string $storeName, $available, $requested): self
    {
        return new self(
            "Insufficient stock for {$itemName} at {$storeName}. Available: {$available}, requested: {$requested}."
        );
    }

    public static function invalidQuantity(): self
    {
        return new self('Quantity must be greater than zero.');
    }

    public static function invalidDirection(string $direction): self
    {
        return new self("Invalid stock direction: {$direction}.");
    }

    public static function unitCostRequired(): self
    {
        return new self('Unit cost is required for stock-in movements.');
    }

    public static function purchaseOrderNotDraft(string $poNumber): self
    {
        return new self("Purchase order {$poNumber} is not in draft status.");
    }

    public static function purchaseOrderNotReceivable(string $poNumber): self
    {
        return new self("Purchase order {$poNumber} is not open for receiving.");
    }

    public static function overReceive(string $itemName, $remaining, $requested): self
    {
        return new self(
            "Cannot receive more than remaining for {$itemName}. Remaining: {$remaining}, requested: {$requested}."
        );
    }

    public static function transferNotDraft(string $transferNo): self
    {
        return new self("Transfer {$transferNo} is not in draft status.");
    }

    public static function transferNotDispatchable(string $transferNo): self
    {
        return new self("Transfer {$transferNo} is not ready to dispatch.");
    }

    public static function transferNotReceivable(string $transferNo): self
    {
        return new self("Transfer {$transferNo} is not in transit.");
    }

    public static function sameStoreTransfer(): self
    {
        return new self('Source and destination stores must be different.');
    }

    public static function adjustmentNotDraft(string $documentNo): self
    {
        return new self("Adjustment {$documentNo} is not in draft status.");
    }

    public static function adjustmentNotApproved(string $documentNo): self
    {
        return new self("Adjustment {$documentNo} must be approved before posting.");
    }

    public static function adjustmentApprovalRequired(float $threshold): self
    {
        return new self(
            'This adjustment exceeds the approval threshold ('.number_format($threshold, 2).') and needs elevated approval.'
        );
    }

    public static function invalidAdjustmentReason(): self
    {
        return new self('Invalid or inactive adjustment reason.');
    }

    public static function periodLocked(string $period): self
    {
        return new self("Inventory period {$period} is locked. Unlock it before posting or reversing documents in that month.");
    }
}
