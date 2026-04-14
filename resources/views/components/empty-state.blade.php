@props(['title', 'description' => null, 'action' => null, 'actionUrl' => null])
<div class="text-center py-16">
    <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
    </div>
    <h3 class="text-lg font-medium text-white mb-1">{{ $title }}</h3>
    @if($description)
        <p class="text-gray-400 text-sm mb-6">{{ $description }}</p>
    @endif
    @if($action && $actionUrl)
        <a href="{{ $actionUrl }}"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            {{ $action }}
        </a>
    @endif
</div>
