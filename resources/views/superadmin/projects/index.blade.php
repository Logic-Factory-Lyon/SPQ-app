@extends('layouts.app')
@section('title', __('app.projects'))
@section('header')
    <h2 class="text-lg font-semibold text-white">{{ __('app.projects') }}</h2>
@endsection
@section('content')
    <x-page-header title="{{ __('app.all_projects') }}" />
    <form method="GET" class="mb-6 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search') }}"
            class="flex-1 max-w-sm bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('app.all_statuses') }}</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('app.status_active') }}</option>
            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>{{ __('app.status_suspended') }}</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('app.status_cancelled') }}</option>
        </select>
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">{{ __('app.filter') }}</button>
    </form>

    @if($projects->isEmpty())
        <x-empty-state title="{{ __('app.no_project') }}" description="{{ __('app.no_project_desc') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.project') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">{{ __('app.clients') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden lg:table-cell">{{ __('app.members') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.status') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($projects as $project)
                    @php
                        $colors = ['active' => 'green', 'suspended' => 'yellow', 'cancelled' => 'gray'];
                        $labels = ['active' => __('app.status_active'), 'suspended' => __('app.status_suspended'), 'cancelled' => __('app.status_cancelled')];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-white">{{ $project->name }}</div>
                            @if($project->macMachines->count())
                                <div class="text-xs text-gray-500 mt-0.5">
                                    {{ $project->macMachines->count() }} {{ __('app.machines_count') }}
                                    @php $online = $project->macMachines->where('status', 'online')->count() @endphp
                                    @if($online) &mdash; <span class="text-green-400">{{ $online }} {{ __('app.online_count') }}</span>@endif
                                </div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $project->client->name }}</td>
                        <td class="px-5 py-4 text-gray-400 hidden lg:table-cell">{{ $project->members_count }}</td>
                        <td class="px-5 py-4">
                            <x-badge :color="$colors[$project->status] ?? 'gray'">{{ $labels[$project->status] ?? $project->status }}</x-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.projects.show', $project) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">{{ __('app.see') }} &rarr;</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $projects->links() }}</div>
    @endif
@endsection
