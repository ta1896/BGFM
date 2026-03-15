import React from 'react';
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
    GK: 0, SW: 0.3,
    LB: 1, RB: 1, CB: 1, 'CB-L': 1, 'CB-R': 1, 'CB-M': 1, 'CB-3L': 1, 'CB-3R': 1, LWB: 1.5, RWB: 1.5,
    DM: 2, 'DM-L': 2, 'DM-R': 2, HL: 2, HR: 2,
    CM: 3, 'CM-L': 3, 'CM-R': 3, 'CM-M': 3, LM: 3, RM: 3,
    AM: 4, 'AM-L': 4, 'AM-R': 4,
    LW: 5, RW: 5, SS: 5, 'SS-L': 5, 'SS-R': 5,
    ST: 6, 'ST-L': 6, 'ST-R': 6, CF: 6,
};

const getActionConfig = (type) => ACTION_CONFIG[type] || ACTION_CONFIG.default;

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
                <div className="h-full rounded-full bg-amber-500 transition-all duration-700 ease-out" style={{ width: `${homePct}%` }} />
                <div className="h-full rounded-full bg-[#d4af37] transition-all duration-700 ease-out" style={{ width: `${awayPct}%` }} />
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

export const ScoreHero = ({ home_club, away_club, home_score, away_score, status, live_minute, kickoff_formatted, competition, matchday, weather, type }) => {
    const isLive = status === 'live';
    const isPlayed = status === 'played';

    return (
        <div className="relative overflow-hidden rounded-[2rem] border border-[var(--border-muted)] bg-gradient-to-br from-[#080d1a] to-[#0e1628] shadow-2xl">
            <div className="pointer-events-none absolute inset-0 bg-gradient-to-r from-amber-500/5 via-transparent to-amber-600/5" />
            <div className="pointer-events-none absolute -top-32 left-1/2 h-64 w-96 -translate-x-1/2 rounded-full bg-amber-500/5 blur-[100px]" />

            <div className="flex items-center justify-center gap-3 pt-8 pb-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                <Trophy size={12} weight="fill" className="text-amber-500" />
                {competition || type || 'Spiel'}
                {matchday && <span>- Spieltag {matchday}</span>}
            </div>

            <div className="flex items-center justify-between gap-2 px-4 pb-6 sm:gap-6 sm:px-12 sm:pb-8">
                <div className="flex flex-1 flex-col items-center gap-2 sm:gap-4">
                    <div className="h-16 w-16 rounded-full border border-white/10 bg-white/5 p-2 shadow-2xl sm:h-24 sm:w-24 sm:p-3">
                        <img loading="lazy" src={home_club?.logo_url} alt={home_club?.name} className="h-full w-full object-contain" />
                    </div>
                    <div className="text-center">
                        <p className="text-sm font-black uppercase tracking-tighter text-white italic sm:text-xl">{home_club?.short_name || home_club?.name}</p>
                        <p className="text-[8px] font-black uppercase tracking-widest text-slate-600 sm:text-[9px]">Heim</p>
                    </div>
                </div>

                <div className="flex shrink-0 flex-col items-center gap-2 sm:gap-3">
                    {isLive || isPlayed ? (
                        <div className="flex items-center gap-3 sm:gap-6">
                            <span className="text-5xl font-black leading-none text-white italic tabular-nums sm:text-7xl md:text-8xl">{home_score ?? 0}</span>
                            <span className="text-2xl font-black text-slate-700 italic sm:text-4xl">:</span>
                            <span className="text-5xl font-black leading-none text-white italic tabular-nums sm:text-7xl md:text-8xl">{away_score ?? 0}</span>
                        </div>
                    ) : (
                        <div className="text-center">
                            <p className="text-3xl font-black text-slate-300 italic">{kickoff_formatted}</p>
                            <p className="mt-1 text-xs uppercase tracking-widest text-slate-600">Anstoss</p>
                        </div>
                    )}

                    <div className={`flex items-center gap-2 rounded-full border px-5 py-2 text-[10px] font-black uppercase tracking-widest ${
                        isLive ? 'border-rose-500/40 bg-rose-500/15 text-rose-400' :
                        isPlayed ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400' :
                        'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                    }`}>
                        {isLive && <span className="relative flex h-2 w-2"><span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-400 opacity-75" /><span className="relative inline-flex h-2 w-2 rounded-full bg-rose-500" /></span>}
                        {isLive ? `${live_minute}'` : isPlayed ? 'Beendet' : 'Geplant'}
                    </div>
                </div>

                <div className="flex flex-1 flex-col items-center gap-2 sm:gap-4">
                    <div className="h-16 w-16 rounded-full border border-white/10 bg-white/5 p-2 shadow-2xl sm:h-24 sm:w-24 sm:p-3">
                        <img loading="lazy" src={away_club?.logo_url} alt={away_club?.name} className="h-full w-full object-contain" />
                    </div>
                    <div className="text-center">
                        <p className="text-sm font-black uppercase tracking-tighter text-white italic sm:text-xl">{away_club?.short_name || away_club?.name}</p>
                        <p className="text-[8px] font-black uppercase tracking-widest text-slate-600 sm:text-[9px]">Gast</p>
                    </div>
                </div>
            </div>

            {(weather || kickoff_formatted) && (
                <div className="flex items-center justify-center gap-6 border-t border-white/5 px-8 py-3 text-[10px] font-black uppercase tracking-widest text-slate-600">
                    {kickoff_formatted && <span>{kickoff_formatted}</span>}
                    {weather && <span>{weather}</span>}
                    {type && <span>{type === 'league' ? 'Liga' : type === 'cup_national' ? 'Pokal' : 'Testspiel'}</span>}
                </div>
            )}
        </div>
    );
};

export const TickerItem = ({ action, homeClubId }) => {
    const key = isKeyEvent(action.action_type);
    const { bg, color } = getActionConfig(action.action_type);

    if (!key && !action.narrative) {
        return null;
    }

    return (
        <div className={`flex items-start gap-4 border-b border-white/5 px-6 py-4 transition-all hover:bg-white/[0.02] ${key ? '' : 'opacity-70'}`}>
            <div className="w-12 shrink-0 pt-1 text-right">
                <span className={`text-xs font-black italic tabular-nums ${key ? 'text-white' : 'text-slate-600'}`}>{action.minute}'</span>
            </div>
            <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl border transition-all ${
                key ? bg : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60'
            }`}>
                <ActionIcon type={action.action_type} size={key ? 18 : 15} />
            </div>
            <div className="min-w-0 flex-1">
                {key && (
                    <p className="mb-0.5 text-xs font-black uppercase tracking-tight leading-tight text-white">
                        <span className={`${color} mr-1`}>{EVENT_LABELS[action.action_type] || action.action_type}</span>
                        {action.player_name && <span className="text-slate-200">- {action.player_name}</span>}
                        {action.assister_name && <span className="text-[var(--text-muted)]"> (V: {action.assister_name})</span>}
                        {action.metadata?.score && <span className="ml-2 italic text-[var(--text-muted)]">[{action.metadata.score}]</span>}
                    </p>
                )}
                {!key && action.action_type && (
                    <p className="mb-0.5 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                        {EVENT_LABELS[action.action_type] || action.action_type}
                        {action.player_name && <span className="ml-1 text-slate-600">- {action.player_name}</span>}
                    </p>
                )}
                {action.narrative && <p className={`text-xs leading-snug ${key ? 'text-[var(--text-muted)]' : 'text-slate-600'}`}>{action.narrative}</p>}
                {key && action.club_short_name && <p className="mt-1 text-[9px] font-black uppercase tracking-widest text-slate-700">{action.club_short_name}{action.club_id === homeClubId ? ' (Heim)' : ' (Gast)'}</p>}
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

    return (
        <div className="relative flex flex-col items-center gap-1">
            <div className={`relative flex h-8 w-8 items-center justify-center rounded-full border-2 shadow-md ${
                isOff ? 'border-rose-500/60 bg-rose-950/60 opacity-40' : `border-${accent}-400/80 bg-[var(--sim-shell-bg)]`
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
            <span className={`max-w-[44px] truncate text-center text-[7px] font-black uppercase leading-none text-${accent}-200/80`}>
                {player.name?.split(' ').pop()?.slice(0, 7)}
            </span>
        </div>
    );
};

const HalfPitch = ({ club, lineup, accent, livePlayerStates }) => {
    const starters = lineup?.starters || [];
    const bench = lineup?.bench || [];
    const rows = {};

    starters.forEach((player) => {
        const row = getRow(player.slot);
        rows[row] = rows[row] || [];
        rows[row].push(player);
    });

    const sortedRows = Object.keys(rows).sort((a, b) => parseFloat(a) - parseFloat(b));
    const bgColor = accent === 'amber' ? '#0a0a0a' : '#0d0d0d';

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
                <div className="absolute inset-x-0 inset-y-0 flex flex-col justify-between px-2 py-4">
                    {sortedRows.map((rowKey) => (
                        <div key={rowKey} className="flex flex-wrap items-center justify-center gap-2">
                            {rows[rowKey].map((player) => (
                                <LineupToken key={player.id} player={player} accent={accent} livePlayerStates={livePlayerStates} />
                            ))}
                        </div>
                    ))}
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
                    <span>{action.minute}'</span>
                    {action.player_name && <span className="hidden sm:inline">{action.player_name.split(' ').pop()}</span>}
                </div>
            ))}
        </div>
    );
};

