<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Billing\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    public function index(Request $request): View
    {
        $payments = Payment::with(['invoice', 'client'])->latest('paid_at')->paginate(25);
        return view('superadmin.billing.payments.index', compact('payments'));
    }

    public function create(Invoice $invoice): View
    {
        return view('superadmin.billing.payments.create', compact('invoice'));
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:0.01',
            'method'    => 'required|in:stripe,bank_transfer,cheque,cash,other',
            'reference' => 'nullable|string|max:100',
            'notes'     => 'nullable|string',
            'paid_at'   => 'nullable|date',
        ]);
        $this->paymentService->recordManual($invoice, $data);
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Paiement enregistré.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;
        $this->paymentService->reverse($payment);
        return redirect()->route('admin.invoices.show', $invoice)
            ->with('success', 'Paiement annulé.');
    }
}
