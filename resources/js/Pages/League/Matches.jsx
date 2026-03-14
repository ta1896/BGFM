import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { SoccerBall, FunnelSimple, X, CaretRight } from '@phosphor-icons/react';
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

export default function Matches({ competitionSeasons, matchesByGroup, groupType, ownedClubIds, filters, hasActiveFilters }) {
    const [localFilters, setLocalFilters] = useState(filters);
    const groups = Object.entries(matchesByGroup || {});
    const totalMatches = groups.reduce((accumulator, [, matches]) => accumulator + matches.length, 0);

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
