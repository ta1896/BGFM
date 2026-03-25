import React, { Suspense, lazy } from 'react';
import { Link, router } from '@inertiajs/react';
import { 
    Selection, ChartBar, ShieldCheck, Broadcast, TrendUp, Lightning, Smiley, Info, Heartbeat, 
    IdentificationCard, FileText, Calendar, Money, Crown, UserFocus, Warning, Heart, 
    CheckCircle, XCircle, Gear, ArrowLeft, Binoculars, ChatCircleText, Camera, 
    ClockCounterClockwise, FloppyDisk, IdentificationBadge, SoccerBall, Target, Trophy, 
    UsersThree, FirstAidKit, ArrowsLeftRight, Flag, Ruler, Footprints, TShirt 
} from '@phosphor-icons/react';
import { POSITIONS, POSITION_COORDS } from '@/constants/positions';
const PlayerRadarChart = lazy(() => import('@/Pages/Players/components/PlayerRadarChart'));
import PerformanceTab from '@/Pages/Players/components/PerformanceTab';
export { PerformanceTab };


const moduleActionIconMap = {
    binoculars: Binoculars,
    firstAidKit: FirstAidKit,
    broadcast: Broadcast,
};

const moduleActionAccentMap = {
    emerald: 'border-emerald-500/20 bg-emerald-500/8 text-emerald-200',
    rose: 'border-rose-500/20 bg-rose-500/8 text-rose-200',
    amber: 'border-amber-500/20 bg-amber-500/8 text-amber-200',
    cyan: 'border-cyan-500/20 bg-cyan-500/8 text-cyan-200',
    slate: 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 text-slate-200',
};

