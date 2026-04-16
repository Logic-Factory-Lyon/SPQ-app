@extends('layouts.app')
@section('title', __('app.agents'))
@section('header-actions')
    <a href="{{ route('admin.agents.create') }}"
       class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + {{ __('app.add_agent') }}
    </a>
@endsection
@section('content')
    <x-page-header title="{{ __('app.agents') }}" />

    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-800 text-left">
                    <th class="px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('app.agent_name') }}</th>
                    <th class="px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('app.status') }}</th>
                    <th class="px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('app.machine') }}</th>
                    <th class="px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('app.skills') }}</th>
                    <th class="px-5 py-3 text-xs font-medium text-gray-400 uppercase tracking-wide">{{ __('app.last_sync') }}</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($agents as $agent)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.agents.edit', $agent) }}" class="text-white font-medium hover:text-indigo-400">
                            {{ $agent->name }}
                        </a>
                        @if($agent->profile)
                            <span class="text-xs text-gray-500 ml-2">{{ $agent->profile }}</span>
                        @endif
                        @if($agent->parentAgent)
                            <span class="text-xs text-gray-600 ml-1">← {{ $agent->parentAgent->name }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @php
                            $statusColors = [
                                'draft' => 'bg-gray-700 text-gray-300',
                                'initializing' => 'bg-yellow-900/50 text-yellow-300',
                                'ready' => 'bg-green-900/50 text-green-300',
                                'error' => 'bg-red-900/50 text-red-300',
                            ];
                        @endphp
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$agent->status] ?? 'bg-gray-700 text-gray-300' }}">
                            {{ $agent->status }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-sm text-gray-400">
                        {{ $agent->macMachine?->name ?? '—' }}
                    </td>
                    <td class="px-5 py-3">
                        @foreach($agent->skills as $skill)
                            <span class="inline-block px-2 py-0.5 rounded text-xs bg-indigo-900/50 text-indigo-300 mr-1">{{ $skill->name }}</span>
                        @endforeach
                        @if($agent->skills->isEmpty())
                            <span class="text-xs text-gray-600">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">
                        {{ $agent->openclaw_profile_synced_at?->diffForHumans() ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-right">
                        <a href="{{ route('admin.agents.edit', $agent) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">{{ __('app.edit') }}</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($agents->hasPages())
            <div class="px-5 py-3 border-t border-gray-800">{{ $agents->links() }}</div>
        @endif
    </div>
@endsection