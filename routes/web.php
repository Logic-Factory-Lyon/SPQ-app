<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Superadmin\DashboardController as AdminDashboard;
use App\Http\Controllers\Superadmin\ClientController;
use App\Http\Controllers\Superadmin\ProjectController;
use App\Http\Controllers\Superadmin\UserController;
use App\Http\Controllers\Superadmin\MacMachineController;
use App\Http\Controllers\Superadmin\VatRateController;
use App\Http\Controllers\Superadmin\ServiceCatalogController;
use App\Http\Controllers\Superadmin\QuoteController as AdminQuoteController;
use App\Http\Controllers\Superadmin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Superadmin\CreditNoteController as AdminCreditNoteController;
use App\Http\Controllers\Superadmin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Superadmin\ProjectMemberController as AdminMemberController;
use App\Http\Controllers\Superadmin\EmailTemplateController;
use App\Http\Controllers\Superadmin\AgentController;
use App\Http\Controllers\Superadmin\AgentCrudController;
use App\Http\Controllers\Superadmin\SkillController;
use App\Http\Controllers\Employee\SkillController as EmployeeSkillController;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;
use App\Http\Controllers\Client\ProjectController as ClientProjectController;
use App\Http\Controllers\Client\MemberController as ClientMemberController;
use App\Http\Controllers\Client\QuoteController as ClientQuoteController;
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboard;
use App\Http\Controllers\Employee\ConversationController as EmployeeConversationController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboard;
use App\Http\Controllers\Manager\ConversationController as ManagerConversationController;
use App\Http\Controllers\Manager\MemberController as ManagerMemberController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'update'])->name('password.update');
});

// Logout
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')->name('logout');

// Locale switcher
Route::post('locale', function (\Illuminate\Http\Request $request) {
    $request->validate(['locale' => 'required|in:fr,en']);
    auth()->user()->update(['locale' => $request->locale]);
    return back();
})->middleware('auth')->name('locale.switch');

// Redirect root based on role
Route::get('/', function () {
    if (!auth()->check()) return redirect()->route('login');
    return match(auth()->user()->role) {
        'superadmin' => redirect()->route('admin.dashboard'),
        'client'     => redirect()->route('portal.dashboard'),
        'manager'    => redirect()->route('manager.dashboard'),
        'employee'   => redirect()->route('employee.dashboard'),
        default      => redirect()->route('login'),
    };
})->middleware('auth');