export const PlayerShowHeader = React.memo(function PlayerShowHeader({ player, isOwner, activeTab, onTabChange }) {
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
                                    fetchpriority="high"
                                    src={player.photo_url}
                                    alt={player.full_name}
                                    className="h-full w-full rounded-full object-cover mix-blend-luminosity transition-all duration-500 hover:mix-blend-normal"
                                />
                                {player.shirt_number && (
                                    <div className="absolute -bottom-1 -right-1 flex h-12 w-12 items-center justify-center rounded-full border-4 border-[#0c1222] bg-white text-black shadow-2xl">
                                        <span className="text-lg font-black italic tracking-tighter">
                                            #{player.shirt_number}
                                        </span>
                                    </div>
                                )}
                                {isOwner && (
                                    <div className="absolute -top-2 -right-2 flex h-10 w-10 items-center justify-center rounded-full border-4 border-[#0c1222] bg-amber-500 text-black shadow-xl">
                                        <Crown size={20} weight="fill" />
                                    </div>
                                )}
                            </div>
                        </div>

                        <div className="flex-1 text-center lg:text-left">
                                {player.tm_profile_url && (
                                    <a
                                        href={player.tm_profile_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-1.5 rounded-lg border border-red-500/20 bg-red-500/10 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-red-400 transition-all hover:bg-red-500/20 hover:scale-105 active:scale-95"
                                    >
                                        <SoccerBall size={12} weight="fill" />
                                        Transfermarkt
                                    </a>
                                )}
                                {player.sofa_profile_url && (
                                    <a
                                        href={player.sofa_profile_url}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="flex items-center gap-1.5 rounded-lg border border-blue-500/20 bg-blue-500/10 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-blue-400 transition-all hover:bg-blue-500/20 hover:scale-105 active:scale-95"
                                    >
                                        <Broadcast size={12} weight="fill" />
                                        Sofascore
                                    </a>
                                )}
                             <h1 className="mb-4 text-5xl font-black uppercase tracking-tighter text-white italic md:text-7xl">
                                {player.first_name} <span className="text-amber-500">{player.last_name}</span>
                            </h1>

                            <div className="mb-6 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 lg:justify-start border-y border-white/5 py-4">
                                <div className="flex items-center gap-3">
                                    {player.nationality_code ? (
                                        <img 
                                            src={`https://flagcdn.com/w40/${player.nationality_code}.png`} 
                                            className="h-4 w-6 rounded-sm object-cover shadow-sm ring-1 ring-white/10" 
                                            alt={player.nationality} 
                                        />
                                    ) : (
                                        <Flag className="text-amber-500" size={14} />
                                    )}
                                    <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nationalität</span>
                                    <span className="text-sm font-bold text-white uppercase italic">{player.nationality || 'Unbekannt'}</span>
                                </div>
                                
                                <div className="flex items-center gap-2">
                                    <Calendar className="text-amber-500" size={14} />
                                    <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Geburtstag</span>
                                    <span className="text-sm font-bold text-white uppercase italic">{player.birthday || 'Unbekannt'} ({player.age})</span>
                                </div>

                                <div className="flex items-center gap-2">
                                    <Ruler className="text-amber-500" size={14} />
                                    <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Größe</span>
                                    <span className="text-sm font-bold text-white uppercase italic">{player.height ? `${player.height} cm` : 'Unbekannt'}</span>
                                </div>

                                <div className="flex items-center gap-2 border-l border-white/5 pl-8 lg:border-none lg:pl-0">
                                    <Footprints className="text-amber-500" size={14} />
                                    <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Fuß</span>
                                    <span className="text-sm font-bold text-white uppercase italic">
                                        {player.preferred_foot === 'right' ? 'Rechts' : player.preferred_foot === 'left' ? 'Links' : player.preferred_foot === 'both' ? 'Beidfüßig' : 'Unbekannt'}
                                    </span>
                                </div>

                                <div className="flex items-center gap-2">
                                    <TShirt className="text-amber-500" size={14} />
                                    <span className="text-[10px] font-black uppercase tracking-widest text-slate-400">Nummer</span>
                                    <span className="text-sm font-bold text-white uppercase italic">#{player.shirt_number || '-'}</span>
                                </div>
                            </div>

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
                        ['performance', 'Leistung', Target],
                        ['career', 'Karriere', Trophy],
                        ['contract', 'Vertrag & Dynamik', IdentificationBadge],
                        ['matches', 'Spiele', SoccerBall],
                        ['transfers', 'Transfers', ArrowsLeftRight],
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
});

const StatRing = React.memo(function StatRing({ value, max = 99, label, color = 'emerald' }) {
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
});

const ResultCircle = React.memo(function ResultCircle({ result }) {
    const colors = {
        W: 'bg-emerald-500',
        D: 'bg-slate-500',
        L: 'bg-rose-500',
        '?': 'bg-slate-700',
    };
    return (
        <div className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-black text-white shadow-lg ${colors[result] || colors['?']}`}>
            {result}
        </div>
    );
});

const StatCircle = React.memo(function StatCircle({ value }) {
    if (!value || value === 0) return <span className="text-slate-600 font-bold">-</span>;
    return (
        <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white text-[10px] font-black text-black shadow-md border border-slate-200">
            {value}
        </div>
    );
});

const RatingBadge = React.memo(function RatingBadge({ rating }) {
    let colorClass = 'bg-slate-800 text-slate-400';
    if (rating >= 9.5) colorClass = 'bg-[#3b82f6] text-white'; // Blue for 10
    else if (rating >= 8.5) colorClass = 'bg-[#4f46e5] text-white'; // Indigo
    else if (rating >= 7.0) colorClass = 'bg-[#22c55e] text-white'; // Emerald/Green
    else if (rating >= 6.0) colorClass = 'bg-[#f59e0b] text-white'; // Amber
    else if (rating > 0) colorClass = 'bg-[#ef4444] text-white'; // Rose

    return (
        <div className={`flex h-7 w-10 shrink-0 items-center justify-center rounded-md text-[11px] font-black italic shadow-lg ${colorClass}`}>
            {rating > 0 ? rating.toFixed(1) : '-'}
        </div>
    );
});

const TabButton = React.memo(function TabButton({ active, onClick, icon: Icon, children }) {
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
});

export const PlayerOverviewTab = React.memo(function PlayerOverviewTab({ player, squadDynamics, modulePlayerActions = [], onModuleAction }) {
    const isGK = player.position === 'TW' || player.position === 'GK';

    const stats = isGK ? [
        { label: 'Aerial', value: player.attr_attacking || 50, icon: Target, color: 'text-rose-400', gradient: 'from-rose-400/60' },
        { label: 'Ball Distr.', value: player.attr_technical || 50, icon: ChartBar, color: 'text-cyan-400', gradient: 'from-cyan-400/60' },
        { label: 'Tactical', value: player.attr_tactical || 50, icon: Selection, color: 'text-indigo-400', gradient: 'from-indigo-400/60' },
        { label: 'Saves', value: player.attr_defending || 50, icon: ShieldCheck, color: 'text-emerald-400', gradient: 'from-emerald-400/60' },
        { label: 'Anticip.', value: player.attr_creativity || 50, icon: Broadcast, color: 'text-amber-400', gradient: 'from-amber-400/60' },
        { label: 'Marktwert-Stärke', value: player.attr_market || 50, icon: TrendUp, color: 'text-purple-400', gradient: 'from-purple-400/60' },
    ] : [
        { label: 'Attacking', value: player.attr_attacking || 50, icon: Target, color: 'text-rose-400', gradient: 'from-rose-400/60' },
        { label: 'Technical', value: player.technical || 50, icon: ChartBar, color: 'text-cyan-400', gradient: 'from-cyan-400/60' },
        { label: 'Tactical', value: player.attr_tactical || 50, icon: Selection, color: 'text-indigo-400', gradient: 'from-indigo-400/60' },
        { label: 'Defending', value: player.attr_defending || 50, icon: ShieldCheck, color: 'text-emerald-400', gradient: 'from-emerald-400/60' },
        { label: 'Creativity', value: player.attr_creativity || 50, icon: Broadcast, color: 'text-amber-400', gradient: 'from-amber-400/60' },
        { label: 'Marktwert-Stärke', value: player.attr_market || 50, icon: TrendUp, color: 'text-purple-400', gradient: 'from-purple-400/60' },
    ];

    const handleSyncSofascore = () => {
        router.post(route('players.sync-sofascore', player.id), {}, { preserveScroll: true });
    };

    return (
        <div className="grid gap-8 xl:grid-cols-3">
            {/* Column 1: Attributes & Profile */}
            <div className="sim-card p-8 bg-black/40 shadow-2xl relative overflow-hidden">
                <div className="absolute top-0 right-0 w-32 h-32 bg-cyan-500/5 blur-3xl rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div className="relative z-10">
                    <div className="mb-8 flex items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                            <div className="p-2 rounded-xl bg-cyan-400/10 border border-cyan-400/20 shadow-lg">
                                <ChartBar size={20} weight="duotone" className="text-cyan-400" />
                            </div>
                            <div>
                                <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Profil</h3>
                                <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Physis & Technik</p>
                            </div>
                        </div>
                        {player.sofascore_id && (
                            <button
                                onClick={handleSyncSofascore}
                                className="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 transition-all border border-cyan-400/10"
                                title="Sync Sofascore"
                            >
                                <Lightning size={16} weight="duotone" />
                            </button>
                        )}
                    </div>

                    <div className="space-y-6">
                        <div className="flex items-center justify-center py-6 px-4 rounded-3xl border border-white/[0.03] bg-black/20 relative">
                             <div className="absolute top-4 left-4 text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] opacity-50">Radar</div>
                             <Suspense fallback={<div className="h-[300px] w-full animate-pulse rounded-2xl bg-white/[0.03]" />}>
                                 <PlayerRadarChart stats={stats.filter(s => s.label !== 'Marktwert-Stärke')} />
                             </Suspense>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            {stats.map((stat) => (
                                <div key={stat.label} className="bg-white/[0.02] p-3 rounded-2xl border border-white/[0.03] group hover:bg-white/[0.04] transition-all">
                                    <div className="flex items-center justify-between mb-2">
                                        <div className="flex items-center gap-2">
                                            <stat.icon size={12} className={stat.color} weight="bold" />
                                            <span className="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{stat.label}</span>
                                        </div>
                                        <span className="text-xs font-black text-white italic">{stat.value}</span>
                                    </div>
                                    <div className="h-1 overflow-hidden rounded-full bg-black/40">
                                        <div className={`h-full rounded-full bg-gradient-to-r ${stat.gradient} to-transparent transition-all duration-700 ease-out`} style={{ width: `${stat.value}%` }} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>

            {/* Column 2: Tactical Positioning & Bio */}
            <div className="sim-card p-8 bg-black/40 shadow-2xl overflow-hidden relative">
                <div className="absolute top-0 right-0 w-64 h-64 bg-indigo-500/5 blur-3xl rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div className="relative z-10 h-full flex flex-col">
                    <div className="mb-10 flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 shadow-lg">
                                <Selection size={20} weight="duotone" className="text-indigo-400" />
                            </div>
                            <div>
                                <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Taktik</h3>
                                <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Rollen & Position</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-2 rounded-2xl border border-white/[0.05] bg-white/[0.02] px-3 py-1.5 shadow-xl backdrop-blur-md">
                            <span className="text-xs font-black uppercase tracking-tighter text-white">{player.display_position}</span>
                            <div className="h-2 w-2 rounded-full bg-rose-500 animate-pulse"></div>
                        </div>
                    </div>

                    <div className="flex-1 flex flex-col gap-10">
                         <div className="flex flex-col items-center justify-center flex-1">
                            <PlayerPositionPitch player={player} />
                        </div>
                        <div className="mt-auto">
                            <PlayerAttributesBio player={player} />
                        </div>
                    </div>
                </div>
            </div>

            {/* Column 3: Condition, Contract & Dynamics */}
            <div className="space-y-8">
                {/* Condition */}
                <div className="sim-card p-6 bg-black/40 shadow-xl border-l border-emerald-500/20">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="p-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                            <Smiley size={18} weight="duotone" className="text-emerald-400" />
                        </div>
                        <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Zustand</h4>
                    </div>
                    <div className="space-y-4">
                        <ProgressBar label="Glück" value={player.happiness} positive={player.happiness >= 55} compact />
                        <ProgressBar label="Sharpness" value={player.sharpness} positive={player.sharpness >= 60} compact />
                        <ProgressBar label="Belastung" value={player.fatigue} positive={player.fatigue <= 45} compact />
                    </div>
                </div>

                {/* Contract Summary */}
                <div className="sim-card p-6 bg-black/40 shadow-xl border-l border-indigo-500/20">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="p-1.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20">
                            <Info size={18} weight="duotone" className="text-indigo-400" />
                        </div>
                        <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Vertrag</h4>
                    </div>
                    <div className="space-y-3">
                        <CompactInfoRow label="Gehalt" value={new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(player.salary || 0)} />
                        <CompactInfoRow label="Marktwert" value={player.market_value_formatted} />
                        <CompactInfoRow label="Kaderrolle" value={player.squad_role} />
                        <CompactInfoRow label="Medizin" value={player.medical_status} />
                    </div>
                </div>

                {/* Team Dynamics */}
                <div className="sim-card p-6 bg-black/40 shadow-xl border-l border-rose-500/20">
                    <div className="mb-6 flex items-center gap-3">
                        <div className="p-1.5 rounded-lg bg-rose-500/10 border border-rose-500/20">
                            <Heartbeat size={18} weight="duotone" className="text-rose-400" />
                        </div>
                        <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Dynamik</h4>
                    </div>
                    <div className="space-y-3">
                        <CompactInfoRow label="Verletzungsrisiko" value={`${player.injury_risk}%`} />
                        <CompactInfoRow label="Promise-Druck" value={`${player.promise_pressure}%`} />
                        <CompactInfoRow label="Moral-Status" value={player.last_morale_reason || 'Neutral'} />
                    </div>
                </div>
            </div>
        </div>
    );
});

export const PlayerContractTab = React.memo(function PlayerContractTab({ player, modulePlayerActions = [], onModuleAction }) {
    return (
        <div className="grid gap-8 lg:grid-cols-2">
            <div className="sim-card bg-black/20 p-8 shadow-2xl overflow-hidden relative">
                <div className="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 blur-3xl rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div className="mb-8 flex items-center gap-4 relative z-10">
                    <div className="p-3 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 shadow-lg">
                        <Info size={24} weight="duotone" className="text-indigo-400" />
                    </div>
                    <div>
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Vertragsdetails</h3>
                        <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Konditionen & Status</p>
                    </div>
                </div>
                <div className="space-y-4 relative z-10">
                    <InfoRow label="Gehalt" value={new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(player.salary || 0)} />
                    <InfoRow label="Marktwert" value={player.market_value_formatted} />
                    <InfoRow label="Kaderrolle" value={player.squad_role} />
                    <InfoRow label="Hierarchie" value={player.leadership_level} />
                    <InfoRow label="Erwartete Spielzeit" value={`${player.expected_playtime}%`} />
                    <InfoRow label="Medizin" value={player.medical_status} />
                </div>
            </div>

            <div className="sim-card bg-black/20 p-8 shadow-2xl overflow-hidden relative">
                <div className="absolute top-0 right-0 w-32 h-32 bg-rose-500/5 blur-3xl rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div className="mb-8 flex items-center gap-4 relative z-10">
                    <div className="p-3 rounded-2xl bg-rose-500/10 border border-rose-500/20 shadow-lg">
                        <Heartbeat size={24} weight="duotone" className="text-rose-400" />
                    </div>
                    <div>
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Team-Dynamik</h3>
                        <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Belastung & Moral</p>
                    </div>
                </div>
                <div className="space-y-4 relative z-10">
                    <InfoRow label="Verletzungsrisiko" value={`${player.injury_risk}%`} />
                    <InfoRow label="Promise-Druck" value={`${player.promise_pressure}%`} />
                    <InfoRow label="Grund" value={player.last_morale_reason || '-'} />
                    {player.injury && <InfoRow label="Aktuelle Verletzung" value={`${player.injury.type} bis ${player.injury.expected_return || '?'}`} />}
                </div>

                {modulePlayerActions.length > 0 && (
                    <div className="mt-8 pt-8 border-t border-white/5">
                        <ModuleActionPanel actions={modulePlayerActions} onAction={onModuleAction} compact />
                    </div>
                )}
            </div>
        </div>
    );
});

const ProgressBar = React.memo(function ProgressBar({ label, value, positive, compact = false }) {
    return (
        <div className={compact ? "mb-4 last:mb-0" : "mb-8 last:mb-0"}>
            <div className={compact ? "mb-2 flex justify-between px-1" : "mb-4 flex justify-between px-1"}>
                <span className="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
                <span className={`text-[10px] font-black italic ${positive ? 'text-emerald-400' : 'text-amber-400'}`}>{value}%</span>
            </div>
            <div className={compact ? "h-3 overflow-hidden rounded-lg border border-white/[0.05] bg-black/20 p-0.5" : "h-6 overflow-hidden rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-1 shadow-inner"}>
                <div className={`h-full rounded-md bg-gradient-to-r ${positive ? 'from-emerald-600 to-emerald-400' : 'from-amber-600 to-amber-400'} transition-all duration-700 ease-out`} style={{ width: `${value}%` }} />
            </div>
        </div>
    );
});

const CompactInfoRow = React.memo(function CompactInfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-white/[0.03] py-2 last:border-0 grow">
            <span className="text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
            <span className="text-[11px] font-black text-white italic truncate ml-4 translate-y-[1px]">{value}</span>
        </div>
    );
});

const PlayerPositionPitch = React.memo(function PlayerPositionPitch({ player }) {
    const mainPos = player.position;
    const secondPos = player.position_second;
    const thirdPos = player.position_third;

    const renderMarker = (pos, type) => {
        const coords = POSITION_COORDS[pos];
        if (!coords) return null;

        const isMain = type === 'main';
        const color = isMain ? 'bg-rose-500' : 'bg-slate-700/80';
        const zIndex = isMain ? 'z-20' : 'z-10';
        const scale = isMain ? 'scale-110' : 'scale-90 opacity-60';

        return (
            <div
                key={`${pos}-${type}`}
                className={`absolute flex h-7 w-7 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full text-[9px] font-black text-white shadow-xl ring-2 ring-black/40 ${color} ${zIndex} ${scale} transition-all duration-500 hover:scale-125`}
                style={{ left: `${coords.x}%`, top: `${coords.y}%` }}
                title={pos}
            >
                {pos}
            </div>
        );
    };

    return (
        <div className="relative aspect-[2/3] w-full max-w-[240px] overflow-hidden rounded-2xl border border-emerald-500/20 bg-emerald-900/10 shadow-inner group">
            <svg viewBox="0 0 100 150" className="absolute inset-0 h-full w-full opacity-30">
                <rect x="0" y="0" width="100" height="150" fill="#14532d" />
                <path d="M 0 75 H 100" stroke="white" strokeWidth="0.5" fill="none" />
                <circle cx="50" cy="75" r="15" stroke="white" strokeWidth="0.5" fill="none" />
                <circle cx="50" cy="75" r="0.5" fill="white" />
                <rect x="25" y="0" width="50" height="18" stroke="white" strokeWidth="0.5" fill="none" />
                <rect x="35" y="0" width="30" height="6" stroke="white" strokeWidth="0.5" fill="none" />
                <rect x="25" y="132" width="50" height="18" stroke="white" strokeWidth="0.5" fill="none" />
                <rect x="35" y="144" width="30" height="6" stroke="white" strokeWidth="0.5" fill="none" />
                <path d="M 40 18 A 12 12 0 0 0 60 18" stroke="white" strokeWidth="0.5" fill="none" />
                <path d="M 40 132 A 12 12 0 0 1 60 132" stroke="white" strokeWidth="0.5" fill="none" />
            </svg>
            <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-black/20 pointer-events-none"></div>

            <div className="relative h-full w-full">
                {thirdPos && renderMarker(thirdPos, 'third')}
                {secondPos && renderMarker(secondPos, 'second')}
                {mainPos && renderMarker(mainPos, 'main')}
            </div>
        </div>
    );
});

const PlayerAttributesBio = React.memo(function PlayerAttributesBio({ player }) {
    const strengths = [];
    const weaknesses = [];

    const isGK = player.position === 'TW' || player.position_main === 'TW';

    if (isGK) {
        // Torhüter-spezifische Stärken
        if (player.overall >= 70) strengths.push('Sicherer Rückhalt');
        if (player.attr_technical >= 75) strengths.push('Starke Reflexe');
        if (player.attr_tactical >= 75) strengths.push('Antizipationsstark');
        if (player.attr_defending >= 75) strengths.push('Sicher auf der Linie');
        if (player.attr_creativity >= 70) strengths.push('Modernes Torwartspiel');
        if (player.player_style?.includes('Torwart')) strengths.push(player.player_style);
    } else {
        // Standard Feldspieler-Stärken
        if (player.attr_attacking >= 75) strengths.push('Torgefährlich');
        if (player.technical >= 78) strengths.push('Exzellente Technik');
        if (player.attr_tactical >= 78) strengths.push('Spielintelligence');
        if (player.attr_defending >= 75) strengths.push('Zweikampfstark');
        if (player.attr_creativity >= 75) strengths.push('Kreativer Ideengeber');
        if (player.player_style?.includes('Regisseur')) strengths.push('Hervorragender Regisseur');
        if (player.player_style?.includes('Abräumer')) strengths.push('Defensiv-Anker');
        if (player.player_style?.includes('Knipser')) strengths.push('Eiskalter Abschluss');
        if (player.stamina >= 85) strengths.push('Enormes Laufpensum');

        // Vielseitigkeit & Positions-Backup Analyse
        if (player.position_second || player.position_third) {
            strengths.push('Vielseitiger Spieler');
            
            const backupPositions = [player.position_second, player.position_third].filter(Boolean);
            const isDefensiveBackup = backupPositions.some(p => [POSITIONS.IV, POSITIONS.LV, POSITIONS.RV].includes(p));
            const isOffensiveBackup = backupPositions.some(p => [POSITIONS.MS, POSITIONS.LF, POSITIONS.RF, POSITIONS.HS].includes(p));
            const isMidfieldBackup = backupPositions.some(p => [POSITIONS.ZM, POSITIONS.OM, POSITIONS.DM, POSITIONS.RM, POSITIONS.LM].includes(p));

            if (isDefensiveBackup && ![POSITIONS.IV, POSITIONS.TW].includes(player.position)) strengths.push('Defensiv-Backup');
            if (isOffensiveBackup && ![POSITIONS.MS].includes(player.position)) strengths.push('Offensiv-Option');
            if (isMidfieldBackup && ![POSITIONS.ZM, POSITIONS.OM, POSITIONS.DM].includes(player.position)) strengths.push('Mittelfeld-Allrounder');
}
}

    if (strengths.length === 0) strengths.push(isGK ? 'Zuverlässiger Torwart' : 'Vielseitiger Allrounder');

    if (player.attr_defending < 40 && !player.position?.includes('TW')) weaknesses.push('Ausbaufähige Defensive');
    if (player.attr_attacking < 40 && (player.position === 'MS' || player.position === 'ST')) weaknesses.push('Wenig Tordrang');
    if (player.attr_tactical < 45) weaknesses.push('Taktische Defizite');
    if (player.pace < 50) weaknesses.push('Mangelndes Tempo');

    if (weaknesses.length === 0) weaknesses.push('Keine herausragenden Schwächen');

    return (
        <div className="flex flex-col justify-center gap-8 h-full">
            <div>
                <h4 className="mb-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-400">
                    <div className="h-1.5 w-1.5 rounded-full bg-emerald-400"></div>
                    Stärken
                </h4>
                <div className="flex flex-col gap-2">
                    {strengths.map(s => (
                        <span key={s} className="text-lg font-black tracking-tight text-white italic">{s}</span>
                    ))}
                </div>
            </div>

            <div>
                <h4 className="mb-4 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-rose-400">
                    <div className="h-1.5 w-1.5 rounded-full bg-rose-400"></div>
                    Schwächen
                </h4>
                <div className="flex flex-col gap-2">
                    {weaknesses.map(w => (
                        <span key={w} className="text-lg font-black tracking-tight text-slate-400 italic">{w}</span>
                    ))}
                </div>
            </div>
        </div>
    );
});

const InfoRow = React.memo(function InfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-[var(--border-pillar)] py-2">
            <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</span>
            <span className="text-xs font-black text-white">{value}</span>
        </div>
    );
});

export const PlayerCareerTab = React.memo(function PlayerCareerTab({ careerStats }) {
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
});

export const PlayerMatchesTab = React.memo(function PlayerMatchesTab({ player, recentMatches }) {
    if (!recentMatches || recentMatches.length === 0) {
        return (
            <div className="sim-card border border-dashed border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-20 text-center">
                <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                <p className="text-sm font-bold uppercase tracking-widest text-[var(--text-muted)] italic">Keine aktuellen Spieldaten erfasst</p>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {/* Desktop View: Table */}
            <div className="hidden md:block sim-card overflow-hidden p-0 border-[var(--border-muted)] bg-black/40">
                <div className="overflow-x-auto">
                    <table className="w-full text-left">
                        <tbody className="divide-y divide-white/5">
                            {recentMatches.map((stat, index) => (
                                <tr key={`${stat.match?.home_club_id}-${stat.match?.away_club_id}-${index}`} className="group transition-colors hover:bg-white/[0.03]">
                                    <td className="py-4 pl-8 pr-4 w-24">
                                        <div className="flex flex-col gap-1">
                                            <span className="text-[10px] font-black text-slate-400 opacity-60">14:00</span>
                                            <span className="text-[11px] font-black text-white italic tracking-tighter">{stat.match?.kickoff_date_formatted}</span>
                                        </div>
                                    </td>

                                    <td className="py-4 px-4 w-12 text-center">
                                        <div className="flex flex-col items-center gap-1">
                                            <div className="h-6 w-6">
                                                {stat.match?.competition_season?.competition?.logo_url ? (
                                                    <img src={stat.match.competition_season.competition.logo_url} className="h-full w-full object-contain mix-blend-luminosity brightness-200" alt="comp" />
                                                ) : (
                                                    <Flag size={20} className="text-slate-600" />
                                                )}
                                            </div>
                                            <span className="text-[8px] font-black text-slate-500 uppercase tracking-widest">Endst.</span>
                                        </div>
                                    </td>

                                    <td className="py-4 px-6 flex-1 min-w-[200px]">
                                        <div className="flex flex-col gap-2">
                                            <div className="flex items-center justify-between gap-4">
                                                <div className="flex items-center gap-3">
                                                    <img src={stat.match?.home_club?.logo_url} className="h-5 w-5 object-contain" alt="H" />
                                                    <span className={`text-sm font-black italic tracking-tight ${stat.match?.home_club_id === player.club_id ? 'text-white' : 'text-slate-400'}`}>
                                                        {stat.match?.home_club?.short_name}
                                                    </span>
                                                </div>
                                                <span className="text-sm font-black text-white italic">{stat.match?.home_score}</span>
                                            </div>
                                            <div className="flex items-center justify-between gap-4">
                                                <div className="flex items-center gap-3">
                                                    <img src={stat.match?.away_club?.logo_url} className="h-5 w-5 object-contain" alt="A" />
                                                    <span className={`text-sm font-black italic tracking-tight ${stat.match?.away_club_id === player.club_id ? 'text-white' : 'text-slate-400'}`}>
                                                        {stat.match?.away_club?.short_name}
                                                    </span>
                                                </div>
                                                <span className="text-sm font-black text-white italic">{stat.match?.away_score}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td className="py-4 px-6 w-16 text-center">
                                        <div className="flex justify-center">
                                            <ResultCircle result={stat.result} />
                                        </div>
                                    </td>

                                    <td className="py-4 px-6 w-20 text-center">
                                        <span className="text-sm font-black text-white italic tracking-tighter">{stat.minutes_played}'</span>
                                    </td>

                                    <td className="py-4 px-4 w-12 text-center">
                                        <div className="flex justify-center">
                                            <StatCircle value={stat.goals} />
                                        </div>
                                    </td>

                                    <td className="py-4 px-4 w-12 text-center">
                                        <div className="flex justify-center">
                                            <StatCircle value={stat.assists} />
                                        </div>
                                    </td>

                                    <td className="py-4 px-4 w-12 text-center">
                                        <div className="flex justify-center">
                                            <StatCircle value={stat.yellow_cards} />
                                        </div>
                                    </td>

                                    <td className="py-4 px-4 w-12 text-center">
                                        <div className="flex justify-center">
                                            <StatCircle value={stat.red_cards} />
                                        </div>
                                    </td>

                                    <td className="py-4 pl-4 pr-8 w-24">
                                        <div className="flex justify-end">
                                            <RatingBadge rating={stat.rating} />
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Mobile View: Cards */}
            <div className="md:hidden space-y-3">
                {recentMatches.map((stat, index) => (
                    <div key={index} className="sim-card border-[var(--border-muted)] bg-black/40 p-4">
                        <div className="flex justify-between items-center mb-4">
                            <div className="flex items-center gap-3">
                                <div className="h-6 w-6">
                                    {stat.match?.competition_season?.competition?.logo_url ? (
                                        <img src={stat.match.competition_season.competition.logo_url} className="h-full w-full object-contain mix-blend-luminosity brightness-200" alt="comp" />
                                    ) : (
                                        <Flag size={20} className="text-slate-600" />
                                    )}
                                </div>
                                <span className="text-[11px] font-black text-white italic tracking-tighter">{stat.match?.kickoff_date_formatted}</span>
                            </div>
                            <RatingBadge rating={stat.rating} />
                        </div>

                        <div className="space-y-2 mb-4">
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <img src={stat.match?.home_club?.logo_url} className="h-4 w-4 object-contain" alt="H" />
                                    <span className={`text-xs font-black italic ${stat.match?.home_club_id === player.club_id ? 'text-white' : 'text-slate-400'}`}>
                                        {stat.match?.home_club?.short_name}
                                    </span>
                                </div>
                                <span className="text-xs font-black text-white italic">{stat.match?.home_score}</span>
                            </div>
                            <div className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <img src={stat.match?.away_club?.logo_url} className="h-4 w-4 object-contain" alt="A" />
                                    <span className={`text-xs font-black italic ${stat.match?.away_club_id === player.club_id ? 'text-white' : 'text-slate-400'}`}>
                                        {stat.match?.away_club?.short_name}
                                    </span>
                                </div>
                                <span className="text-xs font-black text-white italic">{stat.match?.away_score}</span>
                            </div>
                        </div>

                        <div className="flex items-center justify-between pt-3 border-t border-white/5">
                            <div className="flex items-center gap-3">
                                <ResultCircle result={stat.result} />
                                <span className="text-xs font-black text-white italic">{stat.minutes_played}'</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <StatCircle value={stat.goals} />
                                <StatCircle value={stat.assists} />
                                <StatCircle value={stat.yellow_cards} />
                                <StatCircle value={stat.red_cards} />
                            </div>
                        </div>
                    </div>
                ))}
            </div>
        </div>
    );
});

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

export const PlayerHistoryTab = React.memo(function PlayerHistoryTab({ squadDynamics, playerConversationsEnabled = false }) {
    return (
        <div className="grid gap-8 xl:grid-cols-[1.35fr_0.95fr]">
            <div className="sim-card p-8">
                <div className="mb-6 flex items-center gap-4">
                    <ChatCircleText size={24} weight="duotone" className="text-amber-400" />
                    <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Manager Timeline</h3>
                </div>
                <div className="space-y-4">
                    {squadDynamics?.manager_decisions?.length ? squadDynamics.manager_decisions.map((decision, index) => (
                        <div key={`${decision.kind}-${decision.created_at}-${index}`} className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50 p-5">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="text-xs font-black uppercase tracking-wider text-white">{decision.title}</p>
                                    <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{decision.created_at}</p>
                                </div>
                                <div className="flex flex-wrap justify-end gap-2">
                                    <span className={`rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-widest ${decisionAccentClasses(decision.accent)}`}>
                                        {decision.impact_label}
                                    </span>
                                    <span className={`rounded-full px-2.5 py-1 text-[9px] font-black uppercase tracking-widest ${decisionAccentClasses(decision.evaluation?.accent || 'slate')}`}>
                                        {decision.evaluation?.label || 'Neutral'}
                                    </span>
                                </div>
                            </div>
                            <p className="mt-4 text-sm leading-relaxed text-slate-300">{decision.summary}</p>
                        </div>
                    )) : (
                        <p className="text-sm font-medium text-[var(--text-muted)]">Noch keine Manager-Entscheidungen dokumentiert.</p>
                    )}
                </div>
            </div>

            <div className="space-y-8">
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
                                    <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-widest ${promise.status === 'broken' ? 'bg-rose-500/10 text-rose-300' : promise.status === 'at_risk' ? 'bg-amber-500/10 text-amber-300' : 'bg-emerald-500/10 text-emerald-300'}`}>{promise.status}</span>
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
            </div>

            {playerConversationsEnabled && (
                <div className="xl:col-span-2 sim-card p-8">
                    <div className="mb-6 flex items-center gap-4">
                        <ChatCircleText size={24} weight="duotone" className="text-amber-400" />
                        <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Gespraechsverlauf</h3>
                    </div>
                    <div className="space-y-3">
                        {squadDynamics?.conversations?.length ? squadDynamics.conversations.map((conversation, index) => (
                            <div key={`${conversation.topic}-${conversation.created_at}-${index}`} className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50 p-4">
                                <div className="flex items-center justify-between gap-3">
                                    <div>
                                        <span className="block text-xs font-black uppercase tracking-wider text-white">{conversation.topic_label}</span>
                                        <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{conversation.approach_label}</span>
                                    </div>
                                    <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-widest ${
                                        conversation.outcome === 'breakthrough'
                                            ? 'bg-emerald-500/10 text-emerald-300'
                                            : conversation.outcome === 'positive'
                                              ? 'bg-cyan-500/10 text-cyan-300'
                                              : conversation.outcome === 'steady'
                                                ? 'bg-slate-500/10 text-slate-300'
                                                : 'bg-rose-500/10 text-rose-300'
                                    }`}>
                                        {conversation.outcome}
                                    </span>
                                </div>
                                <p className="mt-3 text-xs font-medium leading-relaxed text-slate-300">{conversation.summary}</p>
                                <p className="mt-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{conversation.player_response}</p>
                                <div className="mt-3 flex items-center justify-between text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    <span>{conversation.created_at}</span>
                                    <span className={conversation.happiness_delta >= 0 ? 'text-emerald-400' : 'text-rose-400'}>
                                        {conversation.happiness_delta >= 0 ? '+' : ''}{conversation.happiness_delta} Mood
                                    </span>
                                </div>
                            </div>
                        )) : (
                            <p className="text-sm font-medium text-[var(--text-muted)]">Noch keine Gespraeche dokumentiert.</p>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
});

function decisionAccentClasses(accent) {
    return {
        emerald: 'bg-emerald-500/10 text-emerald-300',
        cyan: 'bg-cyan-500/10 text-cyan-300',
        amber: 'bg-amber-500/10 text-amber-300',
        rose: 'bg-rose-500/10 text-rose-300',
        fuchsia: 'bg-fuchsia-500/10 text-fuchsia-300',
        slate: 'bg-slate-500/10 text-slate-300',
    }[accent] || 'bg-slate-500/10 text-slate-300';
}

export const PlayerCustomizeTab = React.memo(function PlayerCustomizeTab({
    isOwner,
    data,
    setData,
    positions,
    processing,
    onSubmit,
    promiseForm,
    onPromiseSubmit,
    conversationForm,
    onConversationSubmit,
    player,
    squadDynamics,
    playerConversationsEnabled = false,
    modulePlayerActions = [],
    onModuleAction,
}) {
    if (!isOwner) {
        return null;
    }

    return (
        <div className="mx-auto grid max-w-6xl gap-8 xl:grid-cols-[1.25fr_0.9fr_0.9fr]">
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

            <div className="space-y-8">
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

                {modulePlayerActions.length > 0 && (
                    <ModuleActionPanel actions={modulePlayerActions} onAction={onModuleAction} compact />
                )}
            </div>

            {playerConversationsEnabled && (
                <div className="sim-card border-[var(--border-muted)] bg-[#0c1222]/80 p-8 backdrop-blur-xl">
                    <div className="mb-8 flex items-center gap-4 border-b border-[var(--border-pillar)] pb-5">
                        <ChatCircleText size={28} weight="duotone" className="text-cyan-400" />
                        <div>
                            <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Spielergespraech</h3>
                            <p className="text-[10px] font-medium uppercase tracking-widest text-[var(--text-muted)]">Direkter Eingriff in Stimmung und Rollenakzeptanz</p>
                        </div>
                    </div>

                    <div className="mb-6 grid grid-cols-3 gap-3 text-center">
                        <QuickBadge label="Mood" value={`${player.happiness}%`} tone={player.happiness >= 60 ? 'good' : 'warn'} />
                        <QuickBadge label="Load" value={`${player.fatigue}%`} tone={player.fatigue <= 45 ? 'good' : 'warn'} />
                        <QuickBadge
                            label="Promise"
                            value={squadDynamics?.promises?.[0] ? `${squadDynamics.promises[0].fulfilled_ratio}%` : '-'}
                            tone={squadDynamics?.promises?.[0]?.status === 'at_risk' || squadDynamics?.promises?.[0]?.status === 'broken' ? 'bad' : 'good'}
                        />
                    </div>

                    <form onSubmit={onConversationSubmit} className="space-y-5">
                        <Field label="Thema">
                            <select value={conversationForm.data.topic} onChange={(event) => conversationForm.setData('topic', event.target.value)} className="sim-select w-full uppercase text-xs">
                                <option value="morale">Stimmung</option>
                                <option value="role">Rolle</option>
                                <option value="playtime">Spielzeit</option>
                                <option value="load">Belastung</option>
                            </select>
                        </Field>

                        <Field label="Ansatz">
                            <select value={conversationForm.data.approach} onChange={(event) => conversationForm.setData('approach', event.target.value)} className="sim-select w-full uppercase text-xs">
                                <option value="supportive">Supportiv</option>
                                <option value="honest">Offen</option>
                                <option value="protective">Vorsichtig</option>
                                <option value="demanding">Hart</option>
                            </select>
                        </Field>

                        <Field label="Manager-Notiz">
                            <textarea
                                value={conversationForm.data.manager_message}
                                onChange={(event) => conversationForm.setData('manager_message', event.target.value)}
                                className="sim-input-indigo min-h-32 w-full"
                                placeholder="Optionaler Gespraechsvermerk fuer die Akte"
                            />
                        </Field>

                        <button type="submit" disabled={conversationForm.processing} className="sim-btn-primary flex w-full items-center justify-center gap-3 px-6 py-4">
                            <ChatCircleText size={18} weight="bold" />
                            <span className="text-xs font-black uppercase tracking-widest">Gespraech fuehren</span>
                        </button>
                    </form>
                </div>
            )}
        </div>
    );
});

function QuickBadge({ label, value, tone = 'good' }) {
    const toneClasses = tone === 'bad'
        ? 'border-rose-500/20 bg-rose-500/10 text-rose-300'
        : tone === 'warn'
            ? 'border-amber-500/20 bg-amber-500/10 text-amber-300'
            : 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300';

    return (
        <div className={`rounded-2xl border px-3 py-3 ${toneClasses}`}>
            <span className="block text-[9px] font-black uppercase tracking-widest opacity-80">{label}</span>
            <span className="mt-1 block text-sm font-black text-white">{value}</span>
        </div>
    );
}

function ModuleActionPanel({ actions, onAction, compact = false }) {
    return (
        <div className="sim-card border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 p-8">
            <div className="mb-6 flex items-center gap-4">
                <Gear size={24} weight="duotone" className="text-cyan-300" />
                <div>
                    <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Module Actions</h3>
                    <p className="text-[10px] font-medium uppercase tracking-widest text-[var(--text-muted)]">Kontextaktionen aus aktiven Feature-Modulen</p>
                </div>
            </div>

            <div className={`grid gap-4 ${compact ? '' : 'sm:grid-cols-2'}`}>
                {actions.map((action) => {
                    const Icon = moduleActionIconMap[action.icon] || Gear;
                    const classes = moduleActionAccentMap[action.accent] || moduleActionAccentMap.slate;

                    return (
                        <button
                            key={action.key}
                            type="button"
                            onClick={() => onAction?.(action)}
                            className={`rounded-2xl border p-5 text-left transition hover:-translate-y-0.5 hover:border-white/20 ${classes}`}
                        >
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div className="flex items-center gap-3">
                                    <div className="rounded-xl border border-white/10 bg-black/20 p-2">
                                        <Icon size={18} weight="bold" />
                                    </div>
                                    <span className="text-sm font-black uppercase tracking-tight text-white">{action.title}</span>
                                </div>
                                <span className="rounded-full border border-white/10 px-2 py-1 text-[9px] font-black uppercase tracking-[0.18em] text-white/70">
                                    {action.method === 'post' ? 'Action' : 'Open'}
                                </span>
                            </div>
                            <p className="text-xs leading-relaxed text-slate-300">{action.description}</p>
                        </button>
                    );
                })}
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
