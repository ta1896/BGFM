import React from 'react';
import { Link } from '@inertiajs/react';
import {
    SoccerBall,
    Lightning,
    ArrowRight,
    Target,
    Star,
    CaretRight,
    ShieldCheck,
    HandFist,
    Wind,
    ArrowBendUpRight,
    Crosshair,
    CornersOut,
    WarningDiamond,
    Prohibit,
    Timer,
    PersonSimpleRun,
    Flag,
    Trophy,
    FirstAidKit,
    Cards,
    ArrowsCounterClockwise as Swap,
    Broadcast,
    User,
} from '@phosphor-icons/react';

const ACTION_CONFIG = {
    goal: { Icon: SoccerBall, color: 'text-emerald-400', bg: 'bg-emerald-500/20 border-emerald-500/30' },
    penalty: { Icon: Target, color: 'text-amber-400', bg: 'bg-amber-500/20 border-amber-500/30' },
    own_goal: { Icon: SoccerBall, color: 'text-rose-400', bg: 'bg-rose-500/20 border-rose-500/30' },
    yellow_card: { Icon: Cards, color: 'text-amber-400', bg: 'bg-amber-500/20 border-amber-500/30' },
    red_card: { Icon: Cards, color: 'text-rose-500', bg: 'bg-rose-500/20 border-rose-500/30' },
    yellow_red_card: { Icon: Cards, color: 'text-orange-400', bg: 'bg-orange-500/20 border-orange-500/30' },
    substitution: { Icon: Swap, color: 'text-indigo-400', bg: 'bg-indigo-500/20 border-indigo-500/30' },
    injury: { Icon: FirstAidKit, color: 'text-rose-300', bg: 'bg-rose-500/15 border-rose-500/20' },
    save: { Icon: ShieldCheck, color: 'text-amber-400', bg: 'bg-amber-500/20 border-amber-500/30' },
    block: { Icon: HandFist, color: 'text-amber-300', bg: 'bg-amber-500/15 border-amber-500/20' },
    tackle: { Icon: HandFist, color: 'text-amber-600', bg: 'bg-amber-600/15 border-amber-600/20' },
    tackle_won: { Icon: HandFist, color: 'text-amber-600', bg: 'bg-amber-600/15 border-amber-600/20' },
    tackle_lost: { Icon: HandFist, color: 'text-rose-400', bg: 'bg-rose-500/10 border-rose-500/15' },
    clearance: { Icon: Wind, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    interception: { Icon: CornersOut, color: 'text-teal-400', bg: 'bg-teal-500/15 border-teal-500/20' },
    shot: { Icon: Lightning, color: 'text-amber-300', bg: 'bg-amber-500/15 border-amber-500/20' },
    chance: { Icon: Lightning, color: 'text-amber-300', bg: 'bg-amber-500/15 border-amber-500/20' },
    shot_on_target: { Icon: Crosshair, color: 'text-amber-400', bg: 'bg-amber-500/20 border-amber-500/30' },
    shot_off_target: { Icon: Crosshair, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    shot_blocked: { Icon: Prohibit, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    corner: { Icon: Flag, color: 'text-sky-400', bg: 'bg-sky-500/15 border-sky-500/20' },
    free_kick: { Icon: Target, color: 'text-violet-400', bg: 'bg-violet-500/15 border-violet-500/20' },
    pass: { Icon: ArrowBendUpRight, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pass_completed: { Icon: ArrowBendUpRight, color: 'text-emerald-500/70', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pass_failed: { Icon: ArrowBendUpRight, color: 'text-rose-400/50', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    key_pass: { Icon: Star, color: 'text-amber-400', bg: 'bg-amber-500/20 border-amber-500/30' },
    dribble: { Icon: PersonSimpleRun, color: 'text-purple-400', bg: 'bg-purple-500/15 border-purple-500/20' },
    dribble_success: { Icon: PersonSimpleRun, color: 'text-purple-400', bg: 'bg-purple-500/15 border-purple-500/20' },
    dribble_failed: { Icon: PersonSimpleRun, color: 'text-rose-400/50', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    foul: { Icon: WarningDiamond, color: 'text-orange-400', bg: 'bg-orange-500/15 border-orange-500/20' },
    offside: { Icon: Flag, color: 'text-rose-300', bg: 'bg-rose-500/10 border-rose-500/15' },
    possession_loss: { Icon: Timer, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pressure: { Icon: Wind, color: 'text-[var(--text-muted)]', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    assist: { Icon: ArrowRight, color: 'text-amber-500', bg: 'bg-amber-500/15 border-amber-500/20' },
    default: { Icon: CaretRight, color: 'text-slate-600', bg: 'bg-transparent border-transparent' },
};

const EVENT_LABELS = {
    goal: 'Tor',
    own_goal: 'Eigentor',
    assist: 'Vorlage',
    yellow_card: 'Gelbe Karte',
    red_card: 'Rote Karte',
    yellow_red_card: 'Gelb-Rote Karte',
    substitution: 'Wechsel',
    injury: 'Verletzung',
    chance: 'Chance',
    shot: 'Schuss',
    shot_on_target: 'Schuss aufs Tor',
    shot_off_target: 'Schuss daneben',
    shot_blocked: 'Schuss geblockt',
    corner: 'Ecke',
    free_kick: 'Freistoss',
    foul: 'Foul',
    offside: 'Abseits',
    penalty: 'Elfmeter',
    save: 'Parade',
    tackle: 'Zweikampf',
    tackle_won: 'Zweikampf gewonnen',
    interception: 'Ballgewinn',
    dribble: 'Dribbling',
    key_pass: 'Schluesselpass',
    block: 'Block',
    clearance: 'Klaerung',
    pressure: 'Pressing',
    possession_loss: 'Ballverlust',
};

const POSITION_ROWS = {
    TW: 0, GK: 0,
    LV: 1, LB: 1, IV: 1, CB: 1, RV: 1, RB: 1,
    DM: 2, CDM: 2,
    ZM: 3, CM: 3, LM: 3, RM: 3,
    OM: 4, CAM: 4,
    LF: 5, LW: 5, RF: 5, RW: 5, HS: 5.5, CF: 5.5,
    MS: 6, ST: 6,
};

const getActionConfig = (type) => ACTION_CONFIG[type] || ACTION_CONFIG.default;

const getTeamVisuals = (isHomeAction) => ({
    badge: isHomeAction
        ? 'border-cyan-400/25 bg-cyan-400/12 text-cyan-200'
        : 'border-amber-400/25 bg-amber-400/12 text-amber-200',
    subtleBadge: isHomeAction
        ? 'border-cyan-400/15 bg-cyan-500/[0.08] text-cyan-100'
        : 'border-amber-400/15 bg-amber-500/[0.08] text-amber-100',
    panel: isHomeAction
        ? 'border-cyan-400/20 bg-cyan-950/30'
        : 'border-amber-400/20 bg-amber-950/30',
    panelStrong: isHomeAction
        ? 'border-cyan-300/30 bg-cyan-950/40'
        : 'border-amber-300/30 bg-amber-950/40',
    text: isHomeAction ? 'text-cyan-200' : 'text-amber-200',
    textStrong: isHomeAction ? 'text-cyan-300' : 'text-amber-300',
    line: isHomeAction ? 'before:bg-cyan-400/80' : 'before:bg-amber-400/80',
    glow: isHomeAction ? 'shadow-cyan-500/20' : 'shadow-amber-500/20',
    dot: isHomeAction
        ? 'border-cyan-400/20 bg-cyan-400/8'
        : 'border-amber-400/20 bg-amber-400/8',
});

const PlayerNameLink = ({ playerId, name, className = '', title }) => {
    if (!name) {
        return null;
    }

    if (!playerId) {
        return <span className={className}>{name}</span>;
    }

    return (
        <Link
            href={route('players.show', playerId)}
            className={`${className} underline decoration-white/15 underline-offset-4 transition-colors hover:text-white hover:decoration-current`}
            title={title || `${name} ansehen`}
        >
            {name}
        </Link>
    );
};

const ClubLogo = ({ club, className = '', imgClassName = '' }) => {
    const [hasError, setHasError] = React.useState(false);
    const label = (club?.short_name || club?.name || '?').trim();
    const fallback = label.slice(0, 3).toUpperCase();
    const showImage = Boolean(club?.logo_url) && !hasError;

    return (
        <div className={`flex items-center justify-center overflow-hidden ${className}`}>
            {showImage ? (
                <img
                    loading="lazy"
                    src={club.logo_url}
                    alt={club?.name}
                    className={imgClassName}
                    onError={() => setHasError(true)}
                />
            ) : (
                <span className="text-sm font-black uppercase tracking-[-0.04em] text-white/85">
                    {fallback}
                </span>
            )}
        </div>
    );
};

const PlayerTickerAvatar = ({ photoUrl, playerName, clubLogoUrl, clubShortName, className = '' }) => {
    const [photoError, setPhotoError] = React.useState(false);
    const [logoError, setLogoError] = React.useState(false);

    return (
        <div className={`relative h-10 w-10 shrink-0 ${className}`}>
            <div className="flex h-full w-full items-center justify-center overflow-hidden rounded-full border border-white/15 bg-white/5">
                {photoUrl && !photoError ? (
                    <img
                        loading="lazy"
                        src={photoUrl}
                        alt={playerName || 'Spieler'}
                        className="h-full w-full object-cover"
                        onError={() => setPhotoError(true)}
                    />
                ) : (
                    <User size={18} weight="fill" className="text-slate-500" />
                )}
            </div>

            <div className="absolute -right-0.5 -bottom-0.5 flex h-4.5 w-4.5 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-[#0a1522] shadow-lg">
                {clubLogoUrl && !logoError ? (
                    <img
                        loading="lazy"
                        src={clubLogoUrl}
                        alt={clubShortName || 'Club'}
                        className="h-full w-full object-contain p-[2px]"
                        onError={() => setLogoError(true)}
                    />
                ) : (
                    <span className="text-[7px] font-black uppercase leading-none text-white/80">
                        {(clubShortName || '?').slice(0, 2)}
                    </span>
                )}
            </div>
        </div>
    );
};

export const ActionIcon = ({ type, size = 16, className = '' }) => {
    const { Icon, color } = getActionConfig(type);
    return <Icon size={size} weight="fill" className={`${color} ${className}`} />;
};

export const isKeyEvent = (type) => ['goal', 'own_goal', 'yellow_card', 'red_card', 'yellow_red_card', 'substitution', 'injury', 'penalty'].includes(type);

export const StatBar = ({ label, home, away }) => {
    const total = (home || 0) + (away || 0);
    const homePct = total > 0 ? Math.round((home / total) * 100) : 50;
    const awayPct = 100 - homePct;

    return (
        <div className="space-y-1.5">
            <div className="flex justify-between text-[10px] font-black uppercase tracking-widest">
                <span className="text-white">{home ?? 0}</span>
                <span className="text-[var(--text-muted)]">{label}</span>
                <span className="text-white">{away ?? 0}</span>
            </div>
            <div className="flex h-1.5 overflow-hidden rounded-full gap-0.5">
                <div className="h-full rounded-full bg-cyan-400 transition-all duration-700 ease-out" style={{ width: `${homePct}%` }} />
                <div className="h-full rounded-full bg-amber-400 transition-all duration-700 ease-out" style={{ width: `${awayPct}%` }} />
            </div>
        </div>
    );
};

const SmallPulse = ({ label, value }) => (
    <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
        <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{label}</div>
        <div className="mt-1 text-lg font-black text-white">{value}</div>
    </div>
);

const PulseStat = ({ club, state, accent }) => (
    <div className="rounded-2xl border border-white/10 bg-black/10 p-4">
        <div className="mb-3 flex items-center gap-3">
            <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-10 w-10 object-contain" />
            <div>
                <div className="text-[11px] font-black uppercase tracking-[0.08em] text-white">{club?.name}</div>
                <div className={`text-[9px] font-black uppercase tracking-[0.16em] ${accent === 'amber' ? 'text-amber-300' : 'text-[#d4af37]'}`}>Live State</div>
            </div>
        </div>
        <div className="grid grid-cols-2 gap-2">
            <SmallPulse label="Shots" value={state?.shots ?? 0} />
            <SmallPulse label="On Target" value={state?.shots_on_target ?? 0} />
            <SmallPulse label="Danger" value={state?.dangerous_attacks ?? 0} />
            <SmallPulse label="Cards" value={(state?.yellow_cards ?? 0) + (state?.red_cards ?? 0)} />
        </div>
    </div>
);

export const MatchPulse = ({ homeClub, awayClub, homeState, awayState, livePlayerStates = [] }) => {
    const topRated = [...livePlayerStates]
        .filter((player) => Number.isFinite(Number(player.rating)))
        .sort((a, b) => Number(b.rating) - Number(a.rating))
        .slice(0, 3);

    return (
        <div className="grid gap-4 lg:grid-cols-[1.1fr_0.9fr]">
            <div className="sim-card p-5">
                <div className="mb-4 flex items-center justify-between gap-3">
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Match Pulse</div>
                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-amber-300">Live Summary</div>
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <PulseStat club={homeClub} state={homeState} accent="amber" />
                    <PulseStat club={awayClub} state={awayState} accent="gold" />
                </div>
            </div>
            <div className="sim-card p-5">
                <div className="mb-4 flex items-center justify-between gap-3">
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Top Performers</div>
                    <Star size={14} weight="fill" className="text-amber-400" />
                </div>
                <div className="space-y-2.5">
                    {topRated.length > 0 ? topRated.map((player) => (
                        <div key={player.player_id} className="flex items-center justify-between gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                            <div className="min-w-0">
                                <div className="truncate text-[11px] font-black uppercase tracking-[0.08em] text-white">{player.player_name}</div>
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                    {player.goals || 0} Tore / {player.assists || 0} Vorlagen
                                </div>
                            </div>
                            <div className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.12em] text-emerald-200">
                                {Number(player.rating).toFixed(1)}
                            </div>
                        </div>
                    )) : (
                        <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-6 text-sm text-[var(--text-muted)]">
                            Noch keine Ratings verfuegbar.
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

const panelAccentMap = {
    cyan: 'border-cyan-400/20 bg-cyan-400/8 text-cyan-200',
    amber: 'border-amber-400/20 bg-amber-400/8 text-amber-200',
    rose: 'border-rose-400/20 bg-rose-400/8 text-rose-200',
    emerald: 'border-emerald-400/20 bg-emerald-400/8 text-emerald-200',
};

const panelIconMap = {
    broadcast: Broadcast,
    firstAidKit: FirstAidKit,
    trophy: Trophy,
};

const shoutOptions = [
    { key: 'encourage', label: 'Encourage' },
    { key: 'demand_more', label: 'Demand More' },
    { key: 'concentrate', label: 'Concentrate' },
    { key: 'calm_down', label: 'Calm Down' },
];

const styleOptions = [
    { key: 'balanced', label: 'Balanced' },
    { key: 'offensive', label: 'Offensive' },
    { key: 'defensive', label: 'Defensive' },
    { key: 'counter', label: 'Counter' },
];

const TIMELINE_TYPES = new Set(['goal', 'own_goal', 'yellow_card', 'red_card', 'yellow_red_card', 'substitution']);

const getTimelineEvents = (actions = []) => actions
    .filter((action) => TIMELINE_TYPES.has(action.action_type))
    .slice()
    .sort((a, b) => {
        const minuteDiff = Number(a.minute || 0) - Number(b.minute || 0);
        if (minuteDiff !== 0) {
            return minuteDiff;
        }

        return Number(a.second || 0) - Number(b.second || 0);
    });

const getScorelineEvents = (actions = []) => actions
    .filter((action) => ['goal', 'own_goal'].includes(action.action_type))
    .slice()
    .sort((a, b) => {
        const minuteDiff = Number(b.minute || 0) - Number(a.minute || 0);
        if (minuteDiff !== 0) {
            return minuteDiff;
        }

        return Number(b.second || 0) - Number(a.second || 0);
    })
    .slice(0, 4);

const formatTimelineLabel = (action) => {
    if (action.action_type === 'goal') return 'Goal';
    if (action.action_type === 'own_goal') return 'Own Goal';
    if (action.action_type === 'yellow_card') return 'Yellow';
    if (action.action_type === 'red_card' || action.action_type === 'yellow_red_card') return 'Red';
    if (action.action_type === 'substitution') return 'Sub';
    return EVENT_LABELS[action.action_type] || action.action_type;
};

const scorelineFromEvent = (action) => {
    const score = action.metadata?.score;
    if (typeof score === 'string' && score.includes(':')) {
        return score;
    }

    return null;
};

const getActionLookupKey = (action) => String(action?.id ?? '');

const formatMatchMinute = (minute, displayMinute = null) => {
    if (typeof displayMinute === 'string' && displayMinute.trim() !== '') {
        const normalized = displayMinute.trim().replace(/'+$/, '');
        return `${normalized}'`;
    }

    const explicitValue = Number(displayMinute);
    if (Number.isFinite(explicitValue) && explicitValue > 0) {
        return `${explicitValue}'`;
    }

    const value = Number(minute || 0);

    if (!Number.isFinite(value) || value <= 0) {
        return "0'";
    }

    return `${value}'`;
};

const buildScorelineLookup = (actions = [], homeClubId) => {
    let home = 0;
    let away = 0;
    const lookup = {};

    actions
        .slice()
        .sort((a, b) => {
            const minuteDiff = Number(a.minute || 0) - Number(b.minute || 0);
            if (minuteDiff !== 0) {
                return minuteDiff;
            }

            const secondDiff = Number(a.second || 0) - Number(b.second || 0);
            if (secondDiff !== 0) {
                return secondDiff;
            }

            return Number(a.sequence || 0) - Number(b.sequence || 0);
        })
        .forEach((action, index) => {
            if (action.action_type === 'goal') {
                if (action.club_id === homeClubId) {
                    home += 1;
                } else {
                    away += 1;
                }
            }

            if (action.action_type === 'own_goal') {
                if (action.club_id === homeClubId) {
                    away += 1;
                } else {
                    home += 1;
                }
            }

            const key = getActionLookupKey(action);
            lookup[key] = `${home}:${away}`;
            lookup[`${key}-${index}`] = `${home}:${away}`;
        });

    return lookup;
};

export const ModulePanels = ({ panels = [] }) => {
    if (panels.length === 0) {
        return null;
    }

    return (
        <div className="grid gap-4 lg:grid-cols-2">
            {panels.map((panel) => {
                const Icon = panelIconMap[panel.icon] || Star;
                const tone = panelAccentMap[panel.accent] || panelAccentMap.cyan;

                return (
                    <div key={panel.key} className="sim-card p-5">
                        <div className="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Module Panel</div>
                                <div className="mt-1 text-lg font-black text-white">{panel.title}</div>
                                <p className="mt-1 text-xs leading-relaxed text-[var(--text-muted)]">{panel.description}</p>
                            </div>
                            <div className={`rounded-2xl border p-3 ${tone}`}>
                                <Icon size={16} weight="duotone" />
                            </div>
                        </div>

                        <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                            <div className="text-sm font-black uppercase tracking-[0.08em] text-white">{panel.data?.headline}</div>
                            <p className="mt-2 text-xs leading-relaxed text-[var(--text-muted)]">{panel.data?.summary}</p>
                        </div>

                        {panel.data?.stats?.length > 0 && (
                            <div className="mt-4 grid grid-cols-3 gap-3">
                                {panel.data.stats.map((stat) => (
                                    <div key={stat.label} className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                                        <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{stat.label}</div>
                                        <div className="mt-2 text-xl font-black text-white">{stat.value}</div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {panel.data?.players?.length > 0 && (
                            <div className="mt-4 space-y-2.5">
                                {panel.data.players.map((player) => (
                                    <div key={player.id} className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                                        <img src={player.photo_url} alt={player.name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.name}</div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.12em] text-[var(--text-muted)]">
                                                {(player.availability_status || player.medical_status || 'fit')} / Fatigue {player.fatigue}%
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}

                        {panel.data?.awards?.length > 0 && (
                            <div className="mt-4 space-y-2.5">
                                {panel.data.awards.map((award) => (
                                    <div key={award.award_key} className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-4">
                                        <div className="flex items-start justify-between gap-3">
                                            <div className="min-w-0">
                                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-amber-200">
                                                    {award.label}
                                                </div>
                                                <div className="mt-2 flex items-center gap-3">
                                                    <div className="flex h-11 w-11 items-center justify-center overflow-hidden rounded-2xl border border-white/10 bg-[var(--bg-content)]/50">
                                                        {award.photo_url ? (
                                                            <img src={award.photo_url} alt={award.player_name || award.label} className="h-full w-full object-cover" />
                                                        ) : award.club_logo_url ? (
                                                            <img src={award.club_logo_url} alt={award.club_name || award.label} className="h-full w-full object-contain p-2" />
                                                        ) : (
                                                            <Trophy size={18} weight="fill" className="text-amber-300" />
                                                        )}
                                                    </div>
                                                    <div className="min-w-0">
                                                        <div className="truncate text-sm font-black text-white">
                                                            <PlayerNameLink
                                                                playerId={award.player_id}
                                                                name={award.player_name || award.club_name || award.label}
                                                                className="text-white"
                                                            />
                                                        </div>
                                                        {award.club_name && (
                                                            <div className="mt-1 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                                {award.club_logo_url && (
                                                                    <img src={award.club_logo_url} alt={award.club_name} className="h-4 w-4 object-contain" />
                                                                )}
                                                                <span>{award.club_name}</span>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </div>
                                            <div className="rounded-full border border-amber-400/20 bg-amber-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-amber-200">
                                                {award.value_label}
                                            </div>
                                        </div>
                                        <p className="mt-3 text-xs leading-relaxed text-[var(--text-muted)]">{award.summary}</p>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                );
            })}
        </div>
    );
};

export const MatchCommandRail = ({ matchStatus, clubs = [], manageableClubIds = [], teamStates = {}, onStyleChange, onShout }) => {
    if (!manageableClubIds.length) {
        return null;
    }

    return (
        <div className="grid gap-4 xl:grid-cols-2">
            {clubs.filter((club) => manageableClubIds.includes(club?.id)).map((club) => {
                const state = teamStates?.[String(club.id)] || {};

                return (
                    <div key={club.id} className="sim-card p-5">
                        <div className="mb-4 flex items-start justify-between gap-3">
                            <div className="flex items-center gap-3">
                                <img src={club.logo_url} alt={club.name} className="h-10 w-10 object-contain" />
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Manager Rail</div>
                                    <div className="text-lg font-black text-white">{club.name}</div>
                                </div>
                            </div>
                            <div className="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-cyan-200">
                                {state.tactical_style || 'balanced'}
                            </div>
                        </div>

                        <div className="mb-4 grid grid-cols-3 gap-3">
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Danger</div>
                                <div className="mt-2 text-xl font-black text-white">{state.dangerous_attacks ?? 0}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Shots</div>
                                <div className="mt-2 text-xl font-black text-white">{state.shots ?? 0}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-3">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Cards</div>
                                <div className="mt-2 text-xl font-black text-white">{(state.yellow_cards ?? 0) + (state.red_cards ?? 0)}</div>
                            </div>
                        </div>

                        <div className="mb-4">
                            <div className="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                <Lightning size={12} weight="fill" />
                                Tactical Style
                            </div>
                            <div className="flex flex-wrap gap-2">
                                {styleOptions.map((option) => (
                                    <button
                                        key={option.key}
                                        type="button"
                                        onClick={() => onStyleChange?.(club.id, option.key)}
                                        disabled={matchStatus !== 'live'}
                                        className={`rounded-full border px-3 py-1.5 text-[9px] font-black uppercase tracking-[0.14em] ${
                                            state.tactical_style === option.key
                                                ? 'border-amber-400/30 bg-amber-400/12 text-amber-200'
                                                : 'border-white/10 bg-white/[0.03] text-white/70'
                                        } disabled:cursor-not-allowed disabled:opacity-50`}
                                    >
                                        {option.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div>
                            <div className="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                <Broadcast size={12} weight="fill" />
                                Shouts
                            </div>
                            <div className="grid gap-2 sm:grid-cols-2">
                                {shoutOptions.map((option) => (
                                    <button
                                        key={option.key}
                                        type="button"
                                        onClick={() => onShout?.(club.id, option.key)}
                                        disabled={matchStatus !== 'live'}
                                        className="rounded-2xl border border-white/10 bg-white/[0.03] px-3 py-2 text-[10px] font-black uppercase tracking-[0.12em] text-white/80 transition-colors hover:border-white/20 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        {option.label}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </div>
                );
            })}
        </div>
    );
};

export const ScoreHero = ({ home_club, away_club, home_score, away_score, status, live_minute, display_minute, kickoff_formatted, competition, matchday, weather, type, actions = [] }) => {
    const isLive = status === 'live';
    const isPlayed = status === 'played';
    const scorelineLabel = isPlayed || isLive ? `${home_score ?? 0}:${away_score ?? 0}` : '-:-';
    const scoreLookup = buildScorelineLookup(actions, home_club?.id);
    const scoreEvents = getScorelineEvents(actions).map((action) => ({
        ...action,
        resolved_scoreline: scoreLookup[getActionLookupKey(action)] || scorelineFromEvent(action),
    }));
    const renderScoreEvent = (action) => {
        const isHomeAction = action.club_id === home_club?.id;
        const actorName = action.player_name || formatTimelineLabel(action);
        const scoreline = action.resolved_scoreline;
        const [homeGoals = '0', awayGoals = '0'] = (scoreline || '0:0').split(':');
        const tone = isHomeAction ? 'text-cyan-200' : 'text-amber-200';

        return (
            <div key={`${action.id}-${action.minute}`} className="mx-auto flex w-full max-w-[16.5rem] items-center justify-center rounded-md bg-[#0b2a3d]/78 px-2.5 py-1.5 shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] backdrop-blur-sm">
                <div className="grid w-fit grid-cols-[7rem_2.25rem_3.25rem] items-center justify-center gap-1.5">
                    <div className={`text-right font-['Outfit'] text-[0.72rem] font-bold leading-none text-white ${isHomeAction ? tone : 'text-white/35'}`}>
                        {isHomeAction ? (
                            <PlayerNameLink playerId={action.player_id} name={actorName} className={tone} title={`${actorName} ansehen`} />
                        ) : '\u00a0'}
                    </div>
                    <div className="text-center font-['Outfit'] text-[0.68rem] font-semibold leading-none text-[#8aa8bc]">
                        {formatMatchMinute(action.minute, action.display_minute)}
                    </div>
                    <div className="text-center font-['Outfit'] text-[0.84rem] font-black leading-none tabular-nums">
                        <span className="text-cyan-200">{homeGoals}</span>
                        <span className="px-0.5 text-white/55">:</span>
                        <span className="text-amber-200">{awayGoals}</span>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <div className="relative overflow-hidden rounded-[2rem] border border-[var(--border-muted)] bg-gradient-to-br from-[#070d18] via-[#0b1322] to-[#11192b] shadow-2xl">
            <div
                className="pointer-events-none absolute inset-0 bg-center bg-cover bg-no-repeat opacity-[0.14]"
                style={{ backgroundImage: "url('/images/stadium-silhouette.svg')" }}
            />
            <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(8,14,24,0.08)_0%,rgba(8,14,24,0.58)_48%,rgba(8,14,24,0.92)_100%)]" />
            <div className="pointer-events-none absolute inset-0 bg-gradient-to-r from-cyan-500/6 via-transparent to-amber-500/6" />
            <div className="pointer-events-none absolute -top-32 left-1/2 h-64 w-96 -translate-x-1/2 rounded-full bg-cyan-400/5 blur-[110px]" />

            <div className="relative z-10 flex items-center justify-center gap-2 pt-6 pb-3 text-[10px] font-medium tracking-[0.08em] text-[#9bb6c8]">
                <Trophy size={12} weight="fill" className="text-amber-500" />
                {competition || type || 'Spiel'}
                {matchday && <span>- Spieltag {matchday}</span>}
            </div>

            <div className="relative z-10 flex items-center justify-between gap-2 px-4 pb-5 sm:gap-6 sm:px-10 sm:pb-6">
                <div className="flex flex-1 flex-col items-center gap-2 sm:gap-3">
                    <ClubLogo
                        club={home_club}
                        className="h-14 w-14 rounded-full border border-white/10 bg-white/5 p-2 shadow-2xl backdrop-blur-sm sm:h-20 sm:w-20 sm:p-2.5"
                        imgClassName="h-full w-full object-contain"
                    />
                    <div className="text-center">
                        <p className="text-[1.8rem] font-black uppercase tracking-[-0.07em] text-white sm:text-[2.2rem]">
                            {home_club?.short_name || home_club?.name}
                        </p>
                        <p className="mt-0.5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#7f93a8] sm:text-[10px]">Heim</p>
                    </div>
                </div>

                <div className="flex shrink-0 flex-col items-center gap-2.5 sm:gap-3">
                    {isLive || isPlayed ? (
                        <div className="flex items-center gap-2.5 sm:gap-4">
                            <span className="text-[4rem] font-black leading-none tracking-[-0.09em] text-white tabular-nums sm:text-[5.4rem] md:text-[6.4rem]">
                                {home_score ?? 0}
                            </span>
                            <span className="mb-1 text-[2.2rem] font-light leading-none text-[#5f7690] sm:text-[3.2rem] md:text-[3.8rem]">:</span>
                            <span className="text-[4rem] font-black leading-none tracking-[-0.09em] text-white tabular-nums sm:text-[5.4rem] md:text-[6.4rem]">
                                {away_score ?? 0}
                            </span>
                        </div>
                    ) : (
                        <div className="text-center">
                            <p className="text-[2.8rem] font-black leading-none tracking-[-0.08em] text-slate-100 tabular-nums sm:text-[3.8rem]">
                                {scorelineLabel}
                            </p>
                            {kickoff_formatted && (
                                <p className="mt-2 text-[0.72rem] font-semibold uppercase tracking-[0.16em] text-slate-400 sm:text-[0.8rem]">
                                    {kickoff_formatted}
                                </p>
                            )}
                            <p className="mt-1 text-[10px] font-medium uppercase tracking-[0.2em] text-slate-500">Anstoss</p>
                        </div>
                    )}

                    <div className={`flex items-center gap-2 rounded-full border px-5 py-2 text-[9px] font-bold uppercase tracking-[0.18em] backdrop-blur-sm ${
                        isLive ? 'border-rose-500/40 bg-rose-500/15 text-rose-400' :
                        isPlayed ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' :
                        'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                    }`}>
                        {isLive && <span className="relative flex h-2 w-2"><span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75" /><span className="relative inline-flex h-2 w-2 rounded-full bg-rose-500" /></span>}
                        {isLive ? formatMatchMinute(live_minute, display_minute) : isPlayed ? 'Abgepfiffen' : 'Geplant'}
                    </div>
                </div>

                <div className="flex flex-1 flex-col items-center gap-2 sm:gap-3">
                    <ClubLogo
                        club={away_club}
                        className="h-14 w-14 rounded-full border border-white/10 bg-white/5 p-2 shadow-2xl backdrop-blur-sm sm:h-20 sm:w-20 sm:p-2.5"
                        imgClassName="h-full w-full object-contain"
                    />
                    <div className="text-center">
                        <p className="text-[1.8rem] font-black uppercase tracking-[-0.07em] text-white sm:text-[2.2rem]">
                            {away_club?.short_name || away_club?.name}
                        </p>
                        <p className="mt-0.5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#7f93a8] sm:text-[10px]">Gast</p>
                    </div>
                </div>
            </div>

            {scoreEvents.length > 0 && (
                <div className="relative z-10 border-t border-white/5 bg-black/15 px-4 py-3 backdrop-blur-[2px] sm:px-8">
                    <div className="mx-auto flex max-w-[17rem] flex-col items-center space-y-1.5">
                        {scoreEvents.map((action) => renderScoreEvent(action))}
                    </div>
                </div>
            )}

            {(weather || kickoff_formatted) && (
                <div className="relative z-10 flex items-center justify-center gap-5 border-t border-white/5 px-8 py-2.5 text-[9px] font-medium tracking-[0.14em] text-slate-500">
                    {kickoff_formatted && <span>{kickoff_formatted}</span>}
                    {weather && <span>{weather}</span>}
                    {type && <span>{type === 'league' ? 'Liga' : type === 'cup_national' ? 'Pokal' : 'Testspiel'}</span>}
                </div>
            )}
        </div>
    );
};

export const TickerItem = ({ action, homeClubId, resolvedScoreline }) => {
    const key = isKeyEvent(action.action_type);
    const { bg, color } = getActionConfig(action.action_type);
    const isHomeAction = action.club_id === homeClubId;
    const teamVisuals = getTeamVisuals(isHomeAction);

    if (!key && !action.narrative) {
        return null;
    }

    if (action.action_type === 'goal' || action.action_type === 'own_goal') {
        const scorerName = action.player_name || 'Torschuetze';
        const firstName = scorerName.split(' ')[0];
        const lastName = scorerName.split(' ').slice(1).join(' ') || scorerName.split(' ')[0];
        const scoreline = scorelineFromEvent(action) || resolvedScoreline;
        const goalTitle = action.action_type === 'own_goal'
            ? `Eigentor fuer ${action.club_short_name || (isHomeAction ? 'Home' : 'Away')}`
            : `Tor fuer ${action.club_short_name || (isHomeAction ? 'Home' : 'Away')}`;

        return (
            <div className={`border-b border-white/5 px-4 py-4 ${teamVisuals.panelStrong}`}>
                <div className="mb-3 flex items-center justify-between gap-3">
                    <span className="text-2xl font-black italic tabular-nums text-white">{formatMatchMinute(action.minute, action.display_minute)}</span>
                    <div className="flex items-center gap-3">
                        <div className={`text-center text-sm font-black uppercase tracking-[0.08em] ${teamVisuals.text}`}>
                            {goalTitle}
                        </div>
                        <SoccerBall size={22} weight="fill" className={teamVisuals.textStrong} />
                    </div>
                </div>

                <div className={`rounded-2xl border px-4 py-4 ${teamVisuals.panel}`}>
                    <div className="mb-3 flex items-center justify-center">
                        <div className="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full border border-white/20 bg-white/5">
                            {action.club_logo_url ? (
                                <img loading="lazy" src={action.club_logo_url} alt={action.club_short_name} className="h-full w-full object-contain p-1.5" />
                            ) : (
                                <ShieldCheck size={18} weight="duotone" className="text-white/80" />
                            )}
                        </div>
                    </div>

                    <div className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-3">
                            <div className="flex h-16 w-16 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-[var(--bg-pillar)]">
                                {action.player_photo_url ? (
                                    <img loading="lazy" src={action.player_photo_url} alt={scorerName} className="h-full w-full object-cover" />
                                ) : (
                                    <User size={28} weight="fill" className="text-slate-400" />
                                )}
                            </div>
                            <div className="min-w-0">
                                <div className="text-sm text-slate-300">{firstName}</div>
                                <div className="truncate text-3xl font-black leading-none text-white">
                                    <PlayerNameLink playerId={action.player_id} name={lastName} className="text-white" title={scorerName} />
                                </div>
                                {action.assister_name && (
                                    <div className={`mt-2 text-sm ${teamVisuals.text}`}>
                                        Assist: <PlayerNameLink playerId={null} name={action.assister_name} className={teamVisuals.text} />
                                    </div>
                                )}
                            </div>
                        </div>

                        {scoreline && (
                            <div className="flex overflow-hidden rounded-xl border border-white/20 bg-black/10">
                                <div className="px-3 py-2 text-3xl font-black tabular-nums text-cyan-300">
                                    {scoreline.split(':')[0]}
                                </div>
                                <div className="px-2 py-2 text-2xl font-black text-white/70">:</div>
                                <div className="px-3 py-2 text-3xl font-black tabular-nums text-amber-300">
                                    {scoreline.split(':')[1]}
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        );
    }

    if (action.action_type === 'substitution') {
        const incomingName = action.player_name || 'Einwechslung';
        const outgoingName = action.opponent_player_name || 'Auswechslung';
        const incomingFirst = incomingName.split(' ')[0];
        const incomingLast = incomingName.split(' ').slice(1).join(' ') || incomingName.split(' ')[0];
        const outgoingFirst = outgoingName.split(' ')[0];
        const outgoingLast = outgoingName.split(' ').slice(1).join(' ') || outgoingName.split(' ')[0];

        return (
            <div className="border-b border-white/5 px-4 py-4">
                <div className="mb-3 flex items-center justify-between gap-3">
                    <span className="text-2xl font-black italic tabular-nums text-white">{formatMatchMinute(action.minute, action.display_minute)}</span>
                    <div className="text-center">
                        <div className="text-xs font-black uppercase tracking-[0.18em] text-white">Spielerwechsel</div>
                        <div className="mt-1 flex items-center justify-center gap-2">
                            <span className="text-lg font-black text-emerald-400">IN</span>
                            <div className={`rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-[0.14em] ${teamVisuals.badge}`}>
                                {action.club_short_name}
                            </div>
                            <span className="text-lg font-black text-rose-400">OUT</span>
                        </div>
                    </div>
                    <div className={`rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${teamVisuals.badge}`}>
                        {isHomeAction ? 'Home' : 'Away'}
                    </div>
                </div>

                <div className="grid gap-3 md:grid-cols-2">
                    <div className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3">
                        <div className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-[var(--bg-pillar)]">
                            {action.opponent_player_photo_url ? (
                                <img loading="lazy" src={action.opponent_player_photo_url} alt={outgoingName} className="h-full w-full object-cover" />
                            ) : (
                                <User size={24} weight="fill" className="text-slate-500" />
                            )}
                        </div>
                        <div className="min-w-0 flex-1">
                            <div className="text-xs text-[var(--text-muted)]">{outgoingFirst}</div>
                            <div className="truncate text-2xl font-black leading-none text-white">
                                <PlayerNameLink playerId={action.opponent_player_id} name={outgoingLast} className="text-white" title={outgoingName} />
                            </div>
                        </div>
                        <div className="rounded-full border border-rose-400/20 bg-rose-400/10 px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-rose-200">
                            Out
                        </div>
                    </div>

                    <div className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3">
                        <div className="min-w-0 flex-1 text-right">
                            <div className="text-xs text-[var(--text-muted)]">{incomingFirst}</div>
                            <div className="truncate text-2xl font-black leading-none text-white">
                                <PlayerNameLink playerId={action.player_id} name={incomingLast} className="text-white" title={incomingName} />
                            </div>
                        </div>
                        <div className="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-white/10 bg-[var(--bg-pillar)]">
                            {action.player_photo_url ? (
                                <img loading="lazy" src={action.player_photo_url} alt={incomingName} className="h-full w-full object-cover" />
                            ) : (
                                <User size={24} weight="fill" className="text-slate-500" />
                            )}
                        </div>
                        <div className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] text-emerald-200">
                            In
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    return (
        <div className={`px-4 py-2.5 sm:px-5 ${key ? '' : 'opacity-80'}`}>
            <div className="flex items-start gap-3">
                <div className="w-9 shrink-0 pt-2 text-right">
                    <span className={`text-[0.95rem] font-black leading-none tabular-nums ${key ? 'text-white' : 'text-slate-500'}`}>{formatMatchMinute(action.minute, action.display_minute)}</span>
                </div>

                <div className={`flex min-w-0 flex-1 items-start gap-3 rounded-2xl border px-3 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.03)] transition-colors ${
                    key ? `${teamVisuals.panelStrong} hover:bg-white/[0.04]` : 'border-white/8 bg-white/[0.03] hover:bg-white/[0.04]'
                }`}>
                    <div className={`mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl border ${
                        key ? bg : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60'
                    }`}>
                        <ActionIcon type={action.action_type} size={15} />
                    </div>

                    <div className="min-w-0 flex-1">
                        {key && (
                            <p className="mb-1 text-[0.84rem] font-semibold leading-snug text-white">
                                <span className={`${color} mr-1.5`}>{EVENT_LABELS[action.action_type] || action.action_type}</span>
                                {action.player_name && (
                                    <span className="text-slate-100">
                                        <PlayerNameLink playerId={action.player_id} name={action.player_name} className="text-slate-100" />
                                    </span>
                                )}
                                {action.assister_name && <span className="text-[0.76rem] text-[var(--text-muted)]"> (V: {action.assister_name})</span>}
                                {action.metadata?.score && <span className="ml-2 text-[0.76rem] text-[var(--text-muted)]">[{action.metadata.score}]</span>}
                            </p>
                        )}
                        {!key && action.action_type && (
                            <p className="mb-1 text-[0.8rem] font-semibold leading-snug text-slate-300">
                                {EVENT_LABELS[action.action_type] || action.action_type}
                                {action.player_name && (
                                    <span className="ml-1 text-slate-200">
                                        <PlayerNameLink playerId={action.player_id} name={action.player_name} className="text-slate-200" />
                                    </span>
                                )}
                            </p>
                        )}
                        {action.narrative && <p className={`text-[0.8rem] leading-snug ${key ? 'text-slate-300' : 'text-slate-500'}`}>{action.narrative}</p>}
                        {action.club_short_name && (
                            <div className={`mt-2 inline-flex items-center gap-1 rounded-full border px-2 py-1 text-[8px] font-black uppercase tracking-[0.14em] ${key ? teamVisuals.badge : teamVisuals.subtleBadge}`}>
                                <span>{action.club_short_name}</span>
                                <span>{isHomeAction ? 'Home' : 'Away'}</span>
                            </div>
                        )}
                    </div>

                    {(action.player_photo_url || action.player_name) && (
                        <PlayerTickerAvatar
                            photoUrl={action.player_photo_url}
                            playerName={action.player_name}
                            clubLogoUrl={action.club_logo_url}
                            clubShortName={action.club_short_name}
                        />
                    )}
                </div>
            </div>
        </div>
    );
};

const getRow = (slot) => {
    if (!slot) {
        return 3;
    }

    const upper = slot.toUpperCase();
    if (POSITION_ROWS[upper] !== undefined) {
        return POSITION_ROWS[upper];
    }

    const key = Object.keys(POSITION_ROWS).find((candidate) => upper.startsWith(candidate));
    return POSITION_ROWS[key] ?? 3;
};

const getFallbackPitchPosition = (slot, index, count) => {
    const row = getRow(slot);
    const top = 12 + (row / 6) * 60;
    const spread = count > 1 ? 70 / (count - 1) : 0;
    const left = count > 1 ? 15 + (index * spread) : 50;

    return { left, top };
};

const HalfPitchSVG = () => (
    <svg className="pointer-events-none absolute inset-0 h-full w-full opacity-30" viewBox="0 0 200 320" preserveAspectRatio="none" fill="none">
        <g stroke="white" strokeWidth="1.2" fill="none">
            <rect x="1" y="1" width="198" height="318" />
            <rect x="60" y="1" width="80" height="28" />
            <circle cx="100" cy="55" r="2.5" fill="white" opacity="0.5" />
            <line x1="0" y1="319" x2="200" y2="319" />
        </g>
    </svg>
);

const LineupToken = ({ player, accent, livePlayerStates }) => {
    const state = livePlayerStates?.find((entry) => entry.player_id === player.id);
    const isOff = state?.is_sent_off || state?.is_injured;
    const hasGoal = (state?.goals || 0) > 0;
    const hasYellow = (state?.yellow_cards || 0) > 0;
    const isRed = state?.is_sent_off;
    const profileHref = route('players.show', player.id);

    return (
        <Link
            href={profileHref}
            className="group relative flex flex-col items-center gap-1 rounded-xl px-1.5 py-1 transition-transform duration-150 hover:z-10 hover:scale-110 focus:z-10 focus:scale-110 focus:outline-none"
            title={`${player.name} ansehen`}
        >
            <div className={`absolute inset-0 rounded-xl opacity-0 blur-md transition-opacity duration-150 group-hover:opacity-100 group-focus:opacity-100 ${
                accent === 'amber' ? 'bg-amber-400/20' : 'bg-cyan-300/20'
            }`} />
            <div className={`relative flex h-8 w-8 items-center justify-center rounded-full border-2 shadow-md transition-all duration-150 group-hover:shadow-lg group-hover:shadow-black/40 ${
                isOff ? 'border-rose-500/60 bg-rose-950/60 opacity-40' : `border-${accent}-400/80 bg-[var(--sim-shell-bg)] group-hover:border-${accent}-300`
            }`}>
                {player.photo_url ? (
                    <img loading="lazy" src={player.photo_url} alt={player.name} className="h-full w-full rounded-full object-cover" />
                ) : (
                    <span className={`text-[9px] font-black text-${accent}-300`}>{player.position?.slice(0, 2)}</span>
                )}
                {hasYellow && !isRed && <div className="absolute -top-1 -right-0.5 h-2.5 w-2 rounded-[2px] border border-black bg-amber-400" />}
                {isRed && <div className="absolute -top-1 -right-0.5 h-2.5 w-2 rounded-[2px] border border-black bg-rose-500" />}
                {hasGoal && <div className="absolute -bottom-1 -left-0.5 text-[9px]">O</div>}
            </div>
            <span className={`max-w-[52px] truncate text-center text-[7px] font-black uppercase leading-none transition-colors duration-150 text-${accent}-200/80 group-hover:text-white group-focus:text-white`}>
                {player.name?.split(' ').pop()?.slice(0, 9)}
            </span>
        </Link>
    );
};

const HalfPitch = ({ club, lineup, accent, livePlayerStates }) => {
    const starters = lineup?.starters || [];
    const bench = lineup?.bench || [];
    const bgColor = accent === 'amber' ? '#0a0a0a' : '#0d0d0d';
    const fallbackRows = {};

    starters.forEach((player) => {
        if (Number.isFinite(Number(player.pitch_x)) && Number.isFinite(Number(player.pitch_y))) {
            return;
        }

        const row = getRow(player.slot);
        fallbackRows[row] = fallbackRows[row] || [];
        fallbackRows[row].push(player.id);
    });

    return (
        <div className="min-w-0 flex-1 space-y-3">
            <div className="flex items-center gap-2">
                <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-6 w-6 object-contain" />
                <div>
                    <p className="text-[10px] font-black uppercase tracking-tight text-white">{club?.short_name || club?.name}</p>
                    <p className={`text-[8px] font-black uppercase tracking-widest text-${accent}-500`}>{lineup?.formation || '-'}</p>
                </div>
            </div>

            <div className="relative overflow-hidden rounded-xl" style={{ height: 300, background: `radial-gradient(ellipse at 50% 0%, #1d4a1d 0%, ${bgColor} 80%)` }}>
                <HalfPitchSVG />
                <div className="absolute inset-0 px-3 py-4">
                    {starters.map((player) => {
                        const rowMembers = fallbackRows[getRow(player.slot)] || [];
                        const fallbackIndex = rowMembers.findIndex((playerId) => playerId === player.id);
                        const hasAbsolutePosition = Number.isFinite(Number(player.pitch_x)) && Number.isFinite(Number(player.pitch_y));
                        const fallbackPosition = getFallbackPitchPosition(player.slot, Math.max(fallbackIndex, 0), rowMembers.length || 1);
                        const left = hasAbsolutePosition ? Number(player.pitch_x) : fallbackPosition.left;
                        const top = hasAbsolutePosition ? 100 - Number(player.pitch_y) : fallbackPosition.top;

                        return (
                            <div
                                key={player.id}
                                className="absolute -translate-x-1/2 -translate-y-1/2"
                                style={{ left: `${left}%`, top: `${top}%` }}
                            >
                                <LineupToken player={player} accent={accent} livePlayerStates={livePlayerStates} />
                            </div>
                        );
                    })}
                </div>
            </div>

            {bench.length > 0 && (
                <div className="space-y-1">
                    <p className={`text-[8px] font-black uppercase tracking-widest text-${accent}-800`}>Bank</p>
                    <div className="flex flex-wrap gap-1.5">
                        {bench.map((player) => (
                            <div key={player.id} className={`flex items-center gap-1.5 rounded-lg border border-${accent}-500/10 bg-${accent}-500/5 px-2 py-1`}>
                                <span className={`text-[8px] font-black text-${accent}-400`}>{player.overall}</span>
                                <span className="max-w-[60px] truncate text-[8px] font-bold text-[var(--text-muted)]">{player.name?.split(' ').pop()}</span>
                                <span className="text-[7px] font-black uppercase text-slate-700">{player.position}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export const LineupPitch = ({ homeClub, awayClub, homeLineup, awayLineup, livePlayerStates }) => (
    <div className="flex flex-col gap-8 lg:flex-row">
        <HalfPitch club={homeClub} lineup={homeLineup} accent="amber" livePlayerStates={livePlayerStates} />
        <div className="hidden w-px shrink-0 self-stretch bg-white/5 lg:block" />
        <HalfPitch club={awayClub} lineup={awayLineup} accent="gold" livePlayerStates={livePlayerStates} />
    </div>
);

export const KeyEventsStrip = ({ actions = [] }) => {
    if (actions.length === 0) {
        return null;
    }

    return (
        <div className="sim-card flex flex-wrap items-center gap-3 p-5">
            {actions.slice(0, 10).map((action, index) => (
                <div
                    key={`${action.id || action.minute}-${index}`}
                    className={`flex items-center gap-2 rounded-xl border px-3 py-1.5 text-[10px] font-black ${
                        action.action_type === 'goal'
                            ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                            : action.action_type === 'yellow_card'
                              ? 'border-amber-500/20 bg-amber-500/10 text-amber-400'
                              : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                    }`}
                >
                    <ActionIcon type={action.action_type} />
                    <span>{formatMatchMinute(action.minute, action.display_minute)}</span>
                    {action.player_name && <span className="hidden sm:inline">{action.player_name.split(' ').pop()}</span>}
                </div>
            ))}
        </div>
    );
};

export const MatchEventTimeline = ({ actions = [], homeClubId }) => {
    const events = getTimelineEvents(actions);

    if (events.length === 0) {
        return null;
    }

    return (
        <div className="sim-card overflow-hidden p-0">
            <div className="border-b border-white/5 bg-[var(--bg-pillar)]/60 px-5 py-3">
                <div className="flex items-center justify-between gap-3">
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Match Timeline</div>
                    <div className="flex items-center gap-2 text-[9px] font-black uppercase tracking-[0.14em]">
                        <span className="inline-flex items-center gap-1 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-2 py-1 text-cyan-200">
                            <span className="h-2 w-2 rounded-full bg-cyan-300" />
                            Home
                        </span>
                        <span className="inline-flex items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-2 py-1 text-amber-200">
                            <span className="h-2 w-2 rounded-full bg-amber-300" />
                            Away
                        </span>
                    </div>
                </div>
            </div>
            <div className="px-5 py-5">
                <div className="relative mx-auto max-w-4xl">
                    <div className="absolute bottom-0 left-1/2 top-0 hidden w-px -translate-x-1/2 bg-white/10 md:block" />
                    <div className="space-y-4">
                        {events.map((action) => {
                            const isHomeAction = action.club_id === homeClubId;
                            const isCard = ['yellow_card', 'red_card', 'yellow_red_card'].includes(action.action_type);
                            const primaryPlayer = action.player_name || action.opponent_player_name || 'Unbekannt';
                            const secondaryPlayer = action.action_type === 'substitution'
                                ? `${action.opponent_player_name || 'Out'} -> ${action.player_name || 'In'}`
                                : action.assister_name || action.club_short_name || '';
                            const teamVisuals = getTeamVisuals(isHomeAction);
                            const alignment = isHomeAction ? 'md:pr-[calc(50%+1.75rem)]' : 'md:pl-[calc(50%+1.75rem)]';
                            const position = isHomeAction ? 'md:mr-auto' : 'md:ml-auto';
                            const minutePosition = isHomeAction ? 'md:right-[calc(50%+1.25rem)]' : 'md:left-[calc(50%+1.25rem)]';

                            return (
                                <div key={`${action.id}-${action.minute}-${action.action_type}`} className={`relative ${alignment}`}>
                                    <div className={`relative md:w-[calc(50%-1.75rem)] ${position}`}>
                                        <div className={`rounded-3xl border px-4 py-4 shadow-lg transition-colors ${teamVisuals.panel} ${teamVisuals.glow}`}>
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <div className={`inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${teamVisuals.badge}`}>
                                                        <span>{action.club_short_name || (isHomeAction ? 'Home' : 'Away')}</span>
                                                        <span>{formatTimelineLabel(action)}</span>
                                                    </div>
                                                    <div className="mt-2 text-base font-semibold leading-snug text-white">
                                                        <PlayerNameLink
                                                            playerId={action.player_id || action.opponent_player_id}
                                                            name={primaryPlayer}
                                                            className="text-white"
                                                        />
                                                    </div>
                                                    {secondaryPlayer && (
                                                        <div className="mt-1 text-xs font-medium text-white/65">
                                                            {secondaryPlayer}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className={`rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] ${teamVisuals.subtleBadge}`}>
                                                    {formatMatchMinute(action.minute, action.display_minute)}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div className={`absolute top-5 left-1/2 hidden h-6 w-6 -translate-x-1/2 items-center justify-center rounded-full border bg-[var(--bg-shell)] shadow-lg md:flex ${teamVisuals.dot} ${teamVisuals.glow}`}>
                                        {isCard ? (
                                            <div className={`h-3.5 w-3 rounded-sm border border-black/30 ${
                                                action.action_type === 'yellow_card' ? 'bg-amber-400' : 'bg-rose-500'
                                            }`} />
                                        ) : (
                                            <ActionIcon type={action.action_type} size={14} className={teamVisuals.textStrong} />
                                        )}
                                    </div>
                                    <div className={`absolute top-[1.2rem] hidden text-[9px] font-black uppercase tracking-[0.16em] text-white/35 md:block ${minutePosition}`}>
                                        {formatMatchMinute(action.minute, action.display_minute)}
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
};

export const MatchTabs = ({ entries, activeTab, onChange }) => (
    <nav className="no-scrollbar flex max-w-full items-center gap-1 overflow-x-auto rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60 p-1">
        {entries.map((entry) => (
            <button
                key={entry.key}
                onClick={() => onChange(entry.key)}
                className={`flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all sm:px-5 ${
                    activeTab === entry.key ? 'bg-amber-500 text-black shadow-lg shadow-amber-900/40' : 'text-[var(--text-muted)] hover:text-slate-300'
                }`}
            >
                {entry.label}
                {entry.count > 0 && (
                    <span className={`rounded-full px-1.5 py-0.5 text-[8px] font-black ${activeTab === entry.key ? 'bg-black/20' : 'bg-[var(--bg-content)] text-[var(--text-muted)]'}`}>
                        {entry.count}
                    </span>
                )}
            </button>
        ))}
    </nav>
);

export const OverviewTab = ({
    status,
    homeClub,
    awayClub,
    homeState,
    awayState,
    livePlayerStates,
    manageableClubIds,
    teamStates,
    onStyleChange,
    onShout,
    modulePanels,
    comparison,
    preMatchReport,
}) => {
    if (status === 'scheduled') {
        return (
            <div className="space-y-6">
                <PreMatchReport
                    homeClub={homeClub}
                    awayClub={awayClub}
                    comparison={comparison}
                    report={preMatchReport}
                />
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <MatchPulse
                homeClub={homeClub}
                awayClub={awayClub}
                homeState={homeState}
                awayState={awayState}
                livePlayerStates={livePlayerStates}
            />

            {manageableClubIds?.length > 0 && (
                <MatchCommandRail
                    matchStatus={status}
                    clubs={[homeClub, awayClub]}
                    manageableClubIds={manageableClubIds}
                    teamStates={teamStates}
                    onStyleChange={onStyleChange}
                    onShout={onShout}
                />
            )}

            {modulePanels?.length > 0 && (
                <ModulePanels panels={modulePanels} />
            )}

            <PreviewTab comparison={comparison} />
        </div>
    );
};

const FormChip = ({ result }) => (
    <span className={`inline-flex h-7 min-w-7 items-center justify-center rounded-full text-[10px] font-black uppercase ${
        result === 'W'
            ? 'bg-emerald-500/20 text-emerald-200'
            : result === 'L'
                ? 'bg-rose-500/20 text-rose-200'
                : 'bg-amber-500/20 text-amber-200'
    }`}>
        {result}
    </span>
);

const ComparisonDuelRow = ({ label, homeValue, awayValue, formatter = (value) => value }) => {
    const homeNumeric = Number(homeValue || 0);
    const awayNumeric = Number(awayValue || 0);
    const total = homeNumeric + awayNumeric;
    const homePct = total > 0 ? Math.max(8, Math.round((homeNumeric / total) * 100)) : 50;
    const awayPct = total > 0 ? Math.max(8, 100 - homePct) : 50;

    return (
        <div className="rounded-2xl border border-white/8 bg-white/[0.03] p-4">
            <div className="mb-3 flex items-center justify-between gap-3 text-[10px] font-black uppercase tracking-[0.16em]">
                <span className="text-cyan-200">{formatter(homeNumeric)}</span>
                <span className="text-[var(--text-muted)]">{label}</span>
                <span className="text-amber-200">{formatter(awayNumeric)}</span>
            </div>
            <div className="flex h-2 overflow-hidden rounded-full gap-1 bg-white/[0.04]">
                <div className="rounded-full bg-cyan-400" style={{ width: `${homePct}%` }} />
                <div className="rounded-full bg-amber-400" style={{ width: `${awayPct}%` }} />
            </div>
        </div>
    );
};

const formResultTone = (result) => (
    result === 'W'
        ? 'border-emerald-400/25 bg-emerald-400/12 text-emerald-200'
        : result === 'L'
            ? 'border-rose-400/25 bg-rose-400/12 text-rose-200'
            : 'border-amber-400/25 bg-amber-400/12 text-amber-200'
);

const trendBadgeTone = (rating) => {
    if (rating >= 8.5) {
        return 'bg-emerald-500 text-white';
    }

    if (rating >= 7.2) {
        return 'bg-lime-600 text-white';
    }

    if (rating >= 6.6) {
        return 'bg-amber-600 text-white';
    }

    return 'bg-slate-700 text-slate-200';
};

const FormTrendBoard = ({ club, form, accent = 'cyan' }) => {
    const matches = form?.matches || [];
    const stroke = accent === 'cyan' ? '#cbd5f5' : '#f8d28b';
    const badgeAccent = accent === 'cyan' ? 'text-cyan-200' : 'text-amber-200';
    const points = matches.map((entry, index) => {
        const step = 100 / (matches.length || 1);
        const x = (index * step) + (step / 2);
        const rating = Number(entry.trend_rating || 0);
        // Rating usually 5.0 to 9.8. Normalize 5.0 -> 0, 9.8 -> 1
        const normalized = Math.max(0, Math.min(1, (rating - 5) / 4.8));
        const y = 65 - (normalized * 45); // Range from 20 to 65 in 80px height

        return { ...entry, x, y, rating };
    });

    const polyline = points.map((point) => `${point.x},${point.y}`).join(' ');

    return (
        <div className="sim-card overflow-hidden p-0">
            <div className="border-b border-white/6 bg-white/[0.03] px-5 py-4">
                <div className="flex items-center gap-3">
                    <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-10 w-10 object-contain" />
                    <div>
                        <div className={`text-[10px] font-black uppercase tracking-[0.18em] ${badgeAccent}`}>Last 5 Matches</div>
                        <div className="text-lg font-black text-white">{club?.name}</div>
                    </div>
                </div>
            </div>

            <div className="p-5">
                <div 
                    className="grid gap-3"
                    style={{ gridTemplateColumns: `repeat(${matches.length || 1}, minmax(0, 1fr))` }}
                >
                    {matches.map((entry, index) => (
                        <div key={`${entry.id || index}-${entry.score}`} className="rounded-[1.7rem] border border-white/8 bg-white/[0.04] px-4 py-5 text-center">
                            <div className="text-xs text-white/45">{entry.relative_label || entry.kickoff_label}</div>
                            <div className="mt-4 flex justify-center">
                                <img loading="lazy" src={entry.opponent_logo_url} alt={entry.opponent_name} className="h-14 w-14 object-contain" />
                            </div>
                            <div className="mt-4 text-[1.05rem] font-black text-white">
                                {entry.opponent_name} <span className="text-white/45">({entry.is_home ? 'H' : 'A'})</span>
                            </div>
                            <div className="mt-4 inline-flex rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-xs font-semibold text-white/70">
                                {entry.competition_name}
                            </div>
                            <div className={`mt-4 inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-lg font-black ${formResultTone(entry.result)}`}>
                                <span className="inline-flex h-3.5 w-3.5 rounded-full border border-current/20 bg-current/90" />
                                {entry.score}
                            </div>
                        </div>
                    ))}
                </div>

                {points.length > 1 && (
                    <div className="mt-5 rounded-[1.6rem] border border-white/8 bg-white/[0.03] px-4 py-5">
                        <svg viewBox="0 0 100 80" className="h-28 w-full">
                            <polyline
                                fill="none"
                                stroke={stroke}
                                strokeWidth="1.5"
                                strokeLinejoin="round"
                                strokeLinecap="round"
                                points={polyline}
                            />
                            {points.map((point, index) => (
                                <g key={`${point.id || index}-trend`}>
                                    <circle cx={point.x} cy={point.y} r="3.4" fill={stroke} fillOpacity="0.18" />
                                    <circle cx={point.x} cy={point.y} r="2" fill={stroke} />
                                </g>
                            ))}
                        </svg>
                        <div 
                            className="-mt-5 grid gap-3"
                            style={{ gridTemplateColumns: `repeat(${matches.length || 1}, minmax(0, 1fr))` }}
                        >
                            {points.map((point, index) => (
                                <div key={`${point.id || index}-badge`} className="flex justify-center">
                                    <span className={`inline-flex rounded-full px-3 py-1 text-sm font-black shadow-lg ${trendBadgeTone(point.rating)}`}>
                                        {point.rating.toFixed(2)}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
};

const ClubFormCard = ({ club, form, tone }) => (
    <div className="sim-card p-5">
        <div className="mb-4 flex items-center gap-3">
            <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-11 w-11 object-contain" />
            <div>
                <div className={`text-[10px] font-black uppercase tracking-[0.16em] ${tone}`}>Formcheck</div>
                <div className="text-lg font-black text-white">{club?.name}</div>
            </div>
        </div>
        <div className="mb-4 flex flex-wrap items-center gap-2">
            {(form?.matches || []).map((entry, index) => (
                <FormChip key={`${entry.id || index}-${entry.result}`} result={entry.result} />
            ))}
        </div>
        <div className="grid grid-cols-4 gap-2">
            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Punkte</div>
                <div className="mt-1 text-lg font-black text-white">{form?.points ?? 0}</div>
            </div>
            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">S</div>
                <div className="mt-1 text-lg font-black text-emerald-200">{form?.wins ?? 0}</div>
            </div>
            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">U</div>
                <div className="mt-1 text-lg font-black text-amber-200">{form?.draws ?? 0}</div>
            </div>
            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">N</div>
                <div className="mt-1 text-lg font-black text-rose-200">{form?.losses ?? 0}</div>
            </div>
        </div>
    </div>
);

const KeyPlayersPanel = ({ club, players, accent = 'cyan' }) => {
    const tone = accent === 'cyan' ? 'text-cyan-200' : 'text-amber-200';
    const borderTone = accent === 'cyan' ? 'border-cyan-400/20' : 'border-amber-400/20';

    return (
        <div className="sim-card p-5">
            <div className="mb-4 flex items-center justify-between">
                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Key Player</div>
                <Star size={14} weight="fill" className={tone} />
            </div>
            <div className="space-y-3">
                {players && players.length > 0 ? players.map(player => (
                    <div key={player.id} className={`flex items-center gap-3 rounded-2xl border ${borderTone} bg-white/[0.03] px-3 py-3`}>
                        <img src={player.photo_url} alt={player.name} className="h-10 w-10 rounded-xl border border-white/10 object-cover" />
                        <div className="min-w-0 flex-1">
                            <div className="truncate text-[11px] font-black uppercase tracking-[0.06em] text-white">{player.name}</div>
                            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                {player.position} · {player.style}
                            </div>
                        </div>
                        <div className={`rounded-full border ${borderTone} bg-white/[0.05] px-2.5 py-1 text-[10px] font-black text-white`}>
                            {player.overall}
                        </div>
                    </div>
                )) : (
                    <div className="py-4 text-center text-xs italic text-[var(--text-muted)]">Keine Daten</div>
                )}
            </div>
        </div>
    );
};

const AbsenteesPanel = ({ homeClub, awayClub, absentees }) => (
    <div className="sim-card p-5">
        <div className="mb-4 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Ausfaelle / Sperren</div>
        <div className="grid gap-4 md:grid-cols-2">
            {[
                { club: homeClub, list: absentees?.home, tone: 'text-cyan-200' },
                { club: awayClub, list: absentees?.away, tone: 'text-amber-200' },
            ].map(({ club, list, tone }) => (
                <div key={club?.id} className="space-y-2">
                    <div className={`text-[9px] font-black uppercase tracking-widest ${tone}`}>{club?.short_name || club?.name}</div>
                    <div className="space-y-1.5">
                        {list && list.length > 0 ? list.map(player => (
                            <div key={player.id} className="flex items-center justify-between gap-2 rounded-xl border border-white/5 bg-white/[0.02] px-3 py-2">
                                <span className="truncate text-[10px] font-bold text-white">{player.name}</span>
                                <span className={`shrink-0 text-[8px] font-black uppercase ${player.type === 'suspension' ? 'text-rose-400' : 'text-amber-300'}`}>
                                    {player.reason}
                                </span>
                            </div>
                        )) : (
                            <div className="text-[10px] italic text-emerald-400/60">Keine Ausfaelle</div>
                        )}
                    </div>
                </div>
            ))}
        </div>
    </div>
);

const KeyDuelsPanel = ({ duels }) => (
    <div className="sim-card p-5">
        <div className="mb-4 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Schluesselduelle</div>
        <div className="space-y-3">
            {duels && duels.length > 0 ? duels.map((duel, idx) => (
                <div key={idx} className="rounded-2xl border border-white/8 bg-white/[0.03] p-3">
                    <div className="mb-2 text-center text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">{duel.label}</div>
                    <div className="flex items-center justify-between gap-2">
                        <div className="flex flex-1 items-center gap-2 min-w-0">
                            <img src={duel.home.photo_url} className="h-8 w-8 rounded-lg border border-cyan-400/20" />
                            <div className="min-w-0 flex-1">
                                <div className="truncate text-[10px] font-black text-white">{duel.home.name}</div>
                                <div className="text-[8px] text-cyan-300">{duel.home.overall} OVR</div>
                            </div>
                        </div>
                        <div className="text-[10px] font-black text-white/20 px-2">VS</div>
                        <div className="flex flex-1 flex-row-reverse items-center gap-2 min-w-0">
                            <img src={duel.away.photo_url} className="h-8 w-8 rounded-lg border border-amber-400/20" />
                            <div className="min-w-0 flex-1 text-right">
                                <div className="truncate text-[10px] font-black text-white">{duel.away.name}</div>
                                <div className="text-[8px] text-amber-300">{duel.away.overall} OVR</div>
                            </div>
                        </div>
                    </div>
                </div>
            )) : (
                <div className="py-2 text-center text-xs italic text-[var(--text-muted)]">Keine Schluesselduelle analysiert</div>
            )}
        </div>
    </div>
);

const ExpectedLineupLight = ({ homeClub, awayClub, lineupPreview }) => (
    <div className="sim-card p-5">
        <div className="mb-4 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Voraussichtliche Aufstellung</div>
        <div className="grid gap-6 md:grid-cols-2">
            {[
                { club: homeClub, players: lineupPreview?.home, accent: 'cyan' },
                { club: awayClub, players: lineupPreview?.away, accent: 'amber' },
            ].map(({ club, players, accent }) => (
                <div key={club?.id} className="space-y-3">
                    <div className="flex items-center gap-2">
                        <ClubLogo club={club} className="h-5 w-5" imgClassName="h-full w-full object-contain" />
                        <span className="text-[10px] font-black uppercase tracking-widest text-white">{club?.short_name || club?.name}</span>
                    </div>
                    <div className="grid grid-cols-2 gap-1.5">
                        {players && players.slice(0, 11).map(player => (
                            <div key={player.id} className={`flex items-center gap-1.5 rounded-lg border border-white/5 bg-white/[0.02] px-2 py-1`}>
                                <span className={`text-[8px] font-black uppercase text-${accent === 'amber' ? 'amber-400' : 'cyan-400'} w-4`}>{player.position}</span>
                                <span className="truncate text-[9px] font-medium text-white/80">{player.name?.split(' ').pop()}</span>
                            </div>
                        ))}
                    </div>
                </div>
            ))}
        </div>
    </div>
);

const SimulationDebugPanel = ({ homeClub, awayClub, comparison }) => {
    const renderClubColumn = (club, metrics, accent = 'cyan') => {
        const debug = metrics?.debug;
        const accentTone = accent === 'amber' ? 'text-amber-200' : 'text-cyan-200';
        const pillTone = accent === 'amber'
            ? 'border-amber-400/20 bg-amber-400/10 text-amber-100'
            : 'border-cyan-400/20 bg-cyan-400/10 text-cyan-100';

        return (
            <div key={club?.id} className="rounded-2xl border border-white/8 bg-white/[0.03] p-4">
                <div className="mb-4 flex items-center justify-between gap-3">
                    <div className="min-w-0">
                        <div className={`truncate text-sm font-black ${accentTone}`}>{club?.short_name || club?.name}</div>
                        <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                            Kernspieler {debug?.core_player_count ?? 0}
                        </div>
                    </div>
                    <div className={`rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${pillTone}`}>
                        Starke {Number(metrics?.strength || 0).toFixed(1)}
                    </div>
                </div>

                <div className="grid gap-2 sm:grid-cols-2">
                    <div className="rounded-xl border border-white/8 bg-black/20 px-3 py-3">
                        <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Moralmodell</div>
                        <div className="mt-1 text-sm font-black text-white">{debug?.morale_source || '-'}</div>
                        <div className="mt-1 text-[10px] font-bold text-white/60">{Number(debug?.morale_value || 0).toFixed(1)} im Schnitt</div>
                    </div>
                    <div className="rounded-xl border border-white/8 bg-black/20 px-3 py-3">
                        <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Fitnessmodell</div>
                        <div className="mt-1 text-sm font-black text-white">{debug?.fitness_source || '-'}</div>
                        <div className="mt-1 text-[10px] font-bold text-white/60">{Number(debug?.fitness_value || 0).toFixed(1)} im Schnitt</div>
                    </div>
                </div>

                <div className="mt-4 space-y-2">
                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Staerke traegt vor allem</div>
                    {(debug?.strength_top_players || []).length > 0 ? debug.strength_top_players.map((player, index) => (
                        <div key={`${club?.id}-strength-${index}`} className="flex items-center justify-between gap-3 rounded-xl border border-white/8 bg-black/20 px-3 py-2">
                            <div className="min-w-0">
                                <div className="truncate text-[10px] font-black text-white">{player.name}</div>
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{player.position}</div>
                            </div>
                            <div className="text-[10px] font-black text-white/75">{Number(player.value || 0).toFixed(1)} OVR</div>
                        </div>
                    )) : (
                        <div className="rounded-xl border border-dashed border-[var(--border-pillar)] px-3 py-3 text-sm text-[var(--text-muted)]">
                            Keine Treiber verfuegbar.
                        </div>
                    )}
                </div>
            </div>
        );
    };

    return (
        <div className="sim-card p-5">
            <div className="mb-4 flex items-center justify-between gap-3">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Simulation Debug</div>
                    <div className="mt-1 text-sm font-black text-white">Woraus die Vorschau ihre Werte ableitet</div>
                </div>
                <Lightning size={16} weight="fill" className="text-amber-400" />
            </div>
            <div className="grid gap-4 xl:grid-cols-2">
                {renderClubColumn(homeClub, comparison?.home, 'cyan')}
                {renderClubColumn(awayClub, comparison?.away, 'amber')}
            </div>
        </div>
    );
};

const PreMatchReport = ({ homeClub, awayClub, comparison, report }) => (
    <div className="space-y-6">
        <div className="sim-card overflow-hidden p-0">
            <div className="border-b border-white/6 bg-[linear-gradient(90deg,rgba(34,211,238,0.08),rgba(217,177,92,0.06),transparent)] px-6 py-4">
                <div className="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--accent-primary)]">Vorbericht</div>
                <div className="mt-1 text-xl font-black text-white">So gehen die Teams ins Spiel</div>
            </div>

            <div className="grid gap-5 p-6 xl:grid-cols-[1.25fr_0.75fr]">
                <div className="space-y-4">
                    <FormTrendBoard club={homeClub} form={report?.recent_form?.home} accent="cyan" />
                    <FormTrendBoard club={awayClub} form={report?.recent_form?.away} accent="amber" />

                    <div className="grid gap-4 md:grid-cols-2">
                        <KeyPlayersPanel club={homeClub} players={report?.key_players?.home} accent="cyan" />
                        <KeyPlayersPanel club={awayClub} players={report?.key_players?.away} accent="amber" />
                    </div>

                    <ExpectedLineupLight homeClub={homeClub} awayClub={awayClub} lineupPreview={report?.expected_lineup_preview} />

                    <div className="grid gap-4 md:grid-cols-2">
                        <ClubFormCard club={homeClub} form={report?.recent_form?.home} tone="text-cyan-200" />
                        <ClubFormCard club={awayClub} form={report?.recent_form?.away} tone="text-amber-200" />
                    </div>

                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                        <ComparisonDuelRow label="Kaderstaerke" homeValue={comparison?.home?.strength} awayValue={comparison?.away?.strength} formatter={(value) => Number(value || 0).toFixed(1)} />
                        <ComparisonDuelRow label="Marktwert" homeValue={comparison?.home?.market_value} awayValue={comparison?.away?.market_value} formatter={(value) => `${(Number(value || 0) / 1000000).toFixed(1)}M`} />
                        <ComparisonDuelRow label="Alter" homeValue={comparison?.home?.avg_age} awayValue={comparison?.away?.avg_age} formatter={(value) => Number(value || 0).toFixed(1)} />
                        <ComparisonDuelRow label="Moral" homeValue={comparison?.home?.morale} awayValue={comparison?.away?.morale} formatter={(value) => Number(value || 0).toFixed(1)} />
                        <ComparisonDuelRow label="Fitness" homeValue={comparison?.home?.fitness} awayValue={comparison?.away?.fitness} formatter={(value) => Number(value || 0).toFixed(1)} />
                    </div>

                    <SimulationDebugPanel homeClub={homeClub} awayClub={awayClub} comparison={comparison} />
                </div>

                <div className="space-y-4">
                    <AbsenteesPanel homeClub={homeClub} awayClub={awayClub} absentees={report?.absentees} />
                    <KeyDuelsPanel duels={report?.key_duels} />

                    {report?.league_snapshot && (
                        <div className="sim-card p-5">
                            <div className="mb-4 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                                Tabelle {report.league_snapshot.competition ? `· ${report.league_snapshot.competition}` : ''}
                            </div>
                            <div className="grid gap-3">
                                {[
                                    { club: homeClub, row: report.league_snapshot.home, tone: 'text-cyan-200' },
                                    { club: awayClub, row: report.league_snapshot.away, tone: 'text-amber-200' },
                                ].map(({ club, row, tone }) => row ? (
                                    <div key={club?.id} className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="min-w-0">
                                                <div className={`truncate text-sm font-black ${tone}`}>{club?.name}</div>
                                                <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                                    Platz {row.position} · {row.points} Punkte
                                                </div>
                                            </div>
                                            <div className="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/75">
                                                TD {row.goal_diff > 0 ? '+' : ''}{row.goal_diff}
                                            </div>
                                        </div>
                                    </div>
                                ) : null)}
                            </div>
                        </div>
                    )}

                    <div className="sim-card p-5">
                        <div className="mb-4 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Direktvergleich</div>
                        <div className="mb-4 grid grid-cols-3 gap-3">
                            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-3 text-center">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-cyan-200">Heim</div>
                                <div className="mt-1 text-xl font-black text-white">{report?.head_to_head?.home_wins ?? 0}</div>
                            </div>
                            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-3 text-center">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-amber-200">Remis</div>
                                <div className="mt-1 text-xl font-black text-white">{report?.head_to_head?.draws ?? 0}</div>
                            </div>
                            <div className="rounded-xl border border-white/8 bg-white/[0.03] px-3 py-3 text-center">
                                <div className="text-[9px] font-black uppercase tracking-[0.14em] text-amber-300">Gast</div>
                                <div className="mt-1 text-xl font-black text-white">{report?.head_to_head?.away_wins ?? 0}</div>
                            </div>
                        </div>
                        <div className="space-y-2">
                            {(report?.head_to_head?.matches || []).length > 0 ? report.head_to_head.matches.map((entry) => (
                                <div key={entry.id} className="flex items-center justify-between gap-3 rounded-xl border border-white/8 bg-white/[0.03] px-3 py-2">
                                    <span className="text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{entry.date}</span>
                                    <span className="text-sm font-black text-white">{entry.score}</span>
                                </div>
                            )) : (
                                <div className="rounded-xl border border-dashed border-[var(--border-pillar)] px-3 py-4 text-sm text-[var(--text-muted)]">
                                    Noch kein Direktvergleich vorhanden.
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {(report?.insights || []).length > 0 && (
            <div className="grid gap-3 md:grid-cols-3">
                {report.insights.map((insight, index) => (
                    <div key={`${insight}-${index}`} className="sim-card p-4">
                        <div className="mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Insight {index + 1}</div>
                        <div className="text-sm leading-relaxed text-white/85">{insight}</div>
                    </div>
                ))}
            </div>
        )}
    </div>
);

export const PreviewTab = ({ comparison }) => (
    <div className="grid gap-8 md:grid-cols-3">
        {[
            { label: 'Kaderstaerke', home: comparison?.home?.strength, away: comparison?.away?.strength, fmt: (value) => parseFloat(value).toFixed(1) },
            { label: 'Marktwert (M)', home: comparison?.home?.market_value, away: comparison?.away?.market_value, fmt: (value) => `${(value / 1_000_000).toFixed(1)}M` },
            { label: 'Durchschnittsalter', home: comparison?.home?.avg_age, away: comparison?.away?.avg_age, fmt: (value) => parseFloat(value).toFixed(1) },
        ].map(({ label, home, away, fmt }) => (
            <div key={label} className="sim-card p-6">
                <p className="mb-6 text-center text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
                <div className="mb-4 flex items-center justify-between text-sm font-black text-white">
                    <span className="text-amber-500">{fmt(home || 0)}</span>
                    <span className="text-slate-700">vs</span>
                    <span className="text-[#d4af37]">{fmt(away || 0)}</span>
                </div>
                <StatBar label="" home={parseFloat(home || 0)} away={parseFloat(away || 0)} />
            </div>
        ))}
    </div>
);

export const TickerTab = ({ actions, homeClubId, status }) => (
    (() => {
        const scoreLookup = buildScorelineLookup(actions, homeClubId);

        return (
            <div className="space-y-5">
                <div className="sim-card overflow-hidden p-0">
                    <div className="flex items-center gap-3 border-b border-white/5 bg-[var(--bg-pillar)]/60 px-6 py-4">
                        <SoccerBall size={18} weight="fill" className="text-amber-500" />
                        <h3 className="text-xs font-black uppercase tracking-widest text-white">Spielverlauf</h3>
                        <span className="ml-auto text-[9px] font-black uppercase tracking-widest text-slate-600">{actions.length} Aktionen</span>
                    </div>
                    {status === 'played' && (
                        <div className="border-b border-emerald-400/15 bg-emerald-500/8 px-6 py-3">
                            <div className="flex items-center gap-3">
                                <span className="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400" />
                                <div>
                                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-200">Schlusspfiff</div>
                                    <div className="text-sm font-black text-white">Das Spiel ist zu Ende und wurde abgepfiffen.</div>
                                </div>
                            </div>
                        </div>
                    )}
                    {actions.length === 0 ? (
                        <div className="p-20 text-center">
                            <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                            <p className="text-sm font-bold italic uppercase tracking-widest text-[var(--text-muted)]">Noch keine Aktionen.</p>
                        </div>
                    ) : (
                        <div className="custom-scrollbar max-h-[70vh] overflow-y-auto">
                            {actions.map((action, index) => (
                                <TickerItem
                                    key={`${action.id}-${index}`}
                                    action={action}
                                    homeClubId={homeClubId}
                                    resolvedScoreline={scoreLookup[getActionLookupKey(action)] || scoreLookup[`${getActionLookupKey(action)}-${index}`] || null}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        );
    })()
);

export const HighlightsTab = ({ actions, homeClubId }) => (
    <div className="space-y-5">
        <MatchEventTimeline actions={actions} homeClubId={homeClubId} />
    </div>
);

export const StatsTab = ({ homeState, awayState }) => (
    <div className="sim-card p-8">
        {!homeState && !awayState ? (
            <p className="py-12 text-center italic text-[var(--text-muted)]">Noch keine Statistiken verfuegbar.</p>
        ) : (
            <div className="mx-auto max-w-2xl space-y-6">
                {[
                    { label: 'Schuesse', home: homeState?.shots, away: awayState?.shots },
                    { label: 'Schuesse aufs Tor', home: homeState?.shots_on_target, away: awayState?.shots_on_target },
                    { label: 'Gefaehrliche Angriffe', home: homeState?.dangerous_attacks, away: awayState?.dangerous_attacks },
                    { label: 'Paesse', home: homeState?.pass_completions, away: awayState?.pass_completions },
                    { label: 'Fouls', home: homeState?.fouls_committed, away: awayState?.fouls_committed },
                    { label: 'Ecken', home: homeState?.corners_won, away: awayState?.corners_won },
                    { label: 'Gelbe Karten', home: homeState?.yellow_cards, away: awayState?.yellow_cards },
                    { label: 'Rote Karten', home: homeState?.red_cards, away: awayState?.red_cards },
                ].map((entry) => (
                    <StatBar key={entry.label} {...entry} />
                ))}
            </div>
        )}
    </div>
);

export const LiveTableTab = ({ liveTable }) => {
    if (!liveTable?.rows?.length) {
        return (
            <div className="sim-card p-8">
                <p className="py-10 text-center italic text-[var(--text-muted)]">Fuer dieses Spiel ist keine Livetabelle verfuegbar.</p>
            </div>
        );
    }

    return (
        <div className="sim-card overflow-hidden p-0">
            <div className="flex items-center gap-3 border-b border-white/5 bg-[var(--bg-pillar)]/60 px-6 py-4">
                <Trophy size={18} weight="fill" className="text-amber-400" />
                <div>
                    <div className="text-xs font-black uppercase tracking-widest text-white">Livetabelle</div>
                    <div className="text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                        {liveTable.competition}
                        {liveTable.is_live_projection ? ' · Live-Projektion' : ''}
                    </div>
                </div>
            </div>

            <div className="overflow-x-auto">
                <div className="grid min-w-[820px] grid-cols-[3rem_3rem_1fr_repeat(6,_4rem)] gap-2 border-b border-white/5 px-6 py-3 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                    <div className="text-center">#</div>
                    <div />
                    <div>Verein</div>
                    <div className="text-center">Sp</div>
                    <div className="text-center">TD</div>
                    <div className="text-center">Pkt</div>
                    <div className="text-center">Form</div>
                    <div className="text-center">Trend</div>
                    <div className="text-center">Live</div>
                </div>

                {liveTable.rows.map((row) => {
                    const isHome = row.club_id === liveTable.home_club_id;
                    const isAway = row.club_id === liveTable.away_club_id;
                    const tone = isHome ? 'border-l-cyan-400 bg-cyan-400/[0.04]' : isAway ? 'border-l-amber-400 bg-amber-400/[0.04]' : '';

                    return (
                        <div
                            key={row.club_id}
                            className={`grid min-w-[820px] grid-cols-[3rem_3rem_1fr_repeat(6,_4rem)] items-center gap-2 border-b border-white/5 border-l-2 px-6 py-3 transition-colors hover:bg-white/[0.02] ${tone}`}
                        >
                            <div className={`text-center text-sm font-black italic ${row.position === 1 ? 'text-amber-400' : 'text-white'}`}>{row.position}</div>
                            <div className="flex justify-center">
                                <img loading="lazy" src={row.club_logo_url} alt={row.club_name} className="h-8 w-8 object-contain" />
                            </div>
                            <div className="truncate text-sm font-black uppercase tracking-tight text-white">{row.club_name}</div>
                            <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.played}</div>
                            <div className="text-center text-xs font-bold text-slate-300">
                                {row.goals_for}:{row.goals_against}
                                <span className={`ml-1 text-[9px] ${row.goal_diff > 0 ? 'text-emerald-400' : row.goal_diff < 0 ? 'text-rose-400' : 'text-slate-500'}`}>
                                    ({row.goal_diff > 0 ? '+' : ''}{row.goal_diff})
                                </span>
                            </div>
                            <div className="text-center text-base font-black italic text-white">{row.points}</div>
                            <div className="flex items-center justify-center gap-1">
                                {(row.form || []).slice(0, 5).map((result, index) => (
                                    <div
                                        key={`${row.club_id}-${index}`}
                                        className={`h-2.5 w-2.5 rounded-full ${result === 'W' ? 'bg-emerald-500' : result === 'D' ? 'bg-amber-400' : 'bg-rose-500'}`}
                                    />
                                ))}
                            </div>
                            <div className="text-center text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                {row.position === 1 ? 'Top' : row.position <= 4 ? 'CL' : row.position >= liveTable.rows.length - 2 ? 'Ab' : '-'}
                            </div>
                            <div className="flex justify-center">
                                {(isHome || isAway) ? (
                                    <div className={`rounded-full border px-2 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${isHome ? 'border-cyan-400/20 bg-cyan-400/10 text-cyan-200' : 'border-amber-400/20 bg-amber-400/10 text-amber-200'}`}>
                                        {isHome ? 'Heim' : 'Gast'}
                                    </div>
                                ) : (
                                    <span className="text-[10px] font-black uppercase tracking-[0.14em] text-slate-600">-</span>
                                )}
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
};

export const PlayersTab = ({ clubs, finalStats }) => (
    <div className="grid gap-8 md:grid-cols-2">
        {clubs.map((club) => {
            const players = finalStats.filter((stat) => stat.club_id === club?.id).sort((a, b) => b.rating - a.rating);

            return (
                <div key={club?.id} className="sim-card overflow-hidden p-0">
                    <div className="flex items-center gap-4 border-b border-white/5 bg-[var(--bg-pillar)]/60 px-6 py-4">
                        <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-8 w-8 object-contain" />
                        <p className="text-xs font-black uppercase tracking-tighter text-white">{club?.name}</p>
                    </div>
                    <div>
                        <div className="grid grid-cols-[1fr_2rem_2rem_2rem_2rem_2rem] border-b border-white/5 px-4 py-2 text-[8px] font-black uppercase tracking-widest text-slate-600 sm:grid-cols-[1fr_3.5rem_3.5rem_3.5rem_3.5rem_3.5rem] sm:text-[9px]">
                            <span>Spieler</span>
                            <span className="text-center">Min</span>
                            <span className="text-center">T</span>
                            <span className="text-center">V</span>
                            <span className="text-center hidden sm:block">Schu</span>
                            <span className="text-center sm:hidden">S</span>
                            <span className="text-center">Note</span>
                        </div>
                        {players.map((player) => (
                            <div key={player.player_id} className="grid grid-cols-[1fr_2rem_2rem_2rem_2rem_2rem] items-center border-b border-white/5 px-4 py-3 hover:bg-white/[0.02] sm:grid-cols-[1fr_3.5rem_3.5rem_3.5rem_3.5rem_3.5rem]">
                                <span className="truncate pr-2 text-[10px] font-bold text-white sm:text-xs">{player.player_name}</span>
                                <span className="text-center text-[9px] text-[var(--text-muted)] sm:text-[10px]">{player.minutes_played}'</span>
                                <span className="text-center text-[9px] font-black text-emerald-400 sm:text-[10px]">{player.goals}</span>
                                <span className="text-center text-[9px] font-black text-amber-500 sm:text-[10px]">{player.assists}</span>
                                <span className="text-center text-[9px] text-[var(--text-muted)] sm:text-[10px]">{player.shots}</span>
                                <span className={`text-center text-[10px] font-black italic sm:text-xs ${player.rating >= 7 ? 'text-emerald-400' : 'text-[var(--text-muted)]'}`}>
                                    {parseFloat(player.rating).toFixed(1)}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            );
        })}
    </div>
);
