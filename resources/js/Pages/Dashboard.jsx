import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    Calendar, Trophy, Users, ChartBar, TrendUp, 
    ArrowRight, Bell, WarningCircle, Info, Bank,
    Smiley, SmileySad
} from '@phosphor-icons/react';
import Skeleton from '@/Components/Skeleton';

const StatCard = ({ label, value, subValue, icon: Icon, color = 'amber', delay = 0 }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay }}
        className="bg-[var(--bg-pillar)]/40 backdrop-blur-md rounded-2xl border border-[var(--border-muted)] p-6 relative overflow-hidden group hover:border-[var(--accent-primary)]/30 transition-all shadow-xl"
    >
        <div className={`absolute -right-6 -top-6 h-24 w-24 rounded-full bg-${color}-500/5 blur-2xl group-hover:bg-${color}-500/10 transition-colors`} />
        
        <div className="flex items-center gap-4 relative z-10">
            <div className={`h-12 w-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-${color}-400 group-hover:scale-110 transition-transform`}>
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
    </motion.div>
);

const TimelineDay = ({ day, delay }) => {
    const isToday = day.is_today;
    const hasMatch = day.match_count > 0;
    const hasTraining = day.training_count > 0;
    
    return (
        <motion.div
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ delay }}
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
                <p className={`text-[10px] font-bold uppercase tracking-widest ${isToday ? 'text-amber-500' : 'text-slate-500'}`}>
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
        </motion.div>
    );
};

