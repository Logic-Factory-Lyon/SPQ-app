@extends('layouts.app')
@section('title', 'Mon agent')
@section('header-actions')
    @if($agent && !$agent->isTelegram())
    <button x-data="" @click="$dispatch('open-modal', 'new-conv')"
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Nouvelle conversation
    </button>
    @endif
@endsection
@section('content')
    <x-page-header title="Mon agent"
        subtitle="{{ $agent ? $agent->name : 'Aucun agent assigné — contactez votre manager.' }}" />

    @if(! $agent)
        <div class="mb-6 p-4 bg-yellow-900/30 border border-yellow-800 rounded-xl text-yellow-400 text-sm">
            Aucun agent assigné. Contactez votre manager.
        </div>

    @elseif($agent->isTelegram())
        {{-- ── Telegram bot agent ─────────────────────────────────────── --}}
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <div class="w-20 h-20 rounded-full bg-blue-600/20 border border-blue-600/30 flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 1 0 24 12 12 12 0 0 0 11.944 0zm5.8 8.226-2.01 9.47c-.15.704-.545.876-1.104.545l-3.04-2.24-1.465 1.41c-.162.162-.298.298-.61.298l.218-3.087 5.63-5.086c.245-.217-.054-.338-.378-.121l-6.96 4.384-2.996-.938c-.652-.204-.663-.652.136-.965l11.7-4.51c.543-.197 1.018.133.879.84z"/>
                </svg>
            </div>

            <h2 class="text-2xl font-bold text-white mb-2">{{ $agent->name }}</h2>
            <p class="text-gray-400 mb-8 max-w-sm">
                Votre agent est disponible sur Telegram. Cliquez ci-dessous pour ouvrir le chat directement dans l'application.
            </p>

            <a href="{{ $agent->telegramUrl() }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-3 bg-blue-600 hover:bg-blue-500 text-white font-semibold px-8 py-4 rounded-xl transition-colors text-lg shadow-lg shadow-blue-900/30">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.944 0A12 12 0 1 0 24 12 12 12 0 0 0 11.944 0zm5.8 8.226-2.01 9.47c-.15.704-.545.876-1.104.545l-3.04-2.24-1.465 1.41c-.162.162-.298.298-.61.298l.218-3.087 5.63-5.086c.245-.217-.054-.338-.378-.121l-6.96 4.384-2.996-.938c-.652-.204-.663-.652.136-.965l11.7-4.51c.543-.197 1.018.133.879.84z"/>
                </svg>
                Ouvrir le chat Telegram
            </a>

            <p class="text-xs text-gray-600 mt-6">
                &#64;{{ $agent->telegram_bot_username }}
                &nbsp;·&nbsp;
                Les conversations se passent directement dans Telegram.
            </p>
        </div>

    @else
        {{-- ── Mac Machine agent (legacy daemon) ─────────────────────── --}}
        <div class="flex items-center gap-2 mb-6 text-sm {{ $machine?->status === 'online' ? 'text-green-400' : 'text-red-400' }}">
            <div class="w-2 h-2 rounded-full {{ $machine?->status === 'online' ? 'bg-green-400 animate-pulse' : 'bg-red-400' }}"></div>
            {{ $agent->name }} — {{ $machine?->status === 'online' ? 'en ligne' : 'hors ligne' }}
        </div>

        @if($conversations->isEmpty())
            <x-empty-state title="Aucune conversation"
                description="Démarrez une nouvelle conversation avec votre agent." />
        @else
            <div class="space-y-3">
                @foreach($conversations as $conv)
                <a href="{{ route('employee.conversations.show', $conv) }}"
                   class="block bg-gray-900 rounded-xl border border-gray-800 hover:border-indigo-700 p-5 transition-colors">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-white">{{ $conv->title ?: 'Conversation #' . $conv->id }}</h3>
                        <span class="text-xs text-gray-500">{{ $conv->created_at->diffForHumans() }}</span>
                    </div>
                    @if($conv->latestMessage)
                        <p class="text-sm text-gray-500 mt-1 truncate">
                            {{ Str::limit($conv->latestMessage->content, 100) }}
                        </p>
                    @endif
                </a>
                @endforeach
            </div>
            <div class="mt-4">{{ $conversations->links() }}</div>
        @endif

        <!-- New conversation modal -->
        <div x-data="{ open: false }" @open-modal.window="open = ($event.detail === 'new-conv')" x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div @click.away="open = false" class="bg-gray-900 rounded-2xl border border-gray-800 p-6 w-full max-w-md">
                <h3 class="text-white font-semibold text-lg mb-4">Nouvelle conversation</h3>
                <form method="POST" action="{{ route('employee.conversations.store') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm text-gray-400 mb-1.5">Titre (optionnel)</label>
                        <input type="text" name="title" placeholder="Ex: Analyse des ventes Q1..."
                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 rounded-lg transition-colors">
                            Créer
                        </button>
                        <button type="button" @click="open = false"
                            class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 font-semibold py-2.5 rounded-lg transition-colors">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
