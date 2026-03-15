import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import { 
    Calendar, Trophy, Users, ChartBar,
    ArrowRight, Bank, Smiley, SmileySad, FlagPennant, Handshake, ChatCircleText, Broadcast, UsersThree, Lightning
} from '@phosphor-icons/react';

const taskTone = {
    warning: 'border-rose-400/20 bg-rose-400/10 text-rose-200',
    info: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200',
};

const taskPriorityTone = {
    sofort: 'border-rose-400/20 bg-rose-400/10 text-rose-200',
    heute: 'border-amber-400/20 bg-amber-400/10 text-amber-200',
    beobachten: 'border-slate-400/20 bg-slate-400/10 text-slate-200',
};

const accentTone = {
    emerald: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200',
    amber: 'border-amber-400/20 bg-amber-400/10 text-amber-200',
    rose: 'border-rose-400/20 bg-rose-400/10 text-rose-200',
    cyan: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200',
    slate: 'border-slate-400/20 bg-slate-400/10 text-slate-200',
};

const StatCard = ({ label, value, subValue, icon: Icon, color = 'amber' }) => (
    <div className="bg-[var(--bg-pillar)]/40 backdrop-blur-md rounded-2xl border border-[var(--border-muted)] p-6 relative overflow-hidden group hover:border-[var(--accent-primary)]/30 transition-all shadow-xl">
        <div className={`absolute -right-6 -top-6 h-24 w-24 rounded-full bg-${color}-500/5 blur-2xl group-hover:bg-${color}-500/10 transition-colors`} />
        
        <div className="flex items-center gap-4 relative z-10">
            <div className={`h-12 w-12 rounded-xl bg-[var(--bg-content)] border border-[var(--border-pillar)] flex items-center justify-center text-${color}-400 group-hover:scale-110 transition-transform`}>
                <Icon size={24} weight="duotone" />
            </div>
            <div>
                <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] mb-0.5">{label}</p>
                <div className="flex items-baseline gap-2">
                    <span className="text-2xl font-bold text-[var(--text-main)] tracking-tight">{value}</span>
                    {subValue && <span className="text-xs font-semibold text-[var(--text-muted)]">{subValue}</span>}
                </div>
            </div>
        </div>
    </div>
);

const TimelineDay = ({ day }) => {
    const isToday = day.is_today;
    const hasMatch = day.match_count > 0;
    const hasTraining = day.training_count > 0;
    
    return (
        <div
            className={`
                relative flex flex-col justify-between rounded-2xl border p-4 transition-all hover:-translate-y-1 hover:shadow-2xl
                ${isToday ? 'border-amber-500/40 bg-amber-500/5 shadow-lg shadow-amber-500/10' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/30'}
                ${hasMatch ? 'hover:border-amber-600/40' : 'hover:border-amber-400/40'}
            `}
        >
            {isToday && (
                <div className="absolute -top-1.5 -right-1.5 h-3 w-3 rounded-full bg-amber-500 shadow-[0_0_12px_rgba(217,177,92,0.8)] z-10" />
            )}
            
            <div>
                <p className={`text-[10px] font-bold uppercase tracking-widest ${isToday ? 'text-amber-500' : 'text-[var(--text-muted)]'}`}>
                    {day.label}
                </p>
                <p className="mt-1 text-xl font-bold text-[var(--text-main)]">{day.date}</p>
            </div>

            <div className="mt-6 flex flex-col gap-2">
                {hasMatch && (
                    <div className="flex items-center gap-2 rounded-lg bg-amber-600/10 border border-amber-600/20 px-2.5 py-1.5">
                        <Calendar size={14} weight="fill" className="text-amber-600" />
                        <span className="text-[10px] font-bold uppercase text-amber-600">{day.match_count} Match</span>
                    </div>
                )}
                {hasTraining && (
                    <div className="flex items-center gap-2 rounded-lg bg-amber-500/10 border border-amber-500/20 px-2.5 py-1.5">
                        <ChartBar size={14} weight="fill" className="text-amber-500" />
                        <span className="text-[10px] font-bold uppercase text-amber-500">{day.training_count} Session</span>
                    </div>
                )}
                {!hasMatch && !hasTraining && (
                    <span className="text-[10px] font-medium text-slate-600 px-1 py-1.5">Rest Day</span>
                )}
            </div>
        </div>
    );
};

