@extends('layouts.app')
@section('title', __('app.users'))
@section('header')
    <h2 class="text-lg font-semibold text-white">{{ __('app.users') }}</h2>
@endsection
@section('content')
    <x-page-header title="{{ __('app.users') }}" subtitle="{{ __('app.all_accounts') }}" />

    <form method="GET" class="mb-6 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.name_or_email') }}"
            class="flex-1 max-w-sm bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="role" class="bg-gray-800 border border-gray-700 rounded-lg px-4 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">{{ __('app.all_roles') }}</option>
            <option value="superadmin" {{ request('role') === 'superadmin' ? 'selected' : '' }}>{{ __('app.role_superadmin') }}</option>
            <option value="client" {{ request('role') === 'client' ? 'selected' : '' }}>{{ __('app.role_client') }}</option>
            <option value="manager" {{ request('role') === 'manager' ? 'selected' : '' }}>{{ __('app.role_manager') }}</option>
            <option value="employee" {{ request('role') === 'employee' ? 'selected' : '' }}>{{ __('app.role_employee') }}</option>
        </select>
        <button class="bg-gray-700 hover:bg-gray-600 text-white text-sm px-4 py-2 rounded-lg transition-colors">{{ __('app.filter') }}</button>
        @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="bg-gray-800 hover:bg-gray-700 text-gray-400 text-sm px-4 py-2 rounded-lg transition-colors">
                {{ __('app.clear') }}
            </a>
        @endif
    </form>

    @if($users->isEmpty())
        <x-empty-state title="{{ __('app.no_user_search') }}" description="{{ __('app.no_user_search') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.user_label') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3 hidden md:table-cell">{{ __('app.clients') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-5 py-3">{{ __('app.role') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($users as $user)
                    @php
                        $rc = ['superadmin' => 'indigo', 'client' => 'blue', 'manager' => 'indigo', 'employee' => 'gray'];
                        $rl = ['superadmin' => __('app.role_superadmin'), 'client' => __('app.role_client'), 'manager' => __('app.role_manager'), 'employee' => __('app.role_employee')];
                    @endphp
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-medium text-white">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-400 hidden md:table-cell">{{ $user->client?->name ?? '—' }}</td>
                        <td class="px-5 py-4">
                            <x-badge :color="$rc[$user->role] ?? 'gray'">{{ $rl[$user->role] ?? $user->role }}</x-badge>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">{{ __('app.edit') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    @endif
@endsection