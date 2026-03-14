import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { User, Envelope, Lock, ArrowRight, WarningCircle, Sparkle } from '@phosphor-icons/react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <div class="min-h-screen bg-[#0f172a] text-slate-100 flex items-center justify-center p-6 relative overflow-hidden font-sans">
            <Head title="Register" />

            {/* Background elements */}
            <div class="absolute top-0 -left-1/4 w-[1000px] h-[1000px] bg-indigo-500/5 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 -right-1/4 w-[800px] h-[800px] bg-cyan-500/5 rounded-full blur-[120px]"></div>

            <motion.div 
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                class="w-full max-w-lg z-10"
            >
                <div class="text-center mb-10">
                    <Link href="/" class="inline-flex items-center gap-3 mb-6">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center text-white font-black shadow-xl shadow-cyan-500/20">
                            OW
                        </div>
                    </Link>
                    <h1 class="text-4xl font-black text-white tracking-tighter uppercase italic">Start Your Legacy.</h1>
                    <p class="text-slate-400 mt-2 font-medium">Erstelle dein Managerkonto in weniger als 30 Sekunden.</p>
                </div>

                <div class="sim-card p-10 border-white/5 backdrop-blur-2xl bg-slate-900/40 shadow-2xl shadow-indigo-500/5 relative">
                    <div class="absolute -top-3 -right-3">
                        <div class="bg-cyan-500 text-white text-[10px] font-black px-3 py-1.5 rounded-full shadow-lg shadow-cyan-500/20 flex items-center gap-2">
                             FREE ACCESS
                        </div>
                    </div>

                    <form onSubmit={submit} class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                             <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Manager Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                        <User size={20} weight="duotone" />
                                    </div>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        placeholder="Alex Mueller"
                                        class="w-full bg-slate-800/50 border border-white/5 rounded-xl py-4 pl-12 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all font-medium"
                                        required
                                    />
                                </div>
                                {errors.name && (
                                    <div class="mt-2 flex items-center gap-2 text-rose-400 text-xs font-bold">
                                        <WarningCircle size={16} weight="fill" /> {errors.name}
                                    </div>
                                )}
                            </div>

                            <div>
                                <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                        <Envelope size={20} weight="duotone" />
                                    </div>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        placeholder="alex@manager.de"
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
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Passwort wählen</label>
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

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">Passwort bestätigen</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                    <Lock size={20} weight="duotone" />
                                </div>
                                <input
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) => setData('password_confirmation', e.target.value)}
                                    placeholder="••••••••"
                                    class="w-full bg-slate-800/50 border border-white/5 rounded-xl py-4 pl-12 pr-4 text-white focus:outline-none focus:ring-2 focus:ring-cyan-500/50 focus:border-cyan-500/50 transition-all font-medium"
                                    required
                                />
                            </div>
                        </div>

                        <button 
                            disabled={processing}
                            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black py-4 rounded-xl shadow-lg shadow-indigo-600/30 transition-all flex items-center justify-center gap-3 group disabled:opacity-50"
                        >
                            {processing ? 'Creating Account...' : 'Create Manager Account'}
                            <ArrowRight size={20} weight="bold" class="group-hover:translate-x-1 transition" />
                        </button>
                    </form>
                </div>

                <div class="mt-8 text-center text-slate-500">
                    <p class="text-sm font-bold">
                        Bereits Manager? <Link href="/login" class="text-indigo-400 hover:text-indigo-300 ml-1">Hier einloggen</Link>
                    </p>
                </div>
            </motion.div>
        </div>
    );
}
