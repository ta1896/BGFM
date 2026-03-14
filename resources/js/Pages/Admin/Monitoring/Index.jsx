import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    Heartbeat, 
    Database, 
    HardDrive, 
    Warning, 
    Info, 
    CaretRight, 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear, 
    Scroll,
    CheckCircle,
    XCircle,
    ArrowClockwise,
    Trash,
    Bug,
    ShieldWarning
} from '@phosphor-icons/react';

export default function Index({ 
    health, 
    logStats, 
    recentLogs, 
    diagnostics, 
    dataTimestamp, 
    liveStatus,
    monitoringLinks 
}) {
    const { delete: destroyLogs } = useForm();
    const { post: repair } = useForm();

    const handleClearLogs = (e) => {
        e.preventDefault();
        if (confirm('Wirklich alle Logs löschen?')) {
            destroyLogs(route('admin.monitoring.logs.clear'));
        }
    };

    const handleRepair = (type, id) => {
        repair(route('admin.monitoring.repair', { type, id }));
    };

    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index'), active: true },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
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
                        <h1 className="mt-1 text-2xl font-bold text-white">Debug & Diagnostic Center</h1>
                        <p className="mt-2 text-sm text-slate-300">Übersicht über Systemgesundheit und Fehlermeldungen.</p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('admin.dashboard')} className="sim-btn-muted">Zurück</Link>
                        <button 
                            onClick={handleClearLogs}
                            className="sim-btn-muted border-red-500/50 text-red-400 hover:bg-red-500/10 flex items-center gap-2"
                        >
                            <Trash size={16} />
                            Logs leeren
                        </button>
                    </div>
                </div>
            }
        >
            <Head title="System Monitoring" />

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
                                : 'bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700 border-[var(--border-pillar)]'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                {/* Monitoring Hub */}
                <section>
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-bold text-white flex items-center gap-2">
                            <span className="flex h-2 w-2 rounded-full bg-cyan-500 shadow-[0_0_8px_rgba(6,182,212,0.8)]"></span>
                            System Monitoring Hub
                        </h2>
                        <p className="text-[10px] text-[var(--text-muted)] uppercase tracking-widest font-bold">Zentrale Steuerung</p>
                    </div>
                    <div className="grid gap-4 md:grid-cols-3">
                        <HubCard 
                            href={monitoringLinks.horizon}
                            title="Laravel Horizon"
                            subtitle="Queues & Worker"
                            description="Überwache Hintergrund-Jobs, Simulationen und Worker-Auslastung in Echtzeit."
                            color="indigo"
                            icon={<Heartbeat size={48} />}
                        />
                        <HubCard 
                            href={monitoringLinks.telescope}
                            title="Laravel Telescope"
                            subtitle="Debugging & Insights"
                            description="Analysiere Queries, Requests, Mail-Versand und Exceptions bis ins kleinste Detail."
                            color="emerald"
                            icon={<Bug size={48} />}
                        />
                        <HubCard 
                            href={monitoringLinks.goaccess}
                            title="Real-Time GoAccess"
                            subtitle="Besucher-Analytics"
                            description="Verfolge Besucherzahlen, beliebte Seiten und Server-Last live in deinem Nginx-Dashboard."
                            color="cyan"
                            icon={<Monitor size={48} />}
                        />
                    </div>
                </section>

                {/* Health Cards */}
                <section className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {/* Live Watchdog */}
                    <div className={`sim-card p-5 border-l-4 ${liveStatus.stalled_matches > 0 ? 'border-l-red-500' : 'border-l-blue-500'}`}>
                        <div className="flex items-center justify-between mb-2">
                            <p className="sim-section-title">Live Watchdog</p>
                            {liveStatus.active_matches > 0 && (
                                <span className="flex h-2 w-2 rounded-full bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)] animate-pulse"></span>
                            )}
                        </div>
                        <div className="flex items-end justify-between">
                            <div>
                                <p className="text-xl font-bold text-white">{liveStatus.active_matches} Aktiv</p>
                                <p className="text-xs text-[var(--text-muted)] mt-1">{liveStatus.stalled_matches} Hängend</p>
                            </div>
                            {liveStatus.stalled_matches > 0 ? (
                                <Warning size={32} className="text-red-500" />
                            ) : (
                                <CheckCircle size={32} className="text-blue-500/20" />
                            )}
                        </div>
                        {liveStatus.stalled_matches > 0 && (
                            <div className="mt-3 space-y-1">
                                {liveStatus.matches.filter(m => m.is_stalled).map(match => (
                                    <div key={match.id} className="flex items-center justify-between bg-red-500/10 p-2 rounded border border-red-500/20">
                                        <span className="text-[10px] font-bold text-red-300">
                                            {match.home} vs {match.away} ({match.minute}')
                                        </span>
                                        <button 
                                            onClick={() => handleRepair('match_stuck', match.id)}
                                            className="text-[10px] bg-red-500 hover:bg-red-600 text-white px-2 py-0.5 rounded transition-colors"
                                        >
                                            FIX
                                        </button>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* DB Status */}
                    <HealthCard 
                        title="Datenbank"
                        value={health.database ? 'Bereit' : 'Fehler'}
                        subtitle="PDO Connection: ACTIVE"
                        status={health.database ? 'success' : 'danger'}
                        icon={<Database size={24} />}
                    />

                    {/* Storage */}
                    <div className={`sim-card p-5 border-l-4 ${health.storage.percentage < 90 ? 'border-l-cyan-500' : 'border-l-orange-500'}`}>
                        <div className="flex items-center justify-between mb-2">
                            <p className="sim-section-title">Festplatte</p>
                            <HardDrive size={20} className="text-cyan-500" />
                        </div>
                        <div className="flex items-end justify-between">
                            <div>
                                <p className="text-xl font-bold text-white">{health.storage.free} frei</p>
                                <p className="text-xs text-[var(--text-muted)] mt-1">Von {health.storage.total}</p>
                            </div>
                            <p className="text-2xl font-bold text-cyan-300">{health.storage.percentage}%</p>
                        </div>
                        <div className="w-full bg-slate-700 h-1.5 rounded-full mt-3 overflow-hidden">
                            <div className="bg-cyan-400 h-full rounded-full transition-all duration-500" style={{ width: `${health.storage.percentage}%` }}></div>
                        </div>
                    </div>

                    {/* Log Stats */}
                    <div className={`sim-card p-5 border-l-4 ${logStats.errors > 0 ? 'border-l-red-500' : 'border-l-emerald-500'}`}>
                        <div className="flex items-center justify-between mb-2">
                            <p className="sim-section-title">Fehlerrate (24h)</p>
                            <Scroll size={20} className={logStats.errors > 0 ? 'text-red-400' : 'text-emerald-400'} />
                        </div>
                        <div className="flex items-center justify-between">
                            <p className={`text-3xl font-bold ${logStats.errors > 0 ? 'text-red-400' : 'text-emerald-400'}`}>
                                {logStats.errors}
                            </p>
                            <div className="text-right text-xs text-[var(--text-muted)]">
                                <p>Gesamt: {logStats.total}</p>
                                <p>Warnungen: {logStats.warnings}</p>
                            </div>
                        </div>
                        <p className="text-[10px] text-[var(--text-muted)] mt-2 uppercase tracking-wider overflow-hidden text-ellipsis whitespace-nowrap">
                            Letzter: {logStats.latest_error || 'Keiner'}
                        </p>
                    </div>
                </section>

                {/* Performance & Sanity */}
                <div className="grid gap-6 lg:grid-cols-2">
                    <section className="sim-card p-5">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <Heartbeat size={20} className="text-indigo-400" />
                                Simulation-Profiling
                            </h2>
                        </div>
                        <div className="flex items-center justify-around mb-6 py-4 bg-[var(--sim-shell-bg)]/30 rounded-2xl border border-white/5">
                            <div className="text-center">
                                <p className="text-[10px] text-[var(--text-muted)] uppercase font-black tracking-widest mb-1">Ø Zeit / Spiel</p>
                                <p className="text-2xl font-black text-cyan-400">{diagnostics.performance.avg_time_ms}ms</p>
                            </div>
                            <div className="text-center border-l border-white/10 pl-8">
                                <p className="text-[10px] text-[var(--text-muted)] uppercase font-black tracking-widest mb-1">Ø Aktionen</p>
                                <p className="text-2xl font-black text-indigo-400">{diagnostics.performance.avg_actions}</p>
                            </div>
                        </div>
                        <div className="space-y-2">
                            {diagnostics.performance.issues.map((issue, idx) => (
                                <div key={idx} className="sim-card-soft p-3 border-l-2 border-l-orange-500 bg-orange-500/5 transition hover:bg-orange-500/10">
                                    <p className="text-xs font-bold text-white flex items-center gap-2">
                                        <Warning size={14} className="text-orange-500" />
                                        {issue.description}
                                    </p>
                                    <p className="text-[10px] text-[var(--text-muted)] mt-1">{issue.reason}</p>
                                </div>
                            ))}
                        </div>
                    </section>

                    <section className="sim-card p-5">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <ShieldWarning size={20} className="text-red-400" />
                                Finanz-Sanity (Geldwäsche)
                            </h2>
                        </div>
                        {diagnostics.finances.issues.length > 0 ? (
                            <div className="space-y-3">
                                {diagnostics.finances.issues.map((issue, idx) => (
                                    <div key={idx} className="sim-card-soft p-3 border-l-2 border-l-red-500 bg-red-500/5 transition hover:bg-red-500/10">
                                        <p className="text-xs font-bold text-white flex items-center gap-2">
                                            <ShieldWarning size={14} className="text-red-500" />
                                            {issue.description}
                                        </p>
                                        <p className="text-[10px] text-[var(--text-muted)] mt-1">{issue.reason}</p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex flex-col items-center justify-center h-48 text-[var(--text-muted)] opacity-50 grayscale">
                                <CheckCircle size={48} weight="thin" />
                                <p className="text-xs mt-4 font-bold tracking-widest uppercase">Finanzen Stabil</p>
                                <p className="text-[10px] mt-1">Keine Anomalien gefunden</p>
                            </div>
                        )}
                    </section>
                </div>

                {/* Data Health */}
                <section className="sim-card p-6">
                    <div className="mb-6 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <Database size={20} className="text-cyan-400" />
                                Daten-Integrität & Diagnose
                            </h2>
                            <p className="text-xs text-[var(--text-muted)] mt-1">
                                Prüfung wichtiger Datenbankfelder. 
                                <span className="text-cyan-400 ml-2 font-mono">Stand: {diagnostics.generated_at}</span>
                            </p>
                        </div>
                        <div className="flex items-center gap-4">
                            <Link 
                                href={route('admin.monitoring.index', { refresh: 1 })}
                                className="sim-btn-muted text-xs flex items-center gap-2"
                            >
                                <ArrowClockwise size={14} />
                                Neu Scannen
                            </Link>
                            <div className="text-center px-4 border-l border-white/10">
                                <p className="text-[10px] text-[var(--text-muted)] uppercase font-black tracking-widest">Total</p>
                                <p className="text-xl font-black text-white">
                                    {diagnostics.matches.count + diagnostics.events.count + diagnostics.clubs.count + diagnostics.inactivity.count}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div className="grid gap-6 lg:grid-cols-2">
                        {/* Matches & Events */}
                        <div className="space-y-4">
                            <h3 className="text-xs font-bold text-cyan-400 uppercase tracking-widest flex items-center gap-2">
                                <Heartbeat size={16} />
                                Spiele & Ereignisse
                            </h3>
                            <div className="space-y-2">
                                {[...diagnostics.matches.issues, ...diagnostics.events.issues].map((issue, idx) => (
                                    <div
                                        key={idx}
                                        className={`sim-card-soft p-3 border-l-2 ${issue.severity === 'CRITICAL' ? 'border-l-red-500 bg-red-500/5' : 'border-l-orange-500 bg-orange-500/5'}`}
                                    >
                                        <p className="text-xs font-bold text-white">{issue.description}</p>
                                        <p className="text-[10px] text-[var(--text-muted)] mt-1"><span className="text-cyan-400 font-bold">Grund:</span> {issue.reason}</p>
                                    </div>
                                ))}
                                {[...diagnostics.matches.issues, ...diagnostics.events.issues].length === 0 && (
                                    <p className="text-xs text-[var(--text-muted)] italic py-4">Keine Probleme gefunden.</p>
                                )}
                            </div>
                        </div>

                        {/* Clubs & Inactivity */}
                        <div className="space-y-4">
                            <h3 className="text-xs font-bold text-indigo-400 uppercase tracking-widest flex items-center gap-2">
                                <ShieldWarning size={16} />
                                Vereine & Kader
                            </h3>
                            <div className="space-y-2">
                                {[...diagnostics.clubs.issues, ...diagnostics.inactivity.issues].map((issue, idx) => (
                                    <div
                                        key={idx}
                                        className={`sim-card-soft p-3 border-l-2 ${issue.severity === 'CRITICAL' ? 'border-l-red-500 bg-red-500/5' : 'border-l-orange-500 bg-orange-500/5'} flex justify-between items-start`}
                                    >
                                        <div>
                                            <p className="text-xs font-bold text-white">
                                                {issue.name && `${issue.name}: `}{issue.description}
                                            </p>
                                            <p className="text-[10px] text-[var(--text-muted)] mt-1"><span className="text-cyan-400 font-bold">Grund:</span> {issue.reason}</p>
                                        </div>
                                        {issue.description.includes('no active lineup') && (
                                            <button 
                                                onClick={() => handleRepair('club_lineup', issue.id)}
                                                className="sim-btn text-[10px] px-2 py-1 flex items-center gap-1 bg-cyan-600/20 text-cyan-400 border-cyan-500/30"
                                            >
                                                <ArrowClockwise size={12} />
                                                Fix It
                                            </button>
                                        )}
                                    </div>
                                ))}
                                {[...diagnostics.clubs.issues, ...diagnostics.inactivity.issues].length === 0 && (
                                    <p className="text-xs text-[var(--text-muted)] italic py-4">Alles im grünen Bereich.</p>
                                )}
                            </div>
                        </div>
                    </div>
                </section>

                {/* Recent Logs */}
                <section className="sim-card overflow-hidden">
                    <div className="p-5 border-b border-white/5 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-white flex items-center gap-2">
                                <Scroll size={20} className="text-indigo-400" />
                                Letzte Log-Einträge
                            </h2>
                            <p className="text-xs text-[var(--text-muted)] mt-1">Auszug aus der laravel.log Datei</p>
                        </div>
                        <Link href={route('admin.monitoring.logs')} className="sim-btn-muted text-xs">Vollständige Logs</Link>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full text-left border-collapse">
                            <thead>
                                <tr className="bg-[var(--bg-content)]/30 text-[10px] uppercase font-bold text-[var(--text-muted)] tracking-widest">
                                    <th className="px-5 py-3 border-b border-white/5">Zeitstempel</th>
                                    <th className="px-5 py-3 border-b border-white/5">Level</th>
                                    <th className="px-5 py-3 border-b border-white/5">Nachricht</th>
                                    <th className="px-5 py-3 border-b border-white/5 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {recentLogs.map((log, idx) => (
                                    <tr key={idx} className="hover:bg-white/5 transition-colors group">
                                        <td className="px-5 py-3 text-xs font-mono text-[var(--text-muted)] whitespace-nowrap">{log.timestamp}</td>
                                        <td className="px-5 py-3 text-xs">
                                            <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-tighter ${
                                                ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'].includes(log.level) 
                                                ? 'bg-red-500/20 text-red-400' 
                                                : log.level === 'WARNING' 
                                                ? 'bg-orange-500/20 text-orange-400' 
                                                : 'bg-slate-700 text-slate-300'
                                            }`}>
                                                {log.level}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 text-xs text-slate-200">
                                            <div 
                                                className="line-clamp-1 group-hover:line-clamp-none transition-all cursor-help max-w-xl"
                                                title={log.message}
                                            >
                                                {log.message}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3 text-right">
                                            <button 
                                                className="text-cyan-400 hover:text-white transition-colors opacity-0 group-hover:opacity-100"
                                                onClick={() => alert(log.message)}
                                            >
                                                <MagnifyingGlass size={16} />
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                                {recentLogs.length === 0 && (
                                    <tr>
                                        <td colSpan="4" className="px-5 py-10 text-center text-[var(--text-muted)] italic">Keine Logs gefunden.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}

function HubCard({ href, title, subtitle, description, color, icon }) {
    const colors = {
        indigo: 'hover:border-indigo-500/50 text-indigo-400',
        emerald: 'hover:border-emerald-500/50 text-emerald-400',
        cyan: 'hover:border-cyan-500/50 text-cyan-400',
    };

    return (
        <a 
            href={href} 
            target="_blank" 
            className={`sim-card group transition-all p-5 relative overflow-hidden ${colors[color]}`}
        >
            <div className="absolute -right-4 -top-4 opacity-5 group-hover:opacity-10 transition-opacity">
                {icon}
            </div>
            <p className="sim-section-title mb-1 uppercase tracking-widest">{subtitle}</p>
            <h3 className="text-xl font-bold text-white mb-2">{title}</h3>
            <p className="text-xs text-[var(--text-muted)] leading-relaxed mb-4">{description}</p>
            <span className="text-xs font-bold group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                Öffnen <CaretRight size={12} weight="bold" />
            </span>
        </a>
    );
}

function HealthCard({ title, value, subtitle, status, icon }) {
    const statusColors = {
        success: 'border-l-emerald-500',
        danger: 'border-l-red-500',
        warning: 'border-l-orange-500',
        info: 'border-l-blue-500',
    };

    const pulseColors = {
        success: 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.8)]',
        danger: 'bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.8)]',
        warning: 'bg-orange-500 shadow-[0_0_8px_rgba(245,158,11,0.8)]',
        info: 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.8)]',
    };

    return (
        <article className={`sim-card p-5 border-l-4 ${statusColors[status]}`}>
            <div className="flex items-center justify-between mb-2">
                <p className="sim-section-title">{title}</p>
                <div className="flex items-center gap-3">
                    {icon && <div className="opacity-40">{icon}</div>}
                    <span className={`flex h-2 w-2 rounded-full ${pulseColors[status]}`}></span>
                </div>
            </div>
            <p className="text-xl font-bold text-white">{value}</p>
            <p className="text-xs text-[var(--text-muted)] mt-1">{subtitle}</p>
        </article>
    );
}

