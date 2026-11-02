@props(['variant' => 'dark', 'href' => null])
@php
    $styles = [
        'dark' => 'bg-gray-800 text-white hover:bg-gray-700 focus:ring-gray-500',
        'light' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:ring-gray-400',
        'danger' => 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-500',
        'success' => 'bg-emerald-600 text-white hover:bg-emerald-500 focus:ring-emerald-500',
    ];
    $class = 'inline-flex items-center rounded-md px-3 py-2 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 '.$styles[$variant];
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $class]) }}>{{ $slot }}</button>
@endif