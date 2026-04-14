@extends('layouts.app')
@section('title', 'Modifier la facture ' . $invoice->number)
@section('content')
<x-page-header title="Modifier la facture {{ $invoice->number }}" />

<form method="POST" action="{{ route('admin.invoices.update', $invoice) }}"
      x-data="documentBuilder({{ json_encode(old('lines', $invoice->lines->map(fn($l) => [
          'description'   => $l->description,
          'quantity'      => $l->quantity,
          'unit_price_ht' => $l->unit_price_ht,
          'vat_rate_id'   => $l->vat_rate_id,
          'service_id'    => $l->service_id,
      ])->toArray())) }})"
      class="space-y-6 max-w-4xl">
    @csrf
    @method('PUT')

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">Informations générales</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Client *</label>
                <select name="client_id" required
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('client_id') border-red-500 @enderror">
                    <option value="">— Sélectionner un client —</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ old('client_id', $invoice->client_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Conditions de paiement</label>
                <input type="text" name="payment_terms" value="{{ old('payment_terms', $invoice->payment_terms) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Date d'émission</label>
                <input type="date" name="issue_date" value="{{ old('issue_date', $invoice->issue_date?->toDateString()) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Date d'échéance</label>
                <input type="date" name="due_date" value="{{ old('due_date', $invoice->due_date?->toDateString()) }}"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
    </div>

    <!-- Lines builder -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-white font-semibold">Lignes</h3>
            <div class="flex items-center gap-3">
                <select x-model="selectedService" @change="addFromService($event)"
                    class="bg-gray-800 border border-gray-700 rounded-lg px-3 py-1.5 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">+ Ajouter depuis catalogue</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}"
                            data-name="{{ $svc->name }}"
                            data-price="{{ $svc->unit_price_ht }}"
                            data-vat="{{ $svc->vat_rate_id }}">
                            {{ $svc->name }} — {{ number_format($svc->unit_price_ht, 2, ',', ' ') }} €
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="space-y-3">
            <template x-for="(line, index) in lines" :key="index">
                <div class="grid grid-cols-12 gap-2 items-start">
                    <div class="col-span-5">
                        <input type="text" :name="`lines[${index}][description]`" x-model="line.description"
                            placeholder="Description *" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <input type="hidden" :name="`lines[${index}][service_id]`" x-model="line.service_id">
                    </div>
                    <div class="col-span-2">
                        <input type="number" :name="`lines[${index}][quantity]`" x-model="line.quantity"
                            min="0.01" step="0.01" placeholder="Qté *" required
                            @input="calcLine(index)"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <input type="number" :name="`lines[${index}][unit_price_ht]`" x-model="line.unit_price_ht"
                            min="0" step="0.01" placeholder="PU HT *" required
                            @input="calcLine(index)"
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2">
                        <select :name="`lines[${index}][vat_rate_id]`" x-model="line.vat_rate_id"
                            @change="calcLine(index)" required
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">TVA *</option>
                            @foreach($vatRates as $vr)
                                <option value="{{ $vr->id }}" data-rate="{{ $vr->rate }}">{{ $vr->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-1 flex items-center justify-between">
                        <span class="text-sm text-gray-300 font-mono" x-text="formatMoney(line.total_ttc)"></span>
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
            Ajouter une ligne
        </button>

        <div class="mt-6 flex justify-end">
            <div class="w-64 space-y-2 text-sm">
                <div class="flex justify-between text-gray-400">
                    <span>Total HT</span>
                    <span x-text="formatMoney(totalHT)"></span>
                </div>
                <div class="flex justify-between text-gray-400">
                    <span>TVA</span>
                    <span x-text="formatMoney(totalVAT)"></span>
                </div>
                <div class="flex justify-between text-white font-bold text-base border-t border-gray-700 pt-2">
                    <span>Total TTC</span>
                    <span x-text="formatMoney(totalTTC)"></span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">Notes</h3>
        <textarea name="notes" rows="3"
            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $invoice->notes) }}</textarea>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-2.5 rounded-lg transition-colors">
            Enregistrer les modifications
        </button>
        <a href="{{ route('admin.invoices.show', $invoice) }}" class="bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold px-6 py-2.5 rounded-lg transition-colors">
            Annuler
        </a>
    </div>
</form>

@push('scripts')
<script>
function documentBuilder(initialLines) {
    const vatRates = @json($vatRates->keyBy('id')->map(fn($v) => $v->rate));

    return {
        lines: initialLines.map(l => ({ ...l, total_ht: 0, total_vat: 0, total_ttc: 0 })),
        selectedService: '',
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
            this.lines.push({ description: '', quantity: 1, unit_price_ht: 0, vat_rate_id: '', service_id: '', total_ht: 0, total_vat: 0, total_ttc: 0 });
        },

        removeLine(index) {
            if (this.lines.length > 1) this.lines.splice(index, 1);
        },

        addFromService(event) {
            const opt = event.target.options[event.target.selectedIndex];
            if (!opt.value) return;
            this.lines.push({
                description: opt.dataset.name,
                quantity: 1,
                unit_price_ht: parseFloat(opt.dataset.price) || 0,
                vat_rate_id: opt.dataset.vat,
                service_id: opt.value,
                total_ht: 0, total_vat: 0, total_ttc: 0
            });
            const last = this.lines.length - 1;
            this.calcLine(last);
            this.selectedService = '';
        },
    };
}
</script>
@endpush
@endsection
