<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; background: #fff; }
        .page { padding: 40px; }
        .header { display: table; width: 100%; margin-bottom: 40px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .logo { font-size: 28px; font-weight: bold; color: #4f46e5; letter-spacing: -1px; }
        .doc-title { font-size: 22px; font-weight: bold; color: #1a1a2e; margin-bottom: 4px; }
        .doc-number { font-size: 14px; color: #6366f1; font-weight: bold; }
        .company-info { font-size: 10px; color: #6b7280; line-height: 1.6; margin-top: 6px; }
        .parties { display: table; width: 100%; margin-bottom: 30px; }
        .party { display: table-cell; width: 48%; vertical-align: top; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; }
        .party-spacer { display: table-cell; width: 4%; }
        .party-label { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .party-name { font-size: 13px; font-weight: bold; color: #1a1a2e; margin-bottom: 4px; }
        .party-detail { font-size: 10px; color: #6b7280; line-height: 1.5; }
        .meta { display: table; width: 100%; margin-bottom: 24px; }
        .meta-item { display: table-cell; text-align: center; padding: 10px; background: #f9fafb; border: 1px solid #e5e7eb; }
        .meta-item:not(:last-child) { border-right: none; }
        .meta-label { font-size: 9px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.05em; }
        .meta-value { font-size: 12px; font-weight: bold; color: #1a1a2e; margin-top: 2px; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.lines th { background: #4f46e5; color: #fff; font-size: 10px; font-weight: bold; padding: 8px 10px; text-align: left; }
        table.lines th.right { text-align: right; }
        table.lines td { padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        table.lines td.right { text-align: right; }
        table.lines tr:nth-child(even) td { background: #f9fafb; }
        .totals { float: right; width: 260px; margin-bottom: 30px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 10px; font-size: 11px; }
        .totals td.label { color: #6b7280; }
        .totals td.value { text-align: right; font-weight: bold; color: #1a1a2e; }
        .totals .total-row td { background: #4f46e5; color: #fff !important; padding: 8px 10px; font-size: 13px; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .notes { clear: both; padding-top: 20px; border-top: 1px solid #e5e7eb; }
        .notes-label { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; margin-bottom: 6px; }
        .notes-text { font-size: 10px; color: #374151; line-height: 1.6; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
        .validity-notice { margin-top: 20px; padding: 10px 14px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 10px; color: #1e40af; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="logo">SPQ</div>
            <div class="company-info">SPQ<br>contact@SPQ.app<br>SPQ.app</div>
        </div>
        <div class="header-right">
            <div class="doc-title">DEVIS</div>
            <div class="doc-number">{{ $document->number }}</div>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="party-label">Émetteur</div>
            <div class="party-name">SPQ</div>
            <div class="party-detail">contact@SPQ.app</div>
        </div>
        <div class="party-spacer"></div>
        <div class="party">
            <div class="party-label">Client</div>
            <div class="party-name">{{ $document->client->name }}</div>
            <div class="party-detail">
                {{ $document->client->full_contact_name }}<br>
                @if($document->client->address_line1){{ $document->client->address_line1 }}<br>@endif
                {{ $document->client->zip_code }} {{ $document->client->city }}<br>
                @if($document->client->vat_number)TVA : {{ $document->client->vat_number }}@endif
            </div>
        </div>
    </div>

    <div class="meta">
        <div class="meta-item">
            <div class="meta-label">Date d'émission</div>
            <div class="meta-value">{{ $document->issue_date?->format('d/m/Y') ?? '—' }}</div>
        </div>
        <div class="meta-item">
            <div class="meta-label">Valable jusqu'au</div>
            <div class="meta-value">{{ $document->expiry_date?->format('d/m/Y') ?? '—' }}</div>
        </div>
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:45%">Description</th>
                <th class="right" style="width:10%">Qté</th>
                <th class="right" style="width:15%">PU HT</th>
                <th class="right" style="width:10%">TVA</th>
                <th class="right" style="width:10%">Total HT</th>
                <th class="right" style="width:10%">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="right">{{ number_format($line->quantity, 2, ',', '') }}</td>
                <td class="right">{{ number_format($line->unit_price_ht, 2, ',', ' ') }} €</td>
                <td class="right">{{ $line->vatRate->rate_percent ?? '0%' }}</td>
                <td class="right">{{ number_format($line->line_total_ht, 2, ',', ' ') }} €</td>
                <td class="right">{{ number_format($line->line_total_ttc, 2, ',', ' ') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="clearfix">
        <div class="totals">
            <table>
                <tr><td class="label">Total HT</td><td class="value">{{ number_format($document->subtotal_ht, 2, ',', ' ') }} €</td></tr>
                <tr><td class="label">TVA</td><td class="value">{{ number_format($document->total_vat, 2, ',', ' ') }} €</td></tr>
                <tr class="total-row">
                    <td>TOTAL TTC</td>
                    <td style="text-align:right; font-weight:bold">{{ number_format($document->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>
    </div>

    @if($document->expiry_date)
    <div class="validity-notice">
        Ce devis est valable jusqu'au {{ $document->expiry_date->format('d/m/Y') }}.
        Pour l'accepter, connectez-vous sur SPQ.app.
    </div>
    @endif

    @if($document->notes || $document->conditions)
    <div class="notes">
        @if($document->conditions)
            <div class="notes-label">Conditions générales</div>
            <div class="notes-text">{{ $document->conditions }}</div>
        @endif
        @if($document->notes)
            <div class="notes-label" style="margin-top:10px">Notes</div>
            <div class="notes-text">{{ $document->notes }}</div>
        @endif
    </div>
    @endif

    <div class="footer">SPQ — {{ $document->number }} — Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>
</body>
</html>