const ManagerLiveRow = ({ manager }) => (
    <Link
        href={route('manager-live.index')}
        className="group flex items-center gap-3 rounded-2xl border border-cyan-400/10 bg-[linear-gradient(135deg,rgba(17,30,48,0.85),rgba(11,22,36,0.95))] px-3 py-3 transition-all hover:-translate-y-0.5 hover:border-cyan-300/25 hover:shadow-[0_14px_30px_-18px_rgba(34,211,238,0.45)]"
    >
        <div className="relative">
            {manager.club?.logo_url ? (
                <img src={manager.club.logo_url} alt={manager.club.name} className="h-11 w-11 rounded-2xl border border-white/10 bg-white/[0.04] object-contain p-1.5" />
            ) : (
                <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04]">
                    <UsersThree size={18} className="text-[var(--text-muted)]" />
                </div>
            )}
            <span className="absolute -right-1 -top-1 h-3 w-3 rounded-full border border-[var(--bg-content)] bg-emerald-400 shadow-[0_0_10px_rgba(74,222,128,0.8)]" />
        </div>

        <div className="min-w-0 flex-1">
            <div className="flex items-start justify-between gap-3">
                <div className="min-w-0">
                    <div className="truncate text-[11px] font-black uppercase tracking-[0.08em] text-white">{manager.manager}</div>
                    <div className="truncate text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100/70">{manager.club?.name || 'Ohne Verein'}</div>
                </div>
                <div className="shrink-0 text-[9px] font-black uppercase tracking-[0.14em] text-emerald-200">
                    {manager.last_seen_label}
                </div>
            </div>
            <div className="mt-2 inline-flex max-w-full items-center gap-2 rounded-full border border-cyan-300/15 bg-cyan-300/8 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-cyan-100">
                <Broadcast size={10} weight="fill" />
                <span className="truncate">{manager.activity_label}</span>
            </div>
        </div>
    </Link>
);

const LiveMatchRow = ({ match }) => (
    <Link
        href={match?.id ? route('matches.show', match.id) : route('live-ticker.index')}
        className="group block rounded-2xl border border-emerald-400/10 bg-[linear-gradient(135deg,rgba(10,34,30,0.9),rgba(7,23,25,0.96))] px-4 py-3 transition-all hover:-translate-y-0.5 hover:border-emerald-300/25 hover:shadow-[0_14px_30px_-18px_rgba(16,185,129,0.45)]"
    >
        <div className="mb-3 flex items-center justify-between gap-3">
            <div className="inline-flex items-center gap-2 rounded-full border border-emerald-300/15 bg-emerald-300/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-emerald-100">
                <div className="h-1.5 w-1.5 rounded-full bg-emerald-300 shadow-[0_0_8px_rgba(110,231,183,0.9)]" />
                Live {match.live_minute}'
            </div>
            <div className="rounded-full border border-white/10 bg-black/20 px-2.5 py-1 text-[11px] font-black tracking-[0.1em] text-white">
                {match.home_score}:{match.away_score}
            </div>
        </div>

        <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
            <div className="min-w-0">
                <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{match.home_club?.name}</div>
            </div>
            <div className="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200/90">vs</div>
            <div className="min-w-0">
                <div className="truncate text-right text-[11px] font-black uppercase tracking-[0.06em] text-white">{match.away_club?.name}</div>
            </div>
        </div>
    </Link>
);

const formTone = {
    S: 'bg-emerald-500 text-black',
    U: 'bg-slate-100 text-black',
    N: 'bg-red-600 text-white',
};

