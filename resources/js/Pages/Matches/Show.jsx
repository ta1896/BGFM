import React, { useState, useEffect, useCallback, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    SoccerBall, Lightning, ArrowLeft,
    ArrowsDownUp, FirstAidKit, Cards, Trophy,
    Play, ArrowRight, Target,
    Star, CaretRight, ShieldCheck, HandFist, Wind,
    ArrowBendUpRight, Crosshair,
    CornersOut, WarningDiamond, Prohibit, Timer,
    PersonSimpleRun, Flag,
    ArrowsCounterClockwise as Swap
} from '@phosphor-icons/react';

/* ─────────────────── Icon config per action type ─────────────────── */
const ACTION_CONFIG = {
    // Goals & Scoring
    goal:              { Icon: SoccerBall,       color: 'text-emerald-400', bg: 'bg-emerald-500/20 border-emerald-500/30' },
    penalty:           { Icon: Target,            color: 'text-amber-400',   bg: 'bg-amber-500/20 border-amber-500/30' },
    own_goal:          { Icon: SoccerBall,        color: 'text-rose-400',    bg: 'bg-rose-500/20 border-rose-500/30' },
    // Cards
    yellow_card:       { Icon: Cards,             color: 'text-amber-400',   bg: 'bg-amber-500/20 border-amber-500/30' },
    red_card:          { Icon: Cards,             color: 'text-rose-500',    bg: 'bg-rose-500/20 border-rose-500/30' },
    yellow_red_card:   { Icon: Cards,             color: 'text-orange-400',  bg: 'bg-orange-500/20 border-orange-500/30' },
    // Substitution
    substitution:      { Icon: Swap,              color: 'text-indigo-400',  bg: 'bg-indigo-500/20 border-indigo-500/30' },
    // Injury
    injury:            { Icon: FirstAidKit,       color: 'text-rose-300',    bg: 'bg-rose-500/15 border-rose-500/20' },
    // Saves & Defense
    save:              { Icon: ShieldCheck,       color: 'text-amber-400',    bg: 'bg-amber-500/20 border-amber-500/30' },
    block:             { Icon: HandFist,          color: 'text-amber-300',    bg: 'bg-amber-500/15 border-amber-500/20' },
    tackle:            { Icon: HandFist,          color: 'text-amber-600',    bg: 'bg-amber-600/15 border-amber-600/20' },
    tackle_won:        { Icon: HandFist,          color: 'text-amber-600',    bg: 'bg-amber-600/15 border-amber-600/20' },
    tackle_lost:       { Icon: HandFist,          color: 'text-rose-400',    bg: 'bg-rose-500/10 border-rose-500/15' },
    clearance:         { Icon: Wind,              color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    interception:      { Icon: CornersOut,        color: 'text-teal-400',    bg: 'bg-teal-500/15 border-teal-500/20' },
    // Shots
    shot:              { Icon: Lightning,         color: 'text-amber-300',   bg: 'bg-amber-500/15 border-amber-500/20' },
    chance:            { Icon: Lightning,         color: 'text-amber-300',   bg: 'bg-amber-500/15 border-amber-500/20' },
    shot_on_target:    { Icon: Crosshair,         color: 'text-amber-400',   bg: 'bg-amber-500/20 border-amber-500/30' },
    shot_off_target:   { Icon: Crosshair,         color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    shot_blocked:      { Icon: Prohibit,          color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-content)] border-[var(--border-pillar)]' },
    // Set Pieces
    corner:            { Icon: Flag,              color: 'text-sky-400',     bg: 'bg-sky-500/15 border-sky-500/20' },
    free_kick:         { Icon: Target,            color: 'text-violet-400',  bg: 'bg-violet-500/15 border-violet-500/20' },
    // Passing
    pass:              { Icon: ArrowBendUpRight,  color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pass_completed:    { Icon: ArrowBendUpRight,  color: 'text-emerald-500/70', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pass_failed:       { Icon: ArrowBendUpRight,  color: 'text-rose-400/50', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    key_pass:          { Icon: Star,              color: 'text-amber-400',   bg: 'bg-amber-500/20 border-amber-500/30' },
    // Dribble
    dribble:           { Icon: PersonSimpleRun,   color: 'text-purple-400',  bg: 'bg-purple-500/15 border-purple-500/20' },
    dribble_success:   { Icon: PersonSimpleRun,   color: 'text-purple-400',  bg: 'bg-purple-500/15 border-purple-500/20' },
    dribble_failed:    { Icon: PersonSimpleRun,   color: 'text-rose-400/50', bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    // Foul / Offside
    foul:              { Icon: WarningDiamond,    color: 'text-orange-400',  bg: 'bg-orange-500/15 border-orange-500/20' },
    offside:           { Icon: Flag,              color: 'text-rose-300',    bg: 'bg-rose-500/10 border-rose-500/15' },
    // Possession / Misc
    possession_loss:   { Icon: Timer,             color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    pressure:          { Icon: Wind,              color: 'text-[var(--text-muted)]',   bg: 'bg-[var(--bg-pillar)] border-[var(--border-pillar)]' },
    assist:            { Icon: ArrowRight,         color: 'text-amber-500',   bg: 'bg-amber-500/15 border-amber-500/20' },
    // Fallback
    default:           { Icon: CaretRight,        color: 'text-slate-600',   bg: 'bg-transparent border-transparent' },
};

const getActionConfig = (type) => ACTION_CONFIG[type] || ACTION_CONFIG.default;

const ActionIcon = ({ type, size = 16, className = '' }) => {
    const { Icon, color } = getActionConfig(type);
    return <Icon size={size} weight="fill" className={`${color} ${className}`} />;
};

const isKeyEvent = (type) => ['goal','own_goal','yellow_card','red_card','yellow_red_card','substitution','injury','penalty'].includes(type);

const StatBar = ({ label, home, away }) => {
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
            <div className="flex h-1.5 rounded-full overflow-hidden gap-0.5">
                <motion.div initial={{ width: 0 }} animate={{ width: `${homePct}%` }} className="h-full bg-amber-500 rounded-full" />
                <motion.div initial={{ width: 0 }} animate={{ width: `${awayPct}%` }} className="h-full bg-[#d4af37] rounded-full" />
            </div>
        </div>
    );
};

/* ──────────────────────────  Scoreboard Hero  ────────────────────────── */
const ScoreHero = ({ home_club, away_club, home_score, away_score, status, live_minute, kickoff_formatted, competition, matchday, weather, type }) => {
    const isLive = status === 'live';
    const isPlayed = status === 'played';

    return (
        <div className="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#080d1a] to-[#0e1628] border border-[var(--border-muted)] shadow-2xl">
            <div className="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-transparent to-amber-600/5 pointer-events-none" />
            <div className="absolute -top-32 left-1/2 -translate-x-1/2 w-96 h-64 bg-amber-500/5 blur-[100px] rounded-full pointer-events-none" />

            {/* Competition bar */}
            <div className="flex items-center justify-center gap-3 pt-8 pb-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                <Trophy size={12} weight="fill" className="text-amber-500" />
                {competition || type || 'Spiel'}
                {matchday && <span>• Spieltag {matchday}</span>}
            </div>

            {/* Main Score */}
            <div className="flex items-center justify-between px-4 sm:px-12 pb-6 sm:pb-8 gap-2 sm:gap-6">
                {/* Home */}
                <div className="flex flex-col items-center gap-2 sm:gap-4 flex-1">
                    <div className="w-16 h-16 sm:w-24 sm:h-24 rounded-full p-2 sm:p-3 bg-white/5 border border-white/10 shadow-2xl">
                        <img loading="lazy" src={home_club?.logo_url} alt={home_club?.name} className="w-full h-full object-contain" />
                    </div>
                    <div className="text-center">
                        <p className="text-sm sm:text-xl font-black text-white uppercase tracking-tighter italic">{home_club?.short_name || home_club?.name}</p>
                        <p className="text-[8px] sm:text-[9px] font-black text-slate-600 uppercase tracking-widest">Heim</p>
                    </div>
                </div>

                {/* Score Center */}
                <div className="flex flex-col items-center gap-2 sm:gap-3 shrink-0">
                    {isLive || isPlayed ? (
                        <div className="flex items-center gap-3 sm:gap-6">
                            <span className="text-5xl sm:text-7xl md:text-8xl font-black text-white italic tabular-nums leading-none">{home_score ?? 0}</span>
                            <span className="text-2xl sm:text-4xl font-black text-slate-700 italic">:</span>
                            <span className="text-5xl sm:text-7xl md:text-8xl font-black text-white italic tabular-nums leading-none">{away_score ?? 0}</span>
                        </div>
                    ) : (
                        <div className="text-center">
                            <p className="text-3xl font-black text-slate-300 italic">{kickoff_formatted}</p>
                            <p className="text-xs text-slate-600 uppercase tracking-widest mt-1">Anstoss</p>
                        </div>
                    )}

                    {/* Status Pill */}
                    <div className={`flex items-center gap-2 px-5 py-2 rounded-full border text-[10px] font-black uppercase tracking-widest ${
                        isLive ? 'bg-rose-500/15 border-rose-500/40 text-rose-400' :
                        isPlayed ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' :
                        'bg-[var(--bg-pillar)] border-[var(--border-pillar)] text-[var(--text-muted)]'
                    }`}>
                        {isLive && <span className="relative flex h-2 w-2"><span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75" /><span className="relative inline-flex rounded-full h-2 w-2 bg-rose-500" /></span>}
                        {isLive ? `${live_minute}'` : isPlayed ? 'Beendet' : 'Geplant'}
                    </div>
                </div>

                {/* Away */}
                <div className="flex flex-col items-center gap-2 sm:gap-4 flex-1">
                    <div className="w-16 h-16 sm:w-24 sm:h-24 rounded-full p-2 sm:p-3 bg-white/5 border border-white/10 shadow-2xl">
                        <img loading="lazy" src={away_club?.logo_url} alt={away_club?.name} className="w-full h-full object-contain" />
                    </div>
                    <div className="text-center">
                        <p className="text-sm sm:text-xl font-black text-white uppercase tracking-tighter italic">{away_club?.short_name || away_club?.name}</p>
                        <p className="text-[8px] sm:text-[9px] font-black text-slate-600 uppercase tracking-widest">Gast</p>
                    </div>
                </div>
            </div>

            {/* Info Strip */}
            {(weather || kickoff_formatted) && (
                <div className="border-t border-white/5 px-8 py-3 flex items-center justify-center gap-6 text-[10px] font-black text-slate-600 uppercase tracking-widest">
                    {kickoff_formatted && <span>🗓 {kickoff_formatted}</span>}
                    {weather && <span>🌤 {weather}</span>}
                    {type && <span>⚽ {type === 'league' ? 'Liga' : type === 'cup_national' ? 'Pokal' : 'Testspiel'}</span>}
                </div>
            )}
        </div>
    );
};

/* ──────────────────────────  Ticker  ────────────────────────── */
const EVENT_LABELS = {
    goal: 'Tor', own_goal: 'Eigentor', assist: 'Vorlage',
    yellow_card: 'Gelbe Karte', red_card: 'Rote Karte', yellow_red_card: 'Gelb-Rote Karte',
    substitution: 'Wechsel', injury: 'Verletzung',
    chance: 'Chance', shot: 'Schuss', shot_on_target: 'Schuss aufs Tor',
    shot_off_target: 'Schuss daneben', shot_blocked: 'Schuss geblockt',
    corner: 'Ecke', free_kick: 'Freistoß', foul: 'Foul',
    offside: 'Abseits', penalty: 'Elfmeter', save: 'Parade',
    tackle: 'Zweikampf', tackle_won: 'Zweikampf gewonnen', interception: 'Ballgewinn',
    dribble: 'Dribbling', key_pass: 'Schlüsselpass', block: 'Block',
    clearance: 'Klärung', pressure: 'Pressing', possession_loss: 'Ballverlust',
};

const TickerItem = ({ action, homeClubId }) => {
    const isHome = action.club_id === homeClubId;
    const key = isKeyEvent(action.action_type);
    const { bg, color } = getActionConfig(action.action_type);

    // Show all key events; show non-key only if there's a narrative
    if (!key && !action.narrative) return null;

    return (
        <motion.div
            initial={{ opacity: 0, x: isHome ? -8 : 8 }}
            animate={{ opacity: 1, x: 0 }}
            className={`flex gap-4 items-start px-6 py-4 border-b border-white/5 hover:bg-white/[0.02] transition-all ${
                key ? '' : 'opacity-70'
            }`}
        >
            {/* Minute */}
            <div className={`w-12 text-right shrink-0 pt-1 ${key ? '' : ''}`}>
                <span className={`text-xs font-black italic tabular-nums ${key ? 'text-white' : 'text-slate-600'}`}>{action.minute}'</span>
            </div>

            {/* Icon bubble — ALWAYS visible */}
            <div className={`w-9 h-9 rounded-2xl border flex items-center justify-center shrink-0 transition-all ${
                key ? bg : 'bg-[var(--bg-pillar)]/60 border-[var(--border-pillar)]'
            }`}>
                <ActionIcon type={action.action_type} size={key ? 18 : 15} />
            </div>

            {/* Content */}
            <div className="flex-1 min-w-0">
                {/* Key event headline */}
                {key && (
                    <p className="text-xs font-black text-white uppercase tracking-tight leading-tight mb-0.5">
                        <span className={`${color} mr-1`}>{EVENT_LABELS[action.action_type] || action.action_type}</span>
                        {action.player_name && <span className="text-slate-200">• {action.player_name}</span>}
                        {action.assister_name && <span className="text-[var(--text-muted)]"> (V: {action.assister_name})</span>}
                        {action.metadata?.score && <span className="text-[var(--text-muted)] italic ml-2">[{action.metadata.score}]</span>}
                    </p>
                )}
                {/* Regular event label (non-key) */}
                {!key && action.action_type && (
                    <p className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-0.5">
                        {EVENT_LABELS[action.action_type] || action.action_type}
                        {action.player_name && <span className="text-slate-600 ml-1">• {action.player_name}</span>}
                    </p>
                )}
                {/* Narrative */}
                {action.narrative && (
                    <p className={`text-xs leading-snug ${key ? 'text-[var(--text-muted)]' : 'text-slate-600'}`}>{action.narrative}</p>
                )}
                {/* Club badge */}
                {key && action.club_short_name && (
                    <p className="text-[9px] font-black text-slate-700 uppercase tracking-widest mt-1">{action.club_short_name}</p>
                )}
            </div>
        </motion.div>
    );
};

/* ──────────────────────────  Compact Lineup  ────────────────────────── */

// Position rows by formation line — indexed 0..N from GK outward
const POSITION_ROWS = {
    GK: 0, SW: 0.3,
    LB: 1, RB: 1, CB: 1, 'CB-L': 1, 'CB-R': 1, 'CB-M': 1, 'CB-3L': 1, 'CB-3R': 1, LWB: 1.5, RWB: 1.5,
    DM: 2, 'DM-L': 2, 'DM-R': 2, HL: 2, HR: 2,
    CM: 3, 'CM-L': 3, 'CM-R': 3, 'CM-M': 3, LM: 3, RM: 3,
    AM: 4, 'AM-L': 4, 'AM-R': 4,
    LW: 5, RW: 5, SS: 5, 'SS-L': 5, 'SS-R': 5,
    ST: 6, 'ST-L': 6, 'ST-R': 6, CF: 6,
};

const getRow = (slot) => {
    if (!slot) return 3;
    const u = slot.toUpperCase();
    if (POSITION_ROWS[u] !== undefined) return POSITION_ROWS[u];
    const key = Object.keys(POSITION_ROWS).find(k => u.startsWith(k));
    return POSITION_ROWS[key] ?? 3;
};

const HalfPitchSVG = () => (
    <svg className="absolute inset-0 w-full h-full pointer-events-none opacity-30" viewBox="0 0 200 320" preserveAspectRatio="none" fill="none">
        <g stroke="white" strokeWidth="1.2" fill="none">
            <rect x="1" y="1" width="198" height="318" />
            {/* Goal area */}
            <rect x="60" y="1" width="80" height="28" />
            {/* Penalty spot */}
            <circle cx="100" cy="55" r="2.5" fill="white" opacity="0.5" />
            {/* Half line */}
            <line x1="0" y1="319" x2="200" y2="319" />
        </g>
    </svg>
);

const LineupToken = ({ player, accent, livePlayerStates }) => {
    const state = livePlayerStates?.find(s => s.player_id === player.id);
    const isOff = state?.is_sent_off || state?.is_injured;
    const hasGoal = (state?.goals || 0) > 0;
    const hasYellow = (state?.yellow_cards || 0) > 0;
    const isRed = state?.is_sent_off;

    return (
        <div className="flex flex-col items-center gap-1 relative">
            <div className={`relative w-8 h-8 rounded-full border-2 flex items-center justify-center shadow-md 
                ${isOff ? 'opacity-40 border-rose-500/60 bg-rose-950/60' : `bg-[var(--sim-shell-bg)] border-${accent}-400/80`}
            `}>
                {player.photo_url
                    ? <img loading="lazy" src={player.photo_url} className="w-full h-full rounded-full object-cover" />
                    : <span className={`text-[9px] font-black text-${accent}-300`}>{player.position?.slice(0,2)}</span>
                }
                {/* Yellow card pip */}
                {hasYellow && !isRed && <div className="absolute -top-1 -right-0.5 w-2 h-2.5 rounded-[2px] bg-amber-400 border border-black" />}
                {/* Red card pip */}
                {isRed && <div className="absolute -top-1 -right-0.5 w-2 h-2.5 rounded-[2px] bg-rose-500 border border-black" />}
                {/* Goal pip */}
                {hasGoal && <div className="absolute -bottom-1 -left-0.5 text-[9px]">⚽</div>}
            </div>
            <span className={`text-[7px] font-black uppercase truncate max-w-[44px] text-center leading-none text-${accent}-200/80`}>
                {player.name?.split(' ').pop()?.slice(0, 7)}
            </span>
        </div>
    );
};

const HalfPitch = ({ club, lineup, accent, livePlayerStates }) => {
    const starters = lineup?.starters || [];
    const bench    = lineup?.bench    || [];

    // Group starters by row
    const rows = {};
    starters.forEach(p => {
        const r = getRow(p.slot);
        if (!rows[r]) rows[r] = [];
        rows[r].push(p);
    });
    const sortedRows = Object.keys(rows).sort((a, b) => parseFloat(a) - parseFloat(b));

    const bgColor = accent === 'amber' ? '#0a0a0a' : '#0d0d0d';

    return (
        <div className="flex-1 min-w-0 space-y-3">
            {/* Club header */}
            <div className="flex items-center gap-2">
                <img loading="lazy" src={club?.logo_url} className="w-6 h-6 object-contain" />
                <div>
                    <p className="text-[10px] font-black text-white uppercase tracking-tight">{club?.short_name || club?.name}</p>
                    <p className={`text-[8px] font-black text-${accent}-500 uppercase tracking-widest`}>{lineup?.formation || '—'}</p>
                </div>
            </div>

            {/* Half pitch */}
            <div className="relative rounded-xl overflow-hidden" style={{ height: 300, background: `radial-gradient(ellipse at 50% 0%, #1d4a1d 0%, ${bgColor} 80%)` }}>
                <HalfPitchSVG />

                {/* Player rows — GK at top, attackers at bottom */}
                <div className="absolute inset-x-0 inset-y-0 flex flex-col justify-between py-4 px-2">
                    {sortedRows.map((rowKey) => (
                        <div key={rowKey} className="flex items-center justify-center gap-2 flex-wrap">
                            {rows[rowKey].map(p => (
                                <LineupToken key={p.id} player={p} accent={accent} livePlayerStates={livePlayerStates} />
                            ))}
                        </div>
                    ))}
                </div>
            </div>

            {/* Bench */}
            {bench.length > 0 && (
                <div className="space-y-1">
                    <p className={`text-[8px] font-black text-${accent}-800 uppercase tracking-widest`}>Bank</p>
                    <div className="flex flex-wrap gap-1.5">
                        {bench.map(p => (
                            <div key={p.id} className={`flex items-center gap-1.5 px-2 py-1 rounded-lg bg-${accent}-500/5 border border-${accent}-500/10`}>
                                <span className={`text-[8px] font-black text-${accent}-400`}>{p.overall}</span>
                                <span className="text-[8px] font-bold text-[var(--text-muted)] truncate max-w-[60px]">{p.name?.split(' ').pop()}</span>
                                <span className="text-[7px] font-black text-slate-700 uppercase">{p.position}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

const LineupPitch = ({ homeClub, awayClub, homeLineup, awayLineup, livePlayerStates }) => (
    <div className="flex flex-col lg:flex-row gap-8">
        <HalfPitch club={homeClub} lineup={homeLineup} accent="amber"   livePlayerStates={livePlayerStates} />
        <div className="hidden lg:block w-px bg-white/5 self-stretch shrink-0" />
        <HalfPitch club={awayClub} lineup={awayLineup} accent="gold" livePlayerStates={livePlayerStates} />
    </div>
);

/* ──────────────────────────  Main Page  ────────────────────────── */
export default function Show({
    id, status, live_minute, home_score, away_score,
    home_club, away_club, competition, matchday, kickoff_formatted, weather, referee, type,
    events, actions, final_stats, team_states, player_states,
    lineups, planned_substitutions,
    comparison, can_simulate, manageable_club_ids,
}) {
    const [tab, setTab] = useState(status === 'scheduled' ? 'preview' : status === 'live' ? 'ticker' : 'stats');
    const [liveState, setLiveState] = useState({
        status, live_minute, home_score, away_score, actions, team_states, player_states, planned_substitutions
    });
    const pollingRef = useRef(null);

    // Live polling every 8s
    const fetchState = useCallback(async () => {
        try {
            const res = await fetch(route('matches.live-state', id), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) return;
            const data = await res.json();
            setLiveState(prev => ({
                ...prev,
                status: data.status,
                live_minute: data.live_minute,
                home_score: data.home_score,
                away_score: data.away_score,
                actions: data.actions || prev.actions,
                team_states: data.team_states || prev.team_states,
                player_states: data.player_states || prev.player_states,
                planned_substitutions: data.planned_substitutions || prev.planned_substitutions,
            }));
        } catch {}
    }, [id]);

    useEffect(() => {
        if (liveState.status === 'live') {
            pollingRef.current = setInterval(fetchState, 8000);
        }
        return () => clearInterval(pollingRef.current);
    }, [liveState.status, fetchState]);

    const simulate = () => router.post(route('matches.simulate', id));
    const startLive = () => router.post(route('matches.live-start', id));

    const hState = liveState.team_states?.[String(home_club?.id)];
    const aState = liveState.team_states?.[String(away_club?.id)];
    
    const homeLineup = lineups?.[String(home_club?.id)];
    const awayLineup = lineups?.[String(away_club?.id)];

    const keyActions = (liveState.actions || []).filter(a => isKeyEvent(a.action_type));
    const allActions = liveState.actions || [];

    const tabs = [
        { key: 'preview', label: 'Vorschau' },
        { key: 'ticker',  label: 'Ticker',   count: allActions.length },
        { key: 'lineup',  label: 'Aufstellung' },
        { key: 'stats',   label: 'Statistiken' },
        ...(status !== 'scheduled' ? [{ key: 'players', label: 'Spieler' }] : []),
    ];

    return (
        <AuthenticatedLayout>
            <Head title={`${home_club?.short_name} vs ${away_club?.short_name}`} />

            <div className="max-w-[1300px] mx-auto space-y-8">
                {/* Back Link */}
                <Link href={route('league.matches')} className="flex items-center gap-2 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest hover:text-amber-500 transition-colors w-fit">
                    <ArrowLeft size={14} weight="bold" />
                    Spielplan
                </Link>

                {/* Hero Scoreboard */}
                <ScoreHero
                    home_club={home_club} away_club={away_club}
                    home_score={liveState.home_score} away_score={liveState.away_score}
                    status={liveState.status} live_minute={liveState.live_minute}
                    kickoff_formatted={kickoff_formatted} competition={competition}
                    matchday={matchday} weather={weather} type={type}
                />

                {/* Admin Controls */}
                {can_simulate && liveState.status !== 'played' && (
                    <div className="flex items-center gap-3">
                        <button onClick={simulate} className="flex items-center gap-2 px-6 py-3 rounded-2xl bg-amber-500/20 border border-amber-500/30 text-amber-500 text-[10px] font-black uppercase tracking-widest hover:bg-amber-500/30 transition-all font-mono">
                            <Lightning size={16} weight="fill" /> Simulieren
                        </button>
                        {liveState.status === 'scheduled' && (
                            <button onClick={startLive} className="flex items-center gap-2 px-6 py-3 rounded-2xl bg-rose-500/20 border border-rose-500/30 text-rose-300 text-[10px] font-black uppercase tracking-widest hover:bg-rose-500/30 transition-all">
                                <Play size={16} weight="fill" /> Live-Ticker starten
                            </button>
                        )}
                    </div>
                )}

                {/* Key Events Timeline */}
                {keyActions.length > 0 && (
                    <div className="sim-card p-5 flex flex-wrap items-center gap-3">
                        {keyActions.slice(0, 10).map((a, i) => (
                            <div key={i} className={`flex items-center gap-2 px-3 py-1.5 rounded-xl border text-[10px] font-black ${
                                a.action_type === 'goal' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' :
                                a.action_type === 'yellow_card' ? 'bg-amber-500/10 border-amber-500/20 text-amber-400' :
                                'bg-[var(--bg-pillar)] border-[var(--border-pillar)] text-[var(--text-muted)]'
                            }`}>
                                <ActionIcon type={a.action_type} />
                                <span>{a.minute}'</span>
                                {a.player_name && <span className="hidden sm:inline">{a.player_name.split(' ').pop()}</span>}
                            </div>
                        ))}
                    </div>
                )}

                {/* Tab Nav */}
                <nav className="flex items-center gap-1 p-1 rounded-2xl bg-[var(--bg-pillar)]/60 border border-[var(--border-pillar)] w-fit flex-wrap">
                    {tabs.map(t => (
                        <button
                            key={t.key}
                            onClick={() => setTab(t.key)}
                            className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${
                                tab === t.key ? 'bg-amber-500 text-black shadow-lg shadow-amber-900/40' : 'text-[var(--text-muted)] hover:text-slate-300'
                            }`}
                        >
                            {t.label}
                            {t.count > 0 && (
                                <span className={`px-1.5 py-0.5 rounded-full text-[8px] font-black ${tab === t.key ? 'bg-black/20' : 'bg-[var(--bg-content)] text-[var(--text-muted)]'}`}>
                                    {t.count}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>

                <AnimatePresence mode="wait">
                    {/* ── Preview Tab ── */}
                    {tab === 'preview' && (
                        <motion.div key="preview" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}
                            className="grid md:grid-cols-3 gap-8"
                        >
                            {[
                                { label: 'Kaderstärke', home: comparison?.home?.strength, away: comparison?.away?.strength, fmt: (v) => parseFloat(v).toFixed(1) },
                                { label: 'Marktwert (M)', home: comparison?.home?.market_value, away: comparison?.away?.market_value, fmt: (v) => (v/1_000_000).toFixed(1) + 'M' },
                                { label: 'Ø Alter', home: comparison?.home?.avg_age, away: comparison?.away?.avg_age, fmt: (v) => parseFloat(v).toFixed(1) },
                            ].map(({ label, home, away, fmt }) => (
                                <div key={label} className="sim-card p-6">
                                    <p className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest text-center mb-6">{label}</p>
                                    <div className="flex items-center justify-between text-sm font-black text-white mb-4">
                                        <span className="text-amber-500">{fmt(home || 0)}</span>
                                        <span className="text-slate-700">vs</span>
                                        <span className="text-[#d4af37]">{fmt(away || 0)}</span>
                                    </div>
                                    <StatBar label="" home={parseFloat(home || 0)} away={parseFloat(away || 0)} />
                                </div>
                            ))}
                        </motion.div>
                    )}

                    {/* ── Ticker Tab ── */}
                    {tab === 'ticker' && (
                        <motion.div key="ticker" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
                            <div className="sim-card overflow-hidden p-0">
                                <div className="px-6 py-4 bg-[var(--bg-pillar)]/60 border-b border-white/5 flex items-center gap-3">
                                    <SoccerBall size={18} weight="fill" className="text-amber-500" />
                                    <h3 className="text-xs font-black text-white uppercase tracking-widest">Spielverlauf</h3>
                                    <span className="ml-auto text-[9px] font-black text-slate-600 uppercase tracking-widest">{allActions.length} Aktionen</span>
                                </div>
                                {allActions.length === 0 ? (
                                    <div className="p-20 text-center">
                                        <SoccerBall size={48} weight="thin" className="text-slate-700 mx-auto mb-6" />
                                        <p className="text-[var(--text-muted)] italic font-bold uppercase tracking-widest text-sm">Noch keine Aktionen.</p>
                                    </div>
                                ) : (
                                    <div className="max-h-[70vh] overflow-y-auto custom-scrollbar">
                                        {allActions.map((action, i) => (
                                            <TickerItem key={`${action.id}-${i}`} action={action} homeClubId={home_club?.id} />
                                        ))}
                                    </div>
                                )}
                            </div>
                        </motion.div>
                    )}

                    {/* ── Lineup Tab ── */}
                    {tab === 'lineup' && (
                        <motion.div key="lineup" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}>
                            <div className="sim-card p-6">
                                <LineupPitch
                                    homeClub={home_club}
                                    awayClub={away_club}
                                    homeLineup={homeLineup}
                                    awayLineup={awayLineup}
                                    livePlayerStates={liveState.player_states}
                                />
                            </div>
                        </motion.div>
                    )}

                    {/* ── Stats Tab ── */}
                    {tab === 'stats' && (
                        <motion.div key="stats" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                            className="sim-card p-8"
                        >
                            {(!hState && !aState) ? (
                                <p className="text-center text-[var(--text-muted)] italic py-12">Noch keine Statistiken verfügbar.</p>
                            ) : (
                                <div className="space-y-6 max-w-2xl mx-auto">
                                    {[
                                        { label: 'Schüsse', home: hState?.shots, away: aState?.shots },
                                        { label: 'Schüsse aufs Tor', home: hState?.shots_on_target, away: aState?.shots_on_target },
                                        { label: 'Gefährliche Angriffe', home: hState?.dangerous_attacks, away: aState?.dangerous_attacks },
                                        { label: 'Pässe', home: hState?.pass_completions, away: aState?.pass_completions },
                                        { label: 'Fouls', home: hState?.fouls_committed, away: aState?.fouls_committed },
                                        { label: 'Ecken', home: hState?.corners_won, away: aState?.corners_won },
                                        { label: 'Gelbe Karten', home: hState?.yellow_cards, away: aState?.yellow_cards },
                                        { label: 'Rote Karten', home: hState?.red_cards, away: aState?.red_cards },
                                    ].map(s => <StatBar key={s.label} {...s} />)}
                                </div>
                            )}
                        </motion.div>
                    )}

                    {/* ── Players Tab ── */}
                    {tab === 'players' && (
                        <motion.div key="players" initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
                            className="grid md:grid-cols-2 gap-8"
                        >
                            {[home_club, away_club].map(club => {
                                const players = final_stats.filter(s => s.club_id === club?.id).sort((a, b) => b.rating - a.rating);
                                return (
                                    <div key={club?.id} className="sim-card overflow-hidden p-0">
                                        <div className="px-6 py-4 bg-[var(--bg-pillar)]/60 border-b border-white/5 flex items-center gap-4">
                                            <img loading="lazy" src={club?.logo_url} className="w-8 h-8 object-contain" />
                                            <p className="text-xs font-black text-white uppercase tracking-tighter">{club?.name}</p>
                                        </div>
                                        <div>
                                            <div className="grid grid-cols-[1fr_2rem_2rem_2rem_2rem_2rem] sm:grid-cols-[1fr_3.5rem_3.5rem_3.5rem_3.5rem_3.5rem] px-4 py-2 text-[8px] sm:text-[9px] font-black text-slate-600 uppercase tracking-widest border-b border-white/5">
                                                <span>Spieler</span><span className="text-center">Min</span><span className="text-center">T</span><span className="text-center">V</span><span className="text-center hidden sm:block">Schu</span><span className="text-center sm:hidden">S</span><span className="text-center">Note</span>
                                            </div>
                                            {players.map(p => (
                                                <div key={p.player_id} className="grid grid-cols-[1fr_2rem_2rem_2rem_2rem_2rem] sm:grid-cols-[1fr_3.5rem_3.5rem_3.5rem_3.5rem_3.5rem] px-4 py-3 border-b border-white/5 items-center hover:bg-white/[0.02]">
                                                    <span className="text-[10px] sm:text-xs font-bold text-white truncate pr-2">{p.player_name}</span>
                                                    <span className="text-center text-[9px] sm:text-[10px] text-[var(--text-muted)]">{p.minutes_played}'</span>
                                                    <span className="text-center text-[9px] sm:text-[10px] text-emerald-400 font-black">{p.goals}</span>
                                                    <span className="text-center text-[9px] sm:text-[10px] text-amber-500 font-black">{p.assists}</span>
                                                    <span className="text-center text-[9px] sm:text-[10px] text-[var(--text-muted)]">{p.shots}</span>
                                                    <span className={`text-center text-[10px] sm:text-xs font-black italic ${p.rating >= 7 ? 'text-emerald-400' : 'text-[var(--text-muted)]'}`}>{parseFloat(p.rating).toFixed(1)}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                );
                            })}
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `.custom-scrollbar::-webkit-scrollbar{width:4px}.custom-scrollbar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:4px}` }} />
        </AuthenticatedLayout>
    );
}
