@php $control = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500'; @endphp
<form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="space-y-5">
    @csrf
    @if ($user->exists)
        @method('PUT')
    @endif

    <x-field name="name" label="Nama lengkap" required>
        <x-text-input id="name" name="name" type="text" class="block w-full"
                      :value="old('name', $user->name)" placeholder="Contoh: Maya Lestari" required autofocus />
    </x-field>

    <x-field name="email" label="Email" required>
        <x-text-input id="email" name="email" type="email" class="block w-full"
                      :value="old('email', $user->email)" placeholder="nama@sekolah.com" required />
    </x-field>

    <x-field name="role" label="Role" required>
        <select id="role" name="role" class="{{ $control }}" required>
            <option value="user" @selected(old('role', $user->role ?? 'user') === 'user')>User (guru atau siswa)</option>
            <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
        </select>
    </x-field>

    <x-field name="password" label="Kata sandi" :required="! $user->exists">
        <x-text-input id="password" name="password" type="password" class="block w-full"
                      placeholder="{{ $user->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}"
                      :required="! $user->exists" autocomplete="new-password" />
    </x-field>

    <x-field name="password_confirmation" label="Ulangi kata sandi" :required="! $user->exists">
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block w-full"
                      placeholder="Ketik ulang kata sandi" :required="! $user->exists" autocomplete="new-password" />
    </x-field>

    <div class="flex gap-2">
        <x-btn>Simpan</x-btn>
        <x-btn variant="light" :href="route('users.index')">Batal</x-btn>
    </div>
</form>