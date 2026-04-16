@extends('layouts.app')
@section('title', __('app.agent_hierarchy') . ' — ' . $project->name)
@section('header')
    <div class="flex items-center gap-2 text-sm text-gray-400">
        <a href="{{ route('admin.projects.index') }}" class="hover:text-white">{{ __('app.projects') }}</a>
        <span>/</span>
        <a href="{{ route('admin.projects.show', $project) }}" class="hover:text-white">{{ $project->name }}</a>
        <span>/</span>
        <span class="text-white">{{ __('app.agent_hierarchy') }}</span>
    </div>
@endsection
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-white">{{ __('app.agent_hierarchy') }}</h1>
        <a href="{{ route('admin.projects.show', $project) }}" class="text-sm text-gray-400 hover:text-white">&larr; {{ __('app.back_to_project') }}</a>
    </div>

    @if($rootAgents->isEmpty())
        <div class="bg-gray-900 rounded-xl border border-gray-800 p-8 text-center">
            <p class="text-gray-500">{{ __('app.no_agents_configured') }}</p>
        </div>
    @else
    <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 overflow-x-auto">
        {{-- Tree visualization --}}
        <div class="min-w-[600px]">
            @foreach($rootAgents as $agent)
                @include('superadmin.projects.hierarchy-node', ['node' => $agent, 'depth' => 0])
            @endforeach
        </div>
    </div>

    {{-- Orphan agents --}}
    @php
        $orphanAgents = $allAgents->filter(fn($a) => $a->parent_agent_id !== null && !$allAgents->contains('id', $a->parent_agent_id));
    @endphp
    @if($orphanAgents->isNotEmpty())
    <div class="mt-4 bg-gray-900 rounded-xl border border-yellow-800/50 p-6">
        <h3 class="text-yellow-400 text-sm font-semibold mb-3">{{ __('app.orphan_agents') }}</h3>
        <div class="space-y-2">
            @foreach($orphanAgents as $agent)
                <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-4 py-2">
                    <span class="text-white text-sm font-medium">{{ $agent->name }}</span>
                    <code class="text-xs text-gray-500">{{ $agent->profile }}</code>
                    <span class="text-xs text-yellow-500 ml-auto">parent_id={{ $agent->parent_agent_id }}</span>
                </div>
            @endforeach
        </div>
    </div>
    @endif
    @endif
@endsection

@section('scripts')
<style>
.tree-line { position: relative; }
.tree-line::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #374151;
}
.tree-branch { position: relative; padding-left: 48px; }
.tree-branch::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 28px;
    width: 24px;
    height: 2px;
    background: #374151;
}
.agent-card {
    transition: all 0.15s ease;
}
.agent-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.member-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 8px;
    border-radius: 9999px;
    font-size: 11px;
    background: #1e1b4b;
    color: #a5b4fc;
    border: 1px solid #312e81;
}
.skill-pill {
    display: inline-flex;
    align-items: center;
    padding: 1px 6px;
    border-radius: 4px;
    font-size: 10px;
    background: #164e63;
    color: #67e8f9;
    border: 1px solid #155e75;
}
</style>
@endsection
