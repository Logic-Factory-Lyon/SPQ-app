<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VatRate;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceCatalogController extends Controller
{
    public function index(): View
    {
        $services = Service::with('vatRate')->latest()->get();
        return view('superadmin.services.index', compact('services'));
    }

    public function create(): View
    {
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        return view('superadmin.services.create', compact('vatRates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255|unique:services,name',
            'description'  => 'nullable|string',
            'unit_price_ht'=> 'required|numeric|min:0',
            'vat_rate_id'  => 'required|exists:vat_rates,id',
            'billing_type' => 'required|in:one_time,monthly,yearly',
        ]);
        Service::create(array_merge($data, ['active' => true]));
        return redirect()->route('admin.services.index')->with('success', 'Service créé.');
    }

    public function edit(Service $service): View
    {
        $vatRates = VatRate::active()->orderBy('rate', 'desc')->get();
        return view('superadmin.services.edit', compact('service', 'vatRates'));
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'name'         => "required|string|max:255|unique:services,name,{$service->id}",
            'description'  => 'nullable|string',
            'unit_price_ht'=> 'required|numeric|min:0',
            'vat_rate_id'  => 'required|exists:vat_rates,id',
            'billing_type' => 'required|in:one_time,monthly,yearly',
        ]);
        $service->update($data);
        return redirect()->route('admin.services.index')->with('success', 'Service mis à jour.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service supprimé.');
    }

    public function toggle(Service $service): RedirectResponse
    {
        $service->update(['active' => ! $service->active]);
        $label = $service->active ? 'activé' : 'désactivé';
        return back()->with('success', "Service {$label}.");
    }
}
