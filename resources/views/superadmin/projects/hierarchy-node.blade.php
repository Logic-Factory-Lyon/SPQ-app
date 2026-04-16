@php
    $statusColors = [
        'draft' => 'border-gray-600 bg-gray-800/50',
        'initializing' => 'border-yellow-600 bg-yellow-900/20',
        'ready' => 'border-green-600 bg-green-900/20',
        'error' => 'border-red-600 bg-red-900/20',
    ];
    $statusDots = [
        'draft' => 'bg-gray-500',
        'initializing' => 'bg-yellow-400 animate-pulse',
        'ready' => 'bg-green-400',
        'error' => 'bg-red-400',
    ];
    $members = $agentMembers->get($node->id, collect());
    $hasChildren = $node->childAgents && $node->childAgents->isNotEmpty();
    $isTelegram = $node->telegram_bot_username !== null;
@endphp

<div class="tree-line">
    {{-- Agent card --}}
    <div class="agent-card rounded-lg border {{ $statusColors[$node->status] ?? $statusColors['draft'] }} p-4 mb-3">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <div class="w-2.5 h-2.5 rounded-full shrink-0 {{ $statusDots[$node->status] ?? $statusDots['draft'] }}"></div>
                    <span class="text-white font-semibold text-sm truncate">{{ $node->name }}</span>
                    @if($isTelegram)
                        <span class="text-blue-400 text-xs">Telegram</span>
                    @endif
                    @if($node->macMachine)
                        <span class="text-xs text-gray-500 font-mono">{{ $node->macMachine->name }}</span>
                    @endif
                </div>
                <div class="flex items-center gap-2 ml-4">
                    <code class="text-xs text-gray-500">{{ $node->profile }}</code>
                    <span class="text-xs text-gray-600">·</span>
                    <span class="text-xs text-gray-500">{{ $node->status }}</span>
                </div>

                {{-- Skills --}}
                @if($node->skills && $node->skills->isNotEmpty())
                <div class="flex flex-wrap gap-1 ml-4 mt-2">
                    @foreach($node->skills as $skill)
                        <span class="skill-pill">{{ $skill->name }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Members assigned --}}
                @if($members->isNotEmpty())
                <div class="flex flex-wrap gap-1 ml-4 mt-2">
                    @foreach($members as $member)
                        <span class="member-pill">
                            <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 flex items-center justify-center text-white" style="font-size:8px">{{ strtoupper(substr($member->user->name, 0, 1)) }}</span>
                            {{ $member->user->name }}
                            @if($member->role === 'manager')
                                <span class="text-indigo-300">★</span>
                            @endif
                        </span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Edit link --}}
            <a href="{{ route('admin.agents.edit', $node) }}" class="text-xs text-gray-500 hover:text-indigo-400 shrink-0">edit</a>
        </div>
    </div>

    {{-- Children --}}
    @if($hasChildren)
    <div class="tree-branch">
        @foreach($node->childAgents as $child)
            @include('superadmin.projects.hierarchy-node', ['node' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
    @endif
</div>