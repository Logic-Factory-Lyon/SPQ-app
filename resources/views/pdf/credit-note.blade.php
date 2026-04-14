<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a2e; }
        .page { padding: 40px; }
        .header { display: table; width: 100%; margin-bottom: 40px; }
        .header-left { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .logo { font-size: 28px; font-weight: bold; color: #4f46e5; }
        .doc-title { font-size: 22px; font-weight: bold; }
        .doc-number { font-size: 14px; color: #6366f1; font-weight: bold; }
        .company-info { font-size: 10px; color: #6b7280; line-height: 1.6; margin-top: 6px; }
        .parties { display: table; width: 100%; margin-bottom: 30px; }
        .party { display: table-cell; width: 48%; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; vertical-align: top; }
        .party-spacer { display: table-cell; width: 4%; }
        .party-label { font-size: 9px; font-weight: bold; color: #9ca3af; text-transform: uppercase; margin-bottom: 8px; }
        .party-name { font-size: 13px; font-weight: bold; margin-bottom: 4px; }
        .party-detail { font-size: 10px; color: #6b7280; line-height: 1.5; }
        .ref-box { padding: 12px 16px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px; margin-bottom: 20px; font-size: 10px; color: #92400e; }
        table.lines { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.lines th { background: #7c3aed; color: #fff; font-size: 10px; padding: 8px 10px; text-align: left; }
        table.lines th.right { text-align: right; }
        table.lines td { padding: 8px 10px; font-size: 10px; border-bottom: 1px solid #f3f4f6; }
        table.lines td.right { text-align: right; }
        .totals { float: right; width: 260px; margin-bottom: 30px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 5px 10px; font-size: 11px; }
        .totals td.label { color: #6b7280; }
        .totals td.value { text-align: right; font-weight: bold; }
        .totals .total-row td { background: #7c3aed; color: #fff !important; padding: 8px 10px; font-size: 13px; }
        .clearfix::after { content: ''; display: table; clear: both; }
        .footer { margin-top: 40px; padding-top: 16px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 9px; color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="logo">SPQ</div>
            <div class="company-info">SPQ<br>contact@SPQ.app</div>
        </div>
        <div class="header-right">
            <div class="doc-title">AVOIR</div>
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
                {{ $document->client->zip_code }} {{ $document->client->city }}
            </div>
        </div>
    </div>

    <div class="ref-box">
        Avoir émis en référence à la facture <strong>{{ $document->invoice->number }}</strong>
        @if($document->reason) — Motif : {{ $document->reason }}@endif
    </div>

    <table class="lines">
        <thead>
            <tr>
                <th style="width:50%">Description</th>
                <th class="right">Qté</th>
                <th class="right">PU HT</th>
                <th class="right">TVA</th>
                <th class="right">Total TTC</th>
            </tr>
        </thead>
        <tbody>
            @foreach($document->lines as $line)
            <tr>
                <td>{{ $line->description }}</td>
                <td class="right">{{ number_format($line->quantity, 2, ',', '') }}</td>
                <td class="right">{{ number_format($line->unit_price_ht, 2, ',', ' ') }} €</td>
                <td class="right">{{ $line->vatRate->rate_percent ?? '0%' }}</td>
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
                    <td>AVOIR TTC</td>
                    <td style="text-align:right; font-weight:bold">{{ number_format($document->total_ttc, 2, ',', ' ') }} €</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="footer">SPQ — {{ $document->number }} — Généré le {{ now()->format('d/m/Y à H:i') }}</div>
</div>
</body>
</html>
