import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Binoculars, MagnifyingGlass, Handshake, Star, TrendUp, Wallet, GlobeHemisphereWest, VideoCamera, ChartBar, Target, SlidersHorizontal, UsersThree, ShieldCheck, ArrowsClockwise, Timer } from '@phosphor-icons/react';

export default function Index({ club, discoveries, targets, watchlist, scoutOptions, filters, marketCounts, scoutStaff = [], moduleSettings = {}, scanState = null }) {
    const watchlistForm = useForm({
        priority: 'medium',
        status: 'watching',
        focus: 'general',
        scout_level: 'experienced',
        scout_region: 'domestic',
        scout_type: 'live',
        scout_id: scoutStaff[0]?.id ? String(scoutStaff[0].id) : '',
        notes: '',
    });

    const search = filters?.search || '';

    const applyFilters = (patch = {}) => {
        router.get(route('scouting.index'), {
            ...filters,
            ...patch,
        }, { preserveState: true, preserveScroll: true, replace: true });
    };

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
                                        applyFilters({ search: event.currentTarget.value });
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
                        <button
                            type="button"
                            disabled={Boolean(scanState?.cooldown_active)}
                            onClick={() => router.post(route('scouting.discover'), filters, { preserveScroll: true })}
                            className={`rounded-2xl border px-4 py-3 text-[10px] font-black uppercase tracking-[0.16em] ${
                                scanState?.cooldown_active
                                    ? 'cursor-not-allowed border-slate-400/20 bg-slate-500/10 text-slate-300'
                                    : 'border-cyan-400/20 bg-cyan-500/10 text-cyan-200'
                            }`}
                        >
                            {scanState?.cooldown_active ? 'Scan blockiert' : 'Markt scannen'}
                        </button>
                    </div>
                    {scanState && (
                        <div className="mt-4 grid gap-3 md:grid-cols-3">
                            <InfoTile
                                icon={Timer}
                                label="Naechster Scan"
                                value={scanState.cooldown_active ? `${scanState.minutes_remaining} Min.` : 'Jetzt verfuegbar'}
                            />
                            <InfoTile
                                icon={ArrowsClockwise}
                                label="Pool-Rotation"
                                value={`alle ${scanState.rotation_window_minutes} Min.`}
                            />
                            <InfoTile
                                icon={Binoculars}
                                label="Letzter Scan"
                                value={scanState.last_scan_at || 'Noch keiner'}
                            />
                        </div>
                    )}
                    <div className="mt-4 flex flex-wrap gap-2">
                        {scoutOptions.markets.map((market) => (
                            <button
                                key={market}
                                type="button"
                                onClick={() => applyFilters({ market })}
                                className={`rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] ${
                                    filters.market === market
                                        ? 'border-cyan-400/30 bg-cyan-500/15 text-cyan-200'
                                        : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 text-[var(--text-muted)]'
                                }`}
                            >
                                {market} {marketCounts?.[market] ?? 0}
                            </button>
                        ))}
                    </div>
                    <div className="mt-4 grid gap-3 md:grid-cols-4 xl:grid-cols-5">
                        <QuickSelect label="Zielgruppe" value={filters.position} options={scoutOptions.positions} onChange={(value) => applyFilters({ position: value })} />
                        <QuickSelect label="Alter" value={filters.age_band} options={scoutOptions.ageBands} onChange={(value) => applyFilters({ age_band: value })} />
                        <QuickSelect label="Preisfenster" value={filters.value_band} options={scoutOptions.valueBands} onChange={(value) => applyFilters({ value_band: value })} />
                        <QuickSelect label="Scout-Linse" value={filters.discovery_level} options={scoutOptions.levels} onChange={(value) => applyFilters({ discovery_level: value })} />
                        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/40 px-4 py-3">
                            <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                <SlidersHorizontal size={13} weight="bold" />
                                Treffer
                            </div>
                            <div className="mt-2 text-lg font-black text-white">{targets.length}</div>
                        </div>
                    </div>
                </div>

                <SectionCard title="Scout Staff" icon={UsersThree} bodyClassName="p-6 space-y-4">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div className="text-sm text-[var(--text-muted)]">
                            {scoutStaff.length}/{scoutOptions.slot_limit || moduleSettings.scout_slots || 0} Scouts aktiv
                        </div>
                        <Tag tone="slate">Slots {moduleSettings.scout_slots || scoutOptions.slot_limit || scoutStaff.length}</Tag>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {scoutStaff.map((scout) => (
                            <div key={scout.id} className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30 p-5">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-sm font-black uppercase tracking-[0.06em] text-white">{scout.name}</div>
                                        <div className="mt-1 flex flex-wrap gap-2">
                                            <Tag tone="emerald">{scout.level}</Tag>
                                            <Tag tone="cyan">{scout.specialty}</Tag>
                                            <Tag tone="slate">{scout.region}</Tag>
                                        </div>
                                    </div>
                                    <Tag tone={scout.status === 'available' ? 'emerald' : 'amber'}>
                                        {scout.status}
                                    </Tag>
                                </div>
                                <div className="mt-4 grid gap-3 md:grid-cols-2">
                                    <InfoTile icon={ShieldCheck} label="Workload" value={`${scout.workload}%`} />
                                    <InfoTile icon={TrendUp} label="Verfuegbar" value={scout.available_at || 'Jetzt'} />
                                </div>
                            </div>
                        ))}
                    </div>
                </SectionCard>

                <div className="grid gap-8 xl:grid-cols-[1.05fr_0.95fr]">
                    <SectionCard title="Discovery Board" icon={Target} bodyClassName="p-6 space-y-4 xl:col-span-2">
                        {scanState && (
                            <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/45 px-4 py-3 text-sm text-[var(--text-muted)]">
                                Discovery-Leads rotieren pro Filterfenster. Bereits frisch gescannte Spieler werden kurzfristig leicht zurueckgestellt, damit der Markt nicht immer dieselben Namen ausspuckt.
                            </div>
                        )}
                        {discoveries?.length ? (
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                {discoveries.map((entry) => (
                                    <div key={entry.id} className="rounded-3xl border border-cyan-400/20 bg-cyan-500/5 p-5">
                                        <div className="flex items-center gap-4">
                                            <img src={entry.player.photo_url} alt={entry.player.name} className="h-14 w-14 rounded-2xl border border-white/10 object-cover" />
                                            <div className="min-w-0">
                                                <div className="truncate text-sm font-black uppercase tracking-[0.06em] text-white">{entry.player.name}</div>
                                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                                    {entry.player.position} / {entry.player.age} / {entry.player.club_name}
                                                </div>
                                            </div>
                                        </div>
                                        <div className="mt-3 flex flex-wrap gap-2">
                                            <Tag tone="cyan">{entry.region_tag}</Tag>
                                            <Tag tone="amber">{entry.market_band}</Tag>
                                            <Tag tone="emerald">{entry.fit_score}/99 Fit</Tag>
                                        </div>
                                        <p className="mt-3 text-sm text-[var(--text-muted)]">{entry.discovery_note}</p>
                                        <div className="mt-3 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                            Gescannt {entry.scanned_at}
                                        </div>
                                <div className="mt-4 flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        onClick={() => watchlistForm.post(route('scouting.watchlist.store', entry.player.id), { preserveScroll: true })}
                                                className="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200"
                                            >
                                                Als Lead merken
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => router.post(route('scouting.report.generate', entry.player.id), {}, { preserveScroll: true })}
                                                className="rounded-2xl border border-amber-400/20 bg-amber-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200"
                                            >
                                                Report ziehen
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">
                                Noch keine Scout-Leads fuer diesen Markt. Scanne den Zielmarkt, um entdeckte Kandidaten hier zu sammeln.
                            </div>
                        )}
                    </SectionCard>

                    <SectionCard title="Zielmarkt" icon={Binoculars} bodyClassName="p-6 space-y-4">
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
                                            <div className="mt-2 flex flex-wrap gap-2">
                                                <Tag tone="amber">{player.potential_hint}</Tag>
                                                <Tag tone="cyan">{player.region_tag}</Tag>
                                                <Tag tone="slate">{player.country || 'Unbekannt'}</Tag>
                                            </div>
                                        </div>
                                    </div>
                                    <div className="text-right">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Marktfenster</div>
                                        <div className="text-sm font-black text-white">{player.market_band}</div>
                                    </div>
                                </div>

                                <div className="mt-4 grid gap-3 md:grid-cols-2">
                                    <InfoTile icon={Target} label="Scout-Fit" value={`${player.fit_score}/99`} />
                                    <InfoTile icon={Binoculars} label="Ersteinschaetzung" value={player.discovery_note} />
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
                                    <QuickSelect
                                        label="Scout"
                                        value={watchlistForm.data.scout_id}
                                        options={buildScoutOptions(scoutStaff)}
                                        onChange={(value) => watchlistForm.setData('scout_id', value)}
                                    />
                                </div>
                            </div>
                        ))}
                        {targets.length === 0 && (
                            <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">
                                Keine Kandidaten im aktuellen Zielmarkt gefunden.
                            </div>
                        )}
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
                                    {entry.scout && <Tag tone="amber">{entry.scout.name}</Tag>}
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
                                    <QuickSelect
                                        label="Scout"
                                        value={entry.scout_id ? String(entry.scout_id) : ''}
                                        options={buildScoutOptions(scoutStaff)}
                                        onChange={(value) => router.patch(route('scouting.watchlist.update', entry.id), buildWatchlistPayload(entry, { scout_id: value }), { preserveScroll: true })}
                                    />
                                </div>

                                <div className="mt-3 grid gap-3 md:grid-cols-2">
                                    <InfoTile icon={UsersThree} label="Scout" value={entry.scout ? `${entry.scout.name} / ${entry.scout.status}` : 'Auto-Zuweisung'} />
                                    <InfoTile icon={ShieldCheck} label="Auslastung" value={entry.scout ? `${entry.scout.workload}%` : '-'} />
                                </div>

                                <div className="mt-3 grid gap-3 md:grid-cols-1">
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
                    <option key={typeof option === 'string' ? option : option.value} value={typeof option === 'string' ? option : option.value}>
                        {typeof option === 'string' ? option : option.label}
                    </option>
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
        scout_id: patch.scout_id ?? (entry.scout_id ? String(entry.scout_id) : ''),
        notes: patch.notes ?? entry.notes ?? '',
    };
}

function buildScoutOptions(scoutStaff) {
    return [
        { value: '', label: 'auto' },
        ...scoutStaff.map((scout) => ({
            value: String(scout.id),
            label: `${scout.name} (${scout.level}/${scout.status})`,
        })),
    ];
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
