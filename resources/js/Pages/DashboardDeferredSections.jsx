import React from 'react';
import { Link } from '@inertiajs/react';
import { PageReveal } from '@/Components/PageReveal';
import useLiveOverview from '@/hooks/useLiveOverview';
import { ArrowRight } from '@phosphor-icons/react/ArrowRight';
import { Broadcast } from '@phosphor-icons/react/Broadcast';
import { ChatCircleText } from '@phosphor-icons/react/ChatCircleText';
import { FirstAidKit } from '@phosphor-icons/react/FirstAidKit';
import { FlagPennant } from '@phosphor-icons/react/FlagPennant';
import { Handshake } from '@phosphor-icons/react/Handshake';
import { UsersThree } from '@phosphor-icons/react/UsersThree';

const taskPriorityTone = {
    sofort: 'border-rose-400/20 bg-rose-400/10 text-rose-200',
    heute: 'border-amber-400/20 bg-amber-400/10 text-amber-200',
    beobachten: 'border-slate-400/20 bg-slate-400/10 text-slate-200',
};

const formTone = {
    S: 'bg-emerald-500 text-black',
    U: 'bg-slate-100 text-black',
    N: 'bg-red-600 text-white',
};

const ManagerLiveRow = ({ manager }) => (
    <div className="group flex items-center gap-3 rounded-2xl border border-cyan-400/10 bg-[linear-gradient(135deg,rgba(17,30,48,0.85),rgba(11,22,36,0.95))] px-3 py-3 transition-all hover:-translate-y-0.5 hover:border-cyan-300/25 hover:shadow-[0_14px_30px_-18px_rgba(34,211,238,0.45)]">
        <div className="relative">
            {manager.club?.logo_url ? (
                <img loading="lazy" src={manager.club.logo_url} alt={manager.club.name} className="h-11 w-11 rounded-2xl border border-white/10 bg-white/[0.04] object-contain p-1.5" />
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

export default function DashboardDeferredSections({
    activeClub,
    assistantTasks,
    medicalDesk,
    squadPulse,
    managerDecisions,
    liveMatches,
    onlineManagers,
    recentMatchesSummary,
}) {
    const liveOverview = useLiveOverview({
        initialLiveMatches: liveMatches,
        initialOnlineManagers: onlineManagers,
        enabled: true,
    });
    const hasManagerLiveRoute = route().has('manager-live.index');
    const hasLiveTickerRoute = route().has('live-ticker.index');

    return (
        <PageReveal className="space-y-8 lg:col-span-4" delay={180}>
            <section className="grid grid-cols-1 gap-4">
                    {medicalDesk && (medicalDesk.injured_count > 0 || medicalDesk.monitoring_count > 0 || medicalDesk.return_count > 0) ? (
                        <section className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl">
                            <div className="mb-4 flex items-center justify-between gap-3">
                                <h2 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Medical Desk</h2>
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
                                    <Link key={player.id} href={route('players.show', player.id)} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3 transition-colors hover:border-white/20">
                                        <img loading="lazy" src={player.photo_url} alt={player.name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
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
                    ) : null}

                    {squadPulse && (squadPulse.manual_roles_count > 0 || squadPulse.promise_pressure_count > 0) ? (
                        <section className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl">
                            <h2 className="mb-4 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Squad Pulse</h2>
                            <div className="space-y-4">
                                {squadPulse.manual_roles_count > 0 ? (
                                    <div>
                                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-fuchsia-200">
                                            <FlagPennant size={12} weight="fill" />
                                            {squadPulse.manual_roles_count} manuelle Rollen
                                        </div>
                                        <div className="space-y-2">
                                            {squadPulse.manual_role_players.map((player) => (
                                                <Link key={player.id} href={route('players.show', player.id)} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 transition-colors hover:border-fuchsia-400/30">
                                                    <img loading="lazy" src={player.photo_url} alt={player.full_name} className="h-9 w-9 rounded-xl border border-white/10 object-cover" />
                                                    <div className="min-w-0 flex-1">
                                                        <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.full_name}</div>
                                                        <div className="text-[10px] font-black uppercase tracking-[0.12em] text-fuchsia-200">{player.squad_role}</div>
                                                    </div>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                {squadPulse.promise_pressure_count > 0 ? (
                                    <div>
                                        <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-amber-200">
                                            <Handshake size={12} weight="fill" />
                                            {squadPulse.promise_pressure_count} Promise-Konflikte
                                        </div>
                                        <div className="space-y-2">
                                            {squadPulse.pressure_players.map((player) => (
                                                <Link key={player.id} href={route('players.show', player.id)} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 transition-colors hover:border-amber-400/30">
                                                    <img loading="lazy" src={player.photo_url} alt={player.full_name} className="h-9 w-9 rounded-xl border border-white/10 object-cover" />
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
                                ) : null}
                            </div>
                        </section>
                    ) : null}
            </section>

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

                <section className="min-h-[22rem] rounded-3xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/34 p-5">
                    <div className="mb-4 flex items-center justify-between gap-3">
                        <div>
                            <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Priorities</div>
                            <div className="mt-1 text-lg font-black text-white">What needs attention</div>
                        </div>
                        {assistantTasks?.length > 0 ? (
                            <div className="rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/70">
                                {assistantTasks.length} open
                            </div>
                        ) : null}
                    </div>
                    <div className="space-y-3">
                        {(assistantTasks || []).slice(0, 3).map((task, idx) => (
                            <Link key={idx} href={task.url} className="flex items-start justify-between gap-4 rounded-2xl border border-white/10 bg-[var(--bg-content)]/45 px-4 py-3 transition-colors hover:border-white/20">
                                <div className="min-w-0">
                                    <div className="mb-2 flex flex-wrap items-center gap-2">
                                        <span className={`rounded-full px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${taskPriorityTone[task.priority] || taskPriorityTone.beobachten}`}>
                                            {task.priority || 'beobachten'}
                                        </span>
                                        {task.metric ? (
                                            <span className="rounded-full border border-white/10 bg-white/[0.04] px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-white/80">
                                                {task.metric}
                                            </span>
                                        ) : null}
                                    </div>
                                    <div className="text-sm font-black text-white">{task.label}</div>
                                    <div className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{task.description}</div>
                                </div>
                                <ArrowRight size={14} className="mt-1 shrink-0 text-white/50" weight="bold" />
                            </Link>
                        ))}
                        {(!assistantTasks || assistantTasks.length === 0) ? (
                            <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">
                                No urgent tasks right now.
                            </div>
                        ) : null}
                    </div>
                </section>

                <section className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl">
                    <div className="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Die letzten 5 Spiele</h2>
                        </div>
                        <Link href={route('league.matches')} className="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-white/80 transition-colors hover:border-white/20 hover:text-white">
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
                                    <Link key={match.id ?? `${match.opponent_name}-${idx}`} href={match?.id ? route('matches.show', match.id) : route('league.matches')} className="group relative flex flex-col items-center gap-2" title={match?.id ? `Zum Match gegen ${match.opponent_name}` : 'Zu allen Terminen'}>
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
                                                <img loading="lazy" src={match.opponent_logo_url} alt={match.opponent_name} className="h-full w-full object-contain" />
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

                {managerDecisions?.length > 0 ? (
                    <section className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 shadow-xl">
                        <h2 className="mb-4 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Manager Decisions</h2>
                        <div className="space-y-3">
                            {managerDecisions.map((decision, idx) => (
                                <Link key={`${decision.kind}-${decision.player_id}-${idx}`} href={route('players.show', decision.player_id)} className="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3 transition-colors hover:border-white/20">
                                    <img loading="lazy" src={decision.photo_url} alt={decision.player_name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
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
                ) : null}

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
        </PageReveal>
    );
}