// ── Superadmin ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:superadmin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

        // Clients
        Route::resource('clients', ClientController::class);

        // Projects (shallow nested under clients for creation, standalone for CRUD)
        Route::get('clients/{client}/projects/create', [ProjectController::class, 'create'])->name('clients.projects.create');
        Route::post('clients/{client}/projects', [ProjectController::class, 'store'])->name('clients.projects.store');
        Route::resource('projects', ProjectController::class)->except(['create', 'store']);
        Route::get('projects/{project}/hierarchy', [ProjectController::class, 'hierarchy'])->name('projects.hierarchy');

        // Users
        Route::resource('users', UserController::class)->except(['create', 'store']);

        // Mac Machines
        Route::resource('mac-machines', MacMachineController::class)->except(['create', 'store', 'index']);
        Route::post('mac-machines/{macMachine}/regenerate-token', [MacMachineController::class, 'regenerateToken'])->name('mac-machines.regenerate-token');
        Route::post('mac-machines/{macMachine}/restart-daemon', [MacMachineController::class, 'restartDaemon'])->name('mac-machines.restart-daemon');
        Route::get('mac-machines/{macMachine}/launcher', [MacMachineController::class, 'downloadLauncher'])->name('mac-machines.launcher');
        Route::get('mac-machines/{macMachine}/setup-guide', [MacMachineController::class, 'downloadSetupGuide'])->name('mac-machines.setup-guide');

        // VAT Rates
        Route::resource('vat-rates', VatRateController::class);
        Route::post('vat-rates/{vatRate}/set-default', [VatRateController::class, 'setDefault'])->name('vat-rates.set-default');

        // Service catalog
        Route::resource('services', ServiceCatalogController::class);
        Route::post('services/{service}/toggle', [ServiceCatalogController::class, 'toggle'])->name('services.toggle');

        // Quotes
        Route::resource('quotes', AdminQuoteController::class);
        Route::post('quotes/{quote}/send', [AdminQuoteController::class, 'send'])->name('quotes.send');
        Route::post('quotes/{quote}/convert', [AdminQuoteController::class, 'convertToInvoice'])->name('quotes.convert');
        Route::get('quotes/{quote}/pdf', [AdminQuoteController::class, 'downloadPdf'])->name('quotes.downloadPdf');

        // Invoices
        Route::resource('invoices', AdminInvoiceController::class);
        Route::post('invoices/{invoice}/send', [AdminInvoiceController::class, 'send'])->name('invoices.send');
        Route::post('invoices/{invoice}/mark-paid', [AdminInvoiceController::class, 'markPaid'])->name('invoices.mark-paid');
        Route::get('invoices/{invoice}/pdf', [AdminInvoiceController::class, 'downloadPdf'])->name('invoices.downloadPdf');

        // Credit Notes
        Route::get('invoices/{invoice}/credit-notes/create', [AdminCreditNoteController::class, 'create'])->name('credit-notes.create');
        Route::post('invoices/{invoice}/credit-notes', [AdminCreditNoteController::class, 'store'])->name('credit-notes.store');
        Route::get('credit-notes/{creditNote}', [AdminCreditNoteController::class, 'show'])->name('credit-notes.show');
        Route::post('credit-notes/{creditNote}/issue', [AdminCreditNoteController::class, 'issue'])->name('credit-notes.issue');
        Route::get('credit-notes/{creditNote}/pdf', [AdminCreditNoteController::class, 'downloadPdf'])->name('credit-notes.downloadPdf');

        // Payments
        Route::get('invoices/{invoice}/payments/create', [AdminPaymentController::class, 'create'])->name('payments.create');
        Route::post('invoices/{invoice}/payments', [AdminPaymentController::class, 'store'])->name('payments.store');
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::delete('payments/{payment}', [AdminPaymentController::class, 'destroy'])->name('payments.destroy');

        // Project Members
        Route::post('projects/{project}/members', [AdminMemberController::class, 'store'])->name('projects.members.store');
        Route::delete('projects/{project}/members/{member}', [AdminMemberController::class, 'destroy'])->name('projects.members.destroy');
        Route::delete('projects/{project}/invitations/{invitation}', [AdminMemberController::class, 'cancelInvitation'])->name('projects.invitations.cancel');

        // Agents (Mac Machine + Telegram)
        Route::post('projects/{project}/agents', [AgentController::class, 'store'])->name('projects.agents.store');
        Route::delete('projects/{project}/agents/{agent}', [AgentController::class, 'destroy'])->name('projects.agents.destroy');
        Route::post('projects/{project}/agents/{agent}/register-webhook', [AgentController::class, 'registerWebhook'])->name('projects.agents.register-webhook');

        // Skills
        Route::resource('skills', SkillController::class);
        Route::post('projects/{project}/agents/{agent}/skills/{skill}', [SkillController::class, 'attach'])->name('projects.agents.skills.attach');
        Route::delete('projects/{project}/agents/{agent}/skills/{skill}', [SkillController::class, 'detach'])->name('projects.agents.skills.detach');

        // Agent CRUD (standalone)
        Route::resource('agents', AgentCrudController::class)->except(['show']);
        Route::post('agents/{agent}/initialize', [AgentCrudController::class, 'initialize'])->name('agents.initialize');
        Route::post('agents/{agent}/resync', [AgentCrudController::class, 'resync'])->name('agents.resync');

        // Team cloning
        Route::get('projects/{project}/clone', [ProjectController::class, 'showCloneForm'])->name('projects.clone');
        Route::post('projects/{project}/clone', [ProjectController::class, 'clone']);

        // Email Templates
        Route::get('email-templates', [EmailTemplateController::class, 'index'])->name('email-templates.index');
        Route::get('email-templates/{key}/{lang}/edit', [EmailTemplateController::class, 'edit'])->name('email-templates.edit');
        Route::put('email-templates/{key}/{lang}', [EmailTemplateController::class, 'update'])->name('email-templates.update');
        Route::get('email-templates/footer/{lang}/edit', [EmailTemplateController::class, 'editFooter'])->name('email-templates.footer.edit');
        Route::put('email-templates/footer/{lang}', [EmailTemplateController::class, 'updateFooter'])->name('email-templates.footer.update');
    });

