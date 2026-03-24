import React, { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { SoccerBall, FunnelSimple, X, CaretRight, Broadcast, CalendarBlank, ShieldCheck, Sparkle } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function StatusBadge({ status }) {
    const configs = {
        live: 'border-rose-500/40 bg-rose-500/20 text-rose-400',
        scheduled: 'border-[var(--border-pillar)] bg-[var(--bg-content)] text-[var(--text-muted)]',
        played: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-500',
    };

    return (
        <span className={`inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-widest ${configs[status] || configs.scheduled}`}>
            {status === 'live' && <span className="h-1.5 w-1.5 rounded-full bg-rose-500" />}
            {status === 'scheduled' ? 'Geplant' : status === 'live' ? 'Live' : 'Beendet'}
        </span>
    );
}

function MatchCard({ match, ownedClubIds }) {
    const isOwned = ownedClubIds.includes(match.home_club_id) || ownedClubIds.includes(match.away_club_id);

    return (
        <Link
            href={route('matches.show', match.id)}
            className={`flex items-center gap-6 border-b border-white/5 px-6 py-5 transition-all hover:bg-white/[0.03] ${isOwned ? 'bg-[var(--accent-primary)]/[0.03]' : ''}`}
        >
            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[9px] font-black uppercase text-[var(--text-muted)]">
                {match.competition_season?.competition?.code || 'LG'}
            </div>

            <div className="flex min-w-0 flex-1 items-center gap-6">
                <div className={`flex flex-1 items-center justify-end gap-3 min-w-0 ${ownedClubIds.includes(match.home_club_id) ? 'opacity-100' : 'opacity-60'}`}>
                    <span className="truncate text-right text-sm font-black uppercase tracking-tight text-[var(--text-main)]">{match.home_club?.short_name}</span>
                    <img src={match.home_club?.logo_url || '/images/default-club.png'} alt={match.home_club?.short_name} className="h-9 w-9 shrink-0 rounded object-contain" />
                </div>

                <div className="flex w-24 shrink-0 flex-col items-center gap-1">
                    {match.status === 'played' ? (
                        <span className="text-xl font-black italic tabular-nums text-[var(--text-main)]">{match.home_score} : {match.away_score}</span>
                    ) : match.status === 'live' ? (
                        <span className="text-xl font-black italic tabular-nums text-rose-400">{match.home_score ?? 0} : {match.away_score ?? 0}</span>
                    ) : (
                        <span className="text-lg font-black italic text-[var(--text-muted)]">{match.kickoff_formatted?.split(' ')[1] || '-'}</span>
                    )}
                    <StatusBadge status={match.status} />
                </div>

                <div className={`flex min-w-0 flex-1 items-center gap-3 ${ownedClubIds.includes(match.away_club_id) ? 'opacity-100' : 'opacity-60'}`}>
                    <img src={match.away_club?.logo_url || '/images/default-club.png'} alt={match.away_club?.short_name} className="h-9 w-9 shrink-0 rounded object-contain" />
                    <span className="truncate text-sm font-black uppercase tracking-tight text-[var(--text-main)]">{match.away_club?.short_name}</span>
                </div>
            </div>

            <CaretRight size={16} className="shrink-0 text-slate-600 transition-colors" />
        </Link>
    );
}

function OverviewStat({ icon: Icon, label, value, tone = 'primary' }) {
    const tones = {
        primary: 'border-[var(--accent-primary)]/20 bg-[var(--accent-primary)]/10 text-[var(--accent-primary)]',
        secondary: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
        success: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
        live: 'border-rose-400/20 bg-rose-500/10 text-rose-300',
    };

    return (
        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/75 p-4">
            <div className="flex items-center justify-between gap-3">
                <div>
                    <div className="text-[9px] font-black uppercase tracking-[0.32em] text-[var(--text-muted)]">{label}</div>
                    <div className="mt-2 text-2xl font-black italic text-[var(--text-main)]">{value}</div>
                </div>
                <div className={`flex h-11 w-11 items-center justify-center rounded-2xl border ${tones[tone] || tones.primary}`}>
                    <Icon size={20} weight="fill" />
                </div>
            </div>
        </div>
    );
}

function SpotlightRow({ title, eyebrow, description, matches, ownedClubIds, emptyText }) {
    return (
        <SectionCard bodyClassName="overflow-hidden">
            <div className="border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/30 px-5 py-4">
                <div className="text-[9px] font-black uppercase tracking-[0.32em] text-[var(--accent-primary)]">{eyebrow}</div>
                <div className="mt-1 flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-lg font-black uppercase tracking-tight text-[var(--text-main)]">{title}</h2>
                        <p className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{description}</p>
                    </div>
                    <div className="shrink-0 rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] px-3 py-1 text-[10px] font-black uppercase tracking-[0.24em] text-[var(--text-muted)]">
                        {matches.length} Spiel{matches.length === 1 ? '' : 'e'}
                    </div>
                </div>
            </div>

            {matches.length === 0 ? (
                <div className="px-5 py-8 text-sm font-bold uppercase tracking-widest text-[var(--text-muted)]">
                    {emptyText}
                </div>
            ) : (
                <div>
                    {matches.map((match) => (
                        <MatchCard key={match.id} match={match} ownedClubIds={ownedClubIds} />
                    ))}
                </div>
            )}
        </SectionCard>
    );
}

export default function Matches({ competitionSeasons, matchesByGroup, groupType, ownedClubIds, filters, hasActiveFilters }) {
    const [localFilters, setLocalFilters] = useState(filters);
    const [activeTab, setActiveTab] = useState(filters?.type === 'league' || groupType === 'matchday' ? 'league' : 'overview');
    const groups = Object.entries(matchesByGroup || {});
    const totalMatches = groups.reduce((accumulator, [, matches]) => accumulator + matches.length, 0);
    const leagueCompetitionSeasons = useMemo(
        () => (competitionSeasons || []).filter((competitionSeason) => competitionSeason?.competition?.type === 'league'),
        [competitionSeasons],
    );
    const selectedLeagueSeasonId = useMemo(() => {
        const activeLeagueId = localFilters.competition_season
            && leagueCompetitionSeasons.some((competitionSeason) => String(competitionSeason.id) === String(localFilters.competition_season))
            ? String(localFilters.competition_season)
            : '';

        return activeLeagueId || (leagueCompetitionSeasons[0] ? String(leagueCompetitionSeasons[0].id) : '');
    }, [leagueCompetitionSeasons, localFilters.competition_season]);
    const flatMatches = useMemo(() => groups.flatMap(([, matches]) => matches), [groups]);
    const todayKey = new Date().toLocaleDateString('en-CA');
    const todayMatches = useMemo(() => flatMatches.filter((match) => match.kickoff_date === todayKey), [flatMatches, todayKey]);
    const liveMatches = useMemo(() => flatMatches.filter((match) => match.status === 'live'), [flatMatches]);
    const friendlyMatches = useMemo(() => flatMatches.filter((match) => match.type === 'friendly'), [flatMatches]);
    const ownedMatches = useMemo(
        () => flatMatches.filter((match) => ownedClubIds.includes(match.home_club_id) || ownedClubIds.includes(match.away_club_id)),
        [flatMatches, ownedClubIds],
    );
    const latestRelevantMatches = useMemo(() => {
        const ids = new Set();
        return [...todayMatches, ...liveMatches, ...friendlyMatches, ...ownedMatches].filter((match) => {
            if (ids.has(match.id)) {
                return false;
            }
            ids.add(match.id);
            return true;
        }).slice(0, 8);
    }, [todayMatches, liveMatches, friendlyMatches, ownedMatches]);

    const applyFilter = (key, value) => {
        const updated = { ...localFilters, [key]: value };
        setLocalFilters(updated);
        const params = Object.fromEntries(Object.entries(updated).filter(([, currentValue]) => currentValue));
        router.get(route('league.matches'), params, { preserveState: true, replace: true });
    };

    const clearFilters = () => {
        setLocalFilters({});
        router.get(route('league.matches'), {}, { preserveState: false });
    };

    const switchTab = (tab) => {
        setActiveTab(tab);

        if (tab === 'league') {
            const params = {
                ...localFilters,
                type: 'league',
                competition_season: selectedLeagueSeasonId || undefined,
            };

            router.get(route('league.matches'), Object.fromEntries(Object.entries(params).filter(([, value]) => value)), {
                preserveState: true,
                replace: true,
            });

            return;
        }

        const params = {
            ...localFilters,
            type: localFilters.type === 'league' ? undefined : localFilters.type,
            competition_season: localFilters.type === 'league' ? undefined : localFilters.competition_season,
        };

        router.get(route('league.matches'), Object.fromEntries(Object.entries(params).filter(([, value]) => value)), {
            preserveState: true,
            replace: true,
        });
    };

    const applyLeagueSeason = (competitionSeasonId) => {
        const updated = {
            ...localFilters,
            type: 'league',
            competition_season: competitionSeasonId,
        };

        setLocalFilters(updated);
        router.get(route('league.matches'), Object.fromEntries(Object.entries(updated).filter(([, value]) => value)), {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Spielplan" />

            <div className="mx-auto max-w-[1200px] space-y-8">
                <PageHeader
                    eyebrow="Wettbewerb"
                    title="Spielplan"
                    actions={
                        <div className="flex items-center gap-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                            <SoccerBall size={16} weight="fill" className="text-[var(--accent-primary)]" />
                            {totalMatches} Spiele
                        </div>
                    }
                />

                <PageReveal>
                    <div className="flex flex-wrap items-center gap-3">
                        {[
                            { key: 'overview', label: 'Uebersicht' },
                            { key: 'league', label: 'Ligaspieltage' },
                        ].map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => switchTab(tab.key)}
                                className={`rounded-2xl border px-5 py-3 text-[10px] font-black uppercase tracking-[0.24em] transition-all ${
                                    activeTab === tab.key
                                        ? 'border-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/12 text-[var(--accent-primary)]'
                                        : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>
                </PageReveal>

                {activeTab === 'overview' ? (
                    <>
                        <PageReveal>
                            <div className="grid gap-4 lg:grid-cols-[1.45fr_0.95fr]">
                                <SectionCard bodyClassName="p-5">
                                    <div className="rounded-[28px] border border-[var(--border-pillar)] bg-[radial-gradient(circle_at_top_left,rgba(245,158,11,0.18),transparent_40%),linear-gradient(135deg,rgba(15,23,42,0.92),rgba(10,12,24,0.88))] p-5">
                                        <div className="text-[9px] font-black uppercase tracking-[0.32em] text-[var(--accent-primary)]">Match-Zentrale</div>
                                        <div className="mt-2 flex flex-wrap items-end justify-between gap-4">
                                            <div className="max-w-xl">
                                                <h2 className="text-2xl font-black uppercase tracking-tight text-white">Heute, Live und Testspiele auf einen Blick</h2>
                                                <p className="mt-2 text-sm leading-relaxed text-white/70">
                                                    Die wichtigsten Termine stehen sofort oben. So siehst du heutige Begegnungen, laufende Spiele und Freundschaftsspiele,
                                                    ohne dich erst durch den kompletten Spielplan zu arbeiten.
                                                </p>
                                            </div>
                                            <div className="grid min-w-[220px] gap-3 sm:grid-cols-2">
                                                <OverviewStat icon={CalendarBlank} label="Heute" value={todayMatches.length} tone="primary" />
                                                <OverviewStat icon={Broadcast} label="Live" value={liveMatches.length} tone="live" />
                                                <OverviewStat icon={Sparkle} label="Testspiele" value={friendlyMatches.length} tone="secondary" />
                                                <OverviewStat icon={ShieldCheck} label="Deine Spiele" value={ownedMatches.length} tone="success" />
                                            </div>
                                        </div>
                                    </div>
                                </SectionCard>

                                <SectionCard title="Schnellzugriff" icon={SoccerBall} bodyClassName="p-5">
                                    <div className="space-y-3">
                                        {[
                                            {
                                                label: 'Heutige Spiele',
                                                active: localFilters.scope === 'today',
                                                onClick: () => applyFilter('scope', localFilters.scope === 'today' ? '' : 'today'),
                                            },
                                            {
                                                label: 'Live-Spiele',
                                                active: localFilters.status === 'live',
                                                onClick: () => applyFilter('status', localFilters.status === 'live' ? '' : 'live'),
                                            },
                                            {
                                                label: 'Freundschaftsspiele',
                                                active: localFilters.type === 'friendly',
                                                onClick: () => applyFilter('type', localFilters.type === 'friendly' ? '' : 'friendly'),
                                            },
                                            {
                                                label: 'Meine Begegnungen',
                                                active: Boolean(localFilters.club),
                                                onClick: () => applyFilter('club', localFilters.club ? '' : (ownedClubIds[0] || '')),
                                            },
                                        ].map((quickFilter) => (
                                            <button
                                                key={quickFilter.label}
                                                type="button"
                                                onClick={quickFilter.onClick}
                                                className={`flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left transition-all ${
                                                    quickFilter.active
                                                        ? 'border-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/12'
                                                        : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/75 hover:border-[var(--accent-primary)]/20'
                                                }`}
                                            >
                                                <span className="text-[11px] font-black uppercase tracking-[0.24em] text-[var(--text-main)]">{quickFilter.label}</span>
                                                <CaretRight size={14} className={quickFilter.active ? 'text-[var(--accent-primary)]' : 'text-[var(--text-muted)]'} />
                                            </button>
                                        ))}
                                    </div>
                                </SectionCard>
                            </div>
                        </PageReveal>
                    </>
                ) : (
                    <PageReveal>
                        <SectionCard bodyClassName="p-5">
                            <div className="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
                                <div>
                                    <div className="text-[9px] font-black uppercase tracking-[0.32em] text-[var(--accent-primary)]">Ligaspieltage</div>
                                    <h2 className="mt-2 text-2xl font-black uppercase tracking-tight text-[var(--text-main)]">Liga getrennt im Matchday-Modus</h2>
                                    <p className="mt-2 max-w-2xl text-sm leading-relaxed text-[var(--text-muted)]">
                                        Waehle die Liga, die du verfolgen willst. In diesem Tab bleibt der Fokus auf den Spieltagen der ausgewaehlten Liga,
                                        statt Liga-, Pokal- und Freundschaftsspiele zu mischen.
                                    </p>
                                </div>
                                <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/70 p-4">
                                    <div className="text-[9px] font-black uppercase tracking-[0.24em] text-[var(--text-muted)]">Liga auswaehlen</div>
                                    <select
                                        value={selectedLeagueSeasonId}
                                        onChange={(event) => applyLeagueSeason(event.target.value)}
                                        className="sim-select mt-3 w-full py-3 text-[11px] font-black uppercase"
                                    >
                                        {leagueCompetitionSeasons.map((competitionSeason) => (
                                            <option key={competitionSeason.id} value={competitionSeason.id}>
                                                {competitionSeason.competition?.name} - {competitionSeason.season?.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </SectionCard>
                    </PageReveal>
                )}

                <PageReveal>
                    <SectionCard title="Filter" icon={FunnelSimple} bodyClassName="p-5">
                        <div className="flex flex-wrap items-center gap-3">
                            <select
                                value={localFilters.competition_season || ''}
                                onChange={(event) => applyFilter('competition_season', event.target.value)}
                                className="sim-select py-2 text-[10px] font-black uppercase"
                            >
                                <option value="">Alle Wettbewerbe</option>
                                {competitionSeasons.map((competitionSeason) => (
                                    <option key={competitionSeason.id} value={competitionSeason.id}>
                                        {competitionSeason.competition?.name} - {competitionSeason.season?.name}
                                    </option>
                                ))}
                            </select>

                            {['scheduled', 'live', 'played'].map((status) => (
                                <button
                                    key={status}
                                    type="button"
                                    onClick={() => applyFilter('status', localFilters.status === status ? '' : status)}
                                    className={`rounded-xl border px-4 py-2 text-[9px] font-black uppercase tracking-widest transition-all ${
                                        localFilters.status === status
                                            ? 'border-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/15 text-[var(--accent-primary)]'
                                            : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                                    }`}
                                >
                                    {status === 'scheduled' ? 'Geplant' : status === 'live' ? 'Live' : 'Beendet'}
                                </button>
                            ))}

                            {['today', 'week', 'upcoming'].map((scope) => (
                                <button
                                    key={scope}
                                    type="button"
                                    onClick={() => applyFilter('scope', localFilters.scope === scope ? '' : scope)}
                                    className={`rounded-xl border px-4 py-2 text-[9px] font-black uppercase tracking-widest transition-all ${
                                        localFilters.scope === scope
                                            ? 'border-[var(--accent-secondary)]/40 bg-[var(--accent-secondary)]/15 text-[var(--accent-secondary)]'
                                            : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                                    }`}
                                >
                                    {scope === 'today' ? 'Heute' : scope === 'week' ? 'Diese Woche' : 'Bevorstehend'}
                                </button>
                            ))}

                            {['league', 'cup', 'friendly'].map((type) => (
                                <button
                                    key={type}
                                    type="button"
                                    onClick={() => applyFilter('type', localFilters.type === type ? '' : type)}
                                    className={`rounded-xl border px-4 py-2 text-[9px] font-black uppercase tracking-widest transition-all ${
                                        localFilters.type === type
                                            ? 'border-cyan-400/40 bg-cyan-400/15 text-cyan-300'
                                            : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                                    }`}
                                >
                                    {type === 'league' ? 'Liga' : type === 'cup' ? 'Pokal' : 'Freundschaft'}
                                </button>
                            ))}

                            {hasActiveFilters && (
                                <button
                                    type="button"
                                    onClick={clearFilters}
                                    className="ml-auto inline-flex items-center gap-1.5 rounded-xl border border-rose-500/20 bg-rose-500/10 px-3 py-2 text-[9px] font-black uppercase tracking-widest text-rose-400 transition-colors hover:bg-rose-500/20"
                                >
                                    <X size={12} weight="bold" />
                                    Filter loeschen
                                </button>
                            )}
                        </div>
                    </SectionCard>
                </PageReveal>

                {activeTab === 'overview' && (
                    <>
                        <div className="grid gap-6 xl:grid-cols-2">
                            <PageReveal>
                                <SpotlightRow
                                    eyebrow="Heute"
                                    title="Heutige Spiele"
                                    description="Alle Begegnungen mit heutigem Anstoss, ideal fuer den schnellen Tagesueberblick."
                                    matches={todayMatches}
                                    ownedClubIds={ownedClubIds}
                                    emptyText="Heute sind keine Spiele angesetzt."
                                />
                            </PageReveal>

                            <PageReveal>
                                <SpotlightRow
                                    eyebrow="Freundschaft"
                                    title="Testspiele & Sondertermine"
                                    description="Freundschaftsspiele separat gebuendelt, damit sie zwischen Liga und Pokal nicht untergehen."
                                    matches={friendlyMatches}
                                    ownedClubIds={ownedClubIds}
                                    emptyText="Aktuell sind keine Freundschaftsspiele im sichtbaren Zeitraum vorhanden."
                                />
                            </PageReveal>
                        </div>

                        {(liveMatches.length > 0 || latestRelevantMatches.length > 0) && (
                            <PageReveal>
                                <SectionCard title="Im Fokus" icon={Broadcast} bodyClassName="p-5">
                                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                                        {(liveMatches.length > 0 ? liveMatches : latestRelevantMatches).map((match) => (
                                            <Link
                                                key={match.id}
                                                href={route('matches.show', match.id)}
                                                className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/70 p-4 transition-all hover:border-[var(--accent-primary)]/25 hover:bg-[var(--bg-pillar)]"
                                            >
                                                <div className="flex items-center justify-between gap-3">
                                                    <StatusBadge status={match.status} />
                                                    <span className="text-[9px] font-black uppercase tracking-[0.24em] text-[var(--text-muted)]">
                                                        {match.type === 'friendly' ? 'Freundschaft' : match.competition_season?.competition?.code || 'Match'}
                                                    </span>
                                                </div>
                                                <div className="mt-4 text-sm font-black uppercase tracking-tight text-[var(--text-main)]">
                                                    {(match.home_club?.short_name || match.home_club?.name || 'Heim')} vs {(match.away_club?.short_name || match.away_club?.name || 'Gast')}
                                                </div>
                                                <div className="mt-2 text-xs text-[var(--text-muted)]">
                                                    {match.status === 'played' || match.status === 'live'
                                                        ? `${match.home_score ?? 0} : ${match.away_score ?? 0}`
                                                        : match.kickoff_formatted}
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                </SectionCard>
                            </PageReveal>
                        )}
                    </>
                )}

                {groups.length === 0 ? (
                    <PageReveal>
                        <SectionCard bodyClassName="p-20 text-center">
                            <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                            <p className="text-sm font-bold uppercase tracking-widest text-[var(--text-muted)]">Keine Spiele gefunden</p>
                        </SectionCard>
                    </PageReveal>
                ) : (
                    <StaggerGroup className="space-y-6">
                        {groups.map(([groupKey, matches]) => (
                            <SectionCard
                                key={groupKey}
                                title={groupType === 'matchday' ? `Spieltag ${groupKey}` : (matches[0]?.kickoff_day_label || groupKey)}
                                icon={SoccerBall}
                                bodyClassName="overflow-hidden"
                            >
                                <div className="border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/25 px-6 py-3 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    {matches.length} Spiel{matches.length !== 1 ? 'e' : ''}
                                </div>
                                <div>
                                    {matches.map((match) => (
                                        <MatchCard key={match.id} match={match} ownedClubIds={ownedClubIds} />
                                    ))}
                                </div>
                            </SectionCard>
                        ))}
                    </StaggerGroup>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
