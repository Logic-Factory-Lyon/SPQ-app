<?php
namespace App\Services\Billing;

use App\Models\InvoiceSequence;

class InvoiceSequenceService
{
    public static function nextNumber(string $type, ?int $year = null): string
    {
        return InvoiceSequence::nextNumber($type, $year);
    }
}