// ── Client Portal ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:client'])
    ->prefix('portal')
    ->name('portal.')
    ->group(function () {

        Route::get('dashboard', [ClientDashboard::class, 'index'])->name('dashboard');
        Route::resource('projects', ClientProjectController::class)->only(['index', 'show']);
        Route::post('projects/{project}/members', [ClientMemberController::class, 'store'])->name('projects.members.store');
        Route::delete('projects/{project}/members/{member}', [ClientMemberController::class, 'destroy'])->name('projects.members.destroy');
        Route::delete('projects/{project}/invitations/{invitation}', [ClientMemberController::class, 'cancelInvitation'])->name('projects.invitations.cancel');
        Route::get('quotes', [ClientQuoteController::class, 'index'])->name('quotes.index');
        Route::get('quotes/{quote}', [ClientQuoteController::class, 'show'])->name('quotes.show');
        Route::post('quotes/{quote}/accept', [ClientQuoteController::class, 'accept'])->name('quotes.accept');
        Route::post('quotes/{quote}/reject', [ClientQuoteController::class, 'reject'])->name('quotes.reject');
        Route::get('quotes/{quote}/pdf', [ClientQuoteController::class, 'downloadPdf'])->name('quotes.downloadPdf');
        Route::get('invoices', [ClientInvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [ClientInvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/pdf', [ClientInvoiceController::class, 'downloadPdf'])->name('invoices.downloadPdf');
        Route::post('invoices/{invoice}/pay', [ClientInvoiceController::class, 'payWithStripe'])->name('invoices.pay');
        Route::get('invoices/{invoice}/pay/success', [ClientInvoiceController::class, 'paySuccess'])->name('invoices.pay.success');
    });

// ── Manager Portal ─────────────────────────────────────────────────────
Route::middleware(['auth', 'role:manager'])
    ->prefix('manager')
    ->name('manager.')
    ->group(function () {

        Route::get('dashboard', [ManagerDashboard::class, 'index'])->name('dashboard');
        Route::get('conversations', [ManagerConversationController::class, 'index'])->name('conversations.index');
        Route::get('conversations/{conversation}', [ManagerConversationController::class, 'show'])->name('conversations.show');

        // Project Members
        Route::post('projects/{project}/members', [ManagerMemberController::class, 'store'])->name('projects.members.store');
        Route::post('projects/{project}/members/{member}/agent', [ManagerMemberController::class, 'assignAgent'])->name('projects.members.assign-agent');
        Route::delete('projects/{project}/members/{member}', [ManagerMemberController::class, 'destroy'])->name('projects.members.destroy');
        Route::delete('projects/{project}/invitations/{invitation}', [ManagerMemberController::class, 'cancelInvitation'])->name('projects.invitations.cancel');
    });

// ── Employee Portal ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:employee'])
    ->prefix('employee')
    ->name('employee.')
    ->group(function () {

        Route::get('dashboard', [EmployeeDashboard::class, 'index'])->name('dashboard');
        Route::get('skills', [EmployeeSkillController::class, 'index'])->name('skills.index');
        Route::resource('conversations', EmployeeConversationController::class)->only(['index', 'show', 'store', 'destroy']);
        Route::post('conversations/{conversation}/messages', [EmployeeConversationController::class, 'sendMessage'])->name('conversations.messages.store');
        Route::post('conversations/{conversation}/skill', [EmployeeSkillController::class, 'dispatch'])->name('conversations.skill.dispatch');
        Route::get('conversations/{conversation}/poll', [EmployeeConversationController::class, 'poll'])->name('conversations.poll');
    });

// Public daemon script endpoint (no credentials in the script itself)
Route::get('daemon/script', function () {
    $content = file_get_contents(base_path('daemon/spq_daemon.py'));
    return response($content, 200, ['Content-Type' => 'text/plain']);
})->name('daemon.script');

// Stripe webhook (public, verified by Stripe signature)
Route::post('stripe/webhook', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])->name('stripe.webhook');

// Invitations (public)
Route::get('invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');
Route::post('invitation/{token}/register', [InvitationController::class, 'register'])->name('invitation.register');
Route::post('invitation/{token}/login', [InvitationController::class, 'login'])->name('invitation.login');
