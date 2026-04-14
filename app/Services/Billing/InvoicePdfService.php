<?php
namespace App\Services\Billing;

use App\Models\Client;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    private array $typeToView = [
        'invoice'     => 'pdf.invoice',
        'quote'       => 'pdf.quote',
        'credit_note' => 'pdf.credit-note',
    ];

    private array $typeToFolder = [
        'invoice'     => 'invoices',
        'quote'       => 'quotes',
        'credit_note' => 'credit-notes',
    ];

    public function generate(string $type, Model $document): string
    {
        $document->loadMissing(['client', 'lines.vatRate', 'creator']);

        $view  = $this->typeToView[$type];
        $folder = $this->typeToFolder[$type];

        $html = view($view, ['document' => $document])->render();

        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false);

        $filename = "billing/{$folder}/{$document->number}.pdf";
        Storage::put($filename, $pdf->output());

        $document->update(['pdf_path' => $filename]);

        return $filename;
    }

    public function getPath(Model $document): ?string
    {
        if (! $document->pdf_path) return null;
        return Storage::path($document->pdf_path);
    }

    public function streamResponse(Model $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        if (! $document->pdf_path || ! Storage::exists($document->pdf_path)) {
            $type = match (true) {
                $document instanceof Invoice    => 'invoice',
                $document instanceof Quote      => 'quote',
                $document instanceof CreditNote => 'credit_note',
                default => throw new \InvalidArgumentException('Unknown document type'),
            };
            $this->generate($type, $document);
        }

        return Storage::download($document->pdf_path, basename($document->pdf_path));
    }
}
