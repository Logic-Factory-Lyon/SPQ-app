@extends('layouts.app')
@section('title', __('app.service_catalog'))
@section('header')
    <h2 class="text-lg font-semibold text-white">{{ __('app.service_catalog') }}</h2>
@endsection
@section('header-actions')
    <a href="{{ route('admin.services.create') }}"
       class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        {{ __('app.new_service') }}
    </a>
@endsection
@section('content')
    <x-page-header title="{{ __('app.service_catalog') }}" subtitle="{{ __('app.services_subtitle') }}" />
    <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-800">
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.service') }}</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">{{ __('app.price_ht') }}</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">{{ __('app.billing_type') }}</th>
                    <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.status') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($services as $service)
                @php $btl = ['one_time' => __('app.billing_one_time'), 'monthly' => __('app.billing_monthly'), 'yearly' => __('app.billing_yearly')] @endphp
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-5 py-4">
                        <div class="font-medium text-white">{{ $service->name }}</div>
                        @if($service->description)
                            <div class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $service->description }}</div>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-gray-300 hidden md:table-cell">{{ number_format($service->unit_price_ht, 2, ',', ' ') }} &euro;</td>
                    <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $btl[$service->billing_type] ?? $service->billing_type }}</td>
                    <td class="px-5 py-4">
                        <x-badge :color="$service->active ? 'green' : 'gray'">
                            {{ $service->active ? __('app.status_active') : __('app.status_inactive') }}
                        </x-badge>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-3">
                            <form method="POST" action="{{ route('admin.services.toggle', $service) }}">
                                @csrf
                                <button class="text-xs text-gray-500 hover:text-yellow-400 transition-colors">
                                    {{ $service->active ? __('app.disable') : __('app.enable') }}
                                </button>
                            </form>
                            <a href="{{ route('admin.services.edit', $service) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">{{ __('app.edit') }}</a>
                        </div>
                    </td>
                </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">{{ __('app.no_service') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
