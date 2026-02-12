<x-guest-layout>
    <x-auth-session-status class="mb-4 text-sm text-emerald-300" :status="session('status')" />

    <h2 class="text-2xl font-bold text-white">Manager-Login</h2>
    <p class="mt-2 text-sm text-slate-300">Melde dich an und fuehre deinen Verein durch den naechsten Spieltag.</p>

    <div class="mt-4 sim-card-soft p-3">
        <p class="text-xs uppercase tracking-[0.14em] text-slate-400">Direkt nach Login</p>
        <p class="mt-1 text-sm text-slate-200">Aufstellung setzen, Matchcenter starten, Transfermarkt checken.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
        @csrf

        <div>
            <label for="email" class="sim-label">E-Mail</label>
            <input id="email" class="sim-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-rose-300" />
        </div>

        <div>
            <label for="password" class="sim-label">Passwort</label>
            <input id="password" class="sim-input" type="password" name="password" required autocomplete="current-password">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-rose-300" />
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-slate-300">
            <input id="remember_me" type="checkbox" class="rounded border-slate-600 bg-slate-900 text-cyan-400 focus:ring-cyan-400" name="remember">
            Eingeloggt bleiben
        </label>

        <div class="flex flex-col gap-3 pt-2 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm text-cyan-300 hover:text-cyan-200" href="{{ route('password.request') }}">
                    Passwort vergessen?
                </a>
            @endif
            <button class="sim-btn-primary" type="submit">Einloggen</button>
        </div>
    </form>

    <p class="mt-6 text-sm text-slate-300">
        Noch kein Konto?
        <a href="{{ route('register') }}" class="font-semibold text-cyan-300 hover:text-cyan-200">Jetzt registrieren</a>
    </p>
</x-guest-layout>
