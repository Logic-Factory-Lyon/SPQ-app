@extends('layouts.app')
@section('title', $conversation->title ?: __('app.conversations') . ' #' . $conversation->id)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('manager.conversations.index') }}" class="hover:text-white">{{ __('app.conversations') }}</a>
        <span>/</span>
        <span class="text-white truncate max-w-xs">{{ $conversation->title ?: __('app.conversations') . ' #' . $conversation->id }}</span>
    </div>
@endsection
@section('content')

<div class="max-w-3xl mx-auto">
    <!-- Member info banner -->
    <div class="flex items-center gap-3 mb-6 p-4 bg-gray-900 rounded-xl border border-gray-800">
        <div class="w-10 h-10 rounded-full bg-indigo-700 flex items-center justify-center text-white font-bold">
            {{ strtoupper(substr($conversation->projectMember->user->name, 0, 1)) }}
        </div>
        <div>
            <p class="text-white font-semibold">{{ $conversation->projectMember->user->name }}</p>
            <p class="text-xs text-gray-500">
                @php $ag = $conversation->projectMember->agent; @endphp
                {{ __('app.agent') }} : {{ $ag?->name ?? __('app.no_agent_assigned') }}
                @if($ag?->isTelegram()) <span class="text-blue-400 ml-1">• Telegram</span>@endif
            </p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-xs text-gray-500">{{ __('app.started') }}</p>
            <p class="text-sm text-gray-400">{{ $conversation->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Messages (read-only for manager) -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6">
        <h3 class="text-white font-semibold mb-4">{{ __('app.messages') }}</h3>
        @if($messages->isEmpty())
            <p class="text-gray-500 text-sm text-center py-8">{{ __('app.no_messages') }}</p>
        @else
            <div class="space-y-4">
                @foreach($messages as $msg)
                <div class="flex {{ $msg->isFromUser() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-3 text-sm
                        {{ $msg->isFromUser()
                            ? 'bg-indigo-600 text-white rounded-br-sm'
                            : ($msg->status === 'error'
                                ? 'bg-red-900/50 text-red-300 border border-red-800 rounded-bl-sm'
                                : ($msg->status === 'pending'
                                    ? 'bg-gray-700 text-gray-400 rounded-bl-sm border border-gray-600'
                                    : 'bg-gray-800 text-gray-200 rounded-bl-sm')) }}">
                        <div class="whitespace-pre-wrap">{{ $msg->content }}</div>
                        <div class="flex items-center justify-between gap-4 mt-1">
                            <span class="text-xs opacity-40">
                                {{ $msg->isFromUser() ? __('app.member') : __('app.agent') }}
                            </span>
                            <span class="text-xs opacity-60">{{ $msg->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-4 text-center">
        <p class="text-xs text-gray-600">{{ __('app.read_only_view') }}</p>
    </div>
</div>
@endsection
