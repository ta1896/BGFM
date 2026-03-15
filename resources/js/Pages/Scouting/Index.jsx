import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Binoculars, MagnifyingGlass, Handshake, Star, TrendUp, Wallet, GlobeHemisphereWest, VideoCamera, ChartBar } from '@phosphor-icons/react';

export default function Index({ club, targets, watchlist, scoutOptions }) {
    const watchlistForm = useForm({
        priority: 'medium',
        status: 'watching',
        focus: 'general',
        scout_level: 'experienced',
        scout_region: 'domestic',
        scout_type: 'live',
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
                        {club && (
                            <div className="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-200">
                                Budget {new Intl.NumberFormat('de-DE').format(club.budget)}
                            </div>
                        )}
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
                                            <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-200">{player.country || 'Unbekannt'}</div>
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
                                        onClick={() => watchlistForm
                                            .transform((data) => ({
                                                ...data,
                                                priority: data.priority,
                                                status: data.status,
                                                focus: data.focus,
                                                scout_level: data.scout_level,
                                                scout_region: data.scout_region,
                                                scout_type: data.scout_type,
                                                notes: data.notes,
                                            }))
                                            .post(route('scouting.watchlist.store', player.id), { preserveScroll: true })}
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

                                <div className="mt-4 grid gap-3 md:grid-cols-3">
                                    <QuickSelect label="Scout-Level" value={watchlistForm.data.scout_level} options={scoutOptions.levels} onChange={(value) => watchlistForm.setData('scout_level', value)} />
                                    <QuickSelect label="Region" value={watchlistForm.data.scout_region} options={scoutOptions.regions} onChange={(value) => watchlistForm.setData('scout_region', value)} />
                                    <QuickSelect label="Typ" value={watchlistForm.data.scout_type} options={scoutOptions.types} onChange={(value) => watchlistForm.setData('scout_type', value)} />
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
                                    <Tag tone="slate">{entry.focus}</Tag>
                                    <Tag tone="emerald">{entry.scout_level}</Tag>
                                    <Tag tone="slate">{entry.scout_region}</Tag>
                                    <Tag tone="cyan">{entry.scout_type}</Tag>
                                    {entry.latest_report && <Tag tone="emerald">{entry.latest_report.confidence}% sicher</Tag>}
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-4">
                                    <QuickSelect
                                        label="Prioritaet"
                                        value={entry.priority}
                                        options={['low', 'medium', 'high']}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { priority: value }), { preserveScroll: true })}
                                    />
                                    <QuickSelect
                                        label="Status"
                                        value={entry.status}
                                        options={['watching', 'priority', 'negotiating']}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { status: value }), { preserveScroll: true })}
                                    />
                                    <QuickSelect
                                        label="Fokus"
                                        value={entry.focus}
                                        options={scoutOptions.focuses}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { focus: value }), { preserveScroll: true })}
                                    />
                                    <QuickSelect
                                        label="Scout-Level"
                                        value={entry.scout_level}
                                        options={scoutOptions.levels}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { scout_level: value }), { preserveScroll: true })}
                                    />
                                </div>

                                <div className="mt-3 grid gap-3 md:grid-cols-3">
                                    <QuickSelect
                                        label="Region"
                                        value={entry.scout_region}
                                        options={scoutOptions.regions}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { scout_region: value }), { preserveScroll: true })}
                                    />
                                    <QuickSelect
                                        label="Scout-Typ"
                                        value={entry.scout_type}
                                        options={scoutOptions.types}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { scout_type: value }), { preserveScroll: true })}
                                    />
                                    <div>
                                        <div className="mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Notiz</div>
                                        <input
                                            defaultValue={entry.notes || ''}
                                            onBlur={(event) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { notes: event.target.value }), { preserveScroll: true })}
                                            className="sim-input w-full"
                                            placeholder="Scout-Notiz"
                                        />
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-4">
                                    <ReportBox label="Fortschritt" value={`${entry.progress}%`} />
                                    <ReportBox label="Naechster Report" value={entry.next_report_due_at || '-'} />
                                    <ReportBox label="Missionen" value={entry.reports_requested} />
                                    <ReportBox label="Letzte Kosten" value={formatMoney(entry.last_mission_cost)} />
                                </div>

                                <div className="mt-3 grid gap-3 md:grid-cols-3">
                                    <InfoTile icon={Wallet} label="Missionskosten" value={formatMoney(entry.mission_preview?.cost)} />
                                    <InfoTile icon={GlobeHemisphereWest} label="Dauer" value={`${entry.mission_preview?.days || 0} Tage`} />
                                    <InfoTile icon={entry.scout_type === 'data' ? ChartBar : entry.scout_type === 'video' ? VideoCamera : Binoculars} label="Scout-Push" value={`+${entry.mission_preview?.gain || 0}%`} />
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
                                        <div className="md:col-span-2 flex flex-wrap gap-2">
                                            {entry.report_history?.map((report) => (
                                                <Tag key={report.id} tone="slate">{report.created_at} / {report.confidence}%</Tag>
                                            ))}
                                        </div>
                                    </div>
                                ) : (
                                    <div className="mt-4 rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-4 text-sm text-[var(--text-muted)]">
                                        Noch kein Scout-Report vorhanden.
                                    </div>
                                )}

                                <div className="mt-4">
                                    <div className="flex flex-wrap gap-3">
                                        <button
                                            type="button"
                                            onClick={() => router.post(route('scouting.watchlist.advance', entry.id), {}, { preserveScroll: true })}
                                            className="inline-flex items-center gap-2 rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200"
                                        >
                                            <TrendUp size={13} weight="bold" />
                                            Scout weiterschicken
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => router.post(route('scouting.report.generate', entry.player.id), {}, { preserveScroll: true })}
                                            className="inline-flex items-center gap-2 rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200"
                                        >
                                            <Handshake size={13} weight="bold" />
                                            Express-Report
                                        </button>
                                    </div>
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
        slate: 'border-slate-400/20 bg-slate-500/10 text-slate-200',
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

function QuickSelect({ label, value, options, onChange }) {
    return (
        <div>
            <div className="mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{label}</div>
            <select value={value} onChange={(event) => onChange(event.target.value)} className="sim-select w-full">
                {options.map((option) => (
                    <option key={option} value={option}>{option}</option>
                ))}
            </select>
        </div>
    );
}

function buildWatchlistPayload(entry, patch = {}) {
    return {
        priority: patch.priority ?? entry.priority,
        status: patch.status ?? entry.status,
        focus: patch.focus ?? entry.focus,
        scout_level: patch.scout_level ?? entry.scout_level,
        scout_region: patch.scout_region ?? entry.scout_region,
        scout_type: patch.scout_type ?? entry.scout_type,
        notes: patch.notes ?? entry.notes ?? '',
    };
}

function formatMoney(value) {
    return `${new Intl.NumberFormat('de-DE').format(Number(value || 0))} EUR`;
}

function InfoTile({ icon: Icon, label, value }) {
    return (
        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/50 px-4 py-3">
            <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                <Icon size={13} weight="bold" />
                {label}
            </div>
            <div className="mt-2 text-sm font-black text-white">{value}</div>
        </div>
    );
}
