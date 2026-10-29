<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Kartu Informasi Akun -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            {{ __('Informasi Akun') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Detail peran dan status akun Anda saat ini.') }}
                        </p>
                    </header>

                    <div class="mt-6 space-y-4">
                        <div>
                            <span class="block text-sm font-medium text-gray-700">Peran (Role)</span>
                            <div class="mt-1">
                                <span class="px-3 py-1 text-xs font-semibold rounded-full {{ Auth::user()->role === 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst(Auth::user()->role) }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Placeholder untuk Trust Score (TTU) nanti -->
                        @if(Auth::user()->role === 'user')
                        <div>
                            <span class="block text-sm font-medium text-gray-700">Trust Score</span>
                            <div class="mt-1 flex items-center">
                                <span class="text-2xl font-bold text-gray-900">TBA</span>
                                <span class="ml-2 text-sm text-gray-500">/ Tier: Reguler</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Edit Profil Bawaan -->
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