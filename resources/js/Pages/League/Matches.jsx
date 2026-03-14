import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    SoccerBall, FunnelSimple, X, CaretRight,
    Lightning, Clock, CheckCircle, Circle
} from '@phosphor-icons/react';

const StatusBadge = ({ status }) => {
    const configs = {
        live:      { cls: 'bg-rose-500/20 border-rose-500/40 text-rose-400', label: 'LIVE', pulse: true },
        scheduled: { cls: 'bg-slate-800 border-slate-700 text-slate-400',    label: 'GEPLANT', pulse: false },
        played:    { cls: 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500', label: 'BEENDET', pulse: false },
    };
    const cfg = configs[status] || configs.scheduled;
    return (
        <span className={`flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[9px] font-black uppercase tracking-widest ${cfg.cls}`}>
            {cfg.pulse && <span className="relative flex h-1.5 w-1.5"><span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75" /><span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-rose-500" /></span>}
            {cfg.label}
        </span>
    );
};

const MatchCard = ({ match, ownedClubIds }) => {
    const isOwned = ownedClubIds.includes(match.home_club_id) || ownedClubIds.includes(match.away_club_id);
    const isLive = match.status === 'live';

    return (
        <motion.div
            initial={{ opacity: 0, y: 6 }}
            animate={{ opacity: 1, y: 0 }}
        >
            <Link
                href={route('matches.show', match.id)}
                className={`flex items-center gap-6 px-6 py-5 transition-all border-b border-white/5 hover:bg-white/[0.03] group ${isOwned ? 'bg-amber-500/[0.02]' : ''}`}
            >
                {/* Competition Badge */}
                <div className="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center shrink-0">
                    <span className="text-[9px] font-black text-slate-500 uppercase">
                        {match.competition_season?.competition?.code || 'LG'}
                    </span>
                </div>

                {/* Match */}
                <div className="flex-1 flex items-center gap-6 min-w-0">
                    {/* Home */}
                    <div className={`flex items-center gap-3 flex-1 justify-end min-w-0 ${ownedClubIds.includes(match.home_club_id) ? 'opacity-100' : 'opacity-60'}`}>
                        <span className="text-sm font-black text-white uppercase tracking-tight truncate text-right">{match.home_club?.short_name}</span>
                        <img src={match.home_club?.logo_url || '/images/default-club.png'}
                            className="w-9 h-9 object-contain rounded shrink-0" />
                    </div>

                    {/* Score / Time */}
                    <div className="flex flex-col items-center gap-1 shrink-0 w-24">
                        {match.status === 'played' ? (
                            <span className="text-xl font-black text-white italic tabular-nums">
                                {match.home_score} : {match.away_score}
                            </span>
                        ) : match.status === 'live' ? (
                            <span className="text-xl font-black text-rose-400 italic animate-pulse tabular-nums">
                                {match.home_score ?? 0} : {match.away_score ?? 0}
                            </span>
                        ) : (
                            <span className="text-lg font-black text-slate-400 italic">{match.kickoff_formatted?.split(' ')[1] || '-'}</span>
                        )}
                        <StatusBadge status={match.status} />
                    </div>

                    {/* Away */}
                    <div className={`flex items-center gap-3 flex-1 min-w-0 ${ownedClubIds.includes(match.away_club_id) ? 'opacity-100' : 'opacity-60'}`}>
                        <img src={match.away_club?.logo_url || '/images/default-club.png'}
                            className="w-9 h-9 object-contain rounded shrink-0" />
                        <span className="text-sm font-black text-white uppercase tracking-tight truncate">{match.away_club?.short_name}</span>
                    </div>
                </div>

                {/* Arrow */}
                <CaretRight size={16} className="text-slate-700 group-hover:text-amber-500 transition-colors shrink-0" />
            </Link>
        </motion.div>
    );
};

export default function Matches({
    competitionSeasons, activeCompetitionSeason, matchesByGroup, groupType,
    ownedClubIds, clubFilterOptions, activeClub, filters, hasActiveFilters
}) {
    const [localFilters, setLocalFilters] = useState(filters);

    const applyFilter = (key, value) => {
        const updated = { ...localFilters, [key]: value };
        setLocalFilters(updated);
        const params = Object.fromEntries(Object.entries(updated).filter(([, v]) => v));
        router.get(route('league.matches'), params, { preserveState: true, replace: true });
    };

    const clearFilters = () => {
        setLocalFilters({});
        router.get(route('league.matches'), {}, { preserveState: false });
    };

    const groups = Object.entries(matchesByGroup || {});
    const totalMatches = groups.reduce((acc, [, ms]) => acc + ms.length, 0);

    return (
        <AuthenticatedLayout>
            <Head title="Spielplan" />

            <div className="max-w-[1200px] mx-auto space-y-8">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Wettbewerb</p>
                        <h1 className="text-4xl font-black text-white uppercase tracking-tighter italic">
                            Spielplan
                        </h1>
                    </div>
                    <div className="flex items-center gap-3 text-[10px] font-black text-slate-600 uppercase tracking-widest">
                        <SoccerBall size={16} weight="fill" className="text-slate-700" />
                        {totalMatches} Spiele
                    </div>
                </div>

                {/* Filters */}
                <div className="sim-card p-5 bg-[#0c1222]/60 border-slate-800/50">
                    <div className="flex flex-wrap items-center gap-3">
                        <div className="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                            <FunnelSimple size={14} weight="bold" />
                            Filter
                        </div>

                        {/* Competition Season */}
                        <select
                            value={localFilters.competition_season || ''}
                            onChange={e => applyFilter('competition_season', e.target.value)}
                            className="sim-select py-2 text-[10px] uppercase font-black"
                        >
                            <option value="">Alle Wettbewerbe</option>
                            {competitionSeasons.map(cs => (
                                <option key={cs.id} value={cs.id}>
                                    {cs.competition?.name} — {cs.season?.name}
                                </option>
                            ))}
                        </select>

                        {/* Status */}
                        {['scheduled', 'live', 'played'].map(s => (
                            <button
                                key={s}
                                onClick={() => applyFilter('status', localFilters.status === s ? '' : s)}
                                className={`px-4 py-2 rounded-xl border text-[9px] font-black uppercase tracking-widest transition-all ${
                                    localFilters.status === s
                                        ? 'bg-amber-500/20 border-amber-500/40 text-amber-500'
                                        : 'bg-slate-900 border-slate-800 text-slate-500 hover:text-slate-300'
                                }`}
                            >
                                {s === 'scheduled' ? 'Geplant' : s === 'live' ? 'Live' : 'Beendet'}
                            </button>
                        ))}

                        {/* Scope */}
                        {['today', 'week', 'upcoming'].map(s => (
                            <button
                                key={s}
                                onClick={() => applyFilter('scope', localFilters.scope === s ? '' : s)}
                                className={`px-4 py-2 rounded-xl border text-[9px] font-black uppercase tracking-widest transition-all ${
                                    localFilters.scope === s
                                        ? 'bg-amber-600/20 border-amber-600/40 text-amber-600'
                                        : 'bg-slate-900 border-slate-800 text-slate-500 hover:text-slate-300'
                                }`}
                            >
                                {s === 'today' ? 'Heute' : s === 'week' ? 'Diese Woche' : 'Bevorstehend'}
                            </button>
                        ))}

                        {hasActiveFilters && (
                            <button onClick={clearFilters}
                                className="ml-auto flex items-center gap-1.5 px-3 py-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[9px] font-black uppercase tracking-widest hover:bg-rose-500/20 transition-all"
                            >
                                <X size={12} weight="bold" /> Filter löschen
                            </button>
                        )}
                    </div>
                </div>

                {/* Match Groups */}
                <div className="space-y-6">
                    {groups.length === 0 ? (
                        <div className="sim-card p-20 text-center border-dashed border-slate-800">
                            <SoccerBall size={48} weight="thin" className="text-slate-700 mx-auto mb-6" />
                            <p className="text-slate-500 font-bold uppercase tracking-widest text-sm italic">Keine Spiele gefunden</p>
                        </div>
                    ) : groups.map(([groupKey, matches]) => (
                        <div key={groupKey} className="sim-card overflow-hidden p-0">
                            {/* Group Header */}
                            <div className="px-6 py-4 bg-slate-900/60 border-b border-white/5 flex items-center gap-4">
                                <div className="w-8 h-8 rounded-lg bg-amber-600/20 border border-amber-600/20 flex items-center justify-center">
                                    <span className="text-[10px] font-black text-amber-600">
                                        {groupType === 'matchday' ? groupKey : groupKey?.split('-')[2]}
                                    </span>
                                </div>
                                <span className="text-xs font-black text-white uppercase tracking-widest">
                                    {groupType === 'matchday'
                                        ? `Spieltag ${groupKey}`
                                        : (matches[0]?.kickoff_day_label || groupKey)}
                                </span>
                                <span className="ml-auto text-[9px] font-black text-slate-600 uppercase tracking-widest">{matches.length} Spiel{matches.length !== 1 ? 'e' : ''}</span>
                            </div>

                            {/* Matches */}
                            <div>
                                {matches.map(match => (
                                    <MatchCard key={match.id} match={match} ownedClubIds={ownedClubIds} />
                                ))}
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
