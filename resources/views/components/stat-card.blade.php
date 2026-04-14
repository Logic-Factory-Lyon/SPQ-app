@props(['label', 'value', 'color' => 'indigo', 'icon' => null])
<div class="bg-gray-900 rounded-xl border border-gray-800 p-5">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-400">{{ $label }}</p>
        @if($icon)
            <div class="w-9 h-9 bg-{{ $color }}-900/50 rounded-lg flex items-center justify-center">
                {!! $icon !!}
            </div>
        @endif
    </div>
    <p class="text-3xl font-bold text-white mt-2">{{ $value }}</p>
</div>
