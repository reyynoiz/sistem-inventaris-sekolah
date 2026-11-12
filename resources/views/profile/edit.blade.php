<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <!-- Foto Profil -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Foto Profil') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Format JPG, PNG, atau WebP, maksimal 2 MB, minimal 100 x 100 piksel.') }}
                            </p>
                        </header>

                        <div class="mt-6" x-data="{ preview: null }">
                            <div class="flex items-center gap-4">
                                <template x-if="preview">
                                    <img :src="preview" alt="Pratinjau foto baru" class="h-20 w-20 rounded-full object-cover">
                                </template>
                                <div x-show="! preview">
                                    <x-avatar :user="$user" />
                                </div>

                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                    <p class="text-gray-600">{{ $user->email }}</p>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                                @csrf

                                <div>
                                    <x-input-label for="photo">
                                        {{ __('Pilih foto baru') }} <span class="text-red-500">*</span>
                                    </x-input-label>
                                    <input id="photo" name="photo" type="file" required autofocus
                                           accept="image/jpeg,image/png,image/webp"
                                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                           class="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-gray-800 file:px-3 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-widest file:text-white hover:file:bg-gray-700">
                                    <x-input-error class="mt-2" :messages="$errors->get('photo')" />
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button>{{ __('Simpan Foto') }}</x-primary-button>
                                </div>
                            </form>

                            @if ($user->photo)
                                <form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-3"
                                      onsubmit="return confirm('Hapus foto profil?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button>{{ __('Hapus Foto') }}</x-danger-button>
                                </form>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <!-- Informasi Akun -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Informasi Akun') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Detail peran dan aktivitas peminjaman akun Anda saat ini.') }}
                            </p>
                        </header>

                        <dl class="mt-6 space-y-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-700">Peran (Role)</dt>
                                <dd class="mt-1">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $user->isAdmin() ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="font-medium text-gray-700">Bergabung sejak</dt>
                                <dd class="mt-1 text-gray-900">{{ $user->created_at->translatedFormat('j F Y') }}</dd>
                            </div>

                            @if ($stats)
                                <div>
                                    <dt class="font-medium text-gray-700">Ringkasan peminjaman</dt>
                                    <dd class="mt-2 grid grid-cols-3 gap-3 text-center">
                                        <div class="rounded-md bg-gray-50 p-3">
                                            <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['borrowed'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">Sedang dipinjam</p>
                                        </div>
                                        <div class="rounded-md bg-gray-50 p-3">
                                            <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['overdue'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">Terlambat</p>
                                        </div>
                                        <div class="rounded-md bg-gray-50 p-3">
                                            <p class="text-xl font-semibold text-gray-900">{{ number_format($stats['total'], 0, ',', '.') }}</p>
                                            <p class="text-xs text-gray-500">Total</p>
                                        </div>
                                    </dd>
                                </div>

                                <!-- Placeholder Trust Score -->
                                <div>
                                    <dt class="font-medium text-gray-700">Trust Score</dt>
                                    <dd class="mt-1 flex items-center">
                                        <span class="text-2xl font-bold text-gray-900">TBA</span>
                                        <span class="ml-2 text-sm text-gray-500">/ Tier: Reguler</span>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>