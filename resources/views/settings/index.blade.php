<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <p class="sim-section-title">Konfiguration</p>
                <h1
                    class="mt-1 text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400">
                    {{ __('Einstellungen') }}
                </h1>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div x-data="{ activeTab: 'general' }" class="sim-card flex flex-col lg:flex-row min-h-[600px] overflow-hidden">
            <!-- Sidebar Navigation -->
            <div
                class="lg:flex-[3] bg-slate-900/50 border-b lg:border-b-0 lg:border-r border-slate-700/50 p-6 flex flex-col gap-2">
                <button @click="activeTab = 'general'"
                    :class="{ 'bg-cyan-500/10 text-cyan-400 border-cyan-500/50': activeTab === 'general', 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 border-transparent': activeTab !== 'general' }"
                    class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 transition-colors duration-200"
                        :class="activeTab === 'general' ? 'text-cyan-400' : 'text-slate-500'" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    {{ __('Allgemein') }}
                </button>

                <button @click="activeTab = 'security'"
                    :class="{ 'bg-cyan-500/10 text-cyan-400 border-cyan-500/50': activeTab === 'security', 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200 border-transparent': activeTab !== 'security' }"
                    class="w-full text-left px-4 py-3 rounded-lg border transition-all duration-200 font-medium flex items-center gap-3">
                    <svg class="w-5 h-5 transition-colors duration-200"
                        :class="activeTab === 'security' ? 'text-cyan-400' : 'text-slate-500'" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    {{ __('Sicherheit & Login') }}
                </button>
            </div>

            <!-- Content Area -->
            <div class="flex-1 w-full bg-slate-900/20 p-6 lg:p-10">

                <!-- General Tab -->
                <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0">

                    <div class="w-full">
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-white mb-2">{{ __('Allgemeine Einstellungen') }}</h2>
                            <p class="text-slate-400 text-sm">Verwalte deine persönlichen Vorlieben und Standard-Werte
                                für das Spiel.</p>
                        </div>

                        <div class="bg-slate-800/30 rounded-xl border border-slate-700/50 p-6">
                            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <x-input-label for="default_club_id" :value="__('Bevorzugter Verein')"
                                        class="text-base font-semibold text-white mb-2" />
                                    <p class="text-sm text-slate-400 mb-3">Dieser Verein wird automatisch ausgewählt,
                                        wenn du dich einloggst.</p>

                                    <div class="relative">
                                        <select id="default_club_id" name="default_club_id"
                                            class="mt-1 block w-full pl-4 pr-10 py-3 text-base border-slate-700 bg-slate-900 text-white focus:border-cyan-500 focus:ring-cyan-500 rounded-lg shadow-sm transition-colors cursor-pointer hover:bg-slate-800">
                                            <option value="">{{ __('Kein Standard-Verein gewählt') }}</option>
                                            @foreach ($userClubs as $club)
                                                <option value="{{ $club->id }}" {{ (old('default_club_id', $user->default_club_id) == $club->id) ? 'selected' : '' }}>
                                                    {{ $club->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    @error('default_club_id')
                                        <div class="text-red-400 text-sm mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="pt-4 border-t border-slate-700/50 flex items-center justify-end gap-4">
                                    @if (session('status') === 'settings-updated')
                                        <p x-data="{ show: true }" x-show="show" x-transition
                                            x-init="setTimeout(() => show = false, 3000)"
                                            class="text-sm text-green-400 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __('Erfolgreich gespeichert') }}
                                        </p>
                                    @endif

                                    <x-primary-button
                                        class="bg-cyan-600 hover:bg-cyan-500 ring-offset-2 ring-offset-slate-900 focus:ring-cyan-500">
                                        {{ __('Änderungen speichern') }}
                                    </x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div x-show="activeTab === 'security'" style="display: none;"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0">

                    <div class="w-full space-y-8">
                        <div>
                            <h2 class="text-xl font-bold text-white mb-2">{{ __('Sicherheit & Zugangsdaten') }}</h2>
                            <p class="text-slate-400 text-sm">Sichere dein Konto mit einem starken Passwort und
                                Passkeys.</p>
                        </div>

                        <!-- Password Section -->
                        <div class="bg-slate-800/30 rounded-xl border border-slate-700/50 p-6">
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                        </path>
                                    </svg>
                                    {{ __('Passwort ändern') }}
                                </h3>
                                <p class="text-sm text-slate-400 mt-1 ml-7">Stelle sicher, dass du ein langes,
                                    zufälliges Passwort verwendest.</p>
                            </div>
                            @include('profile.partials.update-password-form')
                        </div>

                        <!-- Passkeys Section -->
                        <div class="bg-slate-800/30 rounded-xl border border-slate-700/50 p-6">
                            <header class="mb-6">
                                <h3 class="text-lg font-medium text-white flex items-center gap-2">
                                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.2-2.85.577-4.147l.413-1.405M6.343 6.342l-.71-1.833M4 11.11L2.094 13.064m18.847-1.954l1.97 1.954M12 2a15.918 15.918 0 0110.396 2.993m0 0a17.388 17.388 0 007.618 13.136c-1.258-4.234-2.758-8.243-4.469-12.002C15.955 4.542 12 8.441 12 13a1 1 0 01-1 1h-6">
                                        </path>
                                    </svg>
                                    {{ __('Passkeys (WebAuthn)') }}
                                </h3>
                                <p class="text-sm text-slate-400 mt-1 ml-7">
                                    {{ __('Logge dich schneller und sicherer mit FaceID, TouchID oder deinem Sicherheitsschlüssel ein.') }}
                                </p>
                            </header>

                            <div x-data="{
                                registrationStatus: 'idle', // idle, start, success, error
                                registerPasskey() {
                                    this.registrationStatus = 'start';
                                    new window.WebAuthn().register()
                                        .then(response => {
                                            this.registrationStatus = 'success';
                                            setTimeout(() => window.location.reload(), 1500);
                                        })
                                        .catch(error => {
                                            console.error(error);
                                            this.registrationStatus = 'error';
                                        });
                                }
                            }">
                                <!-- List of Passkeys -->
                                @if ($passkeys->isNotEmpty())
                                    <div class="space-y-3 mb-6">
                                        @foreach ($passkeys as $passkey)
                                            <div
                                                class="flex items-center justify-between bg-slate-900 p-4 rounded-lg border border-slate-700/50 group hover:border-slate-600 transition-colors">
                                                <div class="flex items-center gap-4">
                                                    <div
                                                        class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-purple-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                                            </path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div
                                                            class="text-sm font-medium text-white group-hover:text-cyan-400 transition-colors">
                                                            {{ $passkey->alias ?? 'Unbenannter Schlüssel' }}
                                                        </div>
                                                        <div class="text-xs text-slate-500">
                                                            Hinzugefügt am {{ $passkey->created_at->format('d.m.Y \u\m H:i') }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <form method="POST"
                                                    action="{{ route('settings.passkeys.destroy', $passkey->id) }}"
                                                    onsubmit="return confirm('Möchtest du diesen Passkey wirklich dauerhaft entfernen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-400/10 rounded-lg transition-colors"
                                                        title="Passkey löschen">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-8 border-2 border-dashed border-slate-700 rounded-xl mb-6">
                                        <svg class="w-12 h-12 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                        <p class="text-slate-400">{{ __('Du hast noch keine Passkeys eingerichtet.') }}</p>
                                    </div>
                                @endif

                                <div>
                                    <x-primary-button type="button" @click="registerPasskey()"
                                        ::disabled="registrationStatus === 'start'"
                                        class="w-full sm:w-auto bg-purple-600 hover:bg-purple-500 focus:ring-purple-500">
                                        <div class="flex items-center gap-2" x-show="registrationStatus !== 'start'">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <span>{{ __('Neuen Passkey hinzufügen') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2" x-show="registrationStatus === 'start'">
                                            <svg class="animate-spin h-5 w-5 text-white"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                    stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                </path>
                                            </svg>
                                            <span>{{ __('Warte auf Sicherheitsschlüssel...') }}</span>
                                        </div>
                                    </x-primary-button>

                                    <div class="mt-3">
                                        <p x-show="registrationStatus === 'success'"
                                            class="text-sm text-green-400 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            {{ __('Passkey erfolgreich eingerichtet! Seite wird neu geladen...') }}
                                        </p>
                                        <p x-show="registrationStatus === 'error'"
                                            class="text-sm text-red-400 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ __('Fehler beim Hinzufügen. Bitte versuche es erneut oder nutze einen anderen Browser.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>