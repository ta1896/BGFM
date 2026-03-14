import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Users, UserGear, BuildingOffice, UserCircle, 
    CalendarCheck, Suitcase, Warehouse, Tent,
    Lightning, Play, ChartLineUp, ListNumbers,
    CaretRight, ArrowsClockwise
} from '@phosphor-icons/react';

const StatCard = ({ title, value, icon: Icon, color = 'cyan' }) => (
    <motion.div 
        initial={{ opacity: 0, scale: 0.95 }}
        animate={{ opacity: 1, scale: 1 }}
        className="sim-card p-5 relative overflow-hidden group"
    >
        <div className={`absolute -right-4 -bottom-4 opacity-5 group-hover:scale-110 transition-transform duration-500 text-${color}-400`}>
            <Icon size={100} weight="fill" />
        </div>
        <div className="relative z-10">
            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-1">{title}</p>
            <div className="flex items-end gap-3">
                <p className="text-3xl font-black text-white leading-none">{value}</p>
            </div>
        </div>
    </motion.div>
);

const SectionHeader = ({ title, icon: Icon }) => (
    <div className="flex items-center gap-3 mb-6">
        <div className="p-2 rounded-lg bg-slate-800/50 border border-slate-700/30">
            <Icon size={20} className="text-cyan-400" />
        </div>
        <h2 className="text-lg font-bold text-white tracking-tight leading-none uppercase italic">{title}</h2>
    </div>
);

export default function Dashboard({ stats, latestUsers, latestClubs, activeCompetitionSeasons, simulationSettings }) {
    const { data, setData, post, processing } = useForm({
        competition_season_id: '',
    });

    const runSimulation = (e) => {
        e.preventDefault();
        post(route('admin.simulation.process-matchday'));
    };

    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />

            <div className="space-y-10 pb-20">
                {/* Stats Grid */}
                <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <StatCard title="Total User" value={stats.users} icon={Users} />
                    <StatCard title="Administratoren" value={stats.admins} icon={UserGear} color="indigo" />
                    <StatCard title="Vereine Gesamt" value={stats.clubs} icon={BuildingOffice} />
                    <StatCard title="CPU Teams" value={stats.cpu_clubs} icon={Lightning} color="amber" />
                    <StatCard title="Total Spieler" value={stats.players} icon={UserCircle} />
                    <StatCard title="Aufstellungen" value={stats.lineups} icon={ListNumbers} />
                    <StatCard title="Geplante Spiele" value={stats.scheduled_matches} icon={CalendarCheck} color="cyan" />
                    <StatCard title="Sponsoren" value={stats.active_sponsors} icon={Suitcase} />
                    <StatCard title="Stadionprojekte" value={stats.active_stadium_projects} icon={Warehouse} />
                    <StatCard title="Trainingslager" value={stats.active_training_camps} icon={Tent} />
                </div>

                <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">
                    {/* Quick Actions & Simulation */}
                    <div className="space-y-8">
                        <section className="sim-card p-6 border-cyan-500/10 shadow-[0_0_50px_rgba(34,211,238,0.03)]">
                            <SectionHeader title="Simulation & Kontrolle" icon={Play} />
                            
                            <form onSubmit={runSimulation} className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 p-4 rounded-2xl bg-slate-950/50 border border-slate-800/50">
                                <div className="md:col-span-2">
                                    <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2 px-1">Wettbewerb wählen</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.competition_season_id}
                                        onChange={e => setData('competition_season_id', e.target.value)}
                                    >
                                        <option value="">Alle aktiven Ligen</option>
                                        {activeCompetitionSeasons.map(cs => (
                                            <option key={cs.id} value={cs.id}>{cs.label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <button 
                                        type="submit" 
                                        disabled={processing}
                                        className="sim-btn-primary w-full flex items-center justify-center gap-2 h-[46px]"
                                    >
                                        {processing ? <ArrowsClockwise size={18} className="animate-spin" /> : <Play size={18} weight="fill" />}
                                        Spieltag starten
                                    </button>
                                </div>
                            </form>

                            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <Link href={route('admin.competitions.create')} className="sim-action-btn">Liga erstellen</Link>
                                <Link href={route('admin.clubs.create')} className="sim-action-btn primary">Verein erstellen</Link>
                                <Link href={route('admin.players.create')} className="sim-action-btn">Spieler erstellen</Link>
                                <Link href={route('admin.match-engine.index')} className="sim-action-btn primary">Match Engine</Link>
                                <Link href={route('admin.monitoring.index')} className="sim-action-btn highlight">System Monitor</Link>
                                <Link href={route('admin.simulation.settings.index')} className="sim-action-btn">Simulation Setup</Link>
                            </div>
                        </section>
                    </div>

                    {/* Activity Lists */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-8">
                        {/* Latest Users */}
                        <section className="sim-card p-6">
                            <SectionHeader title="Letzte User" icon={Users} />
                            <div className="space-y-2">
                                {latestUsers.map(user => (
                                    <div key={user.id} className="flex items-center justify-between p-3 rounded-xl bg-slate-800/20 border border-slate-800/50 group hover:bg-slate-800/40 transition">
                                        <div className="min-w-0">
                                            <p className="text-sm font-bold text-white leading-none mb-1 truncate">{user.name}</p>
                                            <p className="text-[10px] text-slate-500 font-medium truncate">{user.email}</p>
                                        </div>
                                        {user.is_admin && (
                                            <span className="text-[9px] font-black bg-cyan-500/10 text-cyan-400 px-2 py-0.5 rounded border border-cyan-500/20 uppercase tracking-widest">Admin</span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </section>

                        {/* Latest Clubs */}
                        <section className="sim-card p-6">
                            <SectionHeader title="Letzte Vereine" icon={BuildingOffice} />
                            <div className="space-y-2">
                                {latestClubs.map(club => (
                                    <div key={club.id} className="flex items-center justify-between p-3 rounded-xl bg-slate-800/20 border border-slate-800/50 group hover:bg-slate-800/40 transition">
                                        <div className="min-w-0">
                                            <p className="text-sm font-bold text-white leading-none mb-1 truncate">{club.name}</p>
                                            <p className="text-[10px] text-slate-500 font-medium truncate italic">Owner: {club.user?.name || 'CPU'}</p>
                                        </div>
                                        <Link 
                                            href={route('admin.clubs.edit', club.id)}
                                            className="p-1.5 text-slate-500 hover:text-cyan-400 hover:bg-cyan-500/10 rounded-lg transition"
                                        >
                                            <CaretRight size={16} weight="bold" />
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>
                </div>
            </div>

        </AdminLayout>
    );
}
