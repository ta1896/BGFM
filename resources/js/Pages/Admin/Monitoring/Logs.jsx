import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Scroll, Monitor, MagnifyingGlass, Flask, Timer, Gear, CaretDown, CaretUp } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function LogEntry({ log }) {
    const [isOpen, setIsOpen] = useState(false);

    const levelStyles = {
        ERROR: 'bg-red-500/20 text-red-400 border-red-500/20',
        CRITICAL: 'bg-red-500/20 text-red-400 border-red-500/20',
        ALERT: 'bg-red-500/20 text-red-400 border-red-500/20',
        EMERGENCY: 'bg-red-500/20 text-red-400 border-red-500/20',
        WARNING: 'bg-orange-500/20 text-orange-400 border-orange-500/20',
        INFO: 'bg-slate-700/50 text-slate-300 border-[var(--border-muted)]',
        DEBUG: 'bg-[var(--bg-content)] text-[var(--text-muted)] border-[var(--border-pillar)]',
    };

    return (
        <div className="group border-b border-white/5 px-5 py-4 transition-colors hover:bg-white/5">
            <div className="flex items-start gap-4">
                <span className="shrink-0 select-none text-[var(--text-muted)] opacity-50">{log.timestamp}</span>
                <span className={`shrink-0 rounded border px-2 py-0.5 text-[10px] font-black uppercase tracking-tighter ${levelStyles[log.level] || levelStyles.INFO}`}>
                    {log.level}
                </span>
                <div className="flex-1 overflow-hidden">
                    <p className="whitespace-pre-wrap break-words leading-relaxed text-slate-200">{log.message}</p>

                    {log.context && (
                        <div className="mt-3">
                            <button
                                type="button"
                                onClick={() => setIsOpen((previous) => !previous)}
                                className="flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-cyan-400 transition-colors hover:text-cyan-300"
                            >
                                {isOpen ? <CaretUp size={12} weight="bold" /> : <CaretDown size={12} weight="bold" />}
                                Context {isOpen ? 'ausblenden' : 'anzeigen'}
                            </button>

                            {isOpen && (
                                <div className="mt-2 max-h-[500px] overflow-y-auto rounded-xl border border-white/5 bg-black/60 p-4 font-mono text-[10px] leading-relaxed text-[var(--text-muted)] shadow-inner ring-1 ring-inset ring-white/5 custom-scrollbar whitespace-pre-wrap">
                                    {log.context}
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}

export default function Logs({ logs }) {
    const navItems = [
        { name: 'Uebersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab') },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler') },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals') },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs'), active: true },
    ];

    return (
        <AdminLayout>
            <Head title="System Logs" />

            <div className="space-y-6">
                <PageHeader
                    eyebrow="System Logs"
                    title="Vollstaendige Log-Uebersicht"
                    actions={<Link href={route('admin.monitoring.index')} className="rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] px-4 py-2 font-black text-[var(--text-muted)]">Zurueck</Link>}
                />

                <div className="flex flex-wrap gap-4">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-2 rounded-xl border px-6 py-3 text-sm font-bold transition ${
                                item.active
                                    ? 'border-indigo-500 bg-indigo-600 text-white shadow-lg shadow-indigo-500/20'
                                    : 'border-[var(--border-pillar)] bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                <PageReveal>
                    <SectionCard title="Laravel Log" icon={Scroll} bodyClassName="overflow-hidden">
                        <div className="border-b border-white/5 bg-[var(--bg-content)]/20 p-5">
                            <div className="flex items-center gap-6">
                                <div className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]" />
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Error</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]" />
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Warning</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-slate-500 shadow-[0_0_8px_rgba(100,116,139,0.5)]" />
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Info</span>
                                </div>
                            </div>
                        </div>

                        <div className="custom-scrollbar h-[750px] overflow-y-auto bg-black/40 font-mono text-xs">
                            {logs.map((log, index) => <LogEntry key={index} log={log} />)}
                            {logs.length === 0 && (
                                <div className="p-20 text-center italic text-[var(--text-muted)]">Keine Log-Eintraege vorhanden.</div>
                            )}
                        </div>
                    </SectionCard>
                </PageReveal>
            </div>
        </AdminLayout>
    );
}
