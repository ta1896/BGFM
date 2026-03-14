import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowRight, Lock, User } from '@phosphor-icons/react';
import AuthField from '@/Components/AuthField';
import AuthLayout from '@/Layouts/AuthLayout';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title="Login"
            heading="Welcome Back."
            subtitle="Logge dich ein, um deinen Kader zu steuern."
            footer={(
                <>
                    Noch keinen Account?
                    <Link href={route('register')} className="ml-1 text-cyan-400 hover:text-cyan-300">
                        Kostenlos registrieren
                    </Link>
                </>
            )}
        >
            {status && (
                <div className="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-400">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-6">
                <AuthField
                    label="Email Adresse"
                    icon={User}
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="manager@openws.de"
                    error={errors.email}
                    required
                />

                <div>
                    <div className="mb-2 flex items-center justify-between">
                        <label className="block text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">
                            Passwort
                        </label>
                        {canResetPassword && (
                            <Link href={route('password.request')} className="text-[10px] font-black uppercase tracking-widest text-cyan-400 hover:text-cyan-300">
                                Forgot?
                            </Link>
                        )}
                    </div>
                    <AuthField
                        label=""
                        icon={Lock}
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder="••••••••"
                        error={errors.password}
                        required
                        className="space-y-0"
                    />
                </div>

                <div className="flex items-center gap-3">
                    <input
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                        className="h-5 w-5 rounded border-white/10 bg-[var(--bg-content)] text-cyan-500 focus:ring-cyan-500/50"
                    />
                    <span className="text-sm font-bold text-[var(--text-muted)]">Angemeldet bleiben</span>
                </div>

                <button
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 py-4 font-black text-white shadow-lg shadow-cyan-600/30 transition-all hover:from-cyan-400 hover:to-indigo-500 disabled:opacity-50"
                >
                    {processing ? 'Signing in...' : 'Sign In'}
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>
            </form>
        </AuthLayout>
    );
}