export default function Dashboard(props) {
    const { 
        activeClub, nextMatch, nextMatchTypeLabel, 
        activeClubReadyForNextMatch, opponentReadyForNextMatch,
        clubRank, clubPoints, recentForm, recentMatchesSummary, weekDays,
        todayMatchesCount, unreadNotificationsCount,
        dashboardVariant, assistantTasks, todayFocus, clubPulseOverview, comparisonStats, quickActions,
        squadPulse, scoutingDesk, managerDecisions, liveMatches, onlineManagers
    } = props;

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <Head title="Welcome" />
                <div className="max-w-4xl mx-auto py-12 text-center">
                    <div className="bg-[var(--bg-pillar)]/40 p-12 rounded-3xl border border-[var(--border-pillar)]">
                        <Trophy size={64} weight="duotone" className="mx-auto text-amber-500 mb-6" />
                        <h1 className="text-4xl font-bold text-white mb-4">Start Your Career</h1>
                        <p className="text-[var(--text-muted)] text-lg mb-8 max-w-xl mx-auto">
                            You don't have an active club yet. Take over a team today and lead them to glory.
                        </p>
                        <Link 
                            href={route('clubs.free')}
                            className="inline-flex items-center gap-3 bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] px-8 py-4 rounded-xl font-bold text-black shadow-xl shadow-amber-900/40 hover:scale-105 transition-all uppercase tracking-widest text-sm"
                        >
                            View Available Clubs
                            <ArrowRight size={20} weight="bold" />
                        </Link>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const fanMood = Math.max(0, Math.min(100, parseInt(activeClub.fan_mood || 50)));

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />
            
            <div className="space-y-8">
                {/* Header Stats */}
                <StaggerGroup className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatCard 
                        label="Account Balance" 
                        value={new Intl.NumberFormat('de-DE').format(activeClub.budget)} 
                        subValue="€" 
                        icon={Bank} 
                        color="emerald"
                    />
                    {clubRank !== undefined ? (
                        <StatCard 
                            label="League Rank" 
                            value={clubRank ? `#${clubRank}` : 'N/A'} 
                            subValue={`${clubPoints || 0} Points`} 
                            icon={Trophy} 
                            color="amber"
                        />
                    ) : (
                        <div className="bg-[var(--bg-pillar)]/40 backdrop-blur-md rounded-2xl border border-[var(--border-muted)] p-6 shadow-xl h-[104px]">
                            <div className="flex h-full items-center">
                                <p className="text-sm font-bold text-[var(--text-muted)]">Ligadaten werden geladen</p>
                            </div>
                        </div>
                    )}
                    <StatCard 
                        label="Today's Games" 
                        value={todayMatchesCount} 
                        subValue="Total" 
                        icon={Calendar} 
                        color="amber"
                    />
                    <StatCard 
                        label="Fan Mood" 
                        value={`${fanMood}%`} 
                        subValue={fanMood > 70 ? 'Excellent' : fanMood > 40 ? 'Stable' : 'Unrest'} 
                        icon={fanMood > 50 ? Smiley : SmileySad} 
                        color={fanMood > 70 ? 'emerald' : fanMood > 40 ? 'amber' : 'rose'}
                    />
                </StaggerGroup>

                {/* Main Dashboard Section */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {/* Left Column (Main Info) */}
                    <PageReveal className="lg:col-span-8 space-y-8" delay={90}>
                        
                        <section>
                            <div className="mb-4 flex items-center justify-between">
                                <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Weekly Overview</h3>
                                 <div className="flex gap-4">
                                     <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-gray-500">
                                        <span className="w-2 h-2 rounded-full bg-amber-600" /> Match
                                     </span>
                                     <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-gray-500">
                                        <span className="w-2 h-2 rounded-full bg-amber-400" /> Training
                                     </span>
                                 </div>
                            </div>
                            <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                                {weekDays.map((day, idx) => (
                                    <TimelineDay key={idx} day={day} />
                                ))}
                            </div>
                        </section>

                        {/* Next Match Card */}
                        <section className="relative overflow-hidden rounded-3xl border border-amber-400/15 bg-[linear-gradient(145deg,rgba(26,21,13,0.96),rgba(12,18,31,0.98))] p-6 shadow-[0_30px_60px_-35px_rgba(217,177,92,0.4)]">
                            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(217,177,92,0.18),transparent_35%),radial-gradient(circle_at_bottom_right,rgba(59,130,246,0.12),transparent_30%)]" />
                            
                            <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <h3 className="text-xs font-black uppercase tracking-[0.2em] text-amber-300">Next Fixture</h3>
                                    <div className="mt-2 text-2xl font-black tracking-tight text-white">{nextMatchTypeLabel || 'Matchday'}</div>
                                </div>
                                <div className="flex flex-wrap gap-2">
                                    {['modern', 'compact', 'classic'].map((variant) => (
                                        <Link
                                            key={variant}
                                            href={`${route('dashboard')}?variant=${variant}`}
                                            className={`rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] transition-colors ${
                                                dashboardVariant === variant
                                                    ? 'border-amber-300/25 bg-amber-300/12 text-amber-100'
                                                    : 'border-white/10 bg-white/[0.03] text-[var(--text-muted)] hover:border-white/20 hover:text-white'
                                            }`}
                                        >
                                            {variant}
                                        </Link>
                                    ))}
                                </div>
                            </div>
                            
                            {nextMatch ? (
                                <div className="relative z-10 grid grid-cols-1 gap-8 lg:grid-cols-[1fr_auto_1fr] lg:items-center">                                     <div className="text-center group">
                                         <div className="mx-auto mb-4 h-20 w-20 rounded-2xl border border-white/10 bg-black/20 p-4 transition group-hover:border-amber-500/50 group-hover:shadow-[0_0_30px_-10px_rgba(217,177,92,0.4)]">
                                            <img className="h-full w-full object-contain" src={nextMatch.home_club.logo_url} alt={nextMatch.home_club.name} />
                                         </div>
                                         <p className="mb-1 text-xl font-bold uppercase tracking-tight text-[var(--text-main)]">{nextMatch.home_club.name}</p>
                                         <span className="text-[10px] font-black uppercase tracking-widest text-amber-100/60">Host</span>
                                    </div>

                                    <div className="flex flex-col items-center gap-4 text-center">
                                         <div className="rounded-full border border-amber-300/20 bg-amber-300/10 px-4 py-1.5">
                                             <span className="text-xs font-black uppercase tracking-[0.2em] text-amber-100">{nextMatch.kickoff_at_formatted}</span>
                                         </div>
                                         <span className="text-5xl font-black tracking-[0.2em] text-amber-200/30">VS</span>
                                         <div className="rounded-2xl border border-white/10 bg-black/20 px-4 py-3">
                                             <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Austragungsort</p>
                                             <p className="mt-1 text-sm font-bold text-white">{nextMatch.stadium_name || 'Neutral Venue'}</p>
                                         </div>
                                    </div>

                                     <div className="text-center group">
                                         <div className="mx-auto mb-4 h-20 w-20 rounded-2xl border border-white/10 bg-black/20 p-4 transition group-hover:border-amber-600/50 group-hover:shadow-[0_0_30px_-10px_rgba(217,177,92,0.4)]">
                                            <img className="h-full w-full object-contain" src={nextMatch.away_club.logo_url} alt={nextMatch.away_club.name} />
                                         </div>
                                         <p className="mb-1 text-xl font-bold uppercase tracking-tight text-[var(--text-main)]">{nextMatch.away_club.name}</p>
                                         <span className="text-[10px] font-black uppercase tracking-widest text-amber-100/60">Guest</span>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <p className="text-[var(--text-muted)] italic">No upcoming matches</p>
                                </div>
                            )}

                            {nextMatch && (
                                <div className="relative z-10 mt-8 flex flex-col justify-between gap-6 xl:flex-row xl:items-center">
                                    <div className="flex flex-wrap gap-3">
                                        <div className={`flex items-center gap-2 rounded-full border px-3 py-2 ${activeClubReadyForNextMatch ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-rose-500/20 bg-rose-500/10 text-rose-300'}`}>
                                            <div className={`h-2 w-2 rounded-full ${activeClubReadyForNextMatch ? 'bg-emerald-400' : 'bg-rose-400'}`} />
                                            <span className="text-[10px] font-bold uppercase">Lineup Ready</span>
                                        </div>
                                        <div className={`flex items-center gap-2 rounded-full border px-3 py-2 ${opponentReadyForNextMatch ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300' : 'border-rose-500/20 bg-rose-500/10 text-rose-300'}`}>
                                            <div className={`h-2 w-2 rounded-full ${opponentReadyForNextMatch ? 'bg-emerald-400' : 'bg-rose-400'}`} />
                                            <span className="text-[10px] font-bold uppercase">Opponent Ready</span>
                                        </div>
                                    </div>
                                    
                                    <Link 
                                        href={nextMatch?.id ? route('matches.show', nextMatch.id) : route('dashboard')} 
                                        className="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-amber-300/20 bg-amber-300/12 px-8 py-3 text-center text-sm font-black uppercase tracking-[0.16em] text-amber-100 transition-colors hover:border-amber-200/35 hover:text-white md:w-auto"
                                    >
                                        Match Center
                                        <ArrowRight size={14} weight="bold" />
                                    </Link>
                                </div>
                            )}
                        </section>

                        <section className="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            <section className="rounded-3xl border border-white/10 bg-[var(--bg-pillar)]/40 p-5 shadow-xl">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Club Pulse</div>
                                        <div className="mt-1 text-lg font-black text-white">Management-Ampeln</div>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/70">
                                        Live
                                    </div>
                                </div>
                                <div className="grid grid-cols-2 gap-3">
                                    {clubPulseOverview?.map((item) => (
                                        <div key={item.label} className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                                            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{item.label}</div>
                                            <div className="mt-2 flex items-end gap-2">
                                                <div className="text-2xl font-black text-white">{item.value}</div>
                                                <div className="pb-1 text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">{item.suffix}</div>
                                            </div>
                                            <div className={`mt-3 inline-flex rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${accentTone[item.tone] || accentTone.slate}`}>
                                                {item.tone}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </section>

                            <section className="rounded-3xl border border-white/10 bg-[var(--bg-pillar)]/40 p-5 shadow-xl">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Quick Actions</div>
                                        <div className="mt-1 text-lg font-black text-white">Direkt springen</div>
                                    </div>
                                    <Lightning size={18} className="text-amber-300" weight="fill" />
                                </div>
                                <div className="grid grid-cols-2 gap-3">
                                    {quickActions?.map((action) => (
                                        <Link key={action.label} href={action.url} className="rounded-2xl border border-white/10 bg-white/[0.03] p-4 transition-colors hover:border-white/20">
                                            <div className={`inline-flex rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${accentTone[action.tone] || accentTone.slate}`}>
                                                {action.label}
                                            </div>
                                            <div className="mt-3 text-xs leading-relaxed text-[var(--text-muted)]">{action.description}</div>
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        </section>

                        {dashboardVariant !== 'classic' && (
                            <section className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                                {comparisonStats?.map((item) => (
                                    <div key={item.label} className="rounded-2xl border border-white/10 bg-[var(--bg-pillar)]/35 p-4 shadow-xl">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{item.label}</div>
                                        <div className="mt-2 flex items-end gap-2">
                                            <div className="text-2xl font-black text-white">{item.display}</div>
                                            <span className="pb-1 text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">{item.suffix}</span>
                                        </div>
                                        <div className={`mt-3 inline-flex rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${accentTone[item.tone] || accentTone.slate}`}>
                                            Vergleich
                                        </div>
                                    </div>
                                ))}
                            </section>
                        )}

                        <section className="rounded-3xl border border-amber-400/15 bg-[linear-gradient(155deg,rgba(29,24,14,0.96),rgba(13,20,33,0.96))] p-6 shadow-[0_25px_50px_-30px_rgba(217,177,92,0.4)]">
                            <div className="mb-4 flex items-start justify-between gap-4">
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-amber-200/75">Heute im Verein</div>
                                    <div className="mt-2 text-2xl font-black tracking-tight text-white">Dein Tagesfokus</div>
                                </div>
                            </div>
                            <div className="grid grid-cols-1 gap-3 md:grid-cols-2">
                                {todayFocus?.map((item, idx) => (
                                    <Link
                                        key={`${item.label}-${idx}`}
                                        href={item.url}
                                        className="rounded-2xl border border-white/10 bg-black/15 p-4 transition-colors hover:border-white/20"
                                    >
                                        <div className="mb-3 flex items-center justify-between gap-3">
                                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{item.label}</div>
                                            <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${accentTone[item.tone] || accentTone.slate}`}>
                                                {item.cta}
                                            </span>
                                        </div>
                                        <div className="text-lg font-black text-white">{item.value}</div>
                                        <div className="mt-1 text-xs font-bold uppercase tracking-[0.12em] text-[var(--text-muted)]">{item.detail}</div>
                                    </Link>
                                ))}
                            </div>
                        </section>

                        <section className="grid grid-cols-1 gap-4 xl:grid-cols-2">
                            {scoutingDesk && (scoutingDesk.watchlist_count > 0 || scoutingDesk.priority_targets.length > 0) && (
                                <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                                    <div className="mb-4 flex items-center justify-between gap-3">
                                        <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Scouting Desk</h3>
                                        <Link href={route('scouting.index')} className="text-[10px] font-black uppercase tracking-[0.14em] text-cyan-300 hover:text-white">
                                            {scoutingDesk.watchlist_count} Watchlist
                                        </Link>
                                    </div>
                                    <div className="space-y-3">
                                        {scoutingDesk.priority_targets.map((target) => (
                                            <Link
                                                key={target.id}
                                                href={route('scouting.index')}
                                                className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3 transition-colors hover:border-white/20"
                                            >
                                                <img src={target.photo_url} alt={target.name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
                                                <div className="min-w-0 flex-1">
                                                    <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{target.name}</div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">{target.club_name}</div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-amber-200">{target.priority}</div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.12em] text-cyan-300">
                                                        {target.overall_band ? `OVR ${target.overall_band}` : 'Kein Report'}
                                                    </div>
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                </section>
                            )}

                            {squadPulse && (squadPulse.manual_roles_count > 0 || squadPulse.promise_pressure_count > 0) && (
                                <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                                    <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)] mb-4">Squad Pulse</h3>
                                    <div className="space-y-4">
                                        {squadPulse.manual_roles_count > 0 && (
                                            <div>
                                                <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-fuchsia-200">
                                                    <FlagPennant size={12} weight="fill" />
                                                    {squadPulse.manual_roles_count} manuelle Rollen
                                                </div>
                                                <div className="space-y-2">
                                                    {squadPulse.manual_role_players.map((player) => (
                                                        <Link key={player.id} href={route('players.show', player.id)} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 transition-colors hover:border-fuchsia-400/30">
                                                            <img src={player.photo_url} alt={player.full_name} className="h-9 w-9 rounded-xl border border-white/10 object-cover" />
                                                            <div className="min-w-0 flex-1">
                                                                <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.full_name}</div>
                                                                <div className="text-[10px] font-black uppercase tracking-[0.12em] text-fuchsia-200">{player.squad_role}</div>
                                                            </div>
                                                        </Link>
                                                    ))}
                                                </div>
                                            </div>
                                        )}

                                        {squadPulse.promise_pressure_count > 0 && (
                                            <div>
                                                <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200">
                                                    <Handshake size={12} weight="fill" />
                                                    {squadPulse.promise_pressure_count} Promise-Konflikte
                                                </div>
                                                <div className="space-y-2">
                                                    {squadPulse.pressure_players.map((player) => (
                                                        <Link key={player.id} href={route('players.show', player.id)} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 transition-colors hover:border-amber-400/30">
                                                            <img src={player.photo_url} alt={player.full_name} className="h-9 w-9 rounded-xl border border-white/10 object-cover" />
                                                            <div className="min-w-0 flex-1">
                                                                <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.full_name}</div>
                                                                <div className="text-[10px] font-black uppercase tracking-[0.12em] text-amber-200">
                                                                    {player.promise_status === 'broken' ? 'Gebrochen' : 'Kritisch'} / Mood {player.happiness}%
                                                                </div>
                                                            </div>
                                                        </Link>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </section>
                            )}
                        </section>
                    </PageReveal>

                    {/* Right Column (Sidebar Widgets) */}
                    <PageReveal className="lg:col-span-4 space-y-8" delay={180}>
                        
                        <section className="rounded-3xl border border-emerald-400/12 bg-[linear-gradient(160deg,rgba(8,25,24,0.94),rgba(5,15,17,0.98))] p-5 shadow-[0_25px_50px_-30px_rgba(16,185,129,0.35)]">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-100/65">Live Matches</div>
                                    <div className="mt-1 text-3xl font-black tracking-tight text-white">{liveMatches?.length || 0}</div>
                                </div>
                                <Link href={route('live-ticker.index')} className="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-100 transition-colors hover:border-emerald-200/35 hover:text-white">
                                    <Broadcast size={12} weight="fill" />
                                    Live-Ticker
                                </Link>
                            </div>
                            <div className="space-y-3">
                                {liveMatches && liveMatches.length > 0 ? liveMatches.map((match) => (
                                    <LiveMatchRow key={match.id} match={match} />
                                )) : (
                                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-6 text-center text-sm text-[var(--text-muted)]">
                                        Derzeit laeuft kein Match live.
                                    </div>
                                )}
                            </div>
                        </section>

                        <section className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl">
                            <div className="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Die letzten 5 Spiele</h3>
                                </div>
                                <Link
                                    href={route('league.matches')}
                                    className="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-white/80 transition-colors hover:border-white/20 hover:text-white"
                                >
                                    Alle Termine
                                </Link>
                            </div>

                            {recentMatchesSummary?.matches?.length > 0 ? (
                                <div className="space-y-5">
                                    <div className="flex flex-wrap items-center gap-3">
                                        <div className="inline-flex overflow-hidden rounded-full border border-emerald-400/20 bg-emerald-400/10">
                                            <span className="inline-flex items-center justify-center bg-emerald-400 px-3 text-[11px] font-black uppercase text-black">S</span>
                                            <span className="inline-flex min-w-[40px] items-center justify-center px-3 text-lg font-black text-white">{recentMatchesSummary.wins}</span>
                                        </div>
                                        <div className="inline-flex overflow-hidden rounded-full border border-slate-300/20 bg-slate-300/10">
                                            <span className="inline-flex items-center justify-center bg-slate-100 px-3 text-[11px] font-black uppercase text-black">U</span>
                                            <span className="inline-flex min-w-[40px] items-center justify-center px-3 text-lg font-black text-white">{recentMatchesSummary.draws}</span>
                                        </div>
                                        <div className="inline-flex overflow-hidden rounded-full border border-rose-400/20 bg-rose-400/10">
                                            <span className="inline-flex items-center justify-center bg-rose-500 px-3 text-[11px] font-black uppercase text-white">N</span>
                                            <span className="inline-flex min-w-[40px] items-center justify-center px-3 text-lg font-black text-white">{recentMatchesSummary.losses}</span>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-5 gap-3">
                                        {recentMatchesSummary.matches.map((match, idx) => (
                                            <Link
                                                key={match.id ?? `${match.opponent_name}-${idx}`}
                                                href={match?.id ? route('matches.show', match.id) : route('league.matches')}
                                                className="group relative flex flex-col items-center gap-2"
                                                title={match?.id ? `Zum Match gegen ${match.opponent_name}` : 'Zu allen Terminen'}
                                            >
                                                <div className="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 hidden w-52 -translate-x-1/2 rounded-2xl border border-white/10 bg-[var(--bg-content)]/95 p-3 shadow-[0_18px_36px_-18px_rgba(0,0,0,0.75)] group-hover:block">
                                                    <div className="flex items-center justify-between gap-3">
                                                        <div className="min-w-0 text-right text-[11px] font-black uppercase tracking-[0.04em] text-white">
                                                            <div className="truncate">{activeClub.name}</div>
                                                            <div className="mt-1 text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                                {match.is_home ? 'Heim' : 'Auswaerts'}
                                                            </div>
                                                        </div>
                                                        <div className="rounded-xl border border-amber-400/15 bg-amber-400/10 px-3 py-1.5 text-lg font-black text-white">
                                                            {match.score}
                                                        </div>
                                                        <div className="min-w-0 text-left text-[11px] font-black uppercase tracking-[0.04em] text-white">
                                                            <div className="truncate">{match.opponent_name}</div>
                                                            <div className="mt-1 text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                                {match.is_home ? 'Gast' : 'Heim'}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div className="flex w-full overflow-hidden rounded-lg border border-white/10 bg-white/[0.03]">
                                                    <span className={`inline-flex h-5 w-full items-center justify-center text-[9px] font-black uppercase ${formTone[match.result_label] || formTone.U}`}>
                                                        {match.result_label}
                                                    </span>
                                                </div>
                                                <div className="relative flex h-12 w-12 cursor-pointer items-center justify-center rounded-2xl border border-white/10 bg-[var(--bg-content)]/40 p-2 transition-all group-hover:-translate-y-0.5 group-hover:border-cyan-300/35 group-hover:bg-[var(--bg-content)]/55 group-hover:shadow-[0_12px_24px_-18px_rgba(34,211,238,0.55)]">
                                                    <span className="pointer-events-none absolute -right-1 -top-1 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-1.5 py-0.5 text-[8px] font-black uppercase tracking-[0.14em] text-cyan-100 opacity-0 transition-opacity group-hover:opacity-100">
                                                        Match
                                                    </span>
                                                    {match.opponent_logo_url ? (
                                                        <img src={match.opponent_logo_url} alt={match.opponent_name} className="h-full w-full object-contain" />
                                                    ) : (
                                                        <span className="text-[10px] font-black uppercase tracking-[0.08em] text-white/60">
                                                            {match.opponent_name?.slice(0, 3) || 'N/A'}
                                                        </span>
                                                    )}
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                </div>
                            ) : (
                                <p className="text-sm italic text-[var(--text-muted)]">Season just started</p>
                            )}
                        </section>

                        {/* Assistant Suggestions */}
                        {assistantTasks && assistantTasks.length > 0 && (
                            <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-fuchsia-500/20 p-6 shadow-xl relative overflow-hidden">
                                <div className="absolute top-0 left-0 w-1 h-full bg-fuchsia-500" />
                                <div className="mb-4 flex items-start justify-between gap-4">
                                    <div>
                                        <h3 className="text-xs font-bold uppercase tracking-widest text-fuchsia-400">Suggestions</h3>
                                        <p className="mt-2 text-xs leading-relaxed text-[var(--text-muted)]">Kompakte Spielleitung fuer Matchday, Kader und Postfach.</p>
                                    </div>
                                    <div className="rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-fuchsia-200">
                                        {assistantTasks.length} offen
                                    </div>
                                </div>
                                <div className="grid grid-cols-1 gap-3">
                                    {assistantTasks.map((task, idx) => (
                                        <div key={idx} className="rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-content)]/40 p-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <div className="min-w-0 flex-1">
                                                    <div className="mb-3 flex flex-wrap items-center gap-2">
                                                        <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${taskPriorityTone[task.priority] || taskPriorityTone.beobachten}`}>
                                                            {task.priority || 'beobachten'}
                                                        </span>
                                                        <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${taskTone[task.kind] || taskTone.info}`}>
                                                            {task.domain || 'system'}
                                                        </span>
                                                        {task.metric ? (
                                                            <span className="rounded-full border border-white/10 bg-white/[0.04] px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-white/80">
                                                                {task.metric}
                                                            </span>
                                                        ) : null}
                                                    </div>
                                                    <p className="text-sm font-bold text-[var(--text-main)]">{task.label}</p>
                                                    <p className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{task.description}</p>
                                                </div>
                                                <Link 
                                                    href={task.url}
                                                    className="inline-flex shrink-0 items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200 transition-colors hover:border-amber-300/35 hover:text-white"
                                                >
                                                    {task.cta}
                                                    <ArrowRight size={12} weight="bold" />
                                                </Link>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </section>
                        )}

                        {managerDecisions && managerDecisions.length > 0 && (
                            <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                                <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)] mb-4">Manager Decisions</h3>
                                <div className="space-y-3">
                                    {managerDecisions.map((decision, idx) => (
                                        <Link
                                            key={`${decision.kind}-${decision.player_id}-${idx}`}
                                            href={route('players.show', decision.player_id)}
                                            className="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3 transition-colors hover:border-white/20"
                                        >
                                            <img src={decision.photo_url} alt={decision.player_name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
                                            <div className="min-w-0 flex-1">
                                                <div className="flex items-start justify-between gap-3">
                                                    <div>
                                                        <div className="text-[11px] font-black uppercase tracking-[0.06em] text-white">{decision.title}</div>
                                                        <div className="text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">{decision.player_name}</div>
                                                    </div>
                                                    <span className={`shrink-0 rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${
                                                        decision.accent === 'emerald'
                                                            ? 'border border-emerald-400/20 bg-emerald-400/10 text-emerald-200'
                                                            : decision.accent === 'rose'
                                                                ? 'border border-rose-400/20 bg-rose-400/10 text-rose-200'
                                                                : decision.accent === 'fuchsia'
                                                                    ? 'border border-fuchsia-400/20 bg-fuchsia-400/10 text-fuchsia-200'
                                                                    : decision.accent === 'amber'
                                                                        ? 'border border-amber-400/20 bg-amber-400/10 text-amber-200'
                                                                        : 'border border-cyan-400/20 bg-cyan-400/10 text-cyan-200'
                                                    }`}>
                                                        {decision.impact_label}
                                                    </span>
                                                </div>
                                                <p className="mt-2 text-xs leading-relaxed text-[var(--text-muted)]">{decision.summary}</p>
                                                <div className="mt-2 flex flex-wrap items-center gap-2">
                                                    <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${
                                                        decision.evaluation?.accent === 'emerald'
                                                            ? 'border border-emerald-400/20 bg-emerald-400/10 text-emerald-200'
                                                            : decision.evaluation?.accent === 'rose'
                                                                ? 'border border-rose-400/20 bg-rose-400/10 text-rose-200'
                                                                : decision.evaluation?.accent === 'amber'
                                                                    ? 'border border-amber-400/20 bg-amber-400/10 text-amber-200'
                                                                    : 'border border-slate-400/20 bg-slate-400/10 text-slate-200'
                                                    }`}>
                                                        {decision.evaluation?.label || 'Neutral'}
                                                    </span>
                                                    <div className="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.14em] text-slate-500">
                                                    <ChatCircleText size={11} weight="fill" />
                                                    {decision.created_at}
                                                    </div>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        )}

                        <section className="rounded-3xl border border-cyan-400/12 bg-[linear-gradient(160deg,rgba(10,20,35,0.94),rgba(8,15,28,0.98))] p-5 shadow-[0_25px_50px_-30px_rgba(14,165,233,0.35)]">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-100/65">Online Manager</div>
                                    <div className="mt-1 text-3xl font-black tracking-tight text-white">{onlineManagers?.length || 0}</div>
                                </div>
                                <Link href={route('manager-live.index')} className="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-100 transition-colors hover:border-cyan-200/35 hover:text-white">
                                    <UsersThree size={12} weight="fill" />
                                    Manager Online
                                </Link>
                            </div>
                            <div className="space-y-3">
                                {onlineManagers && onlineManagers.length > 0 ? onlineManagers.map((manager) => (
                                    <ManagerLiveRow key={manager.id} manager={manager} />
                                )) : (
                                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-6 text-center text-sm text-[var(--text-muted)]">
                                        Aktuell ist kein Manager online.
                                    </div>
                                )}
                            </div>
                        </section>

                    </PageReveal>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .bg-amber-rgb { --amber-rgb: 217, 177, 92; }
                .bg-gold-rgb { --gold-rgb: 217, 177, 92; }
            `}} />
        </AuthenticatedLayout>
    );
}
