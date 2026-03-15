import React from 'react';
import { Link } from '@inertiajs/react';
import {
    ArrowLeft,
    Camera,
    ChartBar,
    ClockCounterClockwise,
    Crown,
    FloppyDisk,
    Heartbeat,
    IdentificationBadge,
    Info,
    Lightning,
    Selection,
    ShieldCheck,
    Smiley,
    SoccerBall,
    Target,
    TrendUp,
    Trophy,
    UsersThree,
    Warning,
} from '@phosphor-icons/react';

export function PlayerShowHeader({ player, isOwner, activeTab, onTabChange }) {
    return (
        <>
            <div className="flex items-center justify-between">
                <Link
                    href={route('players.index')}
                    className="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] transition-colors hover:text-amber-500"
                >
                    <ArrowLeft size={14} weight="bold" />
                    Zurueck zum Kader
                </Link>

                <div className="flex items-center gap-3">
                    {isOwner && (
                        <div className="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-[9px] font-black uppercase tracking-widest text-cyan-400">
                            Dein Spieler
                        </div>
                    )}
                    <span className="rounded-full border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-3 py-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)] italic">
                        ID: #{player.id}
                    </span>
                </div>
            </div>

            <div className="sim-card relative overflow-hidden border-[var(--border-muted)] bg-gradient-to-br from-[#0c1222] to-[#161e32] p-0 shadow-2xl">
                <div className="pointer-events-none absolute top-0 right-0 h-full w-1/2 bg-gradient-to-l from-cyan-500/5 to-transparent" />
                <div className="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-indigo-600/10 blur-[100px]" />

                <div className="relative z-10 p-8 md:p-12">
                    <div className="flex flex-col items-center gap-12 lg:flex-row lg:items-end">
                        <div className="group relative">
                            <div className="absolute inset-0 rounded-full bg-cyan-500/20 opacity-0 blur-2xl transition-opacity group-hover:opacity-100" />
                            <div className="relative h-48 w-48 rounded-full border border-[var(--border-muted)] bg-gradient-to-br from-slate-800 to-slate-950 p-2 shadow-2xl md:h-56 md:w-56">
                                <img
                                    loading="lazy"
                                    src={player.photo_url}
                                    alt={player.full_name}
                                    className="h-full w-full rounded-full object-cover mix-blend-luminosity transition-all duration-500 hover:mix-blend-normal"
                                />
                                {isOwner && (
                                    <div className="absolute -top-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full border-4 border-[#0c1222] bg-amber-500 text-black shadow-xl">
                                        <Crown size={20} weight="fill" />
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="flex-1 text-center lg:text-left">
                            <div className="mb-4 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                                <span className="rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-amber-500 italic">
                                    {player.position}
                                </span>
                                <span className="rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    {player.age} Jahre
                                </span>
                            </div>
                            <h1 className="mb-6 text-5xl font-black uppercase tracking-tighter text-white italic md:text-7xl">
                                {player.first_name} <span className="text-amber-500">{player.last_name}</span>
                            </h1>

                            <div className="flex flex-wrap items-center justify-center gap-8 lg:justify-start">
                                {player.club ? (
                                    <Link href={route('clubs.show', player.club.id)} className="group flex items-center gap-4">
                                        <div className="h-12 w-12 rounded-xl border border-white/10 bg-white/5 p-2 transition-all group-hover:border-cyan-500/30">
                                            <img loading="lazy" src={player.club.logo_url} className="h-full w-full object-contain" alt={player.club.name} />
                                        </div>
                                        <div>
                                            <p className="mb-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Aktueller Verein</p>
                                            <p className="text-lg font-black uppercase tracking-tighter text-white italic transition-colors group-hover:text-cyan-400">{player.club.name}</p>
                                        </div>
                                    </Link>
                                ) : (
                                    <div className="flex items-center gap-4 opacity-50">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] font-black italic text-slate-600">?</div>
                                        <div>
                                            <p className="mb-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Status</p>
                                            <p className="text-lg font-black uppercase tracking-tighter text-[var(--text-muted)] italic">Vereinslos</p>
                                        </div>
                                    </div>
                                )}

                                <div className="hidden h-10 w-px bg-[var(--bg-content)] md:block" />

                                <div>
                                    <p className="mb-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Marktwert</p>
                                    <p className="text-3xl font-black tracking-tighter text-white italic">{player.market_value_formatted}</p>
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-8 self-center rounded-3xl border border-white/5 bg-[var(--bg-pillar)]/50 px-8 py-6 backdrop-blur-md">
                            <StatRing value={player.overall} label="Staerke" color="emerald" />
                            <StatRing value={player.potential} label="Potenzial" color="amber" />
                        </div>
                    </div>
                </div>

                <nav className="no-scrollbar flex items-center overflow-x-auto border-t border-[var(--border-muted)] bg-black/20 px-8">
                    {[
                        ['overview', 'Uebersicht', ChartBar],
                        ['career', 'Karriere', Trophy],
                        ['matches', 'Spiele', SoccerBall],
                        ['history', 'Historie', ClockCounterClockwise],
                        ...(isOwner ? [['customize', 'Anpassen', IdentificationBadge]] : []),
                    ].map(([key, label, Icon]) => (
                        <TabButton key={key} active={activeTab === key} onClick={() => onTabChange(key)} icon={Icon}>
                            {label}
                        </TabButton>
                    ))}
                </nav>
            </div>
        </>
    );
}

function StatRing({ value, max = 99, label, color = 'emerald' }) {
    const radius = 24;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (value / max) * circumference;
    const colors = {
        emerald: 'text-emerald-500',
        amber: 'text-amber-500',
    };

    return (
        <div className="flex flex-col items-center">
            <div className="relative flex h-16 w-16 items-center justify-center">
                <svg className="h-full w-full -rotate-90 transform">
                    <circle cx="32" cy="32" r={radius} stroke="currentColor" strokeWidth="4" fill="transparent" className="text-slate-800" />
                    <circle cx="32" cy="32" r={radius} stroke="currentColor" strokeWidth="4" fill="transparent" strokeDasharray={circumference} strokeDashoffset={offset} className={colors[color]} />
                </svg>
                <span className="absolute text-sm font-black text-white italic">{value}</span>
            </div>
            <span className="mt-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
        </div>
    );
}

function TabButton({ active, onClick, icon: Icon, children }) {
    return (
        <button
            onClick={onClick}
            className={`flex items-center gap-2.5 border-b-2 px-6 py-4 text-xs font-black uppercase tracking-widest transition-all ${
                active ? 'border-amber-500 bg-amber-500/5 text-amber-500' : 'border-transparent text-[var(--text-muted)] hover:bg-white/5 hover:text-slate-300'
            }`}
        >
            <Icon size={18} weight={active ? 'fill' : 'bold'} />
            {children}
        </button>
    );
}

export function PlayerOverviewTab({ player, squadDynamics }) {
    const stats = [
        { label: 'Tempo', value: player.pace, icon: Lightning, color: 'text-amber-500', gradient: 'from-amber-500/60' },
        { label: 'Schuss', value: player.shooting, icon: Target, color: 'text-rose-400', gradient: 'from-rose-400/60' },
        { label: 'Passen', value: player.passing, icon: ChartBar, color: 'text-amber-600', gradient: 'from-amber-600/60' },
        { label: 'Dribbling', value: player.dribbling || 70, icon: SoccerBall, color: 'text-amber-500', gradient: 'from-amber-500/60' },
        { label: 'Defensive', value: player.defending, icon: ShieldCheck, color: 'text-emerald-400', gradient: 'from-emerald-400/60' },
        { label: 'Physis', value: player.physical, icon: TrendUp, color: 'text-purple-400', gradient: 'from-purple-400/60' },
    ];

    return (
        <div className="grid gap-8 lg:grid-cols-3">
            <div className="space-y-8 lg:col-span-2">
                <div className="sim-card p-8">
                    <div className="mb-8 flex items-center gap-4">
                        <ChartBar size={24} weight="duotone" className="text-cyan-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Physische und technische Profile</h3>
                    </div>
                    <div className="grid gap-8 sm:grid-cols-2 md:grid-cols-3">
                        {stats.map((stat) => (
                            <div key={stat.label} className="space-y-3">
                                <div className="flex items-center justify-between px-1">
                                    <div className="flex items-center gap-2">
                                        <stat.icon size={14} className={stat.color} weight="bold" />
                                        <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{stat.label}</span>
                                    </div>
                                    <span className="text-xs font-black text-white italic">{stat.value}</span>
                                </div>
                                <div className="h-2 overflow-hidden rounded-full border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-0.5">
                                    <div className={`h-full rounded-full bg-gradient-to-r ${stat.gradient} to-transparent transition-all duration-700 ease-out`} style={{ width: `${stat.value}%` }} />
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="sim-card p-8">
                    <div className="mb-8 flex items-center gap-4">
                        <Selection size={24} weight="duotone" className="text-indigo-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Positionen</h3>
                    </div>
                    <div className="flex flex-wrap gap-4">
                        {[
                            ['Hauptposition', player.position, 'bg-amber-500/10 border-amber-500/20 text-amber-500'],
                            ['Nebenposition', player.position_second, 'bg-[var(--bg-pillar)] border-[var(--border-pillar)] text-slate-300'],
                            ['Alternativ', player.position_third, 'bg-[var(--bg-pillar)] border-[var(--border-pillar)] text-slate-300'],
                        ].filter(([, value]) => value).map(([label, value, classes]) => (
                            <div key={label} className={`min-w-[200px] flex-1 rounded-3xl border p-6 text-center ${classes}`}>
                                <span className="mb-2 block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
                                <span className="text-2xl font-black text-white">{value}</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <div className="space-y-8">
                <div className="sim-card p-8">
                    <div className="mb-8 flex items-center gap-4">
                        <Smiley size={24} weight="duotone" className="text-emerald-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Kondition</h3>
                    </div>
                    <ProgressBar label="Fitness" value={player.stamina} positive={player.stamina > 80} />
                    <ProgressBar label="Moral" value={player.morale} positive />
                    <ProgressBar label="Zufriedenheit" value={player.happiness} positive={player.happiness >= 55} />
                    <ProgressBar label="Sharpness" value={player.sharpness} positive={player.sharpness >= 60} />
                    <ProgressBar label="Belastung" value={player.fatigue} positive={player.fatigue <= 45} />
                </div>

                <div className="sim-card border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 p-8">
                    <div className="mb-6 flex items-center gap-4">
                        <Info size={24} weight="duotone" className="text-indigo-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Vertrag</h3>
                    </div>
                    <div className="space-y-4">
                        <InfoRow label="Gehalt" value={new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(player.salary || 0)} />
                        <InfoRow label="Marktwert" value={player.market_value_formatted} />
                        <InfoRow label="Kaderrolle" value={player.squad_role} />
                        <InfoRow label="Hierarchie" value={player.leadership_level} />
                        <InfoRow label="Erwartete Spielzeit" value={`${player.expected_playtime}%`} />
                        <InfoRow label="Medizin" value={player.medical_status} />
                    </div>
                </div>

                <div className="sim-card border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 p-8">
                    <div className="mb-6 flex items-center gap-4">
                        <Heartbeat size={24} weight="duotone" className="text-rose-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Dynamik</h3>
                    </div>
                    <div className="space-y-4">
                        <InfoRow label="Verletzungsrisiko" value={`${player.injury_risk}%`} />
                        <InfoRow label="Promise-Druck" value={`${player.promise_pressure}%`} />
                        <InfoRow label="Grund" value={player.last_morale_reason || '-'} />
                        {player.injury && <InfoRow label="Aktuelle Verletzung" value={`${player.injury.type} bis ${player.injury.expected_return || '?'}`} />}
                    </div>
                </div>
            </div>
        </div>
    );
}

function ProgressBar({ label, value, positive }) {
    return (
        <div className="mb-8 last:mb-0">
            <div className="mb-4 flex justify-between px-1">
                <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
                <span className={`text-xs font-black italic ${positive ? 'text-emerald-400' : 'text-amber-400'}`}>{value}%</span>
            </div>
            <div className="h-6 overflow-hidden rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-1 shadow-inner">
                <div className={`h-full rounded-lg bg-gradient-to-r ${positive ? 'from-emerald-600 to-emerald-400' : 'from-amber-600 to-amber-400'} transition-all duration-700 ease-out`} style={{ width: `${value}%` }} />
            </div>
        </div>
    );
}

function InfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-[var(--border-pillar)] py-2">
            <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
            <span className="text-xs font-black text-white">{value}</span>
        </div>
    );
}

export function PlayerCareerTab({ careerStats }) {
    return (
        <div className="sim-card overflow-hidden p-0">
            <div className="overflow-x-auto">
                <table className="w-full text-left">
                    <thead>
                        <tr className="bg-[var(--bg-pillar)]/50">
                            {['Saison', 'Wettbewerb', 'Spiele', 'Tore', 'Vorl.', 'Gelb/Rot', 'Rating'].map((label) => (
                                <th key={label} className="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</th>
                            ))}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800">
                        {careerStats.length > 0 ? careerStats.map((stat, index) => (
                            <tr key={`${stat.season?.name || 'season'}-${index}`} className="group transition-colors hover:bg-white/5">
                                <td className="px-8 py-5 text-xs font-black text-white italic">{stat.season?.name || '-'}</td>
                                <td className="px-8 py-5 text-xs font-bold uppercase tracking-tight text-slate-300">{stat.competition_context === 'league' ? 'LIGA' : stat.competition_context === 'cup_national' ? 'POKAL' : 'INTERNATIONAL'}</td>
                                <td className="px-8 py-5 text-center text-xs font-black text-white">{stat.appearances}</td>
                                <td className="px-8 py-5 text-center text-xs font-bold text-emerald-400">{stat.goals}</td>
                                <td className="px-8 py-5 text-center text-xs font-bold text-amber-500">{stat.assists}</td>
                                <td className="px-8 py-5 text-center text-xs font-bold"><span className="text-amber-500">{stat.yellow_cards}</span><span className="mx-2 text-slate-600">/</span><span className="text-rose-500">{stat.red_cards}</span></td>
                                <td className="px-8 py-5 text-right">
                                    <span className={`rounded px-2 py-1 text-xs font-black italic ${stat.average_rating >= 7 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-[var(--bg-content)] text-[var(--text-muted)]'}`}>
                                        {stat.average_rating > 0 ? parseFloat(stat.average_rating).toFixed(2) : '-'}
                                    </span>
                                </td>
                            </tr>
                        )) : (
                            <tr>
                                <td colSpan="7" className="px-8 py-20 text-center text-sm italic text-[var(--text-muted)]">Keine Karrieredaten gefunden.</td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export function PlayerMatchesTab({ player, recentMatches }) {
    return (
        <div className="grid gap-6">
            {recentMatches.length > 0 ? recentMatches.map((stat, index) => (
                <div key={`${stat.match?.home_score}-${stat.match?.away_score}-${index}`} className="sim-card group flex flex-wrap items-center gap-8 p-6 transition-all hover:border-cyan-500/30 lg:flex-nowrap">
                    <div className="w-32 shrink-0 border-r border-[var(--border-pillar)] pr-6">
                        <span className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{stat.match?.kickoff_date_formatted}</span>
                        <span className="text-[9px] font-black uppercase tracking-[0.2em] text-indigo-400">{stat.match?.competition_season?.competition?.code || 'LG'}</span>
                    </div>
                    <div className="min-w-[300px] flex-1 items-center justify-center gap-12 lg:flex lg:justify-start">
                        <ClubSide side={stat.match?.home_club} active={stat.match?.home_club_id === player.club_id} align="end" />
                        <div className="min-w-[60px] rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-1.5 text-center text-lg font-black text-white italic">
                            {stat.match?.home_score} : {stat.match?.away_score}
                        </div>
                        <ClubSide side={stat.match?.away_club} active={stat.match?.away_club_id === player.club_id} />
                    </div>
                    <div className="flex shrink-0 items-center gap-8 lg:border-l lg:border-[var(--border-pillar)] lg:pl-8">
                        <MiniStat label="Einsatz" value={`${stat.minutes_played}'`} />
                        <MiniStat label="S/A" value={<><span className="text-emerald-400">{stat.goals}</span><span className="mx-1 text-slate-700">/</span><span className="text-amber-500">{stat.assists}</span></>} />
                        <MiniStat label="Rating" value={<span className={`rounded px-2 py-0.5 text-xs font-black italic ${stat.rating >= 7 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-[var(--bg-content)] text-[var(--text-muted)]'}`}>{parseFloat(stat.rating || 0).toFixed(1)}</span>} />
                    </div>
                </div>
            )) : (
                <div className="sim-card border border-dashed border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-20 text-center">
                    <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                    <p className="text-sm font-bold uppercase tracking-widest text-[var(--text-muted)] italic">Keine aktuellen Spieldaten erfasst</p>
                </div>
            )}
        </div>
    );
}

function ClubSide({ side, active, align = 'start' }) {
    return (
        <div className={`flex flex-1 items-center gap-4 ${align === 'end' ? 'justify-end' : ''}`}>
            {align !== 'end' && side?.logo_url && <img loading="lazy" src={side.logo_url} className="h-8 w-8 object-contain opacity-80" alt={side?.short_name} />}
            <span className={`line-clamp-1 text-xs font-black uppercase ${active ? 'text-white' : 'text-[var(--text-muted)]'} ${align === 'end' ? 'text-right' : ''}`}>{side?.short_name}</span>
            {align === 'end' && side?.logo_url && <img loading="lazy" src={side.logo_url} className="h-8 w-8 object-contain opacity-80" alt={side?.short_name} />}
        </div>
    );
}

function MiniStat({ label, value }) {
    return (
        <div className="text-center">
            <p className="mb-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
            <div className="text-xs font-black text-white italic">{value}</div>
        </div>
    );
}

export function PlayerHistoryTab({ squadDynamics }) {
    return (
        <div className="grid gap-8 lg:grid-cols-2">
            <div className="sim-card p-8">
                <div className="mb-6 flex items-center gap-4">
                    <ClockCounterClockwise size={24} weight="duotone" className="text-slate-300" />
                    <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Belastungsverlauf</h3>
                </div>
                <div className="space-y-3">
                    {squadDynamics?.recovery?.length ? squadDynamics.recovery.map((entry) => (
                        <div key={entry.day} className="flex items-center justify-between rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50 px-4 py-3">
                            <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{entry.day}</span>
                            <div className="flex items-center gap-4 text-xs font-black">
                                <span className="text-amber-400">Fatigue {entry.fatigue_after}</span>
                                <span className="text-emerald-400">Sharp {entry.sharpness_after}</span>
                                <span className="text-rose-400">Risk {entry.injury_risk}%</span>
                            </div>
                        </div>
                    )) : (
                        <p className="text-sm font-medium text-[var(--text-muted)]">Noch keine Recovery-Logs vorhanden.</p>
                    )}
                </div>
            </div>

            <div className="sim-card p-8">
                <div className="mb-6 flex items-center gap-4">
                    <UsersThree size={24} weight="duotone" className="text-cyan-400" />
                    <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Spielzeitversprechen</h3>
                </div>
                <div className="space-y-3">
                    {squadDynamics?.promises?.length ? squadDynamics.promises.map((promise, index) => (
                        <div key={`${promise.promise_type}-${index}`} className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50 p-4">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-black uppercase tracking-wider text-white">{promise.promise_type}</span>
                                <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-widest ${promise.status === 'at_risk' ? 'bg-rose-500/10 text-rose-400' : 'bg-emerald-500/10 text-emerald-400'}`}>{promise.status}</span>
                            </div>
                            <div className="mt-3 flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                <span>Ziel {promise.expected_minutes_share}%</span>
                                <span>Erfuellt {promise.fulfilled_ratio}%</span>
                                <span>{promise.deadline_at || 'offen'}</span>
                            </div>
                        </div>
                    )) : (
                        <p className="text-sm font-medium text-[var(--text-muted)]">Keine aktiven Versprechen hinterlegt.</p>
                    )}
                </div>
            </div>
        </div>
    );
}

export function PlayerCustomizeTab({ isOwner, data, setData, positions, processing, onSubmit, promiseForm, onPromiseSubmit }) {
    if (!isOwner) {
        return null;
    }

    return (
        <div className="mx-auto grid max-w-5xl gap-8 lg:grid-cols-[1.4fr_1fr]">
            <div className="sim-card border-[var(--border-muted)] bg-[#0c1222]/80 p-10 backdrop-blur-xl">
            <div className="mb-10 flex items-center gap-4 border-b border-[var(--border-pillar)] pb-6">
                <IdentificationBadge size={32} weight="duotone" className="text-cyan-400" />
                <div>
                    <h3 className="text-2xl font-black uppercase tracking-tighter text-white italic">Spielerprofil anpassen</h3>
                    <p className="text-xs font-medium uppercase tracking-widest text-[var(--text-muted)]">Aenderungen werden nach Pruefung uebernommen</p>
                </div>
            </div>

            <form onSubmit={onSubmit} className="space-y-8">
                <div className="grid gap-8 md:grid-cols-2">
                    <div className="space-y-6">
                        <Field label="Marktwert (EUR)">
                            <input type="number" value={data.market_value} onChange={(event) => setData('market_value', event.target.value)} className="sim-input-indigo w-full text-white font-mono" />
                        </Field>
                        <Field label="Sortitoutsi Bild-ID oder URL">
                            <div className="relative">
                                <Camera size={18} className="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                                <input type="text" placeholder="https://sortitoutsi.net/player/..." value={data.photo_url} onChange={(event) => setData('photo_url', event.target.value)} className="sim-input w-full pl-12 text-xs" />
                            </div>
                        </Field>
                    </div>

                    <div className="space-y-6">
                        <Field label="Hauptposition">
                            <select value={data.position} onChange={(event) => setData('position', event.target.value)} className="sim-select w-full uppercase font-black">
                                {positions.map((position) => <option key={position} value={position}>{position}</option>)}
                            </select>
                        </Field>
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="Nebenposition 1">
                                <select value={data.position_second} onChange={(event) => setData('position_second', event.target.value)} className="sim-select w-full text-xs uppercase">
                                    <option value="">- KEINE -</option>
                                    {positions.map((position) => <option key={position} value={position}>{position}</option>)}
                                </select>
                            </Field>
                            <Field label="Nebenposition 2">
                                <select value={data.position_third} onChange={(event) => setData('position_third', event.target.value)} className="sim-select w-full text-xs uppercase">
                                    <option value="">- KEINE -</option>
                                    {positions.map((position) => <option key={position} value={position}>{position}</option>)}
                                </select>
                            </Field>
                        </div>
                    </div>
                </div>

                <div className="flex items-center justify-between gap-6 border-t border-[var(--border-pillar)] pt-8">
                    <div className="flex flex-1 items-center gap-3 text-amber-500/60">
                        <Warning size={16} weight="bold" />
                        <p className="max-w-xs text-[9px] font-black uppercase tracking-widest leading-relaxed">Achtung: Falschdaten koennen zu Konsequenzen fuer deinen Account fuehren.</p>
                    </div>
                    <button type="submit" disabled={processing} className="sim-btn-primary group flex items-center gap-3 px-10 py-4">
                        <FloppyDisk size={20} weight="bold" className="transition-transform group-hover:rotate-12" />
                        <span className="text-xs font-black uppercase tracking-widest">Antrag speichern</span>
                    </button>
                </div>
            </form>
            </div>

            <div className="sim-card border-[var(--border-muted)] bg-[#0c1222]/80 p-8 backdrop-blur-xl">
                <div className="mb-8 flex items-center gap-4 border-b border-[var(--border-pillar)] pb-5">
                    <UsersThree size={28} weight="duotone" className="text-amber-400" />
                    <div>
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Spielzeitversprechen</h3>
                        <p className="text-[10px] font-medium uppercase tracking-widest text-[var(--text-muted)]">Aktive Erwartung fuer diesen Spieler</p>
                    </div>
                </div>

                <form onSubmit={onPromiseSubmit} className="space-y-5">
                    <Field label="Versprechen">
                        <select value={promiseForm.data.promise_type} onChange={(event) => promiseForm.setData('promise_type', event.target.value)} className="sim-select w-full uppercase text-xs">
                            <option value="starter">Stammspieler</option>
                            <option value="regular_rotation">Regelmaessige Rotation</option>
                            <option value="impact_sub">Joker-Rolle</option>
                            <option value="youth_path">Entwicklungspfad</option>
                        </select>
                    </Field>

                    <Field label="Erwartete Minutenquote">
                        <input
                            type="number"
                            min="5"
                            max="100"
                            value={promiseForm.data.expected_minutes_share}
                            onChange={(event) => promiseForm.setData('expected_minutes_share', event.target.value)}
                            className="sim-input-indigo w-full"
                        />
                    </Field>

                    <Field label="Deadline">
                        <input
                            type="date"
                            value={promiseForm.data.deadline_at}
                            onChange={(event) => promiseForm.setData('deadline_at', event.target.value)}
                            className="sim-input-indigo w-full"
                        />
                    </Field>

                    <Field label="Notiz">
                        <textarea
                            value={promiseForm.data.notes}
                            onChange={(event) => promiseForm.setData('notes', event.target.value)}
                            className="sim-input-indigo min-h-28 w-full"
                            placeholder="z. B. Einsaetze ueber die naechsten 6 Wochen"
                        />
                    </Field>

                    <button type="submit" disabled={promiseForm.processing} className="sim-btn-primary flex w-full items-center justify-center gap-3 px-6 py-4">
                        <FloppyDisk size={18} weight="bold" />
                        <span className="text-xs font-black uppercase tracking-widest">Versprechen setzen</span>
                    </button>
                </form>
            </div>
        </div>
    );
}

function Field({ label, children }) {
    return (
        <div>
            <label className="mb-3 block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</label>
            {children}
        </div>
    );
}
