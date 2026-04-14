<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VatRateController extends Controller
{
    public function index(): View
    {
        $rates = VatRate::orderBy('rate', 'desc')->get();
        return view('superadmin.vat-rates.index', compact('rates'));
    }

    public function create(): View
    {
        return view('superadmin.vat-rates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:60|unique:vat_rates,name',
            'rate' => 'required|numeric|min:0|max:1',
        ]);
        VatRate::create(array_merge($data, ['active' => true, 'is_default' => false]));
        return redirect()->route('admin.vat-rates.index')->with('success', 'Taux TVA ajouté.');
    }

    public function edit(VatRate $vatRate): View
    {
        return view('superadmin.vat-rates.edit', compact('vatRate'));
    }

    public function update(Request $request, VatRate $vatRate): RedirectResponse
    {
        $data = $request->validate([
            'name'   => "required|string|max:60|unique:vat_rates,name,{$vatRate->id}",
            'rate'   => 'required|numeric|min:0|max:1',
            'active' => 'nullable|boolean',
        ]);
        $vatRate->update($data);
        return redirect()->route('admin.vat-rates.index')->with('success', 'Taux TVA mis à jour.');
    }

    public function destroy(VatRate $vatRate): RedirectResponse
    {
        if ($vatRate->is_default) {
            return back()->with('error', 'Impossible de supprimer le taux par défaut.');
        }
        $vatRate->delete();
        return redirect()->route('admin.vat-rates.index')->with('success', 'Taux TVA supprimé.');
    }

    public function setDefault(VatRate $vatRate): RedirectResponse
    {
        VatRate::where('is_default', true)->update(['is_default' => false]);
        $vatRate->update(['is_default' => true]);
        return redirect()->route('admin.vat-rates.index')->with('success', "{$vatRate->name} défini comme taux par défaut.");
    }
}
