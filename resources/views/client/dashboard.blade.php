@extends('layouts.app')
@section('title', __('app.my_client_space'))
@section('content')
    <x-page-header title="{{ __('app.hello', ['name' => auth()->user()->name]) }}"
        subtitle="{{ $client->name }}" />

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="{{ __('app.active_projects_count') }}" value="{{ $projects->count() }}" color="indigo" />
        <x-stat-card label="{{ __('app.unpaid_balance_client') }}" value="{{ number_format($outstandingBalance, 2, ',', ' ') }} €" :color="$outstandingBalance > 0 ? 'red' : 'green'" />
        <x-stat-card label="{{ __('app.pending_quotes_count') }}" value="{{ $pendingQuotes->count() }}" color="yellow" />
    </div>

    @if($pendingQuotes->isNotEmpty())
    <div class="bg-yellow-900/20 border border-yellow-800 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-yellow-300 mb-3">{{ __('app.pending_quotes_response') }}</h3>
        <div class="space-y-2">
            @foreach($pendingQuotes as $quote)
            <div class="flex items-center justify-between bg-gray-900 rounded-lg px-4 py-3">
                <div>
                    <p class="text-white font-medium">{{ $quote->number }}</p>
                    <p class="text-sm text-gray-400">
                        {{ number_format($quote->total_ttc, 2, ',', ' ') }} € {{ __('app.including_tax') }}
                        @if($quote->expiry_date) — {{ __('app.expires_on') }} {{ $quote->expiry_date->format('d/m/Y') }}@endif
                    </p>
                </div>
                <a href="{{ route('portal.quotes.show', $quote) }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.respond') }}
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Projects -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">{{ __('app.my_projects_title') }}</h3>
                <a href="{{ route('portal.projects.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">{{ __('app.see_all') }}</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($projects as $project)
                <a href="{{ route('portal.projects.show', $project) }}"
                   class="flex items-center justify-between px-5 py-3 hover:bg-gray-800/50 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                        <p class="text-xs text-gray-500">{{ $project->members_count }} {{ __('app.member_count') }}</p>
                    </div>
                    <x-badge color="green">{{ __('app.status_active') }}</x-badge>
                </a>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">{{ __('app.no_active_project') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Unpaid invoices -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">{{ __('app.unpaid_invoices') }}</h3>
                <a href="{{ route('portal.invoices.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">{{ __('app.see_all') }}</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($unpaidInvoices as $invoice)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $invoice->number }}</p>
                        <p class="text-xs text-gray-500">
                            {{ __('app.due_label') }} {{ $invoice->due_date?->format('d/m/Y') ?? '—' }}
                            @if($invoice->isOverdue()) <span class="text-red-400">· {{ __('app.status_overdue') }}</span>@endif
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-white font-semibold text-sm">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} €</span>
                        <a href="{{ route('portal.invoices.show', $invoice) }}"
                           class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            {{ __('app.pay') }}
                        </a>
                    </div>
                </div>
                @empty
                    <p class="text-green-400 text-sm text-center py-6">{{ __('app.no_unpaid_invoices') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
