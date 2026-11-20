@props(['item', 'size' => 'h-12 w-12'])
@if ($item->photo_url)
    <img src="{{ $item->photo_url }}" alt="Foto {{ $item->name }}" loading="lazy"
         {{ $attributes->merge(['class' => $size.' shrink-0 rounded-md object-cover']) }}>
@else
    <span role="img" aria-label="Belum ada foto {{ $item->name }}"
          {{ $attributes->merge(['class' => $size.' shrink-0 inline-flex items-center justify-center rounded-md bg-gray-100 text-gray-400']) }}>
        <svg class="h-1/2 w-1/2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
        </svg>
    </span>
@endif