export const MatchTabs = ({ entries, activeTab, onChange }) => (
    <nav className="flex w-fit flex-wrap items-center gap-1 rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60 p-1">
        {entries.map((entry) => (
            <button
                key={entry.key}
                onClick={() => onChange(entry.key)}
                className={`flex items-center gap-2 rounded-xl px-5 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all ${
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

export const TickerTab = ({ actions, homeClubId }) => (
    <div className="sim-card overflow-hidden p-0">
        <div className="flex items-center gap-3 border-b border-white/5 bg-[var(--bg-pillar)]/60 px-6 py-4">
            <SoccerBall size={18} weight="fill" className="text-amber-500" />
            <h3 className="text-xs font-black uppercase tracking-widest text-white">Spielverlauf</h3>
            <span className="ml-auto text-[9px] font-black uppercase tracking-widest text-slate-600">{actions.length} Aktionen</span>
        </div>
        {actions.length === 0 ? (
            <div className="p-20 text-center">
                <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                <p className="text-sm font-bold italic uppercase tracking-widest text-[var(--text-muted)]">Noch keine Aktionen.</p>
            </div>
        ) : (
            <div className="custom-scrollbar max-h-[70vh] overflow-y-auto">
                {actions.map((action, index) => (
                    <TickerItem key={`${action.id}-${index}`} action={action} homeClubId={homeClubId} />
                ))}
            </div>
        )}
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
