import React, { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import {
    Calendar,
    ChartBar,
    Crown,
    GlobeHemisphereWest,
    Info,
    Shield,
    Star,
    Trophy,
    Users,
} from '@phosphor-icons/react';
import PlayerLink from '@/Components/PlayerLink';
import ClubLink from '@/Components/ClubLink';

const tabs = [
    { id: 'overview', label: 'Uebersicht' },
    { id: 'squad', label: 'Kader' },
    { id: 'stats', label: 'Statistiken' },
    { id: 'trophies', label: 'Trophaenschrank' },
    { id: 'hall_of_fame', label: 'Hall of Fame' },
];

export default function Show({
    auth,
    club,
    seasons,
    activeSeason,
    overallStats,
    seasonStats,
    players,
    trophyCabinet,
    isOwner,
    hallOfFame,
    clubRecords,
    historicalComparison,
}) {
    const [activeTab, setActiveTab] = useState('overview');
    const [activeTrophyId, setActiveTrophyId] = useState(null);

    const featuredTrophies = useMemo(() => trophyCabinet?.items?.slice(0, 8) ?? [], [trophyCabinet]);

    const handleSeasonChange = (e) => {
        router.get(route('clubs.show', club.id), { season_id: e.target.value }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex w-full flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center space-x-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-white/10 p-2.5">
                            {club.logo_url ? (
                                <img src={club.logo_url} alt="" className="max-h-full max-w-full object-contain" />
                            ) : (
                                <Shield size={48} weight="fill" className="text-white/20" />
                            )}
                        </div>
                        <div>
                            <h2 className="text-2xl font-black uppercase tracking-tight text-white">{club.name}</h2>
                            <p className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                {club.short_name} · {club.country}
                            </p>
                        </div>
                    </div>
                    {isOwner && (
                        <Link
                            href={route('clubs.edit', club.id)}
                            className="rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-content)] px-4 py-2 text-xs font-black uppercase tracking-widest text-white transition-all hover:bg-slate-700"
                        >
                            Einstellungen
                        </Link>
                    )}
                </div>
            }
        >
            <Head title={club.name} />

            <div className="py-8">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <div className="flex w-full flex-wrap items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-1">
                        {tabs.map((tab) => (
                            <button
                                key={tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                className={`rounded-lg px-4 py-2.5 text-xs font-black uppercase tracking-widest transition-all sm:px-6 ${
                                    activeTab === tab.id
                                        ? 'bg-amber-600 text-black shadow-lg shadow-amber-900/40'
                                        : 'text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {activeTab === 'overview' && (
                        <div className="grid grid-cols-1 gap-8 animate-in fade-in duration-500 lg:grid-cols-3">
                            <div className="space-y-6">
                                <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] shadow-sm">
                                    <div className="flex items-center justify-between border-b border-[var(--border-pillar)] bg-[var(--bg-content)]/50 p-4">
                                        <h4 className="flex items-center text-[10px] font-black uppercase tracking-widest text-white">
                                            <ChartBar size={14} weight="fill" className="mr-2 text-amber-500" />
                                            Saison-Zusammenfassung
                                        </h4>
                                        <select
                                            value={activeSeason?.id ?? ''}
                                            onChange={handleSeasonChange}
                                            className="cursor-pointer border-none bg-transparent py-0 pl-0 pr-6 text-[10px] font-black uppercase text-amber-500 focus:ring-0"
                                        >
                                            {seasons.map((season) => (
                                                <option key={season.id} value={season.id}>
                                                    {season.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4 p-6">
                                        <StatBox label="Spiele" value={seasonStats?.played || 0} color="amber" />
                                        <StatBox label="Punkte" value={seasonStats?.points || 0} color="green" />
                                        <StatBox label="Tore" value={seasonStats?.goals_for || 0} color="slate" />
                                        <StatBox label="Gegentore" value={seasonStats?.goals_against || 0} color="red" />
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-6 shadow-sm">
                                    <h4 className="mb-6 flex items-center text-[10px] font-black uppercase tracking-widest text-white">
                                        <Info size={14} weight="bold" className="mr-2 text-[var(--text-muted)]" />
                                        Vereinsinfos
                                    </h4>
                                    <div className="space-y-4">
                                        <InfoRow label="Stadion" value={club.stadium?.name || 'Kein Stadion'} />
                                        <InfoRow label="Kapazitaet" value={(club.stadium?.capacity || 0).toLocaleString('de-DE')} />
                                        <InfoRow label="Manager" value={club.user?.name || 'CPU'} />
                                        <InfoRow label="Prestige" value={`${club.reputation} / 99`} />
                                    </div>
                                </div>

                                <div className="overflow-hidden rounded-2xl border border-amber-500/20 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.18),_rgba(15,23,42,0.95)_58%)] shadow-lg shadow-amber-950/20">
                                    <div className="border-b border-amber-400/10 px-5 py-4">
                                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-300/70">Vereinsmuseum</p>
                                        <div className="mt-2 flex items-end justify-between gap-4">
                                            <div>
                                                <p className="text-3xl font-black text-white">{trophyCabinet?.total || 0}</p>
                                                <p className="text-xs font-bold uppercase tracking-[0.16em] text-slate-300">Grosse Trophaen</p>
                                            </div>
                                            <button
                                                onClick={() => setActiveTab('history')}
                                                className="rounded-full border border-amber-400/20 bg-black/20 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-amber-200 transition-colors hover:border-amber-300/40 hover:text-white"
                                            >
                                                Schrank oeffnen
                                            </button>
                                        </div>
                                    </div>
                                    <div className="grid grid-cols-3 gap-3 px-5 py-5">
                                        <MiniTrophyStat label="Liga" value={trophyCabinet?.by_type?.league || 0} icon="league" />
                                        <MiniTrophyStat label="Pokal" value={trophyCabinet?.by_type?.national_cup || 0} icon="national_cup" />
                                        <MiniTrophyStat label="Intl." value={trophyCabinet?.by_type?.international_cup || 0} icon="international_cup" />
                                    </div>
                                </div>
                            </div>

                            <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] shadow-sm lg:col-span-2">
                                <div className="flex items-center justify-between border-b border-[var(--border-pillar)] bg-[var(--bg-content)]/50 p-4">
                                    <h4 className="flex items-center text-[10px] font-black uppercase tracking-widest text-white">
                                        <Users size={14} weight="fill" className="mr-2 text-amber-500" />
                                        Schluesselspieler
                                    </h4>
                                    <button
                                        onClick={() => setActiveTab('squad')}
                                        className="text-[10px] font-black uppercase text-amber-500 hover:underline"
                                    >
                                        Gesamter Kader
                                    </button>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="w-full border-collapse text-left">
                                        <thead>
                                            <tr className="bg-[var(--bg-content)]/20">
                                                <th className="px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Spieler</th>
                                                <th className="px-6 py-4 text-center text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Pos</th>
                                                <th className="px-6 py-4 text-center text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Alter</th>
                                                <th className="px-6 py-4 text-center text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Staerke</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-800">
                                            {players.slice(0, 10).map((player) => (
                                                <tr
                                                    key={player.id}
                                                    className="cursor-pointer transition-colors hover:bg-[var(--bg-content)]/30 group"
                                                    onClick={() => router.get(route('players.show', player.id))}
                                                >
                                                    <td className="px-6 py-4">
                                                        <div className="flex items-center space-x-3">
                                                            <div className="h-9 w-9 overflow-hidden rounded border border-[var(--border-pillar)] bg-[var(--bg-content)]">
                                                                <img src={player.photo_url} className="h-full w-full object-cover" alt="" />
                                                            </div>
                                                            <span className="text-sm font-bold text-white transition-colors group-hover:text-amber-500">
                                                                <PlayerLink id={player.id} name={`${player.first_name} ${player.last_name}`} className="text-white hover:text-amber-500" />
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="rounded border border-[var(--border-pillar)] bg-[var(--bg-content)] px-1.5 py-0.5 text-[10px] font-black text-[var(--text-muted)]">
                                                            {player.position}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-center text-sm font-bold text-slate-300">{player.age}</td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="text-sm font-black italic text-amber-500">{player.overall}</span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                <div className="border-t border-[var(--border-pillar)] bg-[linear-gradient(180deg,rgba(20,20,28,0.45),rgba(44,24,8,0.72))] p-6">
                                    <div className="mb-4 flex items-center justify-between gap-4">
                                        <div>
                                            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-300/70">Vorgeschmack</p>
                                            <h3 className="mt-1 text-xl font-black tracking-tight text-white">Trophaenschrank</h3>
                                        </div>
                                        <button
                                            onClick={() => setActiveTab('history')}
                                            className="rounded-full border border-amber-500/20 bg-black/20 px-4 py-2 text-[10px] font-black uppercase tracking-[0.18em] text-amber-200 transition-colors hover:border-amber-300/40 hover:text-white"
                                        >
                                            Alle Titel ansehen
                                        </button>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                        {featuredTrophies.length > 0 ? (
                                            featuredTrophies.map((trophy) => (
                                                <div
                                                    key={trophy.id}
                                                    className="rounded-2xl border border-white/8 bg-black/20 p-4 text-center backdrop-blur-sm"
                                                >
                                                    <div className="mx-auto mb-3 flex h-20 w-16 items-center justify-center rounded-[1.6rem_1.6rem_1rem_1rem] border border-amber-300/20 bg-[radial-gradient(circle_at_50%_15%,rgba(255,239,196,0.95),rgba(217,165,60,0.9)_35%,rgba(78,48,16,0.96)_100%)] shadow-[0_12px_40px_rgba(120,62,5,0.45)]">
                                                        <TrophyGlyph type={trophy.type} compact />
                                                    </div>
                                                    <p className="line-clamp-2 text-xs font-black uppercase tracking-tight text-white">
                                                        {trophy.competition_short_name}
                                                    </p>
                                                    <p className="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-200/70">
                                                        {trophy.season_name}
                                                    </p>
                                                </div>
                                            ))
                                        ) : (
                                            <div className="col-span-full rounded-2xl border border-dashed border-white/10 bg-black/10 p-8 text-center">
                                                <p className="text-sm font-black uppercase tracking-[0.18em] text-slate-300">Noch keine Trophaen</p>
                                                <p className="mt-2 text-sm text-[var(--text-muted)]">Sobald der Verein Titel gewinnt, werden sie hier ausgestellt.</p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {activeTab === 'trophies' && (
                        <div className="space-y-10 animate-in fade-in duration-500">
                            {/* Hero Section: Historischer Puls */}
                            <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                                <HistoryStatCard
                                    label="Gesamt-Titel"
                                    value={trophyCabinet?.total || 0}
                                    icon={<Trophy size={20} weight="fill" />}
                                    accent="from-amber-300/25 to-amber-700/10"
                                />
                                {historicalComparison?.points && (
                                    <div className="rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(15,23,42,0.92),rgba(8,10,18,0.96))] p-5 shadow-lg">
                                        <div className="mb-4 inline-flex rounded-full bg-gradient-to-br from-green-300/20 to-green-700/10 p-3 text-green-500">
                                            <ChartBar size={20} weight="fill" />
                                        </div>
                                        <p className="text-3xl font-black text-white">{historicalComparison.points.current} / {historicalComparison.points.record}</p>
                                        <p className="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Punkte-Rekord-Vergleich</p>
                                        <div className="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-white/5">
                                            <div 
                                                className="h-full bg-green-500" 
                                                style={{ width: `${Math.min(100, (historicalComparison.points.current / historicalComparison.points.record) * 100)}%` }}
                                            />
                                        </div>
                                    </div>
                                )}
                                {historicalComparison?.goals && (
                                    <div className="rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(15,23,42,0.92),rgba(8,10,18,0.96))] p-5 shadow-lg">
                                        <div className="mb-4 inline-flex rounded-full bg-gradient-to-br from-blue-300/20 to-blue-700/10 p-3 text-blue-500">
                                            <Star size={20} weight="fill" />
                                        </div>
                                        <p className="text-3xl font-black text-white">{historicalComparison.goals.current} / {historicalComparison.goals.record}</p>
                                        <p className="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Tore-Rekord-Vergleich</p>
                                        <div className="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-white/5">
                                            <div 
                                                className="h-full bg-blue-500" 
                                                style={{ width: `${Math.min(100, (historicalComparison.goals.current / historicalComparison.goals.record) * 100)}%` }}
                                            />
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Rekord-Wand */}
                            <section>
                                <div className="mb-6 px-4 sm:px-0">
                                    <h3 className="text-2xl font-black uppercase tracking-tight text-[#fff0c5]">Rekord-Wand</h3>
                                    <p className="text-sm text-amber-100/60 uppercase tracking-widest font-bold">Historische Bestmarken</p>
                                </div>
                                <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
                                    {clubRecords.length > 0 ? (
                                        clubRecords.map((record) => (
                                            <div key={record.id} className="rounded-xl border border-white/5 bg-white/5 p-4 transition-all hover:bg-white/10">
                                                <p className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{record.label}</p>
                                                <p className="mt-1 text-2xl font-black italic text-amber-500">{record.value}</p>
                                                <p className="text-[10px] text-slate-500 font-bold uppercase mt-1">{record.achieved_at}</p>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="col-span-full rounded-2xl border border-dashed border-white/10 bg-white/5 py-12 text-center">
                                            <p className="text-sm font-bold uppercase tracking-widest text-[var(--text-muted)]">Keine Rekorde verzeichnet</p>
                                        </div>
                                    )}
                                </div>
                            </section>

                            <div className="overflow-hidden rounded-[28px] border border-[#6f4a19] bg-[linear-gradient(180deg,#3b2513_0%,#24150c_8%,#120f16_8.1%,#120f16_100%)] shadow-[0_28px_80px_rgba(0,0,0,0.45)]">
                                <div className="border-b border-[#8b642f] bg-[linear-gradient(180deg,#7d5928,#4f3519)] px-6 py-5 text-center shadow-[inset_0_1px_0_rgba(255,230,168,0.25)]">
                                    <p className="text-[10px] font-black uppercase tracking-[0.28em] text-amber-100/80">Vereinsmuseum</p>
                                    <h3 className="mt-2 text-3xl font-black uppercase tracking-tight text-[#fff0c5]">Trophaenschrank</h3>
                                </div>

                                <div className="space-y-0 p-5 sm:p-6">
                                    {[0, 1].map((shelfIndex) => {
                                        const shelfItems = trophyCabinet?.items?.slice(shelfIndex * 8, shelfIndex * 8 + 8) ?? [];

                                        return (
                                            <div key={shelfIndex} className="relative mb-6 rounded-[26px] border border-[#5a3716] bg-[radial-gradient(circle_at_20%_10%,rgba(201,124,35,0.28),transparent_30%),linear-gradient(180deg,#4a2c17,#24160d_70%,#170f0f)] px-4 py-6 shadow-[inset_0_1px_0_rgba(255,224,163,0.08)] sm:px-6">
                                                <div className="pointer-events-none absolute inset-x-4 top-0 h-3 rounded-b-[18px] bg-[linear-gradient(180deg,rgba(255,212,138,0.15),rgba(255,212,138,0))]" />
                                                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                                                    {shelfItems.length > 0 ? (
                                                        shelfItems.map((trophy) => (
                                                            <TrophyDisplay
                                                                key={trophy.id}
                                                                trophy={trophy}
                                                                active={activeTrophyId === trophy.id}
                                                                onOpen={() => setActiveTrophyId((current) => (current === trophy.id ? null : trophy.id))}
                                                            />
                                                        ))
                                                    ) : (
                                                        <div className="col-span-full rounded-2xl border border-dashed border-amber-200/10 bg-black/10 px-6 py-10 text-center">
                                                            <p className="text-xs font-black uppercase tracking-[0.18em] text-amber-100/65">Noch frei</p>
                                                            <p className="mt-2 text-sm text-slate-400">Dieses Regal wartet auf die naechste Titelmannschaft.</p>
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="pointer-events-none absolute inset-x-0 bottom-[-8px] h-5 rounded-[0_0_18px_18px] bg-[linear-gradient(180deg,#7d5329,#4c2f17)] shadow-[0_10px_28px_rgba(0,0,0,0.35)]" />
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    )}

                    {activeTab === 'hall_of_fame' && (
                        <div className="space-y-6 animate-in fade-in duration-500">
                            <div className="mb-8 flex items-end justify-between px-4 sm:px-0">
                                <div>
                                    <h3 className="text-2xl font-black uppercase tracking-tight text-[#fff0c5]">Hall of Fame</h3>
                                    <p className="text-sm text-amber-100/60 uppercase tracking-widest font-bold">Die Legenden des Vereins</p>
                                </div>
                                <Crown size={40} weight="fill" className="text-amber-500/20" />
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                {hallOfFame.length > 0 ? (
                                    hallOfFame.map((entry) => (
                                        <div key={entry.id} className="group relative overflow-hidden rounded-2xl border border-amber-500/20 bg-[radial-gradient(circle_at_top,_rgba(245,158,11,0.1),_rgba(15,23,42,0.95))] p-6 shadow-xl transition-all hover:scale-[1.02] hover:border-amber-500/40">
                                            <div className="flex items-center space-x-4">
                                                <div className="h-20 w-20 overflow-hidden rounded-lg border border-amber-500/30 bg-black/40">
                                                    <img src={entry.player.photo_url} className="h-full w-full object-cover grayscale transition-all group-hover:grayscale-0" alt="" />
                                                </div>
                                                <div>
                                                    <p className="text-[10px] font-black uppercase tracking-widest text-amber-500">{entry.legend_type_label}</p>
                                                    <h4 className="text-xl font-black text-white">
                                                        <PlayerLink id={entry.player.id} name={entry.player.name} className="text-white hover:text-amber-500" />
                                                    </h4>
                                                    <p className="text-[10px] font-bold uppercase tracking-widest text-amber-100/40">Aufgenommen {entry.inducted_at}</p>
                                                </div>
                                            </div>
                                            <div className="mt-6 border-t border-white/5 pt-4">
                                                <p className="italic text-sm text-slate-400 line-clamp-3 group-hover:line-clamp-none">
                                                    "{entry.description}"
                                                </p>
                                            </div>
                                            <div className="absolute top-0 right-0 p-4 opacity-10 transition-opacity group-hover:opacity-30">
                                                <Crown size={64} weight="fill" className="text-amber-500" />
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="col-span-full rounded-2xl border border-dashed border-white/10 bg-white/5 py-24 text-center">
                                        <Users size={64} weight="fill" className="mx-auto mb-4 text-white/10" />
                                        <p className="text-lg font-bold uppercase tracking-widest text-[var(--text-muted)]">Noch keine Legenden aufgenommen</p>
                                        <p className="mt-2 text-sm text-slate-500">Diese Galerie wartet auf praegende Persoenlichkeiten deiner Vereinsgeschichte.</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {activeTab !== 'overview' && activeTab !== 'squad' && activeTab !== 'stats' && activeTab !== 'trophies' && activeTab !== 'hall_of_fame' && (
                        <div className="flex flex-col items-center justify-center rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] py-20 text-[var(--text-muted)] animate-in fade-in transition-all">
                            <p className="mb-4 text-sm font-bold uppercase tracking-widest">Bereich in Vorbereitung</p>
                            <button onClick={() => setActiveTab('overview')} className="text-xs font-bold uppercase text-amber-500 underline">
                                Zurueck zur Uebersicht
                            </button>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function TrophyDisplay({ trophy, active, onOpen }) {
    return (
        <button
            type="button"
            onClick={onOpen}
            className="group relative flex min-h-[220px] flex-col items-center justify-end rounded-2xl border border-transparent px-2 pb-3 pt-4 text-center transition-transform duration-300 hover:-translate-y-1 hover:border-amber-200/20"
        >
            <div className="mb-3 flex h-32 w-24 items-center justify-center rounded-[2rem_2rem_1.2rem_1.2rem] border border-amber-200/15 bg-[radial-gradient(circle_at_50%_12%,rgba(255,243,210,0.98),rgba(230,183,72,0.92)_28%,rgba(102,63,18,0.98)_100%)] shadow-[0_18px_48px_rgba(108,55,8,0.5)] transition-transform duration-300 group-hover:scale-[1.04]">
                {trophy.competition_logo_url ? (
                    <img src={trophy.competition_logo_url} alt="" className="h-14 w-14 object-contain opacity-90 drop-shadow-[0_4px_8px_rgba(0,0,0,0.35)]" />
                ) : (
                    <TrophyGlyph type={trophy.type} />
                )}
            </div>

            <p className="line-clamp-2 text-xs font-black uppercase tracking-tight text-[#fff5d6]">{trophy.competition_short_name}</p>
            <p className="mt-1 text-[10px] font-bold uppercase tracking-[0.16em] text-amber-100/70">{trophy.category_label}</p>

            <div
                className={`pointer-events-none absolute left-1/2 top-3 z-20 w-[220px] -translate-x-1/2 rounded-2xl border border-amber-200/15 bg-[linear-gradient(180deg,rgba(17,24,39,0.97),rgba(10,12,22,0.98))] p-4 text-left shadow-2xl transition-all duration-200 ${
                    active ? 'translate-y-0 opacity-100' : 'translate-y-2 opacity-0 group-hover:translate-y-0 group-hover:opacity-100'
                }`}
            >
                <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-300/80">{trophy.category_label}</p>
                <h4 className="mt-2 text-sm font-black text-white">{trophy.competition_name}</h4>
                <div className="mt-3 space-y-2 text-xs text-slate-300">
                    <TooltipRow icon={<Calendar size={13} weight="bold" />} label="Saison" value={trophy.season_name} />
                    <TooltipRow icon={<Star size={13} weight="fill" />} label="Titel" value={trophy.title} />
                    <TooltipRow icon={<Info size={13} weight="bold" />} label="Verliehen" value={trophy.achieved_at || 'Kein Datum'} />
                </div>
            </div>
        </button>
    );
}

function TooltipRow({ icon, label, value }) {
    return (
        <div className="flex items-start gap-2">
            <div className="mt-0.5 text-amber-300">{icon}</div>
            <div>
                <p className="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">{label}</p>
                <p className="mt-0.5 leading-snug text-slate-200">{value}</p>
            </div>
        </div>
    );
}

function MiniTrophyStat({ label, value, icon }) {
    return (
        <div className="rounded-2xl border border-white/8 bg-black/15 p-4 text-center">
            <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full border border-amber-300/20 bg-amber-300/10">
                <TrophyGlyph type={icon} compact />
            </div>
            <p className="text-lg font-black text-white">{value}</p>
            <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-slate-300">{label}</p>
        </div>
    );
}

function HistoryStatCard({ label, value, icon, accent }) {
    return (
        <div className={`rounded-2xl border border-white/8 bg-[linear-gradient(180deg,rgba(15,23,42,0.92),rgba(8,10,18,0.96)),linear-gradient(120deg,transparent,transparent)] p-5 shadow-lg`}>
            <div className={`mb-4 inline-flex rounded-full bg-gradient-to-br ${accent} p-3 text-white`}>{icon}</div>
            <p className="text-3xl font-black text-white">{value}</p>
            <p className="mt-1 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">{label}</p>
        </div>
    );
}

function TrophyGlyph({ type, compact = false }) {
    const sizeClass = compact ? 'h-7 w-7' : 'h-10 w-10';
    const baseClass = `text-[#fff6d0] drop-shadow-[0_3px_8px_rgba(0,0,0,0.28)] ${sizeClass}`;

    if (type === 'league') {
        return <Crown weight="fill" className={baseClass} />;
    }

    if (type === 'international_cup') {
        return <GlobeHemisphereWest weight="fill" className={baseClass} />;
    }

    return <Trophy weight="fill" className={baseClass} />;
}

function StatBox({ label, value, color }) {
    const colorMap = {
        amber: 'text-amber-500',
        green: 'text-green-500',
        red: 'text-red-500',
        slate: 'text-slate-300',
    };

    return (
        <div className="rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/30 p-4">
            <p className="mb-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
            <p className={`text-2xl font-black italic ${colorMap[color] || 'text-white'}`}>{value}</p>
        </div>
    );
}

function InfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-[var(--border-muted)] py-2 last:border-0">
            <span className="text-[10px] font-bold uppercase tracking-tight text-[var(--text-muted)]">{label}</span>
            <span className="text-xs font-black text-white">{value}</span>
        </div>
    );
}
