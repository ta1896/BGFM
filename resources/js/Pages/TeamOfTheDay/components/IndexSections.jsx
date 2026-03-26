import React from 'react';
import { router } from '@inertiajs/react';
import { Calendar, Funnel, Star, Trophy } from '@phosphor-icons/react';
import PlayerLink from '@/Components/PlayerLink';
import ClubLink from '@/Components/ClubLink';

export function TeamOfTheDayHeader({ teams, selectedTeam, onTeamChange }) {
    return (
        <div className="flex flex-col justify-between gap-6 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-6 shadow-sm md:flex-row md:items-center">
            <div className="flex items-center space-x-4">
                <div className="rounded-lg bg-yellow-600/20 p-3 text-yellow-500">
                    <Trophy size={24} weight="fill" />
                </div>
                <div>
                    <h3 className="text-xl font-bold uppercase tracking-tight text-white">Ehrung der Besten</h3>
                    <p className="text-sm text-[var(--text-muted)]">Die herausragenden Leistungen des Spieltags auf einen Blick.</p>
                </div>
            </div>

            <div className="flex items-center space-x-3">
                <Funnel className="text-[var(--text-muted)]" size={16} weight="fill" />
                <select
                    value={selectedTeam}
                    onChange={onTeamChange}
                    className="min-w-[240px] rounded-lg border-[var(--border-pillar)] bg-[var(--bg-content)] text-sm text-white focus:border-yellow-500 focus:ring-yellow-500"
                >
                    {teams.map((team) => (
                        <option key={team.id} value={team.id}>
                            {team.for_date ? new Date(team.for_date).toLocaleDateString('de-DE') : 'Auswahl'} - {team.competition_season?.competition?.name || 'Globale Wertung'}
                        </option>
                    ))}
                </select>
            </div>
        </div>
    );
}

export function TeamOfTheDayPitch({ entries }) {
    const grouped = {
        GK: entries.filter((entry) => entry.position_code?.startsWith('GK')),
        DEF: entries.filter((entry) => entry.position_code?.startsWith('DEF')),
        MID: entries.filter((entry) => entry.position_code?.startsWith('MID')),
        FWD: entries.filter((entry) => entry.position_code?.startsWith('FWD')),
    };

    return (
        <div className="xl:col-span-2">
            <div className="relative aspect-[4/3] max-h-[600px] overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-green-900/20 shadow-2xl">
                <PitchBackdrop />
                <div className="absolute inset-0 flex flex-col justify-between p-8">
                    <PitchRow entries={grouped.FWD} />
                    <PitchRow entries={grouped.MID} />
                    <PitchRow entries={grouped.DEF} />
                    <PitchRow entries={grouped.GK} />
                </div>
            </div>
        </div>
    );
}

function PitchBackdrop() {
    return (
        <div className="pointer-events-none absolute inset-0 opacity-10">
            {[...Array(10)].map((_, index) => (
                <div key={index} className={`h-1/10 border-b border-white ${index % 2 === 0 ? 'bg-green-800/10' : ''}`} />
            ))}
            <div className="absolute inset-0 m-4 rounded-sm border-4 border-white" />
            <div className="absolute top-1/2 left-0 right-0 h-0.5 -translate-y-1/2 bg-white" />
            <div className="absolute top-1/2 left-1/2 h-40 w-40 -translate-x-1/2 -translate-y-1/2 rounded-full border-4 border-white" />
        </div>
    );
}

function PitchRow({ entries }) {
    return (
        <div className="flex h-1/4 items-center justify-around">
            {entries.map((entry) => (
                <PitchPlayer key={entry.id} entry={entry} />
            ))}
        </div>
    );
}

function PitchPlayer({ entry }) {
    if (!entry.player) {
        return null;
    }

    return (
        <div className="group flex cursor-pointer flex-col items-center" onClick={() => router.get(route('players.show', entry.player.id))}>
            <div className="relative mb-2">
                <div className="h-16 w-16 overflow-hidden rounded-full border-2 border-yellow-500 bg-[var(--bg-content)] shadow-xl transition-transform duration-300 group-hover:scale-110 md:h-20 md:w-20">
                    <img loading="lazy" src={entry.player.photo_url} className="h-full w-full object-cover" alt={entry.player.last_name} />
                </div>
                <div className="absolute -right-1 -bottom-1 rounded border border-slate-900 bg-yellow-500 px-1.5 py-0.5 text-xs font-black text-slate-900 shadow-lg">
                    {entry.player.overall}
                </div>
            </div>
            <div className="rounded-full border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/90 px-3 py-1 shadow-md backdrop-blur-sm">
                <PlayerLink
                    id={entry.player.id}
                    name={entry.player.last_name}
                    className="whitespace-nowrap text-[10px] font-black uppercase tracking-tighter text-white hover:text-yellow-500"
                />
            </div>
        </div>
    );
}

export function TeamOfTheDayDetails({ entries }) {
    return (
        <div className="space-y-6">
            <div className="overflow-hidden rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]">
                <div className="flex items-center justify-between border-b border-[var(--border-pillar)] bg-[var(--bg-content)]/50 p-4">
                    <h4 className="flex items-center text-xs font-bold uppercase tracking-widest text-white">
                        <Star className="mr-2 text-yellow-500" weight="fill" size={14} />
                        Details
                    </h4>
                    <span className="rounded bg-slate-700 px-2 py-0.5 text-[10px] font-black uppercase text-slate-300">
                        {entries.length} Spieler
                    </span>
                </div>
                <div className="divide-y divide-slate-800">
                    {entries.map((entry) => (
                        <div
                            key={entry.id}
                            className="group flex cursor-pointer items-center justify-between p-4 transition-colors hover:bg-[var(--bg-content)]/30"
                            onClick={() => router.get(route('players.show', entry.player.id))}
                        >
                            <div className="flex items-center space-x-3">
                                <div className="h-10 w-10 overflow-hidden rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-content)]">
                                    <img loading="lazy" src={entry.player?.photo_url} className="h-full w-full object-cover" alt={entry.player?.last_name} />
                                </div>
                                <div>
                                    <p className="text-sm font-medium text-white transition-colors group-hover:text-yellow-500">
                                        <PlayerLink id={entry.player?.id} name={entry.player?.last_name} className="text-white hover:text-yellow-500" />
                                    </p>
                                    <p className="text-[10px] font-bold uppercase tracking-tighter text-[var(--text-muted)]">
                                        {entry.player?.position} - <ClubLink id={entry.player?.club?.id} name={entry.player?.club?.name} className="text-[var(--text-muted)] hover:text-yellow-500" />
                                    </p>
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="text-sm font-black text-yellow-500 italic">{entry.player?.overall}</div>
                                <div className="text-[10px] font-bold uppercase text-[var(--text-muted)]">OVR</div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>

            <div className="rounded-xl border border-yellow-600/20 bg-yellow-600/5 p-6">
                <h5 className="mb-2 flex items-center text-sm font-bold text-yellow-500">
                    <Calendar className="mr-2" weight="fill" size={16} />
                    Wie wird gewaehlt?
                </h5>
                <p className="text-xs leading-relaxed text-[var(--text-muted)]">
                    Das Team der Woche wird anhand von Match-Ratings, Toren, Assists und weiteren Leistungsdaten automatisch berechnet.
                </p>
            </div>
        </div>
    );
}
