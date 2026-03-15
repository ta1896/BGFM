import React, { useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import {
    Crown,
    ShieldCheck,
    Smiley,
    WarningCircle,
    TrendUp,
    UsersThree,
    ArrowsClockwise,
    Funnel,
    ChatCircleText,
    X,
    Handshake,
    FlagPennant,
} from '@phosphor-icons/react';

const levelStyles = {
    apex: {
        width: 'max-w-xl',
        tone: 'from-amber-500/16 via-amber-400/10 to-transparent',
        border: 'border-amber-400/20',
        glow: 'shadow-[0_18px_50px_rgba(245,158,11,0.08)]',
    },
    core: {
        width: 'max-w-2xl',
        tone: 'from-cyan-500/14 via-cyan-400/8 to-transparent',
        border: 'border-cyan-400/20',
        glow: 'shadow-[0_18px_50px_rgba(34,211,238,0.06)]',
    },
    rotation: {
        width: 'max-w-3xl',
        tone: 'from-sky-500/12 via-sky-400/8 to-transparent',
        border: 'border-sky-400/20',
        glow: 'shadow-[0_18px_50px_rgba(56,189,248,0.05)]',
    },
    development: {
        width: 'max-w-4xl',
        tone: 'from-emerald-500/12 via-emerald-400/8 to-transparent',
        border: 'border-emerald-400/20',
        glow: 'shadow-[0_18px_50px_rgba(16,185,129,0.05)]',
    },
    fringe: {
        width: 'max-w-5xl',
        tone: 'from-slate-500/10 via-slate-400/6 to-transparent',
        border: 'border-slate-400/15',
        glow: 'shadow-[0_18px_50px_rgba(15,23,42,0.24)]',
    },
};

const moodStyles = {
    happy: 'border-emerald-400/25 bg-emerald-400/10 text-emerald-200',
    steady: 'border-cyan-400/25 bg-cyan-400/10 text-cyan-200',
    unsettled: 'border-rose-400/25 bg-rose-400/10 text-rose-200',
};

const fitStyles = {
    aligned: 'border-emerald-400/25 bg-emerald-400/10 text-emerald-200',
    watching: 'border-amber-400/25 bg-amber-400/10 text-amber-200',
    critical: 'border-rose-400/25 bg-rose-400/10 text-rose-200',
};

const signalStyles = {
    manual: 'border-fuchsia-400/25 bg-fuchsia-400/10 text-fuchsia-200',
    promise: 'border-amber-400/25 bg-amber-400/10 text-amber-200',
};

export default function Hierarchy({ clubs, activeClub, hierarchyLevels, summary, hierarchyInsights }) {
    const { features } = usePage().props;
    const playerConversationsEnabled = !!features?.player_conversations_enabled;
    const [activeFilter, setActiveFilter] = useState('all');
    const [tooltip, setTooltip] = useState(null);
    const conversationForm = useForm({
        topic: 'morale',
        approach: 'supportive',
        manager_message: '',
    });
    const roleForm = useForm({
        squad_role: 'rotation',
    });
    const promiseForm = useForm({
        template: 'rotation',
    });

    const allPlayers = useMemo(
        () => hierarchyLevels.flatMap((level) => level.players.map((player) => ({ ...player, levelLabel: level.label }))),
        [hierarchyLevels],
    );

    const filteredLevels = useMemo(
        () =>
            hierarchyLevels.map((level) => ({
                ...level,
                players: level.players.filter((player) => matchesFilter(player, activeFilter)),
            })),
        [hierarchyLevels, activeFilter],
    );

    return (
        <AuthenticatedLayout>
            <Head title="Kaderhierarchie" />

            <div className="mx-auto max-w-[1680px] space-y-6">
                <PageHeader
                    eyebrow="Squad Dynamics"
                    title="Kaderhierarchie"
                    actions={
                        <div className="flex flex-wrap items-center gap-3">
                            <select
                                value={activeClub?.id ?? ''}
                                onChange={(event) => router.get(route('squad-hierarchy.index'), { club: event.target.value })}
                                className="sim-select min-w-[220px] py-2.5 text-[11px] font-black uppercase tracking-[0.16em]"
                            >
                                {clubs.map((club) => (
                                    <option key={club.id} value={club.id}>
                                        {club.name}
                                    </option>
                                ))}
                            </select>
                            <Link
                                href={route('players.index')}
                                className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)] px-4 py-2.5 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)] transition-colors hover:text-white"
                            >
                                Zur Kaderliste
                            </Link>
                        </div>
                    }
                />

                <div className="space-y-4">
                    <PageReveal className="space-y-4">
                        <div className="grid gap-3 md:grid-cols-4">
                            <SummaryCard icon={Smiley} label="Zufrieden" value={summary.satisfied_count} tone="emerald" />
                            <SummaryCard icon={WarningCircle} label="Unruhig" value={summary.unsettled_count} tone="rose" />
                            <SummaryCard icon={ShieldCheck} label="Rolle passt" value={summary.fair_role_count} tone="cyan" />
                            <SummaryCard icon={TrendUp} label="Kritisch" value={summary.critical_role_count} tone="amber" />
                        </div>

                        <div className="sim-card border-[var(--border-pillar)] bg-[var(--bg-content)]/80 p-3.5">
                            <div className="mb-2.5 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                <Funnel size={14} weight="bold" />
                                Fokusfilter
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {[
                                    ['all', 'Alle'],
                                    ['unsettled', 'Nur Unruhe'],
                                    ['critical', 'Nur Rollenprobleme'],
                                    ['captains', 'Nur Kapitaensgruppe'],
                                ].map(([value, label]) => (
                                    <button
                                        key={value}
                                        type="button"
                                        onClick={() => setActiveFilter(value)}
                                        className={`rounded-full border px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.14em] transition-colors ${
                                            activeFilter === value
                                                ? 'border-[var(--accent-primary)] bg-[var(--accent-primary)]/12 text-[var(--accent-primary)]'
                                                : 'border-white/10 bg-white/[0.03] text-[var(--text-muted)] hover:text-white'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="sim-card overflow-hidden border-[var(--border-pillar)] bg-[radial-gradient(circle_at_top,rgba(16,185,129,0.08),transparent_34%),linear-gradient(180deg,rgba(12,18,34,0.95),rgba(7,12,26,0.96))] p-4">
                            <div className="mb-4 flex items-center justify-between gap-4">
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Pyramide</div>
                                    <h2 className="text-xl font-black uppercase italic tracking-tight text-white">Kabinenordnung & Rollendruck</h2>
                                </div>
                                {activeClub && (
                                    <div className="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.04] px-3 py-2">
                                        <img src={activeClub.logo_url} alt={activeClub.name} className="h-8 w-8 object-contain" />
                                        <div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Aktiver Verein</div>
                                            <div className="text-sm font-black uppercase tracking-[0.08em] text-white">{activeClub.name}</div>
                                        </div>
                                    </div>
                                )}
                            </div>

                            <div className="space-y-3">
                                {filteredLevels.map((level, index) => {
                                    const style = levelStyles[level.key] ?? levelStyles.fringe;

                                    return (
                                        <div key={level.key} className="flex justify-center">
                                            <section
                                                className={`w-full ${style.width} rounded-[26px] border bg-gradient-to-b ${style.tone} ${style.border} ${style.glow} px-4 py-4`}
                                            >
                                                <div className="mb-3 flex items-center justify-between gap-4">
                                                    <div>
                                                        <div className="text-[9px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                                                            Ebene {index + 1}
                                                        </div>
                                                        <div className="text-base font-black uppercase tracking-tight text-white">{level.label}</div>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="text-[9px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Spieler</div>
                                                        <div className="text-xl font-black text-white">{level.players.length}</div>
                                                    </div>
                                                </div>

                                                <p className="mb-3 max-w-2xl text-[13px] font-medium leading-relaxed text-slate-300">{level.description}</p>

                                                {level.players.length ? (
                                                    <div className="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                                                        {level.players.map((player) => (
                                                            <button
                                                                key={player.id}
                                                                type="button"
                                                                onMouseEnter={(event) => updateTooltip(setTooltip, player, event)}
                                                                onMouseMove={(event) => updateTooltip(setTooltip, player, event)}
                                                                onMouseLeave={() => clearTooltip(setTooltip)}
                                                                onFocus={(event) => updateTooltip(setTooltip, player, event)}
                                                                onBlur={() => clearTooltip(setTooltip)}
                                                                onClick={(event) => pinTooltip(setTooltip, player, event)}
                                                                className={`min-w-0 rounded-2xl border p-2.5 text-left transition-all ${
                                                                    tooltip?.player?.id === player.id
                                                                        ? 'border-[var(--accent-primary)] bg-white/[0.08] shadow-[0_0_0_1px_rgba(255,255,255,0.06)]'
                                                                        : player.role_override_active
                                                                            ? 'border-fuchsia-400/35 bg-fuchsia-400/[0.05] hover:border-fuchsia-300/60 hover:bg-fuchsia-400/[0.07]'
                                                                            : 'border-white/8 bg-white/[0.03] hover:border-white/20 hover:bg-white/[0.05]'
                                                                }`}
                                                            >
                                                                <div className="flex items-start gap-3">
                                                                    <div className="relative shrink-0">
                                                                        <img
                                                                            src={player.photo_url}
                                                                            alt={player.full_name}
                                                                            className="h-12 w-12 rounded-xl border border-white/10 object-cover"
                                                                        />
                                                                        <div className="absolute -bottom-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-md bg-slate-950 px-1 text-[9px] font-black text-amber-300">
                                                                            {player.overall}
                                                                        </div>
                                                                    </div>

                                                                    <div className="min-w-0 flex-1 overflow-hidden">
                                                                        <div className="truncate text-[13px] font-black tracking-[0.02em] text-white">
                                                                            {player.full_name}
                                                                        </div>
                                                                        <div className="mt-0.5 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-[9px] font-black tracking-[0.08em] text-[var(--text-muted)]">
                                                                            <span className="uppercase">{player.position}</span>
                                                                            <span className="text-slate-600">/</span>
                                                                            <span className="min-w-0 flex-1 uppercase leading-relaxed [overflow-wrap:anywhere]">
                                                                                {player.squad_role_label}
                                                                            </span>
                                                                        </div>
                                                                        <div className="mt-2 flex flex-wrap items-start gap-1.5">
                                                                            {player.role_override_active && (
                                                                                <Tag className={signalStyles.manual} compact>
                                                                                    Manuell
                                                                                </Tag>
                                                                            )}
                                                                            {player.promise && (
                                                                                <Tag className={signalStyles.promise} compact>
                                                                                    Promise
                                                                                </Tag>
                                                                            )}
                                                                            <Tag className={moodStyles[player.mood.status]} compact>
                                                                                {player.mood.label}
                                                                            </Tag>
                                                                            <Tag className={fitStyles[player.role_fit.status]} compact>
                                                                                {player.role_fit.label}
                                                                            </Tag>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </button>
                                                        ))}
                                                    </div>
                                                ) : (
                                                    <div className="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] px-4 py-6 text-center text-sm font-medium text-[var(--text-muted)]">
                                                        Keine Spieler auf dieser Ebene.
                                                    </div>
                                                )}
                                            </section>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </PageReveal>

                    <PageReveal delay={90}>
                        <div className="grid gap-3 xl:grid-cols-3">
                            <RadarPanel
                                title="Kapitaensgruppe"
                                items={hierarchyInsights.captain_group}
                                emptyLabel="Keine Fuehrungsspieler erkannt."
                                tone="amber"
                                setTooltip={setTooltip}
                            />
                            <RadarPanel
                                title="Unruheherde"
                                items={hierarchyInsights.unsettled_players}
                                emptyLabel="Keine akuten Unruheherde."
                                tone="rose"
                                setTooltip={setTooltip}
                            />
                            <RadarPanel
                                title="Rollen-Konflikte"
                                items={hierarchyInsights.role_conflicts}
                                emptyLabel="Keine kritischen Rollenkonflikte."
                                tone="cyan"
                                setTooltip={setTooltip}
                            />
                        </div>
                    </PageReveal>
                </div>
            </div>

            {tooltip?.player && (
                <PlayerTooltip
                    tooltip={tooltip}
                    onClose={() => setTooltip(null)}
                    onQuickConversation={(payload) => {
                        conversationForm.transform(() => payload).post(route('players.conversations.store', tooltip.player.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                setTooltip((current) => current ? { ...current, pinned: false } : current);
                                conversationForm.reset();
                            },
                        });
                    }}
                    processing={conversationForm.processing}
                    playerConversationsEnabled={playerConversationsEnabled}
                    roleForm={roleForm}
                    promiseForm={promiseForm}
                    onRoleChange={(payload) => {
                        roleForm.transform(() => payload).post(route('players.hierarchy-role.update', tooltip.player.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                setTooltip((current) => current ? { ...current, pinned: false } : current);
                                roleForm.reset();
                            },
                        });
                    }}
                    onQuickPromise={(payload) => {
                        promiseForm.transform(() => payload).post(route('players.quick-promise.store', tooltip.player.id), {
                            preserveScroll: true,
                            onSuccess: () => {
                                setTooltip((current) => current ? { ...current, pinned: false } : current);
                                promiseForm.reset();
                            },
                        });
                    }}
                />
            )}
        </AuthenticatedLayout>
    );
}

function SummaryCard({ icon: Icon, label, value, tone }) {
    const toneMap = {
        emerald: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
        rose: 'border-rose-400/20 bg-rose-400/10 text-rose-300',
        cyan: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-300',
        amber: 'border-amber-400/20 bg-amber-400/10 text-amber-300',
    };

    return (
        <div className="sim-card border-[var(--border-pillar)] bg-[var(--bg-content)]/80 p-3.5">
            <div className="mb-2.5 flex items-center gap-2.5">
                <div className={`rounded-lg border p-2 ${toneMap[tone]}`}>
                    <Icon size={16} weight="duotone" />
                </div>
                <div className="text-[9px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{label}</div>
            </div>
            <div className="text-[28px] font-black uppercase tracking-tight text-white">{value}</div>
        </div>
    );
}

function Tag({ className, children, compact = false }) {
    return (
        <span
            className={`inline-flex max-w-full items-center rounded-full border font-black uppercase ${compact ? 'px-2.5 py-1 text-[8px] leading-snug tracking-[0.08em] [overflow-wrap:anywhere]' : 'px-2.5 py-1 text-[9px] tracking-[0.18em]'} ${className}`}
        >
            {children}
        </span>
    );
}

function MiniStat({ label, value }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.04] px-3 py-3">
            <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{label}</div>
            <div className="mt-1 text-lg font-black text-white">{value}</div>
        </div>
    );
}

function StatusRow({ label, value, className }) {
    return (
        <div className="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.04] px-3 py-2">
            <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{label}</div>
            <span className={`inline-flex items-center rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.18em] ${className}`}>
                {value}
            </span>
        </div>
    );
}

function LoadBar({ label, value, color }) {
    return (
        <div className="space-y-1.5">
            <div className="flex items-center justify-between text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                <span>{label}</span>
                <span>{value}%</span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-white/8">
                <div className={`h-full rounded-full ${color}`} style={{ width: `${Math.max(0, Math.min(100, value))}%` }} />
            </div>
        </div>
    );
}

function RadarPanel({ title, items, emptyLabel, tone, setTooltip }) {
    const toneMap = {
        amber: 'border-amber-400/20 bg-amber-400/10 text-amber-200',
        rose: 'border-rose-400/20 bg-rose-400/10 text-rose-200',
        cyan: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200',
    };

    return (
        <div className="rounded-[22px] border border-white/10 bg-white/[0.03] p-3.5">
            <div className="mb-2.5 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{title}</div>
            {items?.length ? (
                <div className="space-y-2">
                    {items.map((player) => (
                        <button
                            key={player.id}
                            type="button"
                            onMouseEnter={(event) => updateTooltip(setTooltip, player, event)}
                            onMouseMove={(event) => updateTooltip(setTooltip, player, event)}
                            onMouseLeave={() => clearTooltip(setTooltip)}
                            onFocus={(event) => updateTooltip(setTooltip, player, event)}
                            onBlur={() => clearTooltip(setTooltip)}
                            onClick={(event) => pinTooltip(setTooltip, player, event)}
                            className="flex w-full items-center gap-3 rounded-2xl border border-white/8 bg-white/[0.03] px-3 py-2 text-left transition-colors hover:border-white/20 hover:bg-white/[0.05]"
                        >
                            <img src={player.photo_url} alt={player.full_name} className="h-9 w-9 rounded-xl border border-white/10 object-cover" />
                            <div className="min-w-0 flex-1">
                                <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.full_name}</div>
                                <div className="mt-1 flex items-center gap-2">
                                    <span className={`inline-flex items-center rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-[0.12em] ${toneMap[tone]}`}>
                                        {player.mood?.label ?? player.role_fit?.label ?? player.leadership_label}
                                    </span>
                                </div>
                            </div>
                        </button>
                    ))}
                </div>
            ) : (
                <div className="rounded-2xl border border-dashed border-white/10 bg-white/[0.02] px-3 py-4 text-sm text-[var(--text-muted)]">
                    {emptyLabel}
                </div>
            )}
        </div>
    );
}

function matchesFilter(player, filter) {
    if (filter === 'unsettled') {
        return player.mood.status === 'unsettled';
    }

    if (filter === 'critical') {
        return ['watching', 'critical'].includes(player.role_fit.status);
    }

    if (filter === 'captains') {
        return player.leadership_level === 'captain_group';
    }

    return true;
}

function updateTooltip(setTooltip, player, event) {
    setTooltip((current) => {
        if (current?.pinned && current.player?.id === player.id) {
            return current;
        }

        return {
            player,
            x: event.clientX,
            y: event.clientY,
            pinned: false,
        };
    });
}

function pinTooltip(setTooltip, player, event) {
    setTooltip({
        player,
        x: event.clientX,
        y: event.clientY,
        pinned: true,
    });
}

function clearTooltip(setTooltip) {
    setTooltip((current) => current?.pinned ? current : null);
}

function PlayerTooltip({ tooltip, onClose, onQuickConversation, processing, playerConversationsEnabled, roleForm, promiseForm, onRoleChange, onQuickPromise }) {
    const { player, x, y, pinned } = tooltip;
    const quickActions = [
        {
            label: 'Beruhigen',
            helper: 'Stimmung',
            payload: { topic: 'morale', approach: 'supportive', manager_message: '' },
            className: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200',
        },
        {
            label: 'Rolle klaeren',
            helper: 'Rolle',
            payload: { topic: 'role', approach: 'honest', manager_message: '' },
            className: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200',
        },
        {
            label: 'Last senken',
            helper: 'Belastung',
            payload: { topic: 'load', approach: 'protective', manager_message: '' },
            className: 'border-amber-400/20 bg-amber-400/10 text-amber-200',
        },
    ];
    const roleActions = [
        { label: 'Auto', helper: 'System', payload: { squad_role: 'auto' }, className: 'border-white/10 bg-white/[0.05] text-slate-200' },
        { label: 'Star', helper: 'Toprolle', payload: { squad_role: 'star_player' }, className: 'border-amber-400/20 bg-amber-400/10 text-amber-200' },
        { label: 'Stamm', helper: 'Kernrolle', payload: { squad_role: 'important_first_team' }, className: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200' },
        { label: 'Rotation', helper: 'Breite', payload: { squad_role: 'rotation' }, className: 'border-sky-400/20 bg-sky-400/10 text-sky-200' },
        { label: 'Talent', helper: 'Aufbau', payload: { squad_role: 'prospect' }, className: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200' },
    ];
    const promiseActions = [
        { label: 'Starter zusagen', helper: '8 Wochen', payload: { template: 'starter' }, className: 'border-amber-400/20 bg-amber-400/10 text-amber-200' },
        { label: 'Rotation zusagen', helper: '6 Wochen', payload: { template: 'rotation' }, className: 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200' },
        { label: 'Entwicklungspfad', helper: '10 Wochen', payload: { template: 'development' }, className: 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200' },
    ];

    return (
        <div
            className="fixed z-[80] hidden w-[320px] rounded-[24px] border border-white/12 bg-[linear-gradient(180deg,rgba(10,16,30,0.98),rgba(8,12,24,0.98))] p-4 shadow-[0_24px_80px_rgba(2,6,23,0.55)] xl:block"
            style={{
                left: Math.min(x + 18, window.innerWidth - 340),
                top: Math.min(y + 18, window.innerHeight - 620),
            }}
        >
            <div className="mb-4 flex items-start gap-4">
                <img
                    src={player.photo_url}
                    alt={player.full_name}
                    className="h-16 w-16 rounded-[20px] border border-white/10 object-cover"
                />
                <div className="min-w-0 flex-1">
                    <div className="text-[10px] font-black uppercase tracking-[0.22em] text-[var(--text-muted)]">
                        {player.levelLabel}
                    </div>
                    <h3 className="truncate text-xl font-black tracking-[0.03em] text-white">
                        {player.full_name}
                    </h3>
                    <div className="mt-2 flex flex-wrap gap-2">
                        <Tag className="border-white/10 bg-white/[0.05] text-slate-200">{player.position}</Tag>
                        <Tag className="border-white/10 bg-white/[0.05] text-slate-200">{player.squad_role_label}</Tag>
                        {player.role_override_active && <Tag className={signalStyles.manual}>Manuell gesetzt</Tag>}
                    </div>
                </div>
                {pinned && (
                    <button
                        type="button"
                        onClick={onClose}
                        className="inline-flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-slate-400 transition-colors hover:text-white"
                    >
                        <X size={14} weight="bold" />
                    </button>
                )}
            </div>

            <div className="grid grid-cols-2 gap-3">
                <MiniStat label="OVR" value={player.overall} />
                <MiniStat label="Alter" value={player.age} />
                <MiniStat label="Zufriedenheit" value={`${player.happiness}%`} />
                <MiniStat label="Einsatzzeit" value={`${player.recent_minutes_share}%`} />
            </div>

            <div className="mt-4 space-y-3">
                <StatusRow label="Stimmung" value={player.mood.label} className={moodStyles[player.mood.status]} />
                <StatusRow label="Rollen-Fit" value={player.role_fit.label} className={fitStyles[player.role_fit.status]} />
                <StatusRow label="Teamstatus" value={player.team_status_label} className="border-white/10 bg-white/[0.05] text-slate-200" />
            </div>

            <div className="mt-4 space-y-3">
                <LoadBar label="Erwartete Spielzeit" value={player.expected_playtime} color="bg-cyan-400" />
                <LoadBar label="Letzte Einsatzquote" value={player.recent_minutes_share} color="bg-amber-400" />
                <LoadBar label="Fatigue" value={player.fatigue} color="bg-rose-400" />
            </div>

            <div className="mt-4 space-y-2 text-sm text-slate-300">
                <p>{player.role_fit.reason}</p>
                <p className="text-[var(--text-muted)]">{player.last_morale_reason || 'Keine Auffaelligkeit gemeldet.'}</p>
            </div>

            {player.promise && (
                <div className="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                    <div className="mb-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Versprechen</div>
                    <div className="flex flex-wrap gap-2">
                        <Tag className={fitStyles[player.role_fit.status]}>{player.promise.status}</Tag>
                        <Tag className="border-white/10 bg-white/[0.05] text-slate-200">{`${player.promise.fulfilled_ratio}% von ${player.promise.expected_minutes_share}%`}</Tag>
                    </div>
                </div>
            )}

            {playerConversationsEnabled && (
                <div className="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                    <div className="mb-3 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                        <ChatCircleText size={14} weight="bold" />
                        Schnellgespraech
                    </div>
                    <div className="grid gap-2">
                        {quickActions.map((action) => (
                            <button
                                key={action.label}
                                type="button"
                                disabled={processing}
                                onClick={() => onQuickConversation(action.payload)}
                                className={`flex items-center justify-between rounded-2xl border px-3 py-2 text-left transition-colors hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 ${action.className}`}
                            >
                                <span>
                                    <span className="block text-[10px] font-black uppercase tracking-[0.14em]">{action.helper}</span>
                                    <span className="mt-0.5 block text-xs font-black text-white">{action.label}</span>
                                </span>
                                <span className="text-[9px] font-black uppercase tracking-[0.14em]">
                                    {processing ? '...' : 'Start'}
                                </span>
                            </button>
                        ))}
                    </div>
                </div>
            )}

            <div className="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                <div className="mb-3 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                    <FlagPennant size={14} weight="bold" />
                    Rolle setzen
                </div>
                {player.role_override_active && (
                    <div className="mb-3 rounded-2xl border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-fuchsia-200">
                        Override aktiv{player.role_override_set_at ? ` seit ${player.role_override_set_at}` : ''}
                    </div>
                )}
                <div className="grid grid-cols-2 gap-2">
                    {roleActions.map((action) => (
                        <button
                            key={action.label}
                            type="button"
                            disabled={roleForm.processing}
                            onClick={() => onRoleChange(action.payload)}
                            className={`flex items-center justify-between rounded-2xl border px-3 py-2 text-left transition-colors hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 ${action.className}`}
                        >
                            <span>
                                <span className="block text-[10px] font-black uppercase tracking-[0.14em]">{action.helper}</span>
                                <span className="mt-0.5 block text-xs font-black text-white">{action.label}</span>
                            </span>
                            <span className="text-[9px] font-black uppercase tracking-[0.14em]">
                                {roleForm.processing ? '...' : 'Set'}
                            </span>
                        </button>
                    ))}
                </div>
            </div>

            <div className="mt-4 rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                <div className="mb-3 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                    <Handshake size={14} weight="bold" />
                    Quick Promise
                </div>
                <div className="grid gap-2">
                    {promiseActions.map((action) => (
                        <button
                            key={action.label}
                            type="button"
                            disabled={promiseForm.processing}
                            onClick={() => onQuickPromise(action.payload)}
                            className={`flex items-center justify-between rounded-2xl border px-3 py-2 text-left transition-colors hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 ${action.className}`}
                        >
                            <span>
                                <span className="block text-[10px] font-black uppercase tracking-[0.14em]">{action.helper}</span>
                                <span className="mt-0.5 block text-xs font-black text-white">{action.label}</span>
                            </span>
                            <span className="text-[9px] font-black uppercase tracking-[0.14em]">
                                {promiseForm.processing ? '...' : 'Set'}
                            </span>
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
