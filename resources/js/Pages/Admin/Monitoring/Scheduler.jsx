import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear, 
    Scroll,
    Heartbeat,
    Clock,
    CheckCircle,
    Info
} from '@phosphor-icons/react';

export default function Scheduler({ runs }) {
    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab') },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler'), active: true },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals') },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs') },
    ];

    const totalProcessed = runs.reduce((acc, run) => acc + (run.matches_processed || 0), 0);
    const avgDuration = runs.length 
        ? runs.reduce((acc, run) => {
            if (run.finished_at) {
                const start = new Date(run.started_at);
                const end = new Date(run.finished_at);
                return acc + (end - start) / 1000;
            }
            return acc;
        }, 0) / runs.filter(r => r.finished_at).length
        : 0;
    
    const successRate = runs.length
        ? (runs.filter(r => r.finished_at).length / runs.length) * 100
        : 0;

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-indigo-400">System Monitoring</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Scheduler-Status</h1>
                        <p className="mt-2 text-sm text-slate-300">Tracking der automatisierten Hintergrund-Tasks.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zur Übersicht</Link>
                </div>
            }
        >
            <Head title="Scheduler Status" />

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

                {/* Table Card */}
                <div className="sim-card overflow-hidden">
                    <div className="p-6 border-b border-white/5 flex justify-between items-center bg-slate-800/20">
                        <h3 className="text-lg font-black text-white flex items-center gap-3">
                            <Heartbeat size={24} className="text-indigo-400" />
                            Letzte 20 Simulation-Runs
                        </h3>
                        <div className="text-[10px] text-slate-500 font-black uppercase tracking-widest flex items-center gap-2">
                            <Clock size={14} />
                            Live Refreshed: {new Date().toLocaleTimeString()}
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="bg-slate-900/50 text-slate-500 uppercase text-[10px] tracking-widest font-black border-b border-white/5">
                                    <th className="py-4 px-6 md:px-8">Run ID</th>
                                    <th className="py-4 px-6 md:px-8">Startzeit</th>
                                    <th className="py-4 px-6 md:px-8">Endzeit</th>
                                    <th className="py-4 px-6 md:px-8 text-center">Matches</th>
                                    <th className="py-4 px-6 md:px-8">Status</th>
                                    <th className="py-4 px-6 md:px-8 text-right">Dauer</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {runs.map((run) => (
                                    <tr key={run.id} className="hover:bg-white/5 transition-colors group">
                                        <td className="py-4 px-6 md:px-8 font-mono text-slate-500 text-xs tabular-nums font-bold">#{run.id}</td>
                                        <td className="py-4 px-6 md:px-8 text-slate-300 text-xs tabular-nums">{run.started_at}</td>
                                        <td className="py-4 px-6 md:px-8 text-slate-400 text-xs tabular-nums">{run.finished_at || '-'}</td>
                                        <td className="py-4 px-6 md:px-8 text-center">
                                            <span className="font-black text-cyan-400 text-sm tabular-nums">{run.matches_processed || 0}</span>
                                        </td>
                                        <td className="py-4 px-6 md:px-8">
                                            {run.finished_at ? (
                                                <span className="px-3 py-1 bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 rounded-full text-[9px] font-black uppercase tracking-tighter">Success</span>
                                            ) : (
                                                <span className="px-3 py-1 bg-amber-500/10 text-amber-400 border border-amber-500/20 rounded-full text-[9px] font-black uppercase tracking-tighter animate-pulse shadow-[0_0_8px_rgba(245,158,11,0.3)]">Running</span>
                                            )}
                                        </td>
                                        <td className="py-4 px-6 md:px-8 text-right font-mono text-indigo-400 text-xs font-black tabular-nums">
                                            {run.finished_at ? (
                                                `${( (new Date(run.finished_at) - new Date(run.started_at)) / 1000 ).toFixed(1)}s`
                                            ) : '-'}
                                        </td>
                                    </tr>
                                ))}
                                {runs.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="py-20 text-center">
                                            <div className="text-6xl mb-6 opacity-10">⏳</div>
                                            <p className="text-slate-500 italic text-[10px] uppercase tracking-widest font-black">Noch keine Scheduler-Runs aufgezeichnet.</p>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Stats Helper */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatBox 
                        label="Total Processed" 
                        value={totalProcessed} 
                        desc="Gesamte simulierte Spiele via Scheduler"
                        color="cyan"
                    />
                    <StatBox 
                        label="Avg. Duration" 
                        value={`${avgDuration.toFixed(1)}s`} 
                        desc="Durchschnittliche Simulationsdauer"
                        color="indigo"
                    />
                    <StatBox 
                        label="Success Rate" 
                        value={`${successRate.toFixed(0)}%`} 
                        desc="Verhältnis abgeschlossener Simulationen"
                        color="emerald"
                    />
                </div>
            </div>
        </AdminLayout>
    );
}

function StatBox({ label, value, desc, color }) {
    const colors = {
        cyan: 'border-l-cyan-500 text-cyan-400',
        indigo: 'border-l-indigo-500 text-indigo-400',
        emerald: 'border-l-emerald-500 text-emerald-400',
    };

    return (
        <div className={`sim-card p-6 border-l-4 ${colors[color]} bg-gradient-to-br from-slate-900 to-slate-950`}>
            <div className="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2">{label}</div>
            <div className="text-4xl font-black text-white tracking-tight tabular-nums">{value}</div>
            <div className="text-[9px] mt-4 font-black text-slate-600 uppercase tracking-tighter italic flex items-center gap-2">
                <Info size={12} />
                {desc}
            </div>
        </div>
    );
}
