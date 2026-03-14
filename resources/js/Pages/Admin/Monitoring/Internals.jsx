import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear, 
    Scroll,
    Database,
    HardDrive,
    Trash,
    ArrowClockwise,
    Info,
    Cpu
} from '@phosphor-icons/react';

export default function Internals({ stats }) {
    const { delete: destroyLogs } = useForm();
    const { post: clearCache } = useForm();

    const handleClearLogs = (e) => {
        e.preventDefault();
        if (confirm('Sicher?')) {
            destroyLogs(route('admin.monitoring.logs.clear'));
        }
    };

    const handleClearCache = (e) => {
        e.preventDefault();
        if (confirm('Wirklich den gesamten Cache leeren?')) {
            clearCache(route('admin.monitoring.clear-cache'));
        }
    };

    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab') },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler') },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals'), active: true },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs') },
    ];

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-orange-400">System Monitoring</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">System-Internals</h1>
                        <p className="mt-2 text-sm text-slate-300">Konfiguration und Ressourcen-Management.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zur Übersicht</Link>
                </div>
            }
        >
            <Head title="System Internals" />

            <div className="space-y-8">
                {/* Sub Navigation */}
                <div className="flex flex-wrap gap-4 mb-2">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm font-bold border ${
                                item.active 
                                ? 'bg-orange-600 text-white shadow-lg shadow-orange-500/20 border-orange-500' 
                                : 'bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700 border-[var(--border-pillar)]'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {/* System Info */}
                    <div className="sim-card p-6 border-b-4 border-b-cyan-500">
                        <div className="flex items-center gap-3 mb-6 pb-2 border-b border-white/5">
                            <Cpu size={24} className="text-cyan-400" />
                            <h3 className="text-sm font-black text-white uppercase tracking-widest">Umgebungs-Informationen</h3>
                        </div>
                        <div className="space-y-4">
                            <InfoRow label="PHP Version" value={stats.php_version} mono />
                            <InfoRow label="Laravel" value={`v${stats.laravel_version}`} mono />
                            <InfoRow 
                                label="Cache Driver" 
                                value={stats.cache_driver} 
                                badge="cyan"
                            />
                            <InfoRow 
                                label="DB Connection" 
                                value={stats.db_connection} 
                                badge="emerald"
                            />
                        </div>
                    </div>

                    {/* Storage & Logs */}
                    <div className="sim-card p-6 border-b-4 border-b-indigo-500">
                        <div className="flex items-center gap-3 mb-6 pb-2 border-b border-white/5">
                            <HardDrive size={24} className="text-indigo-400" />
                            <h3 className="text-sm font-black text-white uppercase tracking-widest">Speicher & Dateisystem</h3>
                        </div>
                        <div className="space-y-4">
                            <InfoRow label="App Storage" value={`${(stats.storage_size / 1024 / 1024).toFixed(2)} MB`} />
                            <InfoRow label="Log Datei (laravel.log)" value={`${(stats.log_size / 1024).toFixed(2)} KB`} color="indigo" />
                            
                            <div className="pt-6 border-t border-white/5 flex gap-3">
                                <button 
                                    onClick={handleClearLogs}
                                    className="px-5 py-2.5 bg-red-600/20 text-red-400 border border-red-500/30 rounded-xl hover:bg-red-500 hover:text-white text-[10px] font-black uppercase tracking-widest transition flex items-center gap-2"
                                >
                                    <Trash size={14} />
                                    Logs leeren
                                </button>
                                <Link 
                                    href={route('admin.monitoring.logs')}
                                    className="px-5 py-2.5 bg-[var(--bg-content)] text-slate-300 border border-[var(--border-pillar)] rounded-xl hover:bg-slate-700 text-[10px] font-black uppercase tracking-widest transition"
                                >
                                    Details
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* Cache Management */}
                    <div className="sim-card p-8 md:col-span-2 bg-gradient-to-br from-slate-900/50 to-orange-950/20 border-b-4 border-b-orange-500 group relative overflow-hidden">
                        <div className="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <ArrowClockwise size={120} weight="bold" />
                        </div>
                        <div className="relative">
                            <h3 className="text-xl font-black text-white mb-2 tracking-tight uppercase flex items-center gap-3">
                                <ArrowClockwise size={24} className="text-orange-400" />
                                System-Reset / Cache-Management
                            </h3>
                            <p className="text-xs text-[var(--text-muted)] mb-8 font-medium italic">Vorsicht: Das Leeren des Caches löscht alle zwischengespeicherten Diagnostik-Berichte.</p>

                            <button 
                                onClick={handleClearCache}
                                className="w-full py-5 bg-orange-600 text-white font-black rounded-2xl hover:bg-orange-500 hover:-translate-y-1 transition-all shadow-xl shadow-orange-500/20 uppercase tracking-widest text-sm flex items-center justify-center gap-3"
                            >
                                <ArrowClockwise size={20} weight="bold" />
                                System-Cache Flush (Global)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}

function InfoRow({ label, value, mono = false, badge = null, color = null }) {
    const badgeColors = {
        cyan: 'text-cyan-400 bg-cyan-400/10 border-cyan-400/20',
        emerald: 'text-emerald-400 bg-emerald-400/10 border-emerald-400/20',
    };

    return (
        <div className="flex justify-between items-center bg-[var(--bg-pillar)]/40 p-3 rounded-2xl border border-white/5 hover:bg-white/5 transition-colors">
            <span className="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest">{label}</span>
            {badge ? (
                <span className={`font-mono text-[10px] px-3 py-1 rounded-lg border font-black uppercase tracking-tighter ${badgeColors[badge]}`}>
                    {value}
                </span>
            ) : (
                <span className={`font-mono text-xs ${mono ? 'text-white' : (color === 'indigo' ? 'text-indigo-400' : 'text-slate-300')} bg-black/40 px-3 py-1 rounded-lg border border-white/5`}>
                    {value}
                </span>
            )}
        </div>
    );
}
