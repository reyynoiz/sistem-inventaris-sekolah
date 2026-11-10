@props(['user', 'size' => 'h-20 w-20'])
@php
    $initials = collect(explode(' ', trim($user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp
@if ($user->photo_url)
    <img src="{{ $user->photo_url }}" alt="Foto profil {{ $user->name }}"
         {{ $attributes->merge(['class' => $size.' rounded-full object-cover']) }}>
@else
    <span role="img" aria-label="Foto profil {{ $user->name }}"
          {{ $attributes->merge(['class' => $size.' inline-flex items-center justify-center rounded-full bg-gray-200 text-lg font-semibold text-gray-600']) }}>{{ $initials }}</span>
@endif