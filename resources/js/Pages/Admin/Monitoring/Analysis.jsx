import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear, 
    Scroll,
    Warning,
    CheckCircle,
    Info,
    ArrowsClockwise,
    Target,
    ChartBar,
    X
} from '@phosphor-icons/react';
import { motion } from 'framer-motion';

export default function Analysis({ match, matchDiagnostics }) {
    const { data, setData, get, processing } = useForm({
        match_id: new URLSearchParams(window.location.search).get('match_id') || ''
    });

    const { post: repair } = useForm();
    const { post: simulate } = useForm();

    const handleSearch = (e) => {
        e.preventDefault();
        get(route('admin.monitoring.analysis'));
    };

    const handleClear = () => {
        router.get(route('admin.monitoring.analysis'));
    };

    const handleRepair = (type, id) => {
        repair(route('admin.monitoring.repair', { type, id }));
    };

    const handleReSimulate = (matchId) => {
        if (confirm('Wirklich Re-Simulieren? Überschreibt alle aktuellen Daten.')) {
            simulate(route('matches.simulate', { match: matchId }));
        }
    };

    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis'), active: true },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab') },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler') },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals') },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs') },
    ];

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-cyan-400">System Monitoring</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Match-Analyse</h1>
                        <p className="mt-2 text-sm text-slate-300">Detailansicht der Simulations-Parameter.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zur Übersicht</Link>
                </div>
            }
        >
            <Head title="Match Analyse" />

            <div className="space-y-8">
                {/* Sub Navigation */}
                <div className="flex flex-wrap gap-4 mb-2">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm font-bold border ${
                                item.active 
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 border-indigo-500' 
                                : 'bg-slate-800 text-slate-300 hover:bg-slate-700 border-slate-700'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                {/* Search Card */}
                <div className="sim-card p-6 border-b-4 border-b-cyan-500">
                    <form onSubmit={handleSearch} className="flex gap-4 items-end">
                        <div className="flex-1">
                            <label className="block text-[10px] font-black text-slate-400 uppercase mb-2 tracking-widest">Match ID suchen</label>
                            <input 
                                type="number" 
                                value={data.match_id}
                                onChange={e => setData('match_id', e.target.value)}
                                className="w-full bg-slate-900/50 border-slate-700 rounded-xl text-white focus:ring-cyan-500/50 focus:border-cyan-500 text-sm p-3" 
                                placeholder="z.B. 546"
                            />
                        </div>
                        <button 
                            type="submit" 
                            disabled={processing}
                            className="px-8 py-3 bg-cyan-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-cyan-500 transition shadow-lg shadow-cyan-500/20 disabled:opacity-50"
                        >
                            {processing ? 'Lädt...' : 'Analysieren'}
                        </button>
                        {match && (
                            <button 
                                type="button"
                                onClick={handleClear}
                                className="px-4 py-3 bg-slate-800 text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-700 transition border border-slate-700 flex items-center gap-2"
                            >
                                <X size={16} />
                                Leeren
                            </button>
                        )}
                    </form>
                </div>

                {match ? (
                    <div className="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                        {/* Diagnostics */}
                        {matchDiagnostics.length > 0 && (
                            <div className="sim-card p-6 border-l-4 border-l-red-500 bg-red-500/5">
                                <div className="flex items-center gap-4 mb-6">
                                    <div className="p-3 bg-red-500/20 rounded-xl text-red-400 ring-1 ring-inset ring-red-500/20">
                                        <Warning size={24} weight="bold" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-black text-white uppercase tracking-tight">Match-Probleme erkannt</h3>
                                        <p className="text-xs text-slate-400 italic">Gezielte Diagnose fand {matchDiagnostics.length} Probleme mit diesem Eintrag.</p>
                                    </div>
                                </div>
                                <div className="grid gap-3">
                                    {matchDiagnostics.map((diag, idx) => (
                                        <div key={idx} className="flex items-center justify-between p-4 bg-slate-900/60 rounded-2xl border border-white/5 hover:border-red-500/20 transition-colors">
                                            <div className="flex items-center gap-4">
                                                <span className={`flex h-2 w-2 rounded-full ${diag.severity === 'CRITICAL' ? 'bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]' : 'bg-orange-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]'}`}></span>
                                                <p className="text-sm text-slate-200 font-bold">{diag.description}</p>
                                            </div>
                                            <button 
                                                onClick={() => handleRepair(diag.action_type, match.id)}
                                                className="px-5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-[10px] font-black uppercase tracking-[0.2em] rounded-xl border border-slate-700 transition active:scale-95"
                                            >
                                                {diag.action_label}
                                            </button>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div className="lg:col-span-1 space-y-6">
                                {/* Score Card */}
                                <div className="sim-card p-6 border-l-4 border-l-cyan-500 bg-gradient-to-br from-slate-900 to-slate-950">
                                    <h3 className="text-xs font-black text-slate-500 uppercase tracking-widest mb-6 pb-2 border-b border-white/5">Spielergebnis</h3>
                                    <div className="flex justify-between items-center bg-black/40 p-6 rounded-[2rem] mb-6 border border-white/5 shadow-inner">
                                        <div className="text-center w-5/12">
                                            <div className="text-[9px] text-slate-500 uppercase font-black mb-1">Heim</div>
                                            <div className="font-black text-xs text-white uppercase truncate">{match.home_club.name}</div>
                                        </div>
                                        <div className="text-3xl font-black text-cyan-400 tabular-nums">{match.home_score} : {match.away_score}</div>
                                        <div className="text-center w-5/12">
                                            <div className="text-[9px] text-slate-500 uppercase font-black mb-1">Gast</div>
                                            <div className="font-black text-xs text-white uppercase truncate">{match.away_club.name}</div>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="bg-slate-900/50 p-4 rounded-2xl border border-white/5 relative overflow-hidden group">
                                            <div className="absolute inset-0 bg-cyan-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            <span className="text-[9px] text-slate-500 block mb-1 uppercase tracking-widest font-black">Status</span> 
                                            <span className="font-black text-cyan-400 uppercase text-xs tracking-wider">{match.status}</span>
                                        </div>
                                        <div className="bg-slate-900/50 p-4 rounded-2xl border border-white/5 relative overflow-hidden group">
                                            <div className="absolute inset-0 bg-white/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                            <span className="text-[9px] text-slate-500 block mb-1 uppercase tracking-widest font-black">Minute</span> 
                                            <span className="font-black text-white text-xs tabular-nums">{match.live_minute}'</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="sim-card p-6 text-center group hover:border-indigo-500/30 transition-all border border-transparent">
                                     <button 
                                        onClick={() => handleReSimulate(match.id)}
                                        className="w-full py-4 bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 font-black rounded-2xl hover:bg-indigo-600 hover:text-white transition-all uppercase tracking-widest text-[10px] flex items-center justify-center gap-3"
                                     >
                                        <ArrowsClockwise size={18} weight="bold" className="group-hover:rotate-180 transition-transform duration-700" />
                                        Match Re-Simulieren
                                     </button>
                                     <p className="text-[9px] text-slate-600 mt-3 uppercase font-black tracking-tighter">Vorsicht: Überschreibt alle aktuellen Daten</p>
                                </div>

                                <div className="sim-card p-6 bg-gradient-to-br from-slate-900 to-slate-950">
                                    <h3 className="text-xs font-black text-slate-500 uppercase tracking-widest mb-6 pb-2 border-b border-white/5 flex items-center gap-2">
                                        <ChartBar size={16} />
                                        Simulation Insights
                                    </h3>
                                    <div className="space-y-6">
                                        <div>
                                            <div className="flex justify-between text-xs mb-2 items-end">
                                                <span className="text-[10px] text-slate-400 uppercase font-black tracking-tighter">Events</span>
                                                <span className="font-black text-cyan-400 text-lg tabular-nums">{match.events?.length || 0}</span>
                                            </div>
                                            <div className="h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                                <motion.div 
                                                    initial={{ width: 0 }}
                                                    animate={{ width: `${Math.min(100, (match.events?.length || 0) * 4)}%` }}
                                                    transition={{ duration: 1, ease: 'easeOut' }}
                                                    className="bg-cyan-500 h-full shadow-[0_0_8px_rgba(6,182,212,0.5)]"
                                                ></motion.div>
                                            </div>
                                        </div>
                                        <div>
                                            <div className="flex justify-between text-xs mb-2 items-end">
                                                <span className="text-[10px] text-slate-400 uppercase font-black tracking-tighter">Live Actions</span>
                                                <span className="font-black text-indigo-400 text-lg tabular-nums">{match.live_actions?.length || 0}</span>
                                            </div>
                                            <div className="h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                                <motion.div 
                                                    initial={{ width: 0 }}
                                                    animate={{ width: `${Math.min(100, (match.live_actions?.length || 0) / 1.5)}%` }}
                                                    transition={{ duration: 1, ease: 'easeOut', delay: 0.2 }}
                                                    className="bg-indigo-500 h-full shadow-[0_0_8px_rgba(99,102,241,0.5)]"
                                                ></motion.div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="lg:col-span-2 space-y-6">
                                <section className="sim-card overflow-hidden bg-slate-900/50">
                                    <div className="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/20">
                                        <h3 className="text-lg font-black text-white flex items-center gap-3 capitalize">
                                            <Target size={24} className="text-indigo-400" />
                                            Event Timeline (Detail)
                                        </h3>
                                        <div className="text-[10px] text-slate-500 font-bold uppercase tracking-widest flex items-center gap-2">
                                            <span className="flex h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Expert Mode Active
                                        </div>
                                    </div>
                                    <div className="overflow-x-auto custom-scrollbar max-h-[800px]">
                                        <table className="w-full text-left">
                                            <thead className="sticky top-0 z-10">
                                                <tr className="bg-slate-900 text-slate-500 uppercase text-[10px] font-black tracking-widest border-b border-white/5">
                                                    <th className="py-4 px-6">Min</th>
                                                    <th className="py-4 px-6">Team</th>
                                                    <th className="py-4 px-6">Typ</th>
                                                    <th className="py-4 px-6 text-center">Outcome</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-white/5">
                                                {(match.live_actions || []).sort((a,b) => a.minute - b.minute).map((action, idx) => (
                                                    <tr key={idx} className="hover:bg-white/5 transition-colors group">
                                                        <td className="py-4 px-6 font-mono font-black text-slate-400 tabular-nums">{action.minute}'</td>
                                                        <td className="py-4 px-6">
                                                            {action.club_id == match.home_club_id ? (
                                                                <span className="text-cyan-400 font-black text-[10px] tracking-widest uppercase bg-cyan-500/10 px-2 py-1 rounded">Heim</span>
                                                            ) : (
                                                                <span className="text-indigo-400 font-black text-[10px] tracking-widest uppercase bg-indigo-500/10 px-2 py-1 rounded">Gast</span>
                                                            )}
                                                        </td>
                                                        <td className="py-4 px-6 text-slate-200 capitalize text-xs font-bold tracking-tight">
                                                            {action.action_type?.replace(/_/g, ' ')}
                                                        </td>
                                                        <td className="py-4 px-6 text-center">
                                                            {action.outcome === 'success' ? (
                                                                <span className="px-3 py-1 bg-emerald-500/20 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase tracking-tighter">Erfolg</span>
                                                            ) : action.outcome === 'fail' ? (
                                                                <span className="px-3 py-1 bg-red-500/20 text-red-400 border border-red-500/20 rounded-full text-[9px] font-black uppercase tracking-tighter">Fehler</span>
                                                            ) : (
                                                                <span className="px-3 py-1 bg-slate-800 text-slate-400 rounded-full text-[9px] font-black uppercase group-hover:bg-slate-700 transition tracking-tighter">
                                                                    {action.outcome || '-'}
                                                                </span>
                                                            )}
                                                        </td>
                                                    </tr>
                                                ))}
                                                {(!match.live_actions || match.live_actions.length === 0) && (
                                                    <tr>
                                                        <td colSpan="4" className="py-40 text-center">
                                                            <div className="text-6xl mb-6 opacity-10 filter grayscale">📭</div>
                                                            <p className="text-slate-500 italic text-[10px] uppercase tracking-widest font-black">Keine Live Actions für dieses Match vorhanden.</p>
                                                        </td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                ) : (
                    <div className="sim-card p-32 text-center border-2 border-dashed border-white/5 bg-slate-900/10 rounded-[3rem] group">
                        <div className="text-8xl mb-10 opacity-10 group-hover:scale-110 group-hover:-rotate-12 transition-all duration-700 pointer-events-none grayscale group-hover:grayscale-0">🔍</div>
                        <h3 className="text-2xl font-black text-white tracking-tight mb-4 uppercase">Simulations-Protokoll Diagnostics</h3>
                        <p className="text-slate-500 font-medium max-w-sm mx-auto leading-relaxed text-sm">
                            Geben Sie oben eine Match ID ein, um das Simulationsprotokoll zu diagnostizieren und Fehler zu beheben.
                        </p>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
