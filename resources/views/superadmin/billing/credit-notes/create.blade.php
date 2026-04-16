@extends('layouts.app')
@section('title', __('app.new_credit_note_title') . ' — ' . $invoice->number)
@section('content')
<x-page-header title="{{ __('app.create_credit_note_title') }}" subtitle="{{ __('app.invoice_label', ['number' => $invoice->number, 'client' => $invoice->client->name]) }}" />

<form method="POST" action="{{ route('admin.credit-notes.store', $invoice) }}"
      x-data="creditNoteBuilder({{ json_encode(old('lines', [['description'=>'','quantity'=>1,'unit_price_ht'=>0,'vat_rate_id'=>'']])) }})"
      class="space-y-6 max-w-3xl">
    @csrf

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">{{ __('app.information') }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.issue_date') }}</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', now()->toDateString()) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.reason') }}</label>
                <input type="text" name="reason" value="{{ old('reason') }}" placeholder="{{ __('app.reason_placeholder') }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Lines from original invoice (pre-fill helper) -->
    <div class="bg-gray-800/50 rounded-xl border border-gray-700 p-4 text-sm">
        <p class="text-gray-400 font-semibold mb-2">{{ __('app.original_invoice_lines') }}</p>
        <div class="space-y-1">
            @foreach($invoice->lines as $line)
            <button type="button"
                @click="prefillLine('{{ addslashes($line->description) }}', {{ $line->quantity }}, {{ $line->unit_price_ht }}, {{ $line->vat_rate_id }})"
                class="flex items-center gap-3 text-left w-full hover:bg-gray-700 rounded-lg px-3 py-2 transition-colors">
                <svg class="w-4 h-4 text-indigo-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="text-gray-300">{{ $line->description }}</span>
                <span class="text-gray-500 ml-auto">{{ number_format($line->total_ttc, 2, ',', ' ') }} €</span>
            </button>
            @endforeach
        </div>
    </div>

    <!-- Lines builder -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">{{ __('app.credit_note_lines') }}</h3>

        <div class="space-y-3">
            <template x-for="(line, index) in lines" :key="index">
                <div class="grid grid-cols-12 gap-2 items-start">
                    <div class="col-span-5">
                        <input type="text" :name="`lines[${index}][description]`" x-model="line.description"
                            placeholder="{{ __('app.description') }} *" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" :name="`lines[${index}][quantity]`" x-model="line.quantity"
                            min="0.01" step="0.01" placeholder="{{ __('app.qty_short') }} *" required
                            @input="calcLine(index)"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" :name="`lines[${index}][unit_price_ht]`" x-model="line.unit_price_ht"
                            min="0" step="0.01" placeholder="{{ __('app.unit_price_ht_short') }} *" required
                            @input="calcLine(index)"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <select :name="`lines[${index}][vat_rate_id]`" x-model="line.vat_rate_id"
                            @change="calcLine(index)" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">{{ __('app.vat') }} *</option>
                            @foreach($vatRates as $vr)
                                <option value="{{ $vr->id }}" data-rate="{{ $vr->rate }}">{{ $vr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1 flex items-center justify-between">
                        <span class="text-sm text-orange-300 font-mono" x-text="'- ' + formatMoney(line.total_ttc)"></span>
                        <button type="button" @click="removeLine(index)"
                            class="text-red-500 hover:text-red-400 ml-2" x-show="lines.length > 1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </template>
        </div>

        <button type="button" @click="addLine()"
            class="mt-4 flex items-center gap-2 text-indigo-400 hover:text-indigo-300 text-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ __('app.add_line') }}
        </button>

        <div class="mt-6 flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between text-gray-400">
                    <span>{{ __('app.total_ht') }}</span>
                    <span class="text-orange-300" x-text="'- ' + formatMoney(totalHT)"></span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>{{ __('app.vat') }}</span>
                    <span class="text-orange-300" x-text="'- ' + formatMoney(totalVAT)"></span>
                </div>
                <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                    <span>{{ __('app.total_credit_ttc') }}</span>
                    <span class="text-orange-400" x-text="'- ' + formatMoney(totalTTC)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <label class="block text-sm text-gray-400 mb-1.5">{{ __('app.notes') }}</label>
        <textarea name="notes" rows="3"
            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-orange-600 hover:bg-orange-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
            {{ __('app.create_credit_note_btn') }}
        </button>
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
            {{ __('app.cancel') }}
        </a>
    </div>
</form>

@push('scripts')
<script>
function creditNoteBuilder(initialLines) {
    const vatRates = @json($vatRates->keyBy('id')->map(fn($v) => $v->rate));

    return {
        lines: initialLines.map(l => ({ ...l, total_ht: 0, total_vat: 0, total_ttc: 0 })),
        get totalHT() { return this.lines.reduce((s, l) => s + (parseFloat(l.total_ht) || 0), 0); },
        get totalVAT() { return this.lines.reduce((s, l) => s + (parseFloat(l.total_vat) || 0), 0); },
        get totalTTC() { return this.lines.reduce((s, l) => s + (parseFloat(l.total_ttc) || 0), 0); },

        formatMoney(val) {
            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(val || 0);
        },

        calcLine(index) {
            const line = this.lines[index];
            const qty = parseFloat(line.quantity) || 0;
            const price = parseFloat(line.unit_price_ht) || 0;
            const rate = parseFloat(vatRates[line.vat_rate_id]) || 0;
            line.total_ht = Math.round(qty * price * 100) / 100;
            line.total_vat = Math.round(line.total_ht * rate * 100) / 100;
            line.total_ttc = Math.round((line.total_ht + line.total_vat) * 100) / 100;
        },

        addLine() {
            this.lines.push({ description: '', quantity: 1, unit_price_ht: 0, vat_rate_id: '', total_ht: 0, total_vat: 0, total_ttc: 0 });
        },

        removeLine(index) {
            if (this.lines.length > 1) this.lines.splice(index, 1);
        },

        prefillLine(description, quantity, unit_price_ht, vat_rate_id) {
            this.lines.push({ description, quantity, unit_price_ht, vat_rate_id: String(vat_rate_id), total_ht: 0, total_vat: 0, total_ttc: 0 });
            this.calcLine(this.lines.length - 1);
        },
    };
}
</script>
@endpush
@endsection
