<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="sim-section-title">Profil</p>
            <h1 class="mt-1 text-2xl font-bold text-white">{{ __('Profil verwalten') }}</h1>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="sim-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
        </div>

        <div class="sim-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
        </div>

        <div class="sim-card p-4 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
        </div>
    </div>
</x-app-layout>
