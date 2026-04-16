@extends('layouts.app')
@section('title', __('app.team_conversations_title'))
@section('content')
    <x-page-header title="{{ __('app.team_conversations_title') }}" />

    @if($conversations->isEmpty())
        <x-empty-state title="{{ __('app.no_conversations') }}" description="{{ __('app.no_team_conversations') }}" />
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.employee') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3">{{ __('app.conversations') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden md:table-cell">{{ __('app.last_message') }}</th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase px-5 py-3 hidden lg:table-cell">{{ __('app.date') }}</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($conversations as $conv)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-indigo-700 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($conv->projectMember->user->name, 0, 1)) }}
                                </div>
                                <span class="text-gray-300 font-medium">{{ $conv->projectMember->user->name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-white font-medium">
                            {{ $conv->title ?: __('app.conversations') . ' #' . $conv->id }}
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs hidden md:table-cell max-w-xs truncate">
                            {{ $conv->latestMessage ? Str::limit($conv->latestMessage->content, 60) : '—' }}
                        </td>
                        <td class="px-5 py-4 text-gray-500 text-xs hidden lg:table-cell">
                            {{ $conv->created_at->diffForHumans() }}
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('manager.conversations.show', $conv) }}"
                               class="text-indigo-400 hover:text-indigo-300 text-sm font-medium">{{ __('app.see') }} →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $conversations->links() }}</div>
    @endif
@endsection
