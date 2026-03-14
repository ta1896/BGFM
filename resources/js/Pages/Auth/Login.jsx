import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { User, Lock, ArrowRight, WarningCircle } from '@phosphor-icons/react';

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
        <div class="min-h-screen bg-[#0f172a] text-slate-100 flex items-center justify-center p-6 relative overflow-hidden font-sans">
            <Head title="Login" />

            {/* Background elements */}
            <div class="absolute top-0 -left-1/4 w-[1000px] h-[1000px] bg-cyan-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 -right-1/4 w-[800px] h-[800px] bg-indigo-500/5 rounded-full blur-[120px]"></div>

            <motion.div 
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                class="w-full max-w-md z-10"
            >
                <div class="text-center mb-10">
                    <Link href="/" class="inline-flex items-center gap-3 mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center text-white font-black shadow-xl shadow-cyan-500/20">
                            OW
                        </div>
                    </Link>
                    <h1 class="text-4xl font-black text-white tracking-tighter">WELCOME BACK.</h1>
                    <p class="text-slate-400 mt-2 font-medium">Logge dich ein um deinen Kader zu steuern.</p>
                </div>

                <div class="sim-card p-10 border-white/5 backdrop-blur-2xl bg-slate-900/40 shadow-2xl shadow-indigo-500/5">
                    {status && (
                        <div class="mb-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-4 rounded-xl text-sm font-bold">
                            {status}
                        </div>
                    )}

                    <form onSubmit={submit} class="space-y-6">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Email Adresse</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                    <User size={20} weight="duotone" />
                                </div>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    placeholder="manager@openws.de"
                                    class="w-full bg-slate-800/50 border border-white/5 rounded-xl py-4 pl-12 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all font-medium"
                                    required
                                />
                            </div>
                            {errors.email && (
                                <div class="mt-2 flex items-center gap-2 text-rose-400 text-xs font-bold">
                                    <WarningCircle size={16} weight="fill" /> {errors.email}
                                </div>
                            )}
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500">Passwort</label>
                                {canResetPassword && (
                                    <Link href={route('password.request')} class="text-[10px] font-black uppercase tracking-widest text-cyan-400 hover:text-cyan-300">
                                        Forgot?
                                    </Link>
                                )}
                            </div>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                    <Lock size={20} weight="duotone" />
                                </div>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    placeholder="••••••••"
                                    class="w-full bg-slate-800/50 border border-white/5 rounded-xl py-4 pl-12 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all font-medium"
                                    required
                                />
                            </div>
                            {errors.password && (
                                <div class="mt-2 flex items-center gap-2 text-rose-400 text-xs font-bold">
                                    <WarningCircle size={16} weight="fill" /> {errors.password}
                                </div>
                            )}
                        </div>

                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                checked={data.remember}
                                onChange={(e) => setData('remember', e.target.checked)}
                                class="w-5 h-5 rounded border-white/10 bg-slate-800 text-cyan-500 focus:ring-cyan-500/50"
                            />
                            <span class="text-sm font-bold text-slate-400">Angemeldet bleiben</span>
                        </div>

                        <button 
                            disabled={processing}
                            class="w-full bg-gradient-to-r from-cyan-500 to-indigo-600 hover:from-cyan-400 hover:to-indigo-500 text-white font-black py-4 rounded-xl shadow-lg shadow-cyan-600/30 transition-all flex items-center justify-center gap-2 group disabled:opacity-50"
                        >
                            {processing ? 'Signing in...' : 'Sign In'}
                            <ArrowRight size={20} weight="bold" class="group-hover:translate-x-1 transition" />
                        </button>
                    </form>
                </div>

                <div class="mt-8 text-center text-slate-500">
                    <p class="text-sm font-bold">
                        Noch keinen Account? <Link href="/register" class="text-cyan-400 hover:text-cyan-300 ml-1">Kostenlos registrieren</Link>
                    </p>
                </div>
            </motion.div>
        </div>
    );
}
