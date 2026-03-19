import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import useLiveOverview from '@/hooks/useLiveOverview';
import { 
    Calendar, Trophy, Users, ChartBar,
    ArrowRight, Bank, Smiley, SmileySad, FlagPennant, Handshake, ChatCircleText, Broadcast, UsersThree, Lightning, FirstAidKit, CaretUp, CaretDown, SlidersHorizontal, Eye, EyeSlash
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
    <div className="rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/32 p-5 transition-colors hover:border-white/15">
        <div className="flex items-center gap-4">
            <div className={`flex h-11 w-11 items-center justify-center rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] text-${color}-400`}>
                <Icon size={24} weight="duotone" />
            </div>
            <div>
                <p className="mb-0.5 text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
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
    <div className="group flex items-center gap-3 rounded-2xl border border-cyan-400/10 bg-[linear-gradient(135deg,rgba(17,30,48,0.85),rgba(11,22,36,0.95))] px-3 py-3 transition-all hover:-translate-y-0.5 hover:border-cyan-300/25 hover:shadow-[0_14px_30px_-18px_rgba(34,211,238,0.45)]">
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
    </div>
);

const LiveMatchRow = ({ match }) => (
    <Link
        href={match?.id ? route('matches.show', match.id) : (route().has('live-ticker.index') ? route('live-ticker.index') : route('dashboard'))}
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

const moduleWidgetIconMap = {
    broadcast: Broadcast,
    trophy: Trophy,
    binoculars: ChartBar,
    firstAidKit: FirstAidKit,
};

const defaultDashboardPreferences = {
    variant: 'modern',
    hidden_sections: [],
    section_order: [],
    hidden_widgets: [],
    widget_order: [],
};

function ModuleWidgetCard({ widget }) {
    const Icon = moduleWidgetIconMap[widget.icon] || Lightning;

    return (
        <Link
            href={route(widget.route)}
            className={`group rounded-2xl border px-4 py-4 transition-all hover:-translate-y-0.5 ${
                widget.accent === 'rose'
                    ? 'border-rose-400/15 bg-rose-400/6 hover:border-rose-300/30'
                    : widget.accent === 'emerald'
                        ? 'border-emerald-400/15 bg-emerald-400/6 hover:border-emerald-300/30'
                        : widget.accent === 'amber'
                            ? 'border-amber-400/15 bg-amber-400/6 hover:border-amber-300/30'
                            : 'border-cyan-400/15 bg-cyan-400/6 hover:border-cyan-300/30'
            }`}
        >
            <div className="flex items-start gap-3">
                <div className={`rounded-2xl border p-3 ${
                    widget.accent === 'rose'
                        ? 'border-rose-400/20 bg-rose-400/10 text-rose-200'
                        : widget.accent === 'emerald'
                            ? 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200'
                            : widget.accent === 'amber'
                                ? 'border-amber-400/20 bg-amber-400/10 text-amber-200'
                                : 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200'
                }`}>
                    <Icon size={16} weight="duotone" />
                </div>
                <div className="min-w-0 flex-1">
                    <div className="text-[11px] font-black uppercase tracking-[0.08em] text-white">{widget.title}</div>
                    <p className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{widget.description}</p>
                    <div className="mt-3 inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/80">
                        Open
                        <ArrowRight size={11} weight="bold" />
                    </div>
                </div>
            </div>
        </Link>
    );
}

function CockpitPreferencesCard({ preferences, widgets, onVariantChange, onToggleWidget, onMoveWidget }) {
    return (
        <section className="rounded-3xl border border-white/10 bg-[var(--bg-pillar)]/35 p-5 shadow-xl">
            <div className="mb-4 flex items-center justify-between gap-3">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Cockpit Preferences</div>
                    <div className="mt-1 text-lg font-black text-white">Your manager layout</div>
                </div>
                <SlidersHorizontal size={16} className="text-cyan-300" weight="bold" />
            </div>

            <div className="mb-4">
                <div className="mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Dashboard Variant</div>
                <div className="flex flex-wrap gap-2">
                    {['modern', 'compact', 'classic'].map((variant) => (
                        <button
                            key={variant}
                            type="button"
                            onClick={() => onVariantChange(variant)}
                            className={`rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] ${
                                preferences.variant === variant
                                    ? 'border-cyan-400/30 bg-cyan-500/15 text-cyan-200'
                                    : 'border-white/10 bg-white/[0.03] text-white/70'
                            }`}
                        >
                            {variant}
                        </button>
                    ))}
                </div>
            </div>

            <div className="space-y-2.5">
                {widgets.length > 0 ? widgets.map((widget, index) => {
                    const hidden = preferences.hidden_widgets.includes(widget.key);

                    return (
                        <div key={widget.key} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                            <button
                                type="button"
                                onClick={() => onToggleWidget(widget.key)}
                                className={`inline-flex h-8 w-8 items-center justify-center rounded-xl border ${
                                    hidden
                                        ? 'border-slate-400/20 bg-slate-500/10 text-slate-300'
                                        : 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200'
                                }`}
                            >
                                {hidden ? <EyeSlash size={14} weight="bold" /> : <Eye size={14} weight="bold" />}
                            </button>
                            <div className="min-w-0 flex-1">
                                <div className="truncate text-[11px] font-black uppercase tracking-[0.08em] text-white">{widget.title}</div>
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                    {widget.placement === 'sidebar' ? 'Sidebar' : 'Main'} / {hidden ? 'hidden' : 'visible'}
                                </div>
                            </div>
                            <div className="flex items-center gap-1">
                                <button
                                    type="button"
                                    onClick={() => onMoveWidget(widget.key, -1)}
                                    disabled={index === 0}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] text-white/70 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <CaretUp size={14} weight="bold" />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => onMoveWidget(widget.key, 1)}
                                    disabled={index === widgets.length - 1}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-xl border border-white/10 bg-white/[0.03] text-white/70 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <CaretDown size={14} weight="bold" />
                                </button>
                            </div>
                        </div>
                    );
                }) : (
                    <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-6 text-sm text-[var(--text-muted)]">
                        No module widgets available for customization.
                    </div>
                )}
            </div>
        </section>
    );
}

