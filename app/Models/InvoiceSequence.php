<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceSequence extends Model
{
    protected $fillable = ['type', 'prefix', 'year', 'last_number'];

    protected $casts = ['year' => 'integer', 'last_number' => 'integer'];

    private static array $prefixes = [
        'quote' => 'DEV',
        'invoice' => 'FAC',
        'credit_note' => 'AVO',
    ];

    public static function nextNumber(string $type, ?int $year = null): string
    {
        $year ??= now()->year;
        $prefix = self::$prefixes[$type] ?? strtoupper(substr($type, 0, 3));

        return DB::transaction(function () use ($type, $year, $prefix): string {
            $seq = self::where('type', $type)->where('year', $year)->lockForUpdate()->first();

            if (! $seq) {
                $seq = self::create(['type' => $type, 'prefix' => $prefix, 'year' => $year, 'last_number' => 0]);
            }

            $seq->increment('last_number');
            $seq->refresh();

            return sprintf('%s-%04d-%04d', $prefix, $year, $seq->last_number);
        });
    }
}
