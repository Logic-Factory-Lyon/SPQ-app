@extends('layouts.app')
@section('title', __('app.quotes'))
@section('header-actions')
    <a href="{{ route('admin.quotes.create') }}"
       class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        {{ __('app.new_quote') }}
    </a>
@endsection
@section('content')
    <x-page-header title="{{ __('app.quotes') }}" />

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('app.all_statuses') }}</option>
            @foreach(['draft'=>__('app.status_draft'),'sent'=>__('app.status_sent'),'accepted'=>__('app.status_accepted'),'rejected'=>__('app.status_rejected'),'expired'=>__('app.status_expired')] as $val => $lab)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lab }}</option>
            @endforeach
        </select>
        <select name="client_id" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('app.all_clients') }}</option>
            @foreach($clients as $c)
                <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.filter') }}</button>
    </form>

    @if($quotes->isEmpty())
        <x-empty-state title="{{ __('app.no_quote') }}" description="{{ __('app.no_quote_desc') }}"
            action="{{ __('app.new_quote_title') }}" actionUrl="{{ route('admin.quotes.create') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.number') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">{{ __('app.clients') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">{{ __('app.amount_ttc') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.status') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">{{ __('app.expiration') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($quotes as $quote)
                    @php
                        $sc = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                        $sl = ['draft'=>__('app.status_draft'),'sent'=>__('app.status_sent'),'accepted'=>__('app.status_accepted'),'rejected'=>__('app.status_rejected'),'expired'=>__('app.status_expired')];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4 font-mono text-white">{{ $quote->number }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $quote->client->name }}</td>
                        <td class="px-5 py-4 text-white font-semibold hidden lg:table-cell">{{ number_format($quote->total_ttc, 2, ',', ' ') }} €</td>
                        <td class="px-5 py-4"><x-badge :color="$sc[$quote->status] ?? 'gray'">{{ $sl[$quote->status] ?? $quote->status }}</x-badge></td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $quote->expiry_date?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.quotes.show', $quote) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">{{ __('app.see') }} →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $quotes->links() }}</div>
    @endif
@endsection
