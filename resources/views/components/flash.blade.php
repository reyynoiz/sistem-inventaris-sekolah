@if (session('success'))
    <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ session('error') }}</div>
@endif