export default function Dashboard(props) {
    const { 
        activeClub, nextMatch, nextMatchTypeLabel, 
        activeClubReadyForNextMatch, opponentReadyForNextMatch,
        clubRank, clubPoints, recentForm, recentMatchesSummary, weekDays,
        todayMatchesCount, unreadNotificationsCount,
        dashboardVariant, assistantTasks, todayFocus, clubPulseOverview, comparisonStats, quickActions,
        squadPulse, scoutingDesk, medicalDesk, managerDecisions, liveMatches, onlineManagers, dashboardPreferences
    } = props;
    const { modules = {} } = usePage().props;

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
    const liveOverview = useLiveOverview({ initialLiveMatches: liveMatches, initialOnlineManagers: onlineManagers });
    const hasManagerLiveRoute = route().has('manager-live.index');
    const hasLiveTickerRoute = route().has('live-ticker.index');
    const preferences = {
        ...defaultDashboardPreferences,
        ...(dashboardPreferences || {}),
    };
    const rawDashboardWidgets = [...(modules.dashboard_widgets || [])];
    const allWidgetKeys = rawDashboardWidgets.map((widget) => widget.key);
    const orderedWidgetKeys = [
        ...preferences.widget_order.filter((key) => allWidgetKeys.includes(key)),
        ...allWidgetKeys.filter((key) => !preferences.widget_order.includes(key)),
    ];
    const dashboardWidgets = orderedWidgetKeys
        .map((key) => rawDashboardWidgets.find((widget) => widget.key === key))
        .filter(Boolean);
    const visibleDashboardWidgets = dashboardWidgets.filter((widget) => !preferences.hidden_widgets.includes(widget.key));
    const mainModuleWidgets = visibleDashboardWidgets.filter((widget) => (widget.placement || 'main') === 'main');
    const sidebarModuleWidgets = visibleDashboardWidgets.filter((widget) => widget.placement === 'sidebar');

    const persistDashboardPreferences = (patch) => {
        router.patch(route('dashboard.preferences.update'), {
            ...preferences,
            ...patch,
        }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    const toggleWidgetVisibility = (key) => {
        const hiddenWidgets = preferences.hidden_widgets.includes(key)
            ? preferences.hidden_widgets.filter((item) => item !== key)
            : [...preferences.hidden_widgets, key];

        persistDashboardPreferences({ hidden_widgets: hiddenWidgets });
    };

    const moveWidget = (key, direction) => {
        const currentOrder = [...orderedWidgetKeys];
        const index = currentOrder.indexOf(key);
        const targetIndex = index + direction;

        if (index < 0 || targetIndex < 0 || targetIndex >= currentOrder.length) {
            return;
        }

        [currentOrder[index], currentOrder[targetIndex]] = [currentOrder[targetIndex], currentOrder[index]];
        persistDashboardPreferences({ widget_order: currentOrder });
    };

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

                        <section className="overflow-hidden rounded-[28px] border border-cyan-400/14 bg-[linear-gradient(180deg,rgba(8,19,34,0.98),rgba(7,18,31,0.92))] shadow-[0_24px_60px_-38px_rgba(8,145,178,0.35)]">
                            <div className="border-b border-white/6 bg-[linear-gradient(90deg,rgba(34,211,238,0.08),rgba(217,177,92,0.04),transparent)] px-6 py-5">
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 className="text-xs font-black uppercase tracking-[0.18em] text-cyan-100/70">Next Fixture</h3>
                                        <div className="mt-2 text-2xl font-black tracking-tight text-white">{nextMatchTypeLabel || 'Matchday'}</div>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] text-white/70">
                                        {dashboardVariant}
                                    </div>
                                </div>
                            </div>

                            <div className="p-6">
                            <div className="mb-5 flex items-start justify-between gap-4">
                                <div>
                                    <div className="text-[11px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Matchday Focus</div>
                                    <div className="mt-1 text-sm text-cyan-50/80">Everything important for your next kickoff in one place.</div>
                                </div>
                            </div>

                            {nextMatch ? (
                                <div className="space-y-5">
                                    <div className="grid gap-4 xl:grid-cols-[1fr_220px_1fr] xl:items-center">
                                        <ClubSide club={nextMatch.home_club} align="left" />
                                        <div className="relative overflow-hidden rounded-[26px] border border-white/10 bg-[linear-gradient(180deg,rgba(16,31,50,0.9),rgba(9,21,35,0.96))] px-5 py-6 text-center shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                                            <div className="absolute inset-x-6 top-0 h-px bg-gradient-to-r from-transparent via-cyan-200/30 to-transparent" />
                                            <div className="space-y-3">
                                                <div className="text-[34px] font-black tracking-[0.22em] text-white/18">VS</div>
                                                <div className="inline-flex items-center rounded-full border border-cyan-300/14 bg-cyan-300/8 px-3 py-1 text-[9px] font-black uppercase tracking-[0.16em] text-cyan-100/85">
                                                    {nextMatch.kickoff_at_formatted}
                                                </div>
                                                <div className="text-sm font-bold text-white">{nextMatch.stadium_name || 'Neutral Venue'}</div>
                                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/35">Kickoff Window</div>
                                            </div>
                                        </div>
                                        <ClubSide club={nextMatch.away_club} align="right" />
                                    </div>

                                    <div className="grid gap-3 md:grid-cols-2">
                                        <ReadyBadge ready={activeClubReadyForNextMatch} label="Lineup Ready" />
                                        <ReadyBadge ready={opponentReadyForNextMatch} label="Opponent Ready" />
                                    </div>

                                    <div className="flex flex-wrap items-center justify-between gap-4 rounded-[24px] border border-white/10 bg-[linear-gradient(180deg,rgba(11,25,41,0.88),rgba(10,22,37,0.94))] px-5 py-4">
                                        <div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.15em] text-amber-200/70">Manager Prompt</div>
                                            <div className="mt-1 text-sm text-white/80">
                                                Prepare your lineup, confirm readiness and step into the match center.
                                            </div>
                                        </div>
                                        <Link
                                            href={nextMatch?.id ? route('matches.show', nextMatch.id) : route('dashboard')}
                                            className="inline-flex items-center gap-2 rounded-2xl border border-amber-300/20 bg-[linear-gradient(135deg,rgba(217,177,92,0.18),rgba(217,177,92,0.08))] px-5 py-3 text-[11px] font-black uppercase tracking-[0.16em] text-amber-100 transition-all hover:-translate-y-0.5 hover:border-amber-200/35 hover:text-white hover:shadow-[0_16px_28px_-18px_rgba(217,177,92,0.55)]"
                                        >
                                            Match Center
                                            <ArrowRight size={13} weight="bold" />
                                        </Link>
                                    </div>
                                </div>
                            ) : (
                                <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-10 text-center text-sm text-[var(--text-muted)]">
                                    No upcoming matches scheduled.
                                </div>
                            )}
                            </div>
                        </section>

                        <section className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                            <section className="rounded-3xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/34 p-5">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Priorities</div>
                                        <div className="mt-1 text-lg font-black text-white">What needs attention</div>
                                    </div>
                                    {assistantTasks?.length > 0 && (
                                        <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/70">
                                            {assistantTasks.length} open
                                        </div>
                                    )}
                                </div>
                                <div className="space-y-3">
                                    {(assistantTasks || []).slice(0, 3).map((task, idx) => (
                                        <Link
                                            key={idx}
                                            href={task.url}
                                            className="flex items-start justify-between gap-4 rounded-2xl border border-white/10 bg-[var(--bg-content)]/45 px-4 py-3 transition-colors hover:border-white/20"
                                        >
                                            <div className="min-w-0">
                                                <div className="mb-2 flex flex-wrap items-center gap-2">
                                                    <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${taskPriorityTone[task.priority] || taskPriorityTone.beobachten}`}>
                                                        {task.priority || 'beobachten'}
                                                    </span>
                                                    {task.metric && (
                                                        <span className="rounded-full border border-white/10 bg-white/[0.04] px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-white/80">
                                                            {task.metric}
                                                        </span>
                                                    )}
                                                </div>
                                                <div className="text-sm font-black text-white">{task.label}</div>
                                                <div className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{task.description}</div>
                                            </div>
                                            <ArrowRight size={14} className="mt-1 shrink-0 text-white/50" weight="bold" />
                                        </Link>
                                    ))}
                                    {(!assistantTasks || assistantTasks.length === 0) && (
                                        <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">
                                            No urgent tasks right now.
                                        </div>
                                    )}
                                </div>
                            </section>

                            <section className="rounded-3xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/34 p-5">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Control Center</div>
                                        <div className="mt-1 text-lg font-black text-white">Pulse and shortcuts</div>
                                    </div>
                                    <Lightning size={16} className="text-amber-300" weight="fill" />
                                </div>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {(clubPulseOverview || []).slice(0, 4).map((item) => (
                                        <div key={item.label} className="rounded-2xl border border-white/10 bg-[var(--bg-content)]/45 px-4 py-3">
                                            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{item.label}</div>
                                            <div className="mt-2 flex items-end gap-2">
                                                <div className="text-xl font-black text-white">{item.value}</div>
                                                <div className="pb-0.5 text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">{item.suffix}</div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                    {(quickActions || []).slice(0, 4).map((action) => (
                                        <Link key={action.label} href={action.url} className="rounded-2xl border border-white/10 bg-[var(--bg-content)]/45 px-4 py-3 transition-colors hover:border-white/20">
                                            <div className={`inline-flex rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${accentTone[action.tone] || accentTone.slate}`}>
                                                {action.label}
                                            </div>
                                            <div className="mt-2 text-xs leading-relaxed text-[var(--text-muted)]">{action.description}</div>
                                        </Link>
                                    ))}
                                </div>
                            </section>
                        </section>

                        <section className="grid grid-cols-1 gap-4 xl:grid-cols-3">
                            {scoutingDesk && (scoutingDesk.watchlist_count > 0 || scoutingDesk.priority_targets.length > 0) && (
                                <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                                    <div className="mb-4 flex items-center justify-between gap-3">
                                        <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Scouting Desk</h3>
                                        <Link href={route('scouting.index')} className="text-[10px] font-black uppercase tracking-[0.14em] text-cyan-300 hover:text-white">
                                            {scoutingDesk.watchlist_count} Watchlist
                                        </Link>
                                    </div>
                                    <div className="mb-4 grid grid-cols-3 gap-2">
                                        <MiniDeskStat label="Faellig" value={scoutingDesk.due_reports_count} tone={scoutingDesk.due_reports_count > 0 ? 'amber' : 'emerald'} />
                                        <MiniDeskStat label="Teuer" value={scoutingDesk.expensive_missions_count} tone={scoutingDesk.expensive_missions_count > 0 ? 'rose' : 'slate'} />
                                        <MiniDeskStat label="Live" value={scoutingDesk.watchlist_count} tone="cyan" />
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
                                                    <div className="mt-1 text-[9px] font-black uppercase tracking-[0.14em] text-cyan-200/80">
                                                        {target.focus} / {target.scout_level} / {target.scout_type}
                                                    </div>
                                                    <div className="mt-1 text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                        {target.progress}% / ETA {target.next_report_due_at || '-'} / {new Intl.NumberFormat('de-DE').format(target.last_mission_cost || 0)} EUR
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-amber-200">{target.priority}</div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.12em] text-cyan-300">
                                                        {target.overall_band ? `OVR ${target.overall_band}` : 'Kein Report'}
                                                    </div>
                                                    <div className="mt-1 text-[9px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                                                        {target.scout_region} / {target.mission_days_left || 0}d
                                                    </div>
                                                </div>
                                            </Link>
                                        ))}
                                    </div>
                                </section>
                            )}

                            {medicalDesk && (medicalDesk.injured_count > 0 || medicalDesk.monitoring_count > 0 || medicalDesk.return_count > 0) && (
                                <section className="bg-[var(--bg-pillar)]/40 rounded-3xl border border-[var(--border-pillar)] p-6 shadow-xl">
                                    <div className="mb-4 flex items-center justify-between gap-3">
                                        <h3 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Medical Desk</h3>
                                        <Link href={route('medical.index')} className="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.14em] text-rose-200 hover:text-white">
                                            <FirstAidKit size={12} weight="fill" />
                                            {medicalDesk.injured_count} out
                                        </Link>
                                    </div>
                                    <div className="mb-4 grid grid-cols-3 gap-2">
                                        <MiniDeskStat label="Out" value={medicalDesk.injured_count} tone="rose" />
                                        <MiniDeskStat label="Monitor" value={medicalDesk.monitoring_count} tone="amber" />
                                        <MiniDeskStat label="Return" value={medicalDesk.return_count} tone="emerald" />
                                    </div>
                                    <div className="space-y-2.5">
                                        {medicalDesk.critical_cases.map((player) => (
                                            <Link
                                                key={player.id}
                                                href={route('players.show', player.id)}
                                                className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3 transition-colors hover:border-white/20"
                                            >
                                                <img src={player.photo_url} alt={player.name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
                                                <div className="min-w-0 flex-1">
                                                    <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.name}</div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                                                        {player.availability_status || player.medical_status} / Fatigue {player.fatigue}%
                                                    </div>
                                                </div>
                                                <div className="text-right">
                                                    <div className={`text-[9px] font-black uppercase tracking-[0.14em] ${
                                                        player.availability_status === 'available'
                                                            ? 'text-emerald-200'
                                                            : player.availability_status === 'limited' || player.availability_status === 'bench_only'
                                                                ? 'text-amber-200'
                                                                : 'text-rose-200'
                                                    }`}>
                                                        {player.availability_status || player.medical_status}
                                                    </div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.12em] text-cyan-300">
                                                        {player.expected_return ? `ETA ${player.expected_return}` : 'laufend'}
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
                        <CockpitPreferencesCard
                            preferences={{ ...preferences, variant: dashboardVariant || preferences.variant }}
                            widgets={dashboardWidgets}
                            onVariantChange={(variant) => persistDashboardPreferences({ variant })}
                            onToggleWidget={toggleWidgetVisibility}
                            onMoveWidget={moveWidget}
                        />
                        
                        <section className="rounded-3xl border border-emerald-400/12 bg-[linear-gradient(160deg,rgba(8,25,24,0.94),rgba(5,15,17,0.98))] p-5 shadow-[0_25px_50px_-30px_rgba(16,185,129,0.35)]">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-100/65">Live Matches</div>
                                    <div className="mt-1 text-3xl font-black tracking-tight text-white">{liveOverview.liveMatchesCount}</div>
                                </div>
                                {hasLiveTickerRoute ? (
                                    <Link href={route('live-ticker.index')} className="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-100 transition-colors hover:border-emerald-200/35 hover:text-white">
                                        <Broadcast size={12} weight="fill" />
                                        Live-Ticker
                                    </Link>
                                ) : null}
                            </div>
                            <div className="space-y-3">
                                {liveOverview.liveMatchesCount > 0 ? liveOverview.liveMatches.map((match) => (
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

                        {mainModuleWidgets.length > 0 && (
                            <section className="rounded-3xl border border-white/10 bg-[var(--bg-pillar)]/35 p-5 shadow-xl">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Module Highlights</div>
                                        <div className="mt-1 text-xl font-black text-white">Enabled feature surfaces</div>
                                    </div>
                                    <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                        {mainModuleWidgets.length} active
                                    </div>
                                </div>
                                <div className="grid gap-3 md:grid-cols-2">
                                    {mainModuleWidgets.map((widget) => (
                                        <ModuleWidgetCard key={widget.key} widget={widget} />
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
                                    <div className="mt-1 text-3xl font-black tracking-tight text-white">{liveOverview.onlineManagersCount}</div>
                                </div>
                                {hasManagerLiveRoute ? (
                                    <Link href={route('manager-live.index')} className="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-100 transition-colors hover:border-cyan-200/35 hover:text-white">
                                        <UsersThree size={12} weight="fill" />
                                        Manager Online
                                    </Link>
                                ) : null}
                            </div>
                            <div className="space-y-3">
                                {liveOverview.onlineManagersCount > 0 ? liveOverview.onlineManagers.map((manager) => (
                                    <ManagerLiveRow key={manager.id} manager={manager} />
                                )) : (
                                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-6 text-center text-sm text-[var(--text-muted)]">
                                        Aktuell ist kein Manager online.
                                    </div>
                                )}
                            </div>
                        </section>

                        {sidebarModuleWidgets.length > 0 && (
                            <section className="rounded-3xl border border-white/10 bg-[var(--bg-pillar)]/35 p-5 shadow-xl">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Module Shortcuts</div>
                                        <div className="mt-1 text-lg font-black text-white">Sidebar hooks</div>
                                    </div>
                                </div>
                                <div className="space-y-3">
                                    {sidebarModuleWidgets.map((widget) => (
                                        <ModuleWidgetCard key={widget.key} widget={widget} />
                                    ))}
                                </div>
                            </section>
                        )}

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

function ClubSide({ club, align = 'left' }) {
    return (
        <div className={`rounded-[24px] border border-white/8 bg-[linear-gradient(180deg,rgba(13,28,45,0.88),rgba(9,20,33,0.96))] p-4 ${align === 'right' ? 'md:text-right' : ''}`}>
            <div className={`flex items-center gap-4 ${align === 'right' ? 'md:flex-row-reverse' : ''}`}>
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-cyan-300/10 bg-[var(--bg-content)]/65 p-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                <img className="h-full w-full object-contain" src={club.logo_url} alt={club.name} />
            </div>
            <div className="min-w-0">
                <div className="truncate text-lg font-black uppercase tracking-[0.04em] text-white">{club.name}</div>
                <div className="mt-1 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-100/65">
                    {align === 'right' ? 'Away' : 'Home'}
                </div>
                <div className="mt-3 h-px w-16 bg-gradient-to-r from-cyan-200/25 to-transparent" />
            </div>
            </div>
        </div>
    );
}

function ReadyBadge({ ready, label }) {
    return (
        <div className={`inline-flex w-full items-center justify-between gap-3 rounded-2xl border px-4 py-3 ${
            ready
                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-200'
                : 'border-rose-500/20 bg-rose-500/10 text-rose-200'
        }`}>
            <div className="flex items-center gap-2">
                <div className={`h-2 w-2 rounded-full ${ready ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.8)]' : 'bg-rose-400 shadow-[0_0_10px_rgba(251,113,133,0.7)]'}`} />
                <span className="text-[10px] font-black uppercase tracking-[0.14em]">{label}</span>
            </div>
            <span className="text-[10px] font-black uppercase tracking-[0.14em] text-white/65">
                {ready ? 'Ready' : 'Pending'}
            </span>
        </div>
    );
}

function MiniDeskStat({ label, value, tone = 'slate' }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{label}</div>
            <div className={`mt-2 text-xl font-black ${
                tone === 'rose'
                    ? 'text-rose-200'
                    : tone === 'amber'
                        ? 'text-amber-200'
                        : tone === 'emerald'
                            ? 'text-emerald-200'
                            : 'text-white'
            }`}>
                {value}
            </div>
        </div>
    );
}
