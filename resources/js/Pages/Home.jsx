import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    SoccerBall, 
    ChartLineUp, 
    UsersThree, 
    ShieldCheck, 
    ArrowRight,
    CaretRight,
    Monitor,
    Trophy
} from '@phosphor-icons/react';

const FeatureCard = ({ icon: Icon, title, description, delay }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, delay }}
        viewport={{ once: true }}
        class="sim-card p-8 group hover:border-cyan-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-cyan-500/10"
    >
        <div class="bg-[var(--bg-content)]/50 w-14 h-14 rounded-2xl flex items-center justify-center mb-6 border border-[var(--border-muted)] group-hover:bg-cyan-500/20 group-hover:border-cyan-500/30 transition-all">
            <Icon size={32} weight="duotone" class="text-[var(--text-muted)] group-hover:text-cyan-400" />
        </div>
        <h3 class="text-xl font-bold text-white mb-3 tracking-tight">{title}</h3>
        <p class="text-[var(--text-muted)] text-sm leading-relaxed">{description}</p>
    </motion.div>
);

export default function Home() {
    return (
        <div class="min-h-screen bg-[#0f172a] text-slate-100 overflow-x-hidden">
            <Head title="Premium Football Manager" />

            {/* Navigation */}
            <nav class="fixed top-0 w-full z-50 border-b border-white/5 bg-[#0f172a]/80 backdrop-blur-xl">
                <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center text-white font-black shadow-lg shadow-cyan-500/20">
                            OW
                        </div>
                        <span class="font-black text-2xl tracking-tighter">OpenWS</span>
                    </div>
                    <div class="flex items-center gap-6">
                        <Link href="/login" class="text-sm font-bold text-[var(--text-muted)] hover:text-white transition">Login</Link>
                        <Link href="/register" class="bg-cyan-600 hover:bg-cyan-500 text-white text-sm font-bold px-6 py-2.5 rounded-full transition shadow-lg shadow-cyan-600/20">
                            Get Started
                        </Link>
                    </div>
                </div>
            </nav>

            {/* Hero Section */}
            <section class="relative pt-40 pb-32 px-6">
                <div class="absolute inset-0 z-0 overflow-hidden">
                    <div class="absolute top-0 -left-1/4 w-[1000px] h-[1000px] bg-cyan-500/5 rounded-full blur-[120px]"></div>
                    <div class="absolute bottom-0 -right-1/4 w-[800px] h-[800px] bg-indigo-500/5 rounded-full blur-[120px]"></div>
                </div>

                <div class="max-w-7xl mx-auto relative z-10">
                    <div class="grid lg:grid-cols-2 gap-16 items-center">
                        <motion.div
                            initial={{ opacity: 0, x: -50 }}
                            animate={{ opacity: 1, x: 0 }}
                            transition={{ duration: 0.8, ease: "easeOut" }}
                        >
                            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-cyan-400 text-xs font-black uppercase tracking-widest mb-8">
                                <Sparkle size={16} weight="fill" /> Match Engine v2.0 Live
                            </span>
                            <h1 class="text-6xl lg:text-8xl font-black text-white leading-[0.9] tracking-tighter mb-8">
                                BUILD YOUR <br />
                                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-indigo-400 to-purple-500">LEGACY.</span>
                            </h1>
                            <p class="text-xl text-[var(--text-muted)] leading-relaxed mb-12 max-w-xl">
                                Erlebe das ultimative Fussball-Management. Taktik, Transfers, Finanzen – alles in Echtzeit. Werde zur Manager-Legende.
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <Link href="/register" class="group flex items-center gap-3 bg-white text-slate-900 px-8 py-4 rounded-full font-black transition hover:bg-cyan-400">
                                    Start Free Online <ArrowRight size={20} weight="bold" class="group-hover:translate-x-1 transition" />
                                </Link>
                                <a href="#features" class="flex items-center gap-2 text-white font-bold px-8 py-4 hover:text-cyan-400 transition">
                                    Explore Features <CaretRight size={16} weight="bold" />
                                </a>
                            </div>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, scale: 0.8 }}
                            animate={{ opacity: 1, scale: 1 }}
                            transition={{ duration: 1, ease: "easeOut" }}
                            class="relative"
                        >
                            <div class="relative z-10 rounded-3xl border border-white/10 overflow-hidden shadow-2xl shadow-indigo-500/20">
                                <img src="/images/hero.png" alt="Football Manager Hero" class="w-full h-auto" />
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0f172a] via-transparent to-transparent"></div>
                                <div class="absolute bottom-8 left-8 right-8 sim-card-soft p-6 backdrop-blur-md">
                                    <div class="flex items-center gap-4">
                                        <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                                        <span class="text-xs font-bold uppercase tracking-widest text-slate-300">Live Match Interaction Active</span>
                                    </div>
                                </div>
                            </div>
                            <div class="absolute -top-10 -right-10 w-64 h-64 bg-cyan-500/20 blur-[100px] rounded-full animate-pulse"></div>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Stats Section */}
            <section class="py-24 border-y border-white/5 bg-[var(--bg-pillar)]/30">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-12 text-center">
                        {[
                            { label: 'Users', value: '12K+' },
                            { label: 'Matches Played', value: '1.4M' },
                            { label: 'Clubs Managed', value: '45K' },
                            { label: 'Success Rate', value: '99.9%' }
                        ].map((stat, i) => (
                            <motion.div
                                initial={{ opacity: 0 }}
                                whileInView={{ opacity: 1 }}
                                transition={{ delay: i * 0.1 }}
                                viewport={{ once: true }}
                            >
                                <p class="text-4xl lg:text-5xl font-black text-white mb-2">{stat.value}</p>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-cyan-500/60">{stat.label}</p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" class="py-32 px-6">
                <div class="max-w-7xl mx-auto">
                    <div class="text-center mb-20">
                        <h2 class="text-4xl lg:text-6xl font-black text-white mb-6 tracking-tighter">TOTAL CONTROL.</h2>
                        <p class="text-[var(--text-muted)] max-w-2xl mx-auto">
                            Jedes Detail zählt. Von der präzisen Spielerrolle bis zum internationalen Leihgeschäft.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <FeatureCard 
                            icon={SoccerBall} 
                            title="Match Engine" 
                            description="Live Simulation mit Echtzeit-Ereignissen, Ticker-Narrativen und dynamischen Taktik-Anpassungen."
                            delay={0.1}
                        />
                        <FeatureCard 
                            icon={ChartLineUp} 
                            title="Analytics Hub" 
                            description="Analysiere Kader-Performances mit detaillierten Statistiken und Live-Monitoring deiner Systeme."
                            delay={0.2}
                        />
                        <FeatureCard 
                            icon={UsersThree} 
                            title="Scouting & Market" 
                            description="Ein lebendiger Transfermarkt mit Geboten, Listings und strategischen Leihoptionen."
                            delay={0.3}
                        />
                        <FeatureCard 
                            icon={ShieldCheck} 
                            title="Infrastructure" 
                            description="Modernste Docker-Architektur mit Redis, Horizon Monitoring und automatischen Backups."
                            delay={0.4}
                        />
                        <FeatureCard 
                            icon={Monitor} 
                            title="System Hub" 
                            description="Zentrale Steuerung für Administratoren mit Horizon, Telescope und GoAccess Integration."
                            delay={0.5}
                        />
                        <FeatureCard 
                            icon={Trophy} 
                            title="Competition" 
                            description="Ligen-Systeme, Pokal-Modi und internationale Turniere für deinen Hunger nach Erfolg."
                            delay={0.6}
                        />
                    </div>
                </div>
            </section>

            {/* Mockup Section */}
            <section class="py-32 bg-gradient-to-b from-[#0f172a] to-slate-900 border-t border-white/5">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="sim-card p-6 lg:p-12 border-[var(--border-muted)] relative overflow-hidden">
                        <div class="absolute inset-0 bg-cyan-500/5 blur-3xl rounded-full translate-y-1/2"></div>
                        <div class="grid lg:grid-cols-2 gap-16 items-center relative z-10">
                            <div>
                                <h2 class="text-4xl lg:text-6xl font-black text-white mb-8 tracking-tighter leading-tight">
                                    DESIGNED FOR <br /> <span class="text-cyan-400">MASTERY.</span>
                                </h2>
                                <ul class="space-y-6">
                                    {[
                                        'Glasklares UI/UX für maximale Informationstiefe',
                                        'Optimiert für Desktop und High-Performance',
                                        'Live-Update System ohne Seiten-Reloads',
                                        'Vollständig integriertes Debug- & Monitoring Hub'
                                    ].map((item, i) => (
                                        <motion.li 
                                            initial={{ opacity: 0, x: -10 }}
                                            whileInView={{ opacity: 1, x: 0 }}
                                            transition={{ delay: i * 0.1 }}
                                            viewport={{ once: true }}
                                            class="flex items-center gap-4 text-slate-300 font-medium"
                                        >
                                            <div class="h-2 w-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]"></div>
                                            {item}
                                        </motion.li>
                                    ))}
                                </ul>
                            </div>
                            <div class="sim-card border-white/5 shadow-2xl overflow-hidden transform lg:rotate-2 hover:rotate-0 transition-transform duration-700">
                                <img src="/images/mockup.png" alt="Dashboard Mockup" class="w-full h-auto" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer class="py-20 border-t border-white/5">
                <div class="max-w-7xl mx-auto px-6 flex flex-col items-center">
                    <div class="flex items-center gap-3 mb-8">
                        <div class="h-10 w-10 rounded-xl bg-[var(--bg-content)] flex items-center justify-center text-white font-black border border-white/10">
                            OW
                        </div>
                        <span class="font-black text-2xl tracking-tighter">OpenWS</span>
                    </div>
                    <p class="text-[var(--text-muted)] text-sm font-bold uppercase tracking-widest text-center">
                        &copy; 2026 OpenWS Laravell. Simulating Greatness.
                    </p>
                </div>
            </footer>
        </div>
    );
}

const Sparkle = ({ size, weight, className }) => (
    <svg width={size} height={size} viewBox="0 0 256 256" className={className}>
        <path fill="currentColor" d="m230.12 119.88l-40.81-16.32l-16.32-40.81a12 12 0 0 0-22.61 0l-16.32 40.81l-40.81 16.32a12 12 0 0 0 0 22.61l40.81 16.32l16.32 40.81a12 12 0 0 0 22.61 0l16.32-40.81l40.81-16.32a12 12 0 0 0 0-22.61ZM172.06 132a12 12 0 0 0-7.94 7.94l-8.12 20.31l-8.12-20.31a12 12 0 0 0-7.94-7.94l-20.31-8.12l20.31-8.12a12 12 0 0 0 7.94-7.94l8.12-20.31l8.12 20.31a12 12 0 0 0 7.94 7.94l20.31 8.12ZM82.43 65.57l-13.43-5.37l-5.37-13.43a8 8 0 0 0-14.86 0l-5.37 13.43l-13.43 5.37a8 8 0 0 0 0 14.86l13.43 5.37l5.37 13.43a8 8 0 0 0 14.86 0l5.37-13.43l13.43-5.37a8 8 0 0 0 0-14.86ZM48 68.32l-3.32-8.32L48 51.68l3.32 8.32ZM92 184a8 8 0 0 1-8 8h-2.43l-5.37 13.43a8 8 0 0 1-14.86 0L56 192h-2.43a8 8 0 0 1 0-16H56l5.37-13.43a8 8 0 0 1 14.86 0l5.37 13.43H84a8 8 0 0 1 8 8Z"></path>
    </svg>
);
