@extends('layouts.app')
@section('title', __('app.dashboard'))
@section('content')
    <x-page-header title="{{ __('app.hello', ['name' => auth()->user()->name]) }}"
        subtitle="{{ $member ? __('app.project_label', ['name' => $member->project->name]) : __('app.no_project') }}" />

    @if($member)
        @php $agent = $member->agent; $machine = $agent?->macMachine; @endphp
        <div class="flex items-center gap-2 mb-6 p-4 bg-gray-900 rounded-xl border border-gray-800">
            <div class="w-2.5 h-2.5 rounded-full {{ $machine?->status === 'online' ? 'bg-green-400 animate-pulse' : 'bg-gray-600' }}"></div>
            <div class="text-sm {{ $machine?->status === 'online' ? 'text-green-400' : 'text-gray-500' }}">
                @if($agent)
                    <span class="font-medium text-white">{{ $agent->name }}</span>
                    <span class="text-gray-500 ml-2">{{ $machine?->status === 'online' ? '· ' . __('app.available') : '· ' . __('app.offline') }}</span>
                @else
                    {{ __('app.no_agent') }}
                @endif
            </div>
        </div>
    @else
        <div class="mb-6 p-4 bg-yellow-900/30 border border-yellow-800 rounded-xl text-yellow-400 text-sm">
            {{ __('app.contact_manager') }}
        </div>
    @endif

    @if($member && $member->agent)
    <div class="mb-6">
        <form method="POST" action="{{ route('employee.conversations.store') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold px-6 py-3 rounded-xl transition-colors text-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                {{ __('app.start_conversation') }}
            </button>
        </form>
    </div>
    @endif

    @if($recentConversations->isNotEmpty())
    <div class="bg-gray-900 rounded-xl border border-gray-800">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
            <h3 class="font-semibold text-white">{{ __('app.recent_conversations') }}</h3>
            <a href="{{ route('employee.conversations.index') }}" class="text-sm text-indigo-400 hover:text-indigo-300">{{ __('app.see_all') }}</a>
        </div>
        <div class="divide-y divide-gray-800">
            @foreach($recentConversations as $conv)
            <a href="{{ route('employee.conversations.show', $conv) }}"
               class="flex items-center justify-between px-5 py-3 hover:bg-gray-800/50 transition-colors">
                <div>
                    <p class="text-sm font-medium text-white">{{ $conv->title ?: 'Conversation #' . $conv->id }}</p>
                    @if($conv->latestMessage)
                        <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($conv->latestMessage->content, 60) }}</p>
                    @endif
                </div>
                <span class="text-xs text-gray-600">{{ $conv->created_at->diffForHumans() }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif
@endsection