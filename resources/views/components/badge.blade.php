@props(['color' => 'gray'])
@php
$colors = [
    'green'  => 'bg-green-900/50 text-green-300 border-green-700',
    'red'    => 'bg-red-900/50 text-red-300 border-red-700',
    'yellow' => 'bg-yellow-900/50 text-yellow-300 border-yellow-700',
    'blue'   => 'bg-blue-900/50 text-blue-300 border-blue-700',
    'indigo' => 'bg-indigo-900/50 text-indigo-300 border-indigo-700',
    'gray'   => 'bg-gray-800 text-gray-300 border-gray-700',
    'orange' => 'bg-orange-900/50 text-orange-300 border-orange-700',
];
$cls = $colors[$color] ?? $colors['gray'];
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2 py-0.5 rounded text-xs font-medium border $cls"]) }}>
    {{ $slot }}
</span>
