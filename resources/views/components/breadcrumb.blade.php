@props(['items' => []])
<nav aria-label="Breadcrumb" class="text-sm text-gray-500">
    <ol class="flex flex-wrap items-center gap-x-2">
        <li><a href="{{ route('dashboard') }}" class="hover:text-gray-900">Dashboard</a></li>
        @foreach ($items as $label => $url)
            <li aria-hidden="true">/</li>
            <li>
                @if ($url)
                    <a href="{{ $url }}" class="hover:text-gray-900">{{ $label }}</a>
                @else
                    <span class="text-gray-900" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>