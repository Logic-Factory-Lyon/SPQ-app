@extends('layouts.app')
@section('title', $client->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.clients.index') }}" class="hover:text-white">{{ __('app.clients') }}</a>
        <span>/</span>
        <span class="text-white">{{ $client->name }}</span>
    </div>
@endsection
@section('header-actions')
    <a href="{{ route('admin.clients.edit', $client) }}"
       class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        {{ __('app.edit') }}
    </a>
@endsection
@section('content')
    <div class="flex items-start justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $client->name }}</h1>
            <p class="text-gray-400 mt-1">{{ $client->full_contact_name }} &mdash; {{ $client->email }}</p>
        </div>
        <x-badge :color="$client->active ? 'green' : 'gray'" class="text-sm px-3 py-1">
            {{ $client->active ? __('app.status_active') : __('app.status_inactive') }}
        </x-badge>
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mb-8">
        <x-stat-card label="{{ __('app.projects') }}" value="{{ $client->projects_count }}" />
        <x-stat-card label="{{ __('app.users') }}" value="{{ $client->users_count }}" />
        <x-stat-card label="{{ __('app.unpaid') }}" value="{{ number_format($outstandingBalance, 2, ',', ' ') }} €" color="{{ $outstandingBalance > 0 ? 'red' : 'green' }}" />
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Billing info -->
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
            <h3 class="font-semibold text-white mb-4">{{ __('app.billing_info') }}</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('app.address') }}</dt><dd class="text-white text-right">{{ $client->full_address ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('app.phone') }}</dt><dd class="text-white">{{ $client->phone ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('app.vat_number_short') }}</dt><dd class="text-white font-mono">{{ $client->vat_number ?: '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">{{ __('app.stripe_id') }}</dt><dd class="text-gray-400 font-mono text-xs">{{ $client->stripe_customer_id ?: '—' }}</dd></div>
            </dl>
        </div>

        <!-- Projects -->
        <div class="bg-gray-900 rounded-xl border border-gray-800">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h3 class="font-semibold text-white">{{ __('app.projects') }}</h3>
                <a href="{{ route('admin.clients.projects.create', $client) }}"
                   class="text-sm text-indigo-400 hover:text-indigo-300">{{ __('app.add_short') }}</a>
            </div>
            <div class="divide-y divide-gray-800">
                @forelse($projects as $project)
                    <div class="flex items-center justify-between px-5 py-3">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $project->name }}</p>
                            <p class="text-xs text-gray-500">{{ $project->members_count }} {{ __('app.member_count') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            @php $colors = ['active' => 'green', 'suspended' => 'yellow', 'cancelled' => 'gray'] @endphp
                            @php $labels = ['active' => __('app.status_active'), 'suspended' => __('app.status_suspended'), 'cancelled' => __('app.status_cancelled')] @endphp
                            <x-badge :color="$colors[$project->status] ?? 'gray'">{{ $labels[$project->status] ?? $project->status }}</x-badge>
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">&rarr;</a>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm text-center py-6">{{ __('app.no_project') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Users -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">{{ __('app.users') }}</h3>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($users as $user)
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php $roleColors = ['client' => 'blue', 'manager' => 'indigo', 'employee' => 'gray'] @endphp
                        @php $roleLabels = ['client' => __('app.role_client'), 'manager' => __('app.role_manager'), 'employee' => __('app.role_employee')] @endphp
                        <x-badge :color="$roleColors[$user->role] ?? 'gray'">{{ $roleLabels[$user->role] ?? $user->role }}</x-badge>
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-500 hover:text-indigo-400 text-sm">{{ __('app.edit') }}</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">{{ __('app.no_user_search') }}</p>
            @endforelse
        </div>
    </div>

    <!-- Recent invoices -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">{{ __('app.recent_invoices') }}</h3>
            <a href="{{ route('admin.invoices.create') }}?client_id={{ $client->id }}" class="text-sm text-indigo-400 hover:text-indigo-300">{{ __('app.new_invoice_btn') }}</a>
        </div>
        <div class="divide-y divide-gray-800">
            @forelse($invoices as $invoice)
                @php $sc = ['draft' => 'gray', 'sent' => 'blue', 'paid' => 'green', 'overdue' => 'red', 'cancelled' => 'gray'] @endphp
                @php $sl = ['draft' => __('app.status_draft'), 'sent' => __('app.status_sent'), 'paid' => __('app.status_paid'), 'overdue' => __('app.status_overdue'), 'cancelled' => __('app.status_cancelled')] @endphp
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-white">{{ $invoice->number }}</p>
                        <p class="text-xs text-gray-500">{{ $invoice->issue_date?->format('d/m/Y') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-white font-semibold text-sm">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} &euro;</span>
                        <x-badge :color="$sc[$invoice->status] ?? 'gray'">{{ $sl[$invoice->status] ?? $invoice->status }}</x-badge>
                        <a href="{{ route('admin.invoices.show', $invoice) }}" class="text-indigo-400 text-sm hover:text-indigo-300">&rarr;</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-6">{{ __('app.no_invoices') }}</p>
            @endforelse
        </div>
    </div>

    @if($client->notes)
    <div class="bg-gray-900 rounded-xl border border-gray-800 mt-6 p-6">
        <h3 class="font-semibold text-white mb-3">{{ __('app.internal_notes') }}</h3>
        <p class="text-gray-400 text-sm whitespace-pre-wrap">{{ $client->notes }}</p>
    </div>
    @endif
@endsection