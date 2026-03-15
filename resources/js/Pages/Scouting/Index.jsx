import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Binoculars, MagnifyingGlass, Handshake, Star } from '@phosphor-icons/react';

export default function Index({ club, targets, watchlist }) {
    const watchlistForm = useForm({
        priority: 'medium',
        status: 'watching',
        notes: '',
    });

    const search = new URLSearchParams(typeof window !== 'undefined' ? window.location.search : '').get('search') || '';

    return (
        <AuthenticatedLayout>
            <Head title="Scouting" />
            <div className="mx-auto max-w-[1550px] space-y-8">
                <PageHeader eyebrow="Market" title="Scouting Desk" description={club ? `${club.name} Watchlist, Reports und Recruiting-Pipeline.` : 'Kein aktiver Verein'} />

                <div className="sim-card p-4">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="relative min-w-[260px] flex-1">
                            <MagnifyingGlass size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                            <input
                                defaultValue={search}
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter') {
                                        router.get(route('scouting.index'), { search: event.currentTarget.value }, { preserveState: true });
                                    }
                                }}
                                className="sim-input w-full pl-11"
                                placeholder="Spieler suchen"
                            />
                        </div>
                        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 px-4 py-3 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                            {watchlist.length} auf Watchlist
                        </div>
                    </div>
                </div>

                <div className="grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                    <SectionCard title="Ziele" icon={Binoculars} bodyClassName="p-6 space-y-4">
                        {targets.map((player) => (
                            <div key={player.id} className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30 p-5">
                                <div className="flex flex-wrap items-center justify-between gap-4">
                                    <div className="flex items-center gap-4">
                                        <img src={player.photo_url} alt={player.name} className="h-14 w-14 rounded-2xl border border-white/10 object-cover" />
                                        <div>
                                            <div className="text-sm font-black uppercase tracking-[0.06em] text-white">{player.name}</div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                                {player.position} / {player.age} / {player.club_name}
                                            </div>
                                            <div className="mt-2 text-[10px] font-black uppercase tracking-[0.14em] text-amber-300">{player.potential_hint}</div>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Wert</div>
                                        <div className="text-sm font-black text-white">{player.market_value}</div>
                                    </div>
                                </div>

                                <div className="mt-4 flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onClick={() => watchlistForm.transform(() => ({ priority: 'medium', status: 'watching', notes: '' })).post(route('scouting.watchlist.store', player.id), { preserveScroll: true })}
                                        className="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200"
                                    >
                                        Auf Watchlist
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => router.post(route('scouting.report.generate', player.id), {}, { preserveScroll: true })}
                                        className="rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200"
                                    >
                                        Report ziehen
                                    </button>
                                </div>
                            </div>
                        ))}
                    </SectionCard>

                    <SectionCard title="Watchlist" icon={Star} bodyClassName="p-6 space-y-4">
                        {watchlist.length ? watchlist.map((entry) => (
                            <div key={entry.id} className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30 p-5">
                                <div className="flex items-center justify-between gap-4">
                                    <div className="flex items-center gap-4">
                                        <img src={entry.player.photo_url} alt={entry.player.name} className="h-12 w-12 rounded-xl border border-white/10 object-cover" />
                                        <div>
                                            <div className="text-xs font-black uppercase tracking-[0.06em] text-white">{entry.player.name}</div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                                {entry.player.position} / {entry.player.club_name}
                                            </div>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={() => router.delete(route('scouting.watchlist.destroy', entry.id), { preserveScroll: true })}
                                        className="rounded-2xl border border-rose-400/20 bg-rose-500/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-rose-200"
                                    >
                                        Entfernen
                                    </button>
                                </div>

                                <div className="mt-4 flex flex-wrap gap-2">
                                    <Tag tone="cyan">{entry.priority}</Tag>
                                    <Tag tone="amber">{entry.status}</Tag>
                                    {entry.latest_report && <Tag tone="emerald">{entry.latest_report.confidence}% sicher</Tag>}
                                </div>

                                {entry.latest_report ? (
                                    <div className="mt-4 grid gap-3 md:grid-cols-2">
                                        <ReportBox label="OVR" value={entry.latest_report.overall_band} />
                                        <ReportBox label="Potential" value={entry.latest_report.potential_band} />
                                        <ReportBox label="Injury" value={entry.latest_report.injury_risk_band} />
                                        <ReportBox label="Charakter" value={entry.latest_report.personality_band} />
                                        <div className="md:col-span-2 rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/50 px-4 py-3 text-sm text-[var(--text-muted)]">
                                            {entry.latest_report.summary}
                                        </div>
                                    </div>
                                ) : (
                                    <div className="mt-4 rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-4 text-sm text-[var(--text-muted)]">
                                        Noch kein Scout-Report vorhanden.
                                    </div>
                                )}

                                <div className="mt-4">
                                    <button
                                        type="button"
                                        onClick={() => router.post(route('scouting.report.generate', entry.player.id), {}, { preserveScroll: true })}
                                        className="inline-flex items-center gap-2 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200"
                                    >
                                        <Handshake size={13} weight="bold" />
                                        Report aktualisieren
                                    </button>
                                </div>
                            </div>
                        )) : (
                            <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">
                                Noch keine beobachteten Spieler.
                            </div>
                        )}
                    </SectionCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function Tag({ children, tone = 'cyan' }) {
    const tones = {
        cyan: 'border-cyan-400/20 bg-cyan-500/10 text-cyan-200',
        amber: 'border-amber-400/20 bg-amber-500/10 text-amber-200',
        emerald: 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200',
    };

    return <span className={`rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${tones[tone]}`}>{children}</span>;
}

function ReportBox({ label, value }) {
    return (
        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/50 px-4 py-3">
            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{label}</div>
            <div className="mt-1 text-sm font-black text-white">{value}</div>
        </div>
    );
}
