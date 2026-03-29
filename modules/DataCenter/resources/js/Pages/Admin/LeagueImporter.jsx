import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Database, Download, Info, CheckCircle, Users, Calendar, ArrowSquareOut, ClockCounterClockwise, Trash, MagnifyingGlass } from '@phosphor-icons/react';

import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function StatusBadge({ status }) {
    const configs = {
        pending: { color: 'bg-slate-500/10 text-slate-400 border-slate-500/20', label: 'Wartend' },
        running: { color: 'bg-amber-500/10 text-amber-500 border-amber-500/20 animate-pulse', label: 'Läuft' },
        completed: { color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', label: 'Fertig' },
        failed: { color: 'bg-red-500/10 text-red-500 border-red-500/20', label: 'Fehler' },
    };

    const config = configs[status] || configs.pending;

    return (
        <span className={`inline-flex items-center rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-widest ${config.color}`}>
            {config.label}
        </span>
    );
}

export default function LeagueImporter({ status, importedClubs = [], importLogs = [], queueSize = 0, playersWithoutSofascoreId = 0 }) {
    // Safety check for logs
    const safeLogs = Array.isArray(importLogs) ? importLogs : [];

    const { data, setData, post, processing, errors } = useForm({
        league_id: 'L1',
        season: '24/25',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.data-center.league-importer.store'));
    };

    return (
        <AdminLayout>
            <Head title="Liga Importer" />

            <div className="space-y-10 pb-20">
                <PageHeader 
                    eyebrow="Daten-Center" 
                    title="Transfermarkt Importer" 
                    description="Importiere eine gesamte Liga direkt von Transfermarkt (Bulk-Import)."
                />

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <PageReveal>
                            <SectionCard title="Import-Einstellungen" icon={Download} bodyClassName="p-6">
                                {status && (
                                    <div className="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-emerald-400">
                                        <CheckCircle size={24} weight="fill" />
                                        <p className="text-sm font-bold">{status}</p>
                                    </div>
                                )}

                                {queueSize > 0 ? (
                                    <div className="mb-6 flex items-center justify-between rounded-xl border border-amber-500/20 bg-amber-500/10 p-4 text-amber-500">
                                        <div className="flex items-center gap-3">
                                            <ClockCounterClockwise size={24} weight="fill" className="animate-spin-slow" />
                                            <div>
                                                <p className="text-sm font-bold">Warteschlange aktiv</p>
                                                <p className="text-[10px] opacity-80 italic">Deine Aufgaben werden nacheinander abgearbeitet.</p>
                                            </div>
                                        </div>
                                        <div className="flex flex-col items-end">
                                            <span className="text-xl font-black">{queueSize}</span>
                                            <span className="text-[9px] uppercase font-bold tracking-widest">Aufgaben offen</span>
                                        </div>
                                    </div>
                                ) : (
                                    <div className="mb-6 flex items-center gap-3 rounded-xl border border-emerald-500/10 bg-emerald-500/5 p-4 text-emerald-400 opacity-60">
                                        <CheckCircle size={20} weight="bold" />
                                        <p className="text-xs font-bold uppercase tracking-widest">Queue: Bereit für neue Aufgaben</p>
                                    </div>
                                )}

                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div>
                                            <label className="mb-2 block px-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                                Liga Kürzel (Transfermarkt)
                                            </label>
                                            <input
                                                type="text"
                                                className="sim-input w-full"
                                                placeholder="z.B. L1, GB1, ES1, IT1"
                                                value={data.league_id}
                                                onChange={e => setData('league_id', e.target.value)}
                                                required
                                            />
                                            {errors.league_id && <p className="mt-1 text-xs text-red-400">{errors.league_id}</p>}
                                        </div>

                                        <div>
                                            <label className="mb-2 block px-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                                Saison (Format: YY/YY)
                                            </label>
                                            <input
                                                type="text"
                                                className="sim-input w-full"
                                                placeholder="z.B. 24/25"
                                                value={data.season}
                                                onChange={e => setData('season', e.target.value)}
                                                required
                                            />
                                            {errors.season && <p className="mt-1 text-xs text-red-400">{errors.season}</p>}
                                        </div>
                                    </div>

                                    <div className="rounded-xl border border-[var(--card-border)] bg-slate-900/50 p-4">
                                        <p className="text-[10px] text-[var(--text-muted)] italic leading-relaxed">
                                            Hinweis: Der Bulk-Import lädt alle Spieler der Liga in einem Schritt herunter. Dies ist effizient, kann aber je nach Ligagröße 1-2 Minuten dauern.
                                        </p>
                                    </div>

                                    <div className="flex justify-end pt-4">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="flex h-[46px] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-8 font-black text-white transition-opacity disabled:opacity-50"
                                        >
                                            {processing ? 'Wird verarbeitet...' : 'Bulk-Import starten'}
                                            <Database size={18} weight="bold" />
                                        </button>
                                    </div>
                                </form>


                            </SectionCard>
                        </PageReveal>
                    </div>

                    <div className="space-y-8">
                        <SectionCard 
                            title="Import-Journal" 
                            icon={ClockCounterClockwise} 
                            bodyClassName="p-0 overflow-hidden"
                            headerAction={
                                safeLogs.length > 0 && (
                                    <button
                                        onClick={() => {
                                            if (confirm('Möchtest du das gesamte Journal wirklich leeren?')) {
                                                router.delete(route('admin.data-center.league-importer.clear'));
                                            }
                                        }}
                                        className="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-500/10 text-red-500 hover:bg-red-500 hover:text-white text-[9px] font-black uppercase tracking-widest transition-all"
                                    >
                                        <Trash size={14} weight="bold" />
                                        <span>Leeren</span>
                                    </button>
                                )
                            }
                        >

                            <div className="max-h-[500px] overflow-y-auto">
                                <table className="w-full text-left">
                                    <tbody className="divide-y divide-slate-800/50">
                                        {safeLogs.length > 0 ? (
                                            safeLogs.map((log) => (
                                                <tr key={log.id} className="group hover:bg-slate-800/20 transition-colors">
                                                    <td className="px-4 py-4">
                                                        <div className="flex flex-col gap-1">
                                                            <div className="flex items-center justify-between">
                                                                <div className="flex items-center gap-2">
                                                                    <span className="text-xs font-black uppercase text-[var(--text-main)]">{log.league_id}</span>
                                                                    <span className="text-[10px] text-[var(--text-muted)]">{log.season}</span>
                                                                </div>
                                                                <StatusBadge status={log.status} />
                                                            </div>
                                                            <p className="line-clamp-1 text-[10px] text-[var(--text-muted)] italic min-h-[14px]">
                                                                {log.message || (log.status === 'running' ? (
                                                                    <span className="flex items-center gap-1.5 overflow-hidden whitespace-nowrap">
                                                                        <span className="animate-pulse">Verarbeite...</span>
                                                                        <span className="text-[var(--text-main)] not-italic font-black text-white px-1 rounded bg-white/5 border border-white/10">
                                                                            {log.details?.current_club || '...'}
                                                                        </span>
                                                                        {log.details?.current_player && (
                                                                            <span className="not-italic opacity-80 border-l border-white/20 pl-1.5">
                                                                                {log.details.current_player}
                                                                            </span>
                                                                        )}
                                                                        {log.details?.players_processed && (
                                                                            <span className="ml-auto text-[9px] not-italic font-black text-amber-500 tabular-nums">
                                                                                ({log.details.players_processed}/{log.details.total_links_found || '?'})
                                                                            </span>
                                                                        )}
                                                                    </span>
                                                                ) : '')}
                                                            </p>

                                                            <div className="mt-1 flex items-center justify-between text-[9px] font-bold tracking-wider text-[var(--text-muted)] uppercase">
                                                                <span>{new Date(log.created_at).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })} Uhr</span>
                                                                {log.finished_at && (
                                                                    <span>{Math.round((new Date(log.finished_at) - new Date(log.started_at)) / 1000)}s Dauer</span>
                                                                )}
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td className="px-4 py-10 text-center text-xs text-[var(--text-muted)]">
                                                    Keine Einträge vorhanden.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </SectionCard>

                        <PageReveal delay={0.1}>
                            <SectionCard title="Anleitung" icon={Info} bodyClassName="p-6 space-y-4">
                                <div className="space-y-4 text-sm leading-relaxed text-[var(--text-muted)]">
                                    <p>
                                        Nutze die offizielle Transfermarkt Wettbewerbs-ID (zu finden in der URL).
                                    </p>
                                    <ul className="list-inside list-disc space-y-2 text-xs">
                                        <li><span className="text-[var(--text-main)] font-bold">GB1</span> = Premier League</li>
                                        <li><span className="text-[var(--text-main)] font-bold">L1</span> = Bundesliga</li>
                                        <li><span className="text-[var(--text-main)] font-bold">ES1</span> = La Liga</li>
                                        <li><span className="text-[var(--text-main)] font-bold">IT1</span> = Serie A</li>
                                        <li><span className="text-[var(--text-main)] font-bold">FR1</span> = Ligue 1</li>
                                    </ul>
                                </div>
                            </SectionCard>
                        </PageReveal>
                    </div>
                </div>

                <PageReveal delay={0.15}>
                    <SectionCard title="Sofascore ID Finder" icon={MagnifyingGlass} bodyClassName="p-6">
                        <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                            <div className="space-y-1">
                                <p className="text-sm text-[var(--text-muted)] leading-relaxed">
                                    Sucht automatisch Sofascore-IDs für alle Spieler, die noch keine haben — über die Sofascore-Suche.
                                </p>
                                <p className="text-[11px] text-[var(--text-muted)] italic">
                                    Nur Spieler ohne bestehende Sofascore-ID werden verarbeitet. Läuft im Hintergrund (~1 Sek./Spieler).
                                </p>
                            </div>

                            <div className="flex shrink-0 flex-col items-end gap-3">
                                <div className="flex items-center gap-2 rounded-xl border border-[var(--card-border)] bg-slate-900/50 px-4 py-2">
                                    <span className="text-xl font-black text-[var(--text-main)]">{playersWithoutSofascoreId}</span>
                                    <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">ohne ID</span>
                                </div>

                                <button
                                    type="button"
                                    disabled={playersWithoutSofascoreId === 0}
                                    onClick={() => {
                                        if (!confirm(`Sofascore ID Finder für ${playersWithoutSofascoreId} Spieler starten?`)) return;
                                        router.post(route('admin.data-center.sofascore-finder.store'));
                                    }}
                                    className="flex h-[40px] items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-6 text-[11px] font-black uppercase tracking-widest text-white transition-opacity disabled:opacity-40 hover:opacity-90"
                                >
                                    <MagnifyingGlass size={16} weight="bold" />
                                    Finder starten
                                </button>
                            </div>
                        </div>
                    </SectionCard>
                </PageReveal>

                <PageReveal delay={0.2}>
                    <SectionCard title="Importierte Vereine" icon={Users} bodyClassName="p-0 overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="border-b border-slate-800/50 bg-slate-900/30">
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Verein</th>
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Liga-ID</th>
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">TM-ID</th>
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Spieler</th>
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Zuletzt aktualisiert</th>
                                        <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-right">Aktion</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/50">
                                    {importedClubs.length > 0 ? (
                                        importedClubs.map((club) => (
                                            <tr key={club.id} className="group hover:bg-slate-800/20 transition-colors">
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-3">
                                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-900/50 p-1 border border-slate-700/30">
                                                            <img src={club.logo_url} alt={club.name} className="h-full w-full object-contain" />
                                                        </div>
                                                        <span className="font-bold text-[var(--text-main)]">{club.name}</span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <span className="inline-flex items-center rounded-full bg-cyan-500/10 px-2.5 py-0.5 text-xs font-bold text-cyan-400">
                                                        {club.league || 'N/A'}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-center">
                                                    <code className="text-[10px] text-[var(--text-muted)] font-mono bg-slate-900/80 px-2 py-1 rounded">
                                                        {club.transfermarkt_id}
                                                    </code>
                                                </td>
                                                <td className="px-6 py-4 text-center">
                                                    <div className="flex flex-col items-center">
                                                        <span className="text-sm font-black text-[var(--text-main)]">{club.players_count}</span>
                                                        <span className="text-[9px] uppercase tracking-tighter text-[var(--text-muted)]">Spieler</span>
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex items-center gap-2 text-xs text-[var(--text-muted)]">
                                                        <Calendar size={14} />
                                                        {new Date(club.updated_at).toLocaleDateString('de-DE', {
                                                            day: '2-digit',
                                                            month: '2-digit',
                                                            year: 'numeric',
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 text-right">
                                                    {club.transfermarkt_url && (
                                                        <a 
                                                            href={club.transfermarkt_url} 
                                                            target="_blank" 
                                                            rel="noopener noreferrer"
                                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-800/50 text-[var(--text-muted)] hover:bg-[var(--accent-primary)] hover:text-white transition-all"
                                                        >
                                                            <ArrowSquareOut size={16} weight="bold" />
                                                        </a>
                                                    )}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="6" className="px-6 py-12 text-center">
                                                <div className="flex flex-col items-center gap-2">
                                                    <Database size={32} className="text-slate-700" />
                                                    <p className="text-sm text-[var(--text-muted)]">Noch keine Vereine importiert.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </SectionCard>
                </PageReveal>
            </div>
        </AdminLayout>
    );
}
