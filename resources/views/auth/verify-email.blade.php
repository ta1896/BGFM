<x-guest-layout>
    <div class="mb-4 text-sm text-slate-300">
        {{ __('Bitte bestaetige deine E-Mail-Adresse ueber den Link in der Mail.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 text-sm font-medium text-emerald-300">
            {{ __('Ein neuer Verifizierungslink wurde versendet.') }}
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Link erneut senden') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm text-cyan-300 hover:text-cyan-200 focus:outline-none">
                {{ __('Logout') }}
            </button>
        </form>
    </div>
</x-guest-layout>
