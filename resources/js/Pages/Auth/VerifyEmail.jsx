import React from 'react';
import { router } from '@inertiajs/react';
import { ArrowRight } from '@phosphor-icons/react';
import AuthLayout from '@/Layouts/AuthLayout';

export default function VerifyEmail() {
    const resend = (e) => {
        e.preventDefault();
        router.post(route('verification.send'));
    };

    const logout = (e) => {
        e.preventDefault();
        router.post(route('logout'));
    };

    return (
        <AuthLayout
            title="Email verifizieren"
            heading="Verify Your Mail."
            subtitle="Bitte bestätige deine E-Mail-Adresse über den Link in der Mail."
        >
            <div className="space-y-6">
                <button
                    onClick={resend}
                    className="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 py-4 font-black text-white shadow-lg shadow-cyan-600/30 transition-all hover:from-cyan-400 hover:to-indigo-500"
                >
                    Link erneut senden
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>

                <button
                    onClick={logout}
                    className="w-full rounded-xl border border-white/10 bg-[var(--bg-content)]/40 py-4 font-black uppercase tracking-widest text-slate-300 transition hover:bg-[var(--bg-content)]/70 hover:text-white"
                >
                    Logout
                </button>
            </div>
        </AuthLayout>
    );
}