export default function Dashboard(props) {
    const { 
        activeClub, nextMatch, nextMatchTypeLabel, 
        activeClubReadyForNextMatch, opponentReadyForNextMatch,
        clubRank, clubPoints, recentForm, weekDays,
        todayMatchesCount, unreadNotificationsCount,
        assistantTasks, metrics
    } = props;

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <Head title="Welcome" />
                <div className="max-w-4xl mx-auto py-12 text-center">
                    <motion.div 
                        initial={{ opacity: 0, scale: 0.9 }} 
                        animate={{ opacity: 1, scale: 1 }}
                        className="bg-slate-900/40 p-12 rounded-3xl border border-slate-800"
                    >
                        <Trophy size={64} weight="duotone" className="mx-auto text-amber-500 mb-6" />
                        <h1 className="text-4xl font-bold text-white mb-4">Start Your Career</h1>
                        <p className="text-slate-400 text-lg mb-8 max-w-xl mx-auto">
                            You don't have an active club yet. Take over a team today and lead them to glory.
                        </p>
                        <Link 
                            href={route('clubs.free')}
                            className="inline-flex items-center gap-3 bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] px-8 py-4 rounded-xl font-bold text-black shadow-xl shadow-amber-900/40 hover:scale-105 transition-all uppercase tracking-widest text-sm"
                        >
                            View Available Clubs
                            <ArrowRight size={20} weight="bold" />
                        </Link>
                    </motion.div>
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
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <StatCard 
                        label="Account Balance" 
                        value={new Intl.NumberFormat('de-DE').format(activeClub.budget)} 
                        subValue="€" 
                        icon={Bank} 
                        color="emerald"
                        delay={0.1}
                    />
                    {clubRank !== undefined ? (
                        <StatCard 
                            label="League Rank" 
                            value={clubRank ? `#${clubRank}` : 'N/A'} 
                            subValue={`${clubPoints || 0} Points`} 
                            icon={Trophy} 
                            color="amber"
                            delay={0.2}
                        />
                    ) : (
                        <div className="bg-slate-900/40 backdrop-blur-md rounded-2xl border border-slate-800/50 p-6 shadow-xl h-[104px]">
                            <div className="flex items-center gap-4">
                                <Skeleton variant="rect" className="h-12 w-12 rounded-xl" />
                                <div className="space-y-2">
                                    <Skeleton variant="text" className="w-20" />
                                    <Skeleton variant="text" className="w-24 h-6" />
                                </div>
                            </div>
                        </div>
                    )}
                    <StatCard 
                        label="Today's Games" 
                        value={todayMatchesCount} 
                        subValue="Total" 
                        icon={Calendar} 
                        color="amber"
                        delay={0.3}
                    />
                    <StatCard 
                        label="Fan Mood" 
                        value={`${fanMood}%`} 
                        subValue={fanMood > 70 ? 'Excellent' : fanMood > 40 ? 'Stable' : 'Unrest'} 
                        icon={fanMood > 50 ? Smiley : SmileySad} 
                        color={fanMood > 70 ? 'emerald' : fanMood > 40 ? 'amber' : 'rose'}
                        delay={0.4}
                    />
                </div>

                {/* Main Dashboard Section */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {/* Left Column (Main Info) */}
                    <div className="lg:col-span-8 space-y-8">
                        
                        {/* Weekly Overview */}
                        <section>
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-xs font-bold uppercase tracking-widest text-slate-500">Weekly Overview</h3>
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
                                    <TimelineDay key={idx} day={day} delay={0.1 + idx * 0.05} />
                                ))}
                            </div>
                        </section>

                        {/* Next Match Card */}
                        <motion.section
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 0.5 }}
                            className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-8 shadow-2xl relative overflow-hidden"
                        >
                            <div className="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent pointer-events-none" />
                            
                            <h3 className="text-xs font-black uppercase tracking-[0.2em] text-amber-500 mb-8">Next Fixture</h3>
                            
                            {nextMatch ? (
                                <div className="grid grid-cols-1 md:grid-cols-3 items-center gap-12 relative z-10">                                     <div className="text-center group">
                                         <div className="mx-auto mb-4 h-20 w-20 rounded-2xl border border-slate-700 bg-slate-900 p-4 transition group-hover:border-amber-500/50 group-hover:shadow-[0_0_30px_-10px_rgba(217,177,92,0.4)]">
                                            <img className="h-full w-full object-contain" src={nextMatch.home_club.logo_url} alt={nextMatch.home_club.name} />
                                         </div>
                                         <p className="text-xl font-bold text-[var(--text-main)] mb-1 uppercase tracking-tight italic">{nextMatch.home_club.name}</p>
                                         <span className="text-[10px] font-black uppercase tracking-widest text-slate-500">Host</span>
                                    </div>

                                    <div className="text-center flex flex-col items-center gap-4">
                                         <div className="px-4 py-1.5 rounded-full bg-slate-800 border border-slate-700">
                                             <span className="text-xs font-black text-amber-500 uppercase tracking-[0.2em]">{nextMatchTypeLabel}</span>
                                         </div>
                                         <span className="text-5xl font-black text-slate-800/50 italic italic italic italic">VS</span>
                                         <div className="text-center">
                                             <p className="text-sm font-bold text-white mb-1">{nextMatch.kickoff_at_formatted}</p>
                                             <p className="text-[10px] font-bold text-slate-500 uppercase">{nextMatch.stadium_name || 'Neutral Venue'}</p>
                                         </div>
                                    </div>

                                     <div className="text-center group">
                                         <div className="mx-auto mb-4 h-20 w-20 rounded-2xl border border-slate-700 bg-slate-900 p-4 transition group-hover:border-amber-600/50 group-hover:shadow-[0_0_30px_-10px_rgba(217,177,92,0.4)]">
                                            <img className="h-full w-full object-contain" src={nextMatch.away_club.logo_url} alt={nextMatch.away_club.name} />
                                         </div>
                                         <p className="text-xl font-bold text-[var(--text-main)] mb-1 uppercase tracking-tight italic">{nextMatch.away_club.name}</p>
                                         <span className="text-[10px] font-black uppercase tracking-widest text-slate-500">Guest</span>
                                    </div>
                                </div>
                            ) : (
                                <div className="text-center py-8">
                                    <p className="text-slate-500 italic">No upcoming matches</p>
                                </div>
                            )}

                            {nextMatch && (
                                <div className="mt-12 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                                    <div className="flex gap-4">
                                        <div className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border ${activeClubReadyForNextMatch ? 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400' : 'border-rose-500/20 bg-rose-500/5 text-rose-400'}`}>
                                            <div className={`h-2 w-2 rounded-full ${activeClubReadyForNextMatch ? 'bg-emerald-400' : 'bg-rose-400'}`} />
                                            <span className="text-[10px] font-bold uppercase">Lineup Ready</span>
                                        </div>
                                        <div className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border ${opponentReadyForNextMatch ? 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400' : 'border-rose-500/20 bg-rose-500/5 text-rose-400'}`}>
                                            <div className={`h-2 w-2 rounded-full ${opponentReadyForNextMatch ? 'bg-emerald-400' : 'bg-rose-400'}`} />
                                            <span className="text-[10px] font-bold uppercase">Opponent Ready</span>
                                        </div>
                                    </div>
                                    
                                    <Link 
                                        href={route('matches.show', nextMatch?.id)} 
                                        className="sim-btn-primary px-10 py-3 shadow-xl shadow-amber-900/10 w-full md:w-auto text-center"
                                    >
                                        Match Center
                                    </Link>
                                </div>
                            )}
                        </motion.section>
                    </div>

                    {/* Right Column (Sidebar Widgets) */}
                    <div className="lg:col-span-4 space-y-8">
                        
                        {/* Squad Metrics */}
                        <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl leading-none">
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-6 font-mono">Squad Strength</h3>
                            <div className="space-y-6">
                                {metrics ? (
                                    [
                                        { label: 'Attack', value: metrics?.attack || 0, color: 'amber' },
                                        { label: 'Midfield', value: metrics?.midfield || 0, color: 'amber' },
                                        { label: 'Defense', value: metrics?.defense || 0, color: 'amber' },
                                        { label: 'Chemistry', value: metrics?.chemistry || 0, color: 'amber' },
                                    ].map((item, id) => (
                                        <div key={id}>
                                            <div className="flex justify-between items-end mb-2">
                                                 <span className="text-sm font-bold text-[var(--text-main)]">{item.label}</span>
                                                <span className={`text-lg font-black text-${item.color}-400`}>{item.value}</span>
                                            </div>
                                            <div className="h-2 w-full bg-slate-800 rounded-full overflow-hidden">
                                                <motion.div 
                                                    initial={{ width: 0 }}
                                                    animate={{ width: `${item.value}%` }}
                                                    transition={{ duration: 1, delay: 0.6 + id * 0.1 }}
                                                    className={`h-full bg-${item.color}-500 shadow-[0_0_10px_rgba(var(--${item.color}-rgb),0.5)]`}
                                                />
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    [1, 2, 3, 4].map((i) => (
                                        <div key={i} className="space-y-2">
                                            <div className="flex justify-between">
                                                <Skeleton variant="text" className="w-16" />
                                                <Skeleton variant="text" className="w-8" />
                                            </div>
                                            <Skeleton variant="rect" className="h-2 w-full" />
                                        </div>
                                    ))
                                )}
                            </div>
                        </section>

                        {/* Recent Form */}
                        <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                            <h3 className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-4">Last 5 Matches</h3>
                            <div className="flex gap-2">
                                {recentForm && recentForm.length > 0 ? recentForm.map((res, idx) => (
                                    <div 
                                        key={idx}
                                        className={`
                                            flex-1 h-10 flex items-center justify-center rounded-xl font-black text-sm border
                                            ${res === 'W' ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' : 
                                              res === 'L' ? 'bg-rose-500/10 border-rose-500/30 text-rose-400' : 
                                              'bg-slate-800 border-slate-700 text-slate-400'}
                                        `}
                                    >
                                        {res}
                                    </div>
                                )) : (
                                    <p className="text-slate-600 text-xs italic">Season just started</p>
                                )}
                            </div>
                        </section>

                        {/* Assistant Suggestions */}
                        {assistantTasks && assistantTasks.length > 0 && (
                            <section className="bg-slate-900/40 rounded-3xl border border-fuchsia-500/20 p-6 shadow-xl relative overflow-hidden">
                                <div className="absolute top-0 left-0 w-1 h-full bg-fuchsia-500" />
                                <h3 className="text-xs font-bold uppercase tracking-widest text-fuchsia-400 mb-4">Suggestions</h3>
                                <div className="space-y-4">
                                    {assistantTasks.map((task, idx) => (
                                        <div key={idx} className="bg-slate-800/40 rounded-xl p-4 border border-slate-700/50">
                                            <div className="flex items-start gap-3">
                                                <div className={`mt-1 h-2 w-2 rounded-full flex-shrink-0 ${task.kind === 'warning' ? 'bg-rose-500' : 'bg-amber-500'}`} />
                                                <div>
                                                     <p className="text-sm font-bold text-[var(--text-main)] mb-1 text-[var(--text-main)]">{task.label}</p>
                                                    <p className="text-xs text-slate-400 leading-relaxed mb-3">{task.description}</p>
                                                    <Link 
                                                        href={task.url}
                                                        className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 hover:text-amber-400 transition-colors inline-flex items-center gap-1"
                                                    >
                                                        {task.cta} <ArrowRight size={12} weight="bold" />
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </section>
                        )}
                    </div>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .bg-amber-rgb { --amber-rgb: 217, 177, 92; }
                .bg-gold-rgb { --gold-rgb: 217, 177, 92; }
            `}} />
        </AuthenticatedLayout>
    );
}
