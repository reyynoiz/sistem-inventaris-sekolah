<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Ubah Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Gunakan password yang panjang dan acak agar akun tetap aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password">
                {{ __('Password saat ini') }} <span class="text-red-500">*</span>
            </x-input-label>
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full"
                          placeholder="Masukkan password saat ini" required autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password">
                {{ __('Password baru') }} <span class="text-red-500">*</span>
            </x-input-label>
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full"
                          placeholder="Minimal 8 karakter" required autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation">
                {{ __('Ulangi password baru') }} <span class="text-red-500">*</span>
            </x-input-label>
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full"
                          placeholder="Ketik ulang password baru" required autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Simpan') }}</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Tersimpan.') }}</p>
            @endif
        </div>
    </form>
</section>