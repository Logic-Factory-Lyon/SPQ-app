@extends('layouts.app')
@section('title', $conversation->title ?: 'Conversation')
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('employee.conversations.index') }}" class="hover:text-white">Conversations</a>
        <span>/</span>
        <span class="text-white truncate max-w-xs">{{ $conversation->title ?: 'Conversation #' . $conversation->id }}</span>
    </div>
@endsection
@section('header-actions')
    <form method="POST" action="{{ route('employee.conversations.store') }}">
        @csrf
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            + Nouvelle conversation
        </button>
    </form>
    <form method="POST" action="{{ route('employee.conversations.destroy', $conversation) }}"
          onsubmit="return confirm('Supprimer cette conversation ?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="bg-gray-800 hover:bg-red-900 text-gray-400 hover:text-red-400 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Supprimer
        </button>
    </form>
@endsection
@section('content')

<div class="flex flex-col h-[calc(100vh-8rem)] max-w-3xl mx-auto"
     x-data="chatInterface({{ $conversation->id }}, {{ $messages->last()?->id ?? 0 }}, {{ $hasPending ? 'true' : 'false' }})"
     x-init="startPolling()">

    <!-- Agent status -->
    @if($agent)
    <div class="flex items-center gap-2 mb-3 text-xs {{ $machine?->status === 'online' ? 'text-green-400' : 'text-orange-400' }}">
        <div class="w-1.5 h-1.5 rounded-full {{ $machine?->status === 'online' ? 'bg-green-400' : 'bg-orange-400' }}"></div>
        <span class="font-medium">{{ $agent->name }}</span>
        <span>— {{ $machine?->status === 'online' ? 'disponible' : 'hors ligne' }}</span>
    </div>
    @endif

    <!-- Messages -->
    <div class="flex-1 overflow-y-auto space-y-4 pr-2" id="messages-container" x-ref="messagesContainer">
        @foreach($messages as $msg)
        @php $isAgent = $msg->status === 'response' || $msg->status === 'error'; @endphp
        <div class="flex {{ $isAgent ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[80%] rounded-2xl px-4 py-3 text-sm
                {{ $isAgent
                    ? ($msg->status === 'error' ? 'bg-red-900/50 text-red-300 border border-red-800 rounded-bl-sm' : 'bg-gray-800 text-gray-200 rounded-bl-sm')
                    : 'bg-indigo-600 text-white rounded-br-sm' }}">
                <div class="whitespace-pre-wrap">{{ $msg->content }}</div>
                <div class="text-xs opacity-60 mt-1 text-right">{{ $msg->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach

        <!-- Dynamic messages from polling -->
        <template x-for="msg in newMessages" :key="msg.id">
            <div :class="(msg.status === 'response' || msg.status === 'error') ? 'flex justify-start' : 'flex justify-end'">
                <div :class="msg.status === 'response'
                    ? 'max-w-[80%] rounded-2xl rounded-bl-sm px-4 py-3 text-sm bg-gray-800 text-gray-200'
                    : (msg.status === 'error'
                        ? 'max-w-[80%] rounded-2xl rounded-bl-sm px-4 py-3 text-sm bg-red-900/50 text-red-300 border border-red-800'
                        : 'max-w-[80%] rounded-2xl rounded-br-sm px-4 py-3 text-sm bg-indigo-600 text-white')"
                >
                    <div class="whitespace-pre-wrap" x-text="msg.content"></div>
                    <div class="text-xs opacity-60 mt-1 text-right" x-text="msg.created_at"></div>
                </div>
            </div>
        </template>

        <!-- Typing indicator -->
        <div x-show="waiting" class="flex justify-start">
            <div class="bg-gray-800 rounded-2xl rounded-bl-sm px-4 py-3">
                <div class="flex gap-1">
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                    <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay:300ms"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Input -->
    <div class="mt-4 border-t border-gray-800 pt-4">
        <form @submit.prevent="sendMessage()" class="flex gap-3 items-end">
            <textarea
                x-model="input"
                @keydown.enter.exact.prevent="sendMessage()"
                @keydown.enter.shift.exact="input += '\n'"
                placeholder="Écrivez votre message... (Entrée pour envoyer, Maj+Entrée pour aller à la ligne)"
                rows="2"
                class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-white text-sm placeholder-gray-500
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            <button type="submit"
                :disabled="!input.trim() || sending"
                class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed
                       text-white p-3 rounded-xl transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                </svg>
            </button>
        </form>
        <p class="text-xs text-gray-600 mt-1.5">Entrée pour envoyer · Maj+Entrée pour nouvelle ligne</p>
    </div>
</div>

@push('scripts')
<script>
function chatInterface(conversationId, lastMessageId, hasPending) {
    return {
        conversationId,
        lastId: lastMessageId,
        input: '',
        sending: false,
        waiting: hasPending,
        newMessages: [],
        pollingInterval: null,

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) container.scrollTop = container.scrollHeight;
            });
        },

        startPolling() {
            this.scrollToBottom();
            this.pollingInterval = setInterval(() => this.poll(), 3000);
        },

        async poll() {
            try {
                const resp = await fetch(`/employee/conversations/${this.conversationId}/poll?after_id=${this.lastId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await resp.json();
                if (data.messages && data.messages.length) {
                    for (const msg of data.messages) {
                        this.newMessages.push(msg);
                        this.lastId = Math.max(this.lastId, msg.id);
                    }
                    const hasAgentResponse = data.messages.some(m => m.status === 'response' || m.status === 'error');
                    if (hasAgentResponse) this.waiting = false;
                    this.scrollToBottom();
                }
            } catch (e) { /* silent */ }
        },

        async sendMessage() {
            if (!this.input.trim() || this.sending) return;
            const content = this.input.trim();
            this.input = '';
            this.sending = true;
            this.waiting = true;

            // Optimistic: show user message immediately (don't update lastId — it's a fake temp ID)
            this.newMessages.push({
                id: null,
                direction: 'in',
                content,
                status: 'done',
                created_at: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
            });
            this.scrollToBottom();

            try {
                await fetch(`/employee/conversations/${this.conversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content })
                });
            } catch (e) {
                this.waiting = false;
            } finally {
                this.sending = false;
            }
        },
    };
}
</script>
@endpush
@endsection
