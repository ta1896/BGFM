import React, { Suspense, lazy, useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import { ArrowRight } from '@phosphor-icons/react/ArrowRight';
import { Bank } from '@phosphor-icons/react/Bank';
import { Calendar } from '@phosphor-icons/react/Calendar';
import { ChartBar } from '@phosphor-icons/react/ChartBar';
import { Lightning } from '@phosphor-icons/react/Lightning';
import { Smiley } from '@phosphor-icons/react/Smiley';
import { SmileySad } from '@phosphor-icons/react/SmileySad';
import { Trophy } from '@phosphor-icons/react/Trophy';

const DashboardDeferredSections = lazy(() => import('@/Pages/DashboardDeferredSections'));

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
                    <span className="text-2xl font-bold tracking-tight text-[var(--text-main)]">{value}</span>
                    {subValue ? <span className="text-xs font-semibold text-[var(--text-muted)]">{subValue}</span> : null}
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
            {isToday ? (
                <div className="absolute right-1.5 top-1.5 z-10 h-3 w-3 rounded-full bg-amber-500 shadow-[0_0_12px_rgba(217,177,92,0.8)]" />
            ) : null}

            <div>
                <p className={`text-[10px] font-bold uppercase tracking-widest ${isToday ? 'text-amber-500' : 'text-[var(--text-muted)]'}`}>
                    {day.label}
                </p>
                <p className="mt-1 text-xl font-bold text-[var(--text-main)]">{day.date}</p>
            </div>

            <div className="mt-6 flex flex-col gap-2">
                {hasMatch ? (
                    <div className="flex items-center gap-2 rounded-lg border border-amber-600/20 bg-amber-600/10 px-2.5 py-1.5">
                        <Calendar size={14} weight="fill" className="text-amber-600" />
                        <span className="text-[10px] font-bold uppercase text-amber-600">{day.match_count} Match</span>
                    </div>
                ) : null}
                {hasTraining ? (
                    <div className="flex items-center gap-2 rounded-lg border border-amber-500/20 bg-amber-500/10 px-2.5 py-1.5">
                        <ChartBar size={14} weight="fill" className="text-amber-500" />
                        <span className="text-[10px] font-bold uppercase text-amber-500">{day.training_count} Session</span>
                    </div>
                ) : null}
                {!hasMatch && !hasTraining ? (
                    <span className="px-1 py-1.5 text-[10px] font-medium text-slate-600">Rest Day</span>
                ) : null}
            </div>
        </div>
    );
};

function DeferredDashboardSkeleton() {
    return (
        <>
            <PageReveal className="space-y-4 lg:col-span-8" delay={120}>
                <section className="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <div className="min-h-[16rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
                    <div className="min-h-[16rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
                    <div className="min-h-[16rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
                </section>
            </PageReveal>
            <PageReveal className="space-y-4 lg:col-span-4" delay={180}>
                <div className="min-h-[13rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
                <div className="min-h-[22rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
                <div className="min-h-[18rem] animate-pulse rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30" />
            </PageReveal>
        </>
    );
}

export default function Dashboard(props) {
    const {
        activeClub,
        nextMatch,
        nextMatchTypeLabel,
        activeClubReadyForNextMatch,
        opponentReadyForNextMatch,
        clubRank,
        clubPoints,
        weekDays,
        todayMatchesCount,
        dashboardVariant,
        assistantTasks,
        clubPulseOverview,
        quickActions,
        squadPulse,
        medicalDesk,
        managerDecisions,
        liveMatches,
        onlineManagers,
        recentMatchesSummary,
    } = props;

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <Head title="Welcome" />
                <div className="mx-auto max-w-4xl py-12 text-center">
                    <div className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-12">
                        <Trophy size={64} weight="duotone" className="mx-auto mb-6 text-amber-500" />
                        <h1 className="mb-4 text-4xl font-bold text-white">Start Your Career</h1>
                        <p className="mx-auto mb-8 max-w-xl text-lg text-[var(--text-muted)]">
                            You don't have an active club yet. Take over a team today and lead them to glory.
                        </p>
                        <Link
                            href={route('clubs.free')}
                            className="inline-flex items-center gap-3 rounded-xl bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] px-8 py-4 text-sm font-bold uppercase tracking-widest text-black shadow-xl shadow-amber-900/40 transition-all hover:scale-105"
                        >
                            View Available Clubs
                            <ArrowRight size={20} weight="bold" />
                        </Link>
                    </div>
                </div>
            </AuthenticatedLayout>
        );
    }

    const fanMood = Math.max(0, Math.min(100, parseInt(activeClub.fan_mood || 50, 10)));
    const [deferNonCritical, setDeferNonCritical] = useState(false);

    useEffect(() => {
        let timeoutId = null;
        let idleId = null;

        const enableDeferredContent = () => setDeferNonCritical(true);

        if ('requestIdleCallback' in window) {
            idleId = window.requestIdleCallback(enableDeferredContent, { timeout: 1200 });
        } else {
            timeoutId = window.setTimeout(enableDeferredContent, 700);
        }

        return () => {
            if (idleId !== null && 'cancelIdleCallback' in window) {
                window.cancelIdleCallback(idleId);
            }

            if (timeoutId !== null) {
                window.clearTimeout(timeoutId);
            }
        };
    }, []);

    return (
        <AuthenticatedLayout>
            <Head title="Dashboard" />

            <div className="space-y-8">
                <StaggerGroup className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
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
                        <div className="h-[104px] rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl backdrop-blur-md">
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

                <div className="grid grid-cols-1 gap-8 lg:grid-cols-12">
                    <PageReveal className="space-y-8 lg:col-span-8" delay={90}>
                        <section>
                            <div className="mb-4 flex items-center justify-between">
                                <h2 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Weekly Overview</h2>
                                <div className="flex gap-4">
                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-gray-500">
                                        <span className="h-2 w-2 rounded-full bg-amber-600" /> Match
                                    </span>
                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-wider text-gray-500">
                                        <span className="h-2 w-2 rounded-full bg-amber-400" /> Training
                                    </span>
                                </div>
                            </div>
                            <div className="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7">
                                {weekDays.map((day, idx) => (
                                    <TimelineDay key={idx} day={day} />
                                ))}
                            </div>
                        </section>

                        <section className="overflow-hidden rounded-[28px] border border-cyan-400/14 bg-[linear-gradient(180deg,rgba(8,19,34,0.98),rgba(7,18,31,0.92))] shadow-[0_24px_60px_-38px_rgba(8,145,178,0.35)]">
                            <div className="border-b border-white/6 bg-[linear-gradient(90deg,rgba(34,211,238,0.08),rgba(217,177,92,0.04),transparent)] px-6 py-5">
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <h2 className="text-xs font-black uppercase tracking-[0.18em] text-cyan-100/70">Next Fixture</h2>
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

                        <section className="grid gap-4">
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
                    </PageReveal>

                    {deferNonCritical ? (
                        <Suspense fallback={<DeferredDashboardSkeleton />}>
                            <DashboardDeferredSections
                                activeClub={activeClub}
                                assistantTasks={assistantTasks}
                                medicalDesk={medicalDesk}
                                squadPulse={squadPulse}
                                managerDecisions={managerDecisions}
                                liveMatches={liveMatches}
                                onlineManagers={onlineManagers}
                                recentMatchesSummary={recentMatchesSummary}
                            />
                        </Suspense>
                    ) : (
                        <DeferredDashboardSkeleton />
                    )}
                </div>
            </div>
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
