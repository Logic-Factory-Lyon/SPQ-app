@props(['title', 'subtitle' => null])
<div class="mb-6">
    <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
    @if($subtitle)
        <p class="text-gray-400 mt-1">{{ $subtitle }}</p>
    @endif
</div>
