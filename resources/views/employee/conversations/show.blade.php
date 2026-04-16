@extends('layouts.app')
@section('title', $conversation->title ?: __('app.conversations'))
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('employee.conversations.index') }}" class="hover:text-white">{{ __('app.conversations') }}</a>
        <span>/</span>
        <span class="text-white truncate max-w-xs">{{ $conversation->title ?: 'Conversation #' . $conversation->id }}</span>
    </div>
@endsection
@section('header-actions')
    <form method="POST" action="{{ route('employee.conversations.store') }}">
        @csrf
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            {{ __('app.new_conversation_btn') }}
        </button>
    </form>
    <form method="POST" action="{{ route('employee.conversations.destroy', $conversation) }}"
          onsubmit="return confirm('{{ __('app.delete_conversation') }}')">
        @csrf @method('DELETE')
        <button type="submit"
                class="bg-gray-800 hover:bg-red-900 text-gray-400 hover:text-red-400 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            {{ __('app.delete') }}
        </button>
    </form>
@endsection
@section('main_class', 'overflow-hidden p-0')
@section('content')

<div class="flex flex-col h-full max-w-3xl mx-auto p-4 lg:p-6"
     x-data="chatInterface({{ $conversation->id }}, {{ $messages->last()?->id ?? 0 }}, {{ $hasPending ? 'true' : 'false' }})"
     x-init="startPolling()">

    <!-- Agent status -->
    @if($agent)
    <div class="flex items-center gap-2 mb-3 text-xs {{ $machine?->status === 'online' ? 'text-green-400' : 'text-orange-400' }}">
        <div class="w-1.5 h-1.5 rounded-full {{ $machine?->status === 'online' ? 'bg-green-400' : 'bg-orange-400' }}"></div>
        <span class="font-medium">{{ $agent->name }}</span>
        <span>— {{ $machine?->status === 'online' ? __('app.available') : __('app.offline') }}</span>
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
                @if($msg->message_type === 'skill' && !$isAgent)
                    <div class="text-xs font-medium text-indigo-200 mb-1">{{ $msg->metadata['skill_name'] ?? __('app.skill') }}</div>
                @endif
                <div class="whitespace-pre-wrap">{{ $msg->content }}</div>
                <div class="text-xs opacity-60 mt-1 text-right">{{ $msg->created_at->format('H:i') }}</div>
            </div>
        </div>
        @endforeach

        <!-- Dynamic messages from polling -->
        <template x-for="msg in newMessages" :key="msg.id ?? Math.random()">
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

    <!-- Skills panel -->
    @if($skills && $skills->count() > 0)
    <div class="mt-2 mb-1">
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">Skills</span>
            <button type="button" @click="showSkills = !showSkills"
                    class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                <span x-text="showSkills ? '{{ __('app.hide') }}' : '{{ __('app.show') }}'"></span>
            </button>
        </div>
        <div x-show="showSkills" x-transition class="flex gap-2 flex-wrap">
            @foreach($skills as $skill)
            <button type="button" @click="openSkillModal('{{ $skill->slug }}', '{{ $skill->name }}', '{{ $skill->description }}')"
                    class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 border border-gray-700 hover:border-indigo-500
                           text-gray-300 hover:text-white text-sm px-3 py-2 rounded-lg transition-colors">
                @if($skill->icon)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.59L5.5 13.5h13l-3.591-3.092A2.25 2.25 0 0114.25 8.818V3.104a.75.75 0 00-.75-.75h-3.5a.75.75 0 00-.75.75z"/>
                </svg>
                @endif
                {{ $skill->name }}
            </button>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Input -->
    <div class="mt-4 border-t border-gray-800 pt-4">
        <form @submit.prevent="sendMessage()" class="flex gap-3 items-end">
            <textarea
                x-model="input"
                @keydown.enter.exact.prevent="sendMessage()"
                @keydown.enter.shift.exact="input += '\n'"
                placeholder="{{ __('app.chat_placeholder') }}"
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
        <p class="text-xs text-gray-600 mt-1.5">{{ __('app.enter_to_send') }}</p>
    </div>

    <!-- Skill modal -->
    <div x-show="skillModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" x-transition.opacity>
        <div class="bg-gray-900 border border-gray-700 rounded-2xl p-6 w-full max-w-md mx-4 shadow-xl" @click.outside="skillModalOpen = false">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-white font-semibold text-lg" x-text="skillModalName"></h3>
                <button @click="skillModalOpen = false" class="text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-gray-400 text-sm mb-4" x-text="skillModalDescription"></p>
            <div id="skill-params-container" class="space-y-3 mb-4">
                <!-- Dynamic param fields injected by JS -->
            </div>
            <div class="flex gap-3 justify-end">
                <button @click="skillModalOpen = false" class="px-4 py-2 text-sm text-gray-400 hover:text-white transition-colors">{{ __('app.cancel') }}</button>
                <button @click="dispatchSkill()" :disabled="skillDispatching"
                        class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    {{ __('app.launch') }}
                </button>
            </div>
        </div>
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
        showSkills: true,
        skillModalOpen: false,
        skillModalSlug: '',
        skillModalName: '',
        skillModalDescription: '',
        skillDispatching: false,

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
                    // Replace optimistic (temp) messages with real ones from server
                    this.newMessages = this.newMessages.filter(m => !String(m.id).startsWith('temp_'));

                    for (const msg of data.messages) {
                        // Prevent duplicates
                        if (!this.newMessages.some(m => m.id === msg.id)) {
                            this.newMessages.push(msg);
                        }
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

            const tempId = 'temp_' + Date.now();
            this.newMessages.push({
                id: tempId,
                direction: 'in',
                content,
                status: 'done',
                created_at: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
            });
            this.scrollToBottom();

            try {
                const resp = await fetch(`/employee/conversations/${this.conversationId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ content })
                });
                if (resp.ok) this.poll();
            } catch (e) {
                this.waiting = false;
            } finally {
                this.sending = false;
            }
        },

        openSkillModal(slug, name, description) {
            this.skillModalSlug = slug;
            this.skillModalName = name;
            this.skillModalDescription = description;
            this.skillModalOpen = true;

            // Fetch skill params template from the API
            fetch(`/employee/skills`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(r => r.json()).then(data => {
                const skill = data.skills?.find(s => s.slug === slug);
                const container = document.getElementById('skill-params-container');
                container.innerHTML = '';
                if (skill && skill.param_fields) {
                    for (const field of skill.param_fields) {
                        const req = field.required ? '<span class="text-red-400 ml-0.5">*</span>' : '';
                        const desc = field.description ? `<p class="text-xs text-gray-500 mt-0.5 mb-1">${field.description}</p>` : '';

                        if (field.enum) {
                            // Select dropdown for enum values
                            const options = field.enum.map(v => `<option value="${v}">${v}</option>`).join('');
                            container.innerHTML += `
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">${field.label}${req}</label>
                                    ${desc}
                                    <select data-skill-param="${field.key}"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">—</option>
                                        ${options}
                                    </select>
                                </div>`;
                        } else if (field.type === 'number' || field.type === 'integer') {
                            container.innerHTML += `
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">${field.label}${req}</label>
                                    ${desc}
                                    <input type="number" data-skill-param="${field.key}"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>`;
                        } else if (field.format === 'uri') {
                            container.innerHTML += `
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-1">${field.label}${req}</label>
                                    ${desc}
                                    <input type="url" data-skill-param="${field.key}"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm font-mono
                                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        placeholder="https://...">
                                </div>`;
                        } else if (field.type === 'boolean') {
                            container.innerHTML += `
                                <div class="flex items-center gap-2">
                                    <input type="checkbox" data-skill-param="${field.key}" value="true" id="param_${field.key}"
                                        class="rounded border-gray-700 bg-gray-800 text-indigo-600 focus:ring-indigo-500">
                                    <label for="param_${field.key}" class="text-sm text-gray-300">${field.label}</label>
                                </div>`;
                        } else {
                            // Default: text input (textarea for long descriptions)
                            const isLong = field.key === 'instructions' || field.key === 'context' || field.key === 'content';
                            if (isLong) {
                                container.innerHTML += `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">${field.label}${req}</label>
                                        ${desc}
                                        <textarea data-skill-param="${field.key}" rows="3"
                                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                                    </div>`;
                            } else {
                                container.innerHTML += `
                                    <div>
                                        <label class="block text-sm font-medium text-gray-300 mb-1">${field.label}${req}</label>
                                        ${desc}
                                        <input type="text" data-skill-param="${field.key}"
                                            class="w-full bg-gray-800 border border-gray-700 rounded-lg px-3 py-2 text-white text-sm
                                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    </div>`;
                            }
                        }
                    }
                }
            });
        },

        async dispatchSkill() {
            this.skillDispatching = true;
            const params = {};
            document.querySelectorAll('[data-skill-param]').forEach(input => {
                const key = input.dataset.skillParam;
                if (input.type === 'checkbox') {
                    params[key] = input.checked ? 'true' : 'false';
                } else {
                    params[key] = input.value;
                }
            });

            this.waiting = true;
            this.skillModalOpen = false;

            const tempId = 'temp_' + Date.now();
            this.newMessages.push({
                id: tempId,
                direction: 'in',
                content: `[Skill: ${this.skillModalName}]` + (Object.keys(params).length ? ' — ' + Object.entries(params).map(([k,v]) => `${k}: ${v}`).join(', ') : ''),
                status: 'done',
                created_at: new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
            });
            this.scrollToBottom();

            try {
                const resp = await fetch(`/employee/conversations/${this.conversationId}/skill`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ skill_slug: this.skillModalSlug, params })
                });
                if (resp.ok) this.poll();
            } catch (e) {
                this.waiting = false;
            } finally {
                this.skillDispatching = false;
            }
        },
    };
}
</script>
@endpush
@endsection