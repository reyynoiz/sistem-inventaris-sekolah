@props(['label', 'value', 'tone' => 'gray'])
@php
    $tones = [
        'gray' => 'border-gray-300',
        'blue' => 'border-blue-500',
        'red' => 'border-red-500',
        'green' => 'border-emerald-500',
    ];
@endphp
<div class="rounded-md border-l-4 bg-white p-4 shadow-sm {{ $tones[$tone] }}">
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format($value, 0, ',', '.') }}</p>
</div>