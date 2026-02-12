<x-guest-layout>
    <h2 class="text-2xl font-bold text-white">Registrierung</h2>
    <p class="mt-2 text-sm text-slate-300">Lege dein Managerkonto an und starte deine Simulation.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="sim-label">Name</label>
            <input id="name" class="sim-input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label for="email" class="sim-label">E-Mail</label>
            <input id="email" class="sim-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label for="password" class="sim-label">Passwort</label>
            <input id="password" class="sim-input" type="password" name="password" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label for="password_confirmation" class="sim-label">Passwort bestaetigen</label>
            <input id="password_confirmation" class="sim-input" type="password" name="password_confirmation" required autocomplete="new-password">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a class="text-sm text-cyan-300 hover:text-cyan-200" href="{{ route('login') }}">
                Bereits registriert?
            </a>
            <button class="sim-btn-primary" type="submit">Account erstellen</button>
        </div>
    </form>
</x-guest-layout>
