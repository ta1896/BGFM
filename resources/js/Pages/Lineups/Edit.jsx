import React, { useState, useEffect, useMemo, useDeferredValue } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import {
    ArrowLeft, 
    Lightning, 
    Strategy, 
    Users,
    House,
    ArrowArcLeft,
    HandPointing,
    SoccerBall,
    Trophy,
    UsersThree,
    Selection,
    CheckCircle,
    CaretDown,
    CaretUp,
    MagnifyingGlass,
    Minus,
    Plus,
    Target,
    Shield,
    Gear,
    Trash,
    FloppyDisk,
    Stack,
    X,
    Calendar,
    MagicWand
} from '@phosphor-icons/react';
import RadialMenu, { INSTRUCTION_LABELS } from '@/Components/RadialMenu';

const TACTICAL_POSITION_GROUPS = {
    'TW': 'GK', 'GK': 'GK',
    'LV': 'DEF', 'IV': 'DEF', 'RV': 'DEF', 'LWB': 'DEF', 'RWB': 'DEF',
    'LM': 'MID', 'ZM': 'MID', 'RM': 'MID', 'DM': 'MID', 'OM': 'MID', 'LAM': 'MID', 'ZOM': 'MID', 'RAM': 'MID',
    'LS': 'FWD', 'MS': 'FWD', 'RS': 'FWD', 'ST': 'FWD', 'LW': 'FWD', 'RW': 'FWD', 'LF': 'FWD', 'RF': 'FWD', 'HS': 'FWD'
};

const calculatePositionPenalty = (player, slotPos) => {
    if (!player || !slotPos) return 0;
    
    const playerPos = player.position;
    if (playerPos === slotPos) return 0;

    // Direct mapping match?
    if (TACTICAL_POSITION_GROUPS[playerPos] === TACTICAL_POSITION_GROUPS[slotPos]) {
        return -5; // Same group but different specific position
    }

    // Major group mismatch
    const playerGroup = TACTICAL_POSITION_GROUPS[playerPos];
    const slotGroup = TACTICAL_POSITION_GROUPS[slotPos];

    if (playerGroup === 'GK' || slotGroup === 'GK') return -45; // GK out of position or field player in goal
    if (playerGroup === 'DEF' && slotGroup === 'FWD') return -25;
    if (playerGroup === 'FWD' && slotGroup === 'DEF') return -25;
    
    return -15; // Typical out of position (e.g. DEF to MID)
};

const ATTRIBUTE_LABELS = {
    overall: 'Overall',
    shooting: 'Shooting',
    passing: 'Passing',
    defending: 'Defending',
    technical: 'Technical',
    pace: 'Pace',
    physical: 'Physical',
    stamina: 'Stamina',
    morale: 'Morale',
    attr_attacking: 'Attacking',
    attr_technical: 'Sofascore Technical',
    attr_tactical: 'Tactical',
    attr_defending: 'Defending Attr.',
    attr_creativity: 'Creativity',
    attr_market: 'Market Value',
    potential: 'Potential',
};

const MARKING_STRATEGY_OPTIONS = [
    {
        value: 'zonal',
        label: 'Raumdeckung',
        description: 'Stabil im Raum, gut fuer klare Zonen und zweite Baelle.',
    },
    {
        value: 'player',
        label: 'Manndeckung',
        description: 'Direkter Zugriff auf Gegenspieler, aber anfaelliger fuer Chaos.',
    },
    {
        value: 'hybrid',
        label: 'Hybrid',
        description: 'Mischt Raum und Mann fuer mehr Balance bei Standards.',
    },
];

const MENTALITY_OPTIONS = [
    { value: 'very_defensive', label: 'Sehr Defensiv', tone: 'Absichern' },
    { value: 'defensive', label: 'Defensiv', tone: 'Kontrolliert' },
    { value: 'normal', label: 'Normal', tone: 'Ausgewogen' },
    { value: 'offensive', label: 'Offensiv', tone: 'Mutig' },
    { value: 'very_offensive', label: 'Sehr Offensiv', tone: 'Volles Risiko' },
];

const PRESSING_LABELS = {
    low: 'Wenig',
    normal: 'Normal',
    high: 'Hoch',
    extreme: 'Extrem',
};

const LINE_HEIGHT_UI = {
    '20': 'Sehr Tief',
    '30': 'Tief',
    '40': 'Kompakt',
    '50': 'Normal',
    '60': 'Hoch',
    '70': 'Sehr Hoch',
    '80': 'Maximal',
};

const POSITION_GROUPS = [
    { key: 'GK', label: 'Torwart' },
    { key: 'DEF', label: 'Abwehr' },
    { key: 'MID', label: 'Mittelfeld' },
    { key: 'FWD', label: 'Sturm' },
];

const instructionLabelsForPlayer = (instructions = []) => (
    instructions
        .map((instructionId) => ({
            id: instructionId,
            label: INSTRUCTION_LABELS[instructionId] ?? instructionId,
        }))
);

const PitchMarkings = () => (
    <svg className="absolute inset-0 w-full h-full z-0 pointer-events-none opacity-40" viewBox="0 0 680 1050" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g stroke="#d9b15c" strokeWidth="2" fill="none">
            <rect x="1" y="1" width="678" height="1048" />
            <line x1="0" y1="525" x2="680" y2="525" />
            <circle cx="340" cy="525" r="91.5" />
            <rect x="138" y="0" width="404" height="165" />
            <rect x="138" y="885" width="404" height="165" />
            <rect x="248" y="0" width="184" height="55" />
            <rect x="248" y="995" width="184" height="55" />
            <circle cx="340" cy="525" r="5" fill="#d9b15c" />
            <circle cx="340" cy="110" r="4" fill="#d9b15c" />
            <circle cx="340" cy="940" r="4" fill="#d9b15c" />
        </g>
    </svg>
);

const normalizePositionCode = (value, aliases = {}) => {
    const normalized = String(value ?? '').trim().toUpperCase();
    if (!normalized) return '';

    const base = normalized.replace(/-(L|R)$/, '');
    return aliases[base] ?? base;
};

const groupFromPosition = (value, positionMeta) => {
    const normalized = normalizePositionCode(value, positionMeta.aliases);
    if (!normalized) return null;

    return positionMeta.groups[normalized] ?? null;
};

const slotAliases = (slot, positionMeta) => {
    const slotCode = normalizePositionCode(slot.slot, positionMeta.aliases);
    const slotLabel = normalizePositionCode(slot.label, positionMeta.aliases);
    const aliasMap = positionMeta.slotAliases ?? {};

    return Array.from(new Set([
        slotCode,
        slotLabel,
        ...(aliasMap[slotCode] ?? []),
        ...(aliasMap[slotLabel] ?? []),
    ].filter(Boolean)));
};

const resolveFitFactor = (player, slot, positionFit, positionMeta) => {
    if (!player || !slot) {
        return positionFit.foreign;
    }

    const slotGroup = typeof slot === 'string'
        ? groupFromPosition(slot, positionMeta)
        : slot.group;
    const aliases = typeof slot === 'string'
        ? [normalizePositionCode(slot, positionMeta.aliases)].filter(Boolean)
        : slotAliases(slot, positionMeta);
    const positions = [
        normalizePositionCode(player.position_main || player.position, positionMeta.aliases),
        normalizePositionCode(player.position_second, positionMeta.aliases),
        normalizePositionCode(player.position_third, positionMeta.aliases),
    ].filter(Boolean);

    const mainGroup = groupFromPosition(positions[0], positionMeta);
    const secondGroup = groupFromPosition(positions[1], positionMeta);
    const thirdGroup = groupFromPosition(positions[2], positionMeta);

    if (aliases.includes(positions[0]) || mainGroup === slotGroup) return positionFit.main;
    if (aliases.includes(positions[1]) || secondGroup === slotGroup) return positionFit.second;
    if (aliases.includes(positions[2]) || thirdGroup === slotGroup) return positionFit.third;
    if (mainGroup === 'GK' || slotGroup === 'GK') return positionFit.foreign_gk;

    return positionFit.foreign;
};

const buildSelectionWithoutPlayer = (starterSlots, benchSlots, playerId) => {
    const normalizedPlayerId = parseInt(playerId, 10);

    return {
        starterSlots: Object.fromEntries(
            Object.entries(starterSlots).map(([slotKey, value]) => [
                slotKey,
                parseInt(value, 10) === normalizedPlayerId ? null : value,
            ])
        ),
        benchSlots: benchSlots.map((value) => (
            parseInt(value, 10) === normalizedPlayerId ? null : value
        )),
    };
};

const playerSlotScore = (player, slot, positionFit, positionMeta, lineupScoring) => {
    const positions = [
        normalizePositionCode(player.position_main || player.position, positionMeta.aliases),
        normalizePositionCode(player.position_second, positionMeta.aliases),
        normalizePositionCode(player.position_third, positionMeta.aliases),
    ].filter(Boolean);
    const aliases = slotAliases(slot, positionMeta);

    const mainGroup = groupFromPosition(positions[0], positionMeta);
    const secondGroup = groupFromPosition(positions[1], positionMeta);
    const thirdGroup = groupFromPosition(positions[2], positionMeta);

    const fit = resolveFitFactor(player, slot, positionFit, positionMeta);

    const slotScoreBonuses = lineupScoring?.slotScoreBonuses ?? {};
    const exactBonus = aliases.includes(positions[0])
        ? (slotScoreBonuses.main ?? 120)
        : aliases.includes(positions[1])
            ? (slotScoreBonuses.second ?? 70)
            : aliases.includes(positions[2])
                ? (slotScoreBonuses.third ?? 35)
                : mainGroup === slot.group
                    ? (slotScoreBonuses.group_fallback ?? 20)
                    : 0;

    return (
        (player.overall * 12)
        + (player.stamina * 0.8)
        + (player.morale * 0.6)
        + ((player.sharpness ?? 50) * 0.4)
        - ((player.fatigue ?? 0) * 0.7)
        + (fit * 100)
        + exactBonus
    );
};

const PlayerCard = React.memo(({ player, isSelected, instructions = [], onDragStart, onAddPitch, onAddBench, onRemove }) => {
    const visibleInstructions = instructionLabelsForPlayer(instructions).slice(0, 2);

    return (
        <div
            draggable
            onDragStart={(e) => onDragStart(e, player.id)}
            className={`sim-card-soft p-2.5 flex items-center justify-between gap-3 border-[var(--border-pillar)]/30 group transition-all cursor-grab active:cursor-grabbing ${isSelected ? 'opacity-40 grayscale-[0.5]' : 'hover:border-amber-500/40 hover:bg-[var(--bg-content)]/50'}`}
        >
            <div className="flex items-center gap-2.5 overflow-hidden">
                <div className="w-8 h-8 rounded-lg bg-[var(--bg-pillar)] border border-[var(--border-pillar)] flex items-center justify-center shrink-0">
                    <span className="text-[10px] font-black text-amber-500">{player.overall}</span>
                </div>
                <div className="overflow-hidden">
                    <p className="text-[11px] font-black text-white truncate">{player.last_name}</p>
                    <p className="text-[9px] font-bold text-[var(--text-muted)] uppercase">{player.position_main}</p>
                    <p className="text-[8px] font-black uppercase tracking-widest text-amber-400">
                        F {player.fatigue} | H {player.happiness} | R {player.injury_risk}
                    </p>
                    {player.selection_warning && (
                        <p className={`mt-1 inline-flex rounded-full border px-1.5 py-0.5 text-[7px] font-black uppercase tracking-[0.14em] ${
                            player.selection_warning === 'medical_hold'
                                ? 'border-rose-400/25 bg-rose-500/12 text-rose-200'
                                : player.selection_warning === 'rehab'
                                ? 'border-rose-400/20 bg-rose-500/10 text-rose-300'
                                : player.selection_warning === 'risk'
                                    ? 'border-amber-400/20 bg-amber-500/10 text-amber-300'
                                    : 'border-cyan-400/20 bg-cyan-500/10 text-cyan-300'
                        }`}>
                            {player.selection_warning === 'medical_hold'
                                ? `Medical Hold${player.medical_clearance === 'bench_only' ? ' / Bank' : ''}`
                                : player.selection_warning === 'rehab'
                                    ? 'Reha'
                                    : player.selection_warning === 'risk'
                                        ? 'Medical Risk'
                                        : 'Promise Druck'}
                        </p>
                    )}
                    {visibleInstructions.length > 0 && (
                        <div className="mt-1 flex flex-wrap gap-1">
                            {visibleInstructions.map((instruction) => (
                                <span
                                    key={instruction.id}
                                    className="inline-flex rounded-full border border-cyan-400/20 bg-cyan-500/10 px-1.5 py-0.5 text-[7px] font-black uppercase tracking-[0.12em] text-cyan-200"
                                >
                                    {instruction.label}
                                </span>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            <div className="flex items-center gap-1">
                {!isSelected ? (
                    <>
                        <button 
                            type="button"
                            onClick={() => onAddPitch(player.id)}
                            className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-amber-500 hover:border-amber-500/30 transition-all flex items-center justify-center"
                        >
                            <Plus size={12} weight="bold" />
                        </button>
                        <button 
                            type="button"
                            onClick={() => onAddBench(player.id)}
                            className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-amber-600 hover:border-amber-600/30 transition-all flex items-center justify-center"
                        >
                            <span className="text-[10px] font-black">B</span>
                        </button>
                    </>
                ) : (
                    <button 
                        type="button"
                        onClick={() => onRemove(player.id)}
                        className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-rose-500 hover:text-rose-400 transition-all flex items-center justify-center"
                    >
                        <Trash size={12} />
                    </button>
                )}
            </div>
        </div>
    );
});

export default function Edit({ 
    lineup, 
    club, 
    clubPlayers, 
    clubMatches, 
    templates, 
    formation, 
    formations, 
    slots, 
    starterDraft, 
    benchDraft, 
    maxBenchPlayers,
    mentality,
    aggression,
    lineHeight,
    attackFocus,
    offsideTrap,
    timeWasting,
    captainPlayerId,
    setPieces,
    positionFit,
    positionMeta,
    lineupScoring,
    teamStrengthConfig,
    isReadOnly = false
}) {
    const { data, setData, put, processing, errors } = useForm({
        name: lineup.name,
        formation: formation,
        mentality: mentality,
        aggression: aggression,
        line_height: lineHeight,
        attack_focus: attackFocus,
        offside_trap: offsideTrap,
        time_wasting: timeWasting,
        captain_player_id: captainPlayerId,
        penalty_taker_player_id: setPieces.penalty_taker_player_id,
        free_kick_near_player_id: setPieces.free_kick_near_player_id,
        free_kick_far_player_id: setPieces.free_kick_far_player_id,
        corner_left_taker_player_id: setPieces.corner_left_taker_player_id,
        corner_right_taker_player_id: setPieces.corner_right_taker_player_id,
        corner_marking_strategy: setPieces.corner_marking_strategy || 'zonal',
        free_kick_marking_strategy: setPieces.free_kick_marking_strategy || 'zonal',
        starter_slots: starterDraft,
        // Pad bench to maxBenchPlayers so all slot dropzones render correctly
        bench_slots: Array.from({ length: maxBenchPlayers }, (_, i) => benchDraft[i] ?? null),
        player_instructions: lineup.player_instructions || {},
        pressing_intensity: lineup.pressing_intensity || 'normal',
        line_of_engagement: lineup.line_of_engagement || 'normal',
        pressing_trap: lineup.pressing_trap || 'none',
        cross_engagement: lineup.cross_engagement || 'none',
        pressing_triggers: lineup.pressing_triggers || [],
        action: 'save',
        template_name: ''
    });

    const [searchTerm, setSearchTerm] = useState('');
    const deferredSearchTerm = useDeferredValue(searchTerm);
    const [selectedTemplateId, setSelectedTemplateId] = useState('');
    const [assigningPlayerId, setAssigningPlayerId] = useState(null);
    const [activeTab, setActiveTab] = useState('kader'); // kader, taktik, spezial
    const [radialMenu, setRadialMenu] = useState({ isOpen: false, playerId: null, slot: null });
    const [explainExpanded, setExplainExpanded] = useState(false);

    const handleInstructionToggle = (playerId, instructionId) => {
        const current = data.player_instructions[playerId] || [];
        const next = current.includes(instructionId)
            ? current.filter(id => id !== instructionId)
            : [...current, instructionId];

        const nextInstructions = { ...data.player_instructions };
        if (next.length > 0) {
            nextInstructions[playerId] = next;
        } else {
            delete nextInstructions[playerId];
        }

        setData('player_instructions', nextInstructions);
    };
    const [positionFilter, setPositionFilter] = useState('ALL');
    const [collapsedGroups, setCollapsedGroups] = useState({
        GK: false,
        DEF: false,
        MID: false,
        FWD: false,
    });

    // Selected Player IDs for quick checking
    const selectedPlayerIds = useMemo(() => {
        const ids = [];
        Object.values(data.starter_slots).forEach(id => id && ids.push(parseInt(id)));
        data.bench_slots.forEach(id => id && ids.push(parseInt(id)));
        return new Set(ids);
    }, [data.starter_slots, data.bench_slots]);

    const playerById = useMemo(() => Object.fromEntries(clubPlayers.map((player) => [player.id, player])), [clubPlayers]);
    const slotByKey = useMemo(() => Object.fromEntries(slots.map((slot) => [slot.slot, slot])), [slots]);
    const getPlayer = (id) => playerById[parseInt(id)] ?? null;
    const freeStarterSlots = useMemo(() => slots.filter((slot) => !data.starter_slots[slot.slot]), [slots, data.starter_slots]);
    const firstFreeBenchIndex = useMemo(() => data.bench_slots.findIndex((id) => !id), [data.bench_slots]);
    const assigningPlayer = useMemo(() => assigningPlayerId ? getPlayer(assigningPlayerId) : null, [assigningPlayerId, playerById]);
    const normalizedSearch = deferredSearchTerm.trim().toLowerCase();
    const assignableStarterSlots = useMemo(() => {
        if (!assigningPlayer) {
            return [];
        }

        return freeStarterSlots
            .map((slot) => ({ ...slot, score: playerSlotScore(assigningPlayer, slot, positionFit, positionMeta, lineupScoring) }))
            .sort((left, right) => right.score - left.score);
    }, [assigningPlayer, freeStarterSlots, positionFit, positionMeta, lineupScoring]);
    const filteredPoolPlayers = useMemo(() => {
        return clubPlayers.filter((player) => {
            const matchesSearch = !normalizedSearch || [
                player.full_name,
                player.last_name,
                player.position_main,
                player.position,
            ].some((value) => String(value ?? '').toLowerCase().includes(normalizedSearch));

            if (!matchesSearch) {
                return false;
            }

            if (positionFilter === 'ALL') {
                return true;
            }

            return groupFromPosition(player.position_main || player.position, positionMeta) === positionFilter;
        });
    }, [clubPlayers, normalizedSearch, positionFilter, positionMeta]);
    const groupedPoolPlayers = useMemo(() => {
        const grouped = {
            GK: [],
            DEF: [],
            MID: [],
            FWD: [],
        };

        filteredPoolPlayers.forEach((player) => {
            const group = groupFromPosition(player.position_main || player.position, positionMeta);
            if (grouped[group]) {
                grouped[group].push(player);
            }
        });

        return grouped;
    }, [filteredPoolPlayers, positionMeta]);

    // Client-side Strength Calculation
    const calculatedMetrics = useMemo(() => {
        const starterIds = Object.entries(data.starter_slots).filter(([slot, id]) => id !== null);
        if (starterIds.length === 0) {
            return {
                overall: 0,
                attack: 0,
                midfield: 0,
                defense: 0,
                chemistry: 0,
                breakdown: {
                    attack: [],
                    midfield: [],
                    defense: [],
                },
                explain: {
                    starters: 0,
                    baseOverall: 0,
                    formationFactor: 0,
                    avgMorale: 0,
                    avgStamina: 0,
                    avgFit: 0,
                    sizeBonus: 0,
                    fitModifier: 0,
                    weakestFits: [],
                    topDrivers: [],
                },
            };
        }

        const entries = starterIds.map(([slotKey, pId]) => {
            const p = getPlayer(pId);
            if (!p) return null; // player not found in pool — skip
            const slot = slotByKey[slotKey];

            return {
                player: p,
                slotKey,
                slotLabel: slot?.label ?? slotKey,
                group: slot?.group ?? null,
                fit: resolveFitFactor(p, slot, positionFit, positionMeta),
            };
        }).filter(Boolean); // remove any null entries (player not found in pool)

        const calculateScore = (players, type) => {
            if (players.length === 0) {
                return { score: 0, contributions: [] };
            }

            const contributionTotals = {};
            const sum = players.reduce((acc, { player: p, fit }) => {
                const weights = teamStrengthConfig?.weights?.[type] ?? {};
                let base = 0;

                Object.entries(weights).forEach(([attribute, weight]) => {
                    const value = Number(p[attribute] ?? 0);
                    const partial = value * Number(weight);
                    base += partial;
                    contributionTotals[attribute] = (contributionTotals[attribute] ?? 0) + partial;
                });

                const condition = ((p.stamina + p.morale) / 200) + 0.5;
                return acc + Math.min(99, base * condition * fit);
            }, 0);

            const contributions = Object.entries(contributionTotals)
                .map(([attribute, value]) => ({
                    attribute,
                    label: ATTRIBUTE_LABELS[attribute] ?? attribute,
                    value: players.length ? value / players.length : 0,
                }))
                .sort((left, right) => right.value - left.value)
                .slice(0, 4);

            return {
                score: sum / players.length,
                contributions,
            };
        };

        const attackMetrics = calculateScore(entries.filter(e => e.group === 'FWD'), 'attack');
        const midfieldMetrics = calculateScore(entries.filter(e => e.group === 'MID'), 'midfield');
        const defenseMetrics = calculateScore(entries.filter(e => ['DEF', 'GK'].includes(e.group)), 'defense');

        const attScore = attackMetrics.score;
        const midScore = midfieldMetrics.score;
        const defScore = defenseMetrics.score;

        const baseOverall = (attScore + midScore + defScore) / 3;
        
        // Chemistry
        const avgMorale = entries.length ? entries.reduce((a, b) => a + b.player.morale, 0) / entries.length : 0;
        const avgStamina = entries.length ? entries.reduce((a, b) => a + b.player.stamina, 0) / entries.length : 0;
        const avgFit = entries.length ? entries.reduce((a, b) => a + b.fit, 0) / entries.length : 0;
        const chemistryConfig = teamStrengthConfig?.chemistry ?? {};
        const sizeBonus = Math.min(chemistryConfig.size_bonus_cap ?? 10, entries.length) / 2;
        const fitModifier = Math.max(chemistryConfig.fit_modifier_min ?? 0.82, Math.min(chemistryConfig.fit_modifier_max ?? 1, avgFit));
        const chemistry = Math.min(
            100,
            (((avgMorale + avgStamina) / 2) + sizeBonus) * fitModifier
        );

        const formationFactorConfig = teamStrengthConfig?.formationFactor ?? {};
        const countFactor = entries.length < (formationFactorConfig.minimum_players ?? 8)
            ? (formationFactorConfig.incomplete_lineup ?? 0.8)
            : (formationFactorConfig.complete_lineup ?? 1.0);
        const overall = Math.round(Math.min(99, baseOverall * countFactor * (chemistry / 100)));

        const driverTotals = entries.reduce((totals, { player, group }) => {
            const type = group === 'FWD' ? 'attack' : group === 'MID' ? 'midfield' : 'defense';
            const weights = teamStrengthConfig?.weights?.[type] ?? {};

            Object.entries(weights).forEach(([attribute, weight]) => {
                totals[attribute] = (totals[attribute] ?? 0) + (Number(player[attribute] ?? 0) * Number(weight));
            });

            return totals;
        }, {});

        return {
            overall,
            attack: Math.round(attScore),
            midfield: Math.round(midScore),
            defense: Math.round(defScore),
            chemistry: Math.round(chemistry),
            breakdown: {
                attack: attackMetrics.contributions,
                midfield: midfieldMetrics.contributions,
                defense: defenseMetrics.contributions,
            },
            explain: {
                starters: entries.length,
                baseOverall: Math.round(baseOverall),
                formationFactor: Number(countFactor.toFixed(2)),
                avgMorale: Math.round(avgMorale),
                avgStamina: Math.round(avgStamina),
                avgFit: Number(avgFit.toFixed(2)),
                sizeBonus: Number(sizeBonus.toFixed(1)),
                fitModifier: Number(fitModifier.toFixed(2)),
                weakestFits: entries
                    .map(({ player, slotLabel, fit }) => ({
                        id: player.id,
                        name: player.last_name || player.full_name,
                        slot: slotLabel,
                        fit: Number(fit.toFixed(2)),
                        effectiveOverall: Math.round(player.overall * fit),
                    }))
                    .sort((left, right) => left.fit - right.fit)
                    .slice(0, 4),
                topDrivers: Object.entries(driverTotals)
                    .map(([attribute, value]) => ({
                        attribute,
                        label: ATTRIBUTE_LABELS[attribute] ?? attribute,
                        value: entries.length ? Number((value / entries.length).toFixed(1)) : 0,
                    }))
                    .sort((left, right) => right.value - left.value)
                    .slice(0, 4),
            },
        };
    }, [data.starter_slots, slotByKey, positionFit, positionMeta, teamStrengthConfig]);

    // Handle Drop to Slot
    const handleDrop = (e, slotKey, isBench = false) => {
        e.preventDefault();
        const playerId = parseInt(e.dataTransfer.getData('playerId'));
        if (!playerId) return;

        assignPlayer(playerId, slotKey, isBench);
    };

    const assignPlayer = (playerId, targetSlot, isBench = false) => {
        const { starterSlots: newStarters, benchSlots: newBench } = buildSelectionWithoutPlayer(
            data.starter_slots,
            data.bench_slots,
            playerId
        );

        if (isBench) {
            newBench[targetSlot] = playerId;
        } else {
            newStarters[targetSlot] = playerId;
        }

        setData({
            ...data,
            starter_slots: newStarters,
            bench_slots: newBench
        });
        setAssigningPlayerId(null);
    };

    const removePlayer = (playerId) => {
        const { starterSlots: newStarters, benchSlots: newBench } = buildSelectionWithoutPlayer(
            data.starter_slots,
            data.bench_slots,
            playerId
        );

        setData({
            ...data,
            starter_slots: newStarters,
            bench_slots: newBench
        });
    };

    const addPitchAuto = (playerId) => {
        setAssigningPlayerId(playerId);
    };

    const addBenchAuto = (playerId) => {
        if (firstFreeBenchIndex === -1) {
            return;
        }

        const { starterSlots: newStarters, benchSlots: newBench } = buildSelectionWithoutPlayer(
            data.starter_slots,
            data.bench_slots,
            playerId
        );
        newBench[firstFreeBenchIndex] = playerId;
        setData({
            ...data,
            starter_slots: newStarters,
            bench_slots: newBench
        });
        setAssigningPlayerId(null);
    };

    const handleAutoFill = (e) => {
        e.preventDefault();
        router.put(route('lineups.update', lineup.id), {
            ...data,
            action: 'auto_pick',
        }, {
            preserveState: false,
            preserveScroll: true,
        });
    };

    const handleSave = (e) => {
        e.preventDefault();
        put(route('lineups.update', lineup.id));
    };

    const handleClearLineup = () => {
        const clearedStarters = Object.fromEntries(
            Object.keys(data.starter_slots).map((slotKey) => [slotKey, null])
        );

        setData({
            ...data,
            starter_slots: clearedStarters,
            bench_slots: Array.from({ length: maxBenchPlayers }, () => null),
            captain_player_id: '',
            penalty_taker_player_id: '',
            free_kick_near_player_id: '',
            free_kick_far_player_id: '',
            corner_left_taker_player_id: '',
            corner_right_taker_player_id: '',
        });
        setAssigningPlayerId(null);
    };

    const handleApplyTemplate = () => {
        if (!selectedTemplateId) {
            return;
        }

        router.get(route('lineups.edit', lineup.id), {
            formation: data.formation,
            template_id: selectedTemplateId,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSaveTemplate = () => {
        router.put(route('lineups.update', lineup.id), {
            ...data,
            save_as_template: true,
        });
    };
    const toggleGroup = (groupKey) => {
        setCollapsedGroups((current) => ({
            ...current,
            [groupKey]: !current[groupKey],
        }));
    };

    // Effects
    useEffect(() => {
        if (data.formation !== formation) {
            router.get(window.location.pathname, { formation: data.formation }, { preserveState: true, preserveScroll: true });
        }
    }, [data.formation, formation]);

    return (
        <AuthenticatedLayout>
            <Head title={`Taktik: ${lineup.name}`} />

            <div className="max-w-[1600px] mx-auto">
                <form onSubmit={handleSave} className="space-y-8">
                    {/* Header */}
                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 sm:gap-6 border-b border-white/5 pb-8">
                        <div className="flex items-center gap-4 sm:gap-6 w-full sm:w-auto">
                            <Link 
                                href={route('lineups.index')}
                                className="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)] flex items-center justify-center shrink-0 text-[var(--text-muted)] hover:text-amber-500 hover:border-amber-500/30 transition-all"
                            >
                                <ArrowLeft size={20} weight="bold" className="sm:hidden" />
                                <ArrowLeft size={24} weight="bold" className="hidden sm:block" />
                            </Link>
                            <div className="flex-1 min-w-0">
                                <input 
                                    className="bg-transparent border-none p-0 text-2xl sm:text-3xl font-black text-white uppercase italic tracking-tighter focus:ring-0 w-full lg:w-96 truncate"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                />
                                <div className="flex items-center gap-4 text-[10px] font-black tracking-widest text-[var(--text-muted)] uppercase mt-1">
                                    <div className="flex items-center gap-2">
                                        <Strategy size={12} weight="bold" className="text-amber-500" />
                                        Taktik-Editor // {club.name}
                                    </div>
                                    
                                    {clubMatches.length > 0 && lineup.match_id && (
                                        <>
                                            <span className="text-slate-700">|</span>
                                            <div className="flex items-center gap-2">
                                                <Calendar size={12} weight="bold" className="text-amber-600" />
                                                <select 
                                                    value={lineup.match_id} 
                                                    onChange={(e) => router.get(route('lineups.match', e.target.value))}
                                                    className="bg-transparent border-none p-0 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] cursor-pointer hover:text-white transition-colors focus:ring-0"
                                                >
                                                    {clubMatches.map(m => (
                                                        <option key={m.id} value={m.id} className="bg-[var(--bg-pillar)] border-none">
                                                            {m.match_date} vs {m.home_club_id === club.id ? (m.away_club?.short_name || m.away_club?.name || 'Gegner') : (m.home_club?.short_name || m.home_club?.name || 'Gegner')}
                                                        </option>
                                                    ))}
                                                </select>
                                                <CaretDown size={10} weight="bold" className="text-slate-600" />
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
                            <button 
                                type="button"
                                onClick={handleAutoFill}
                                className="sim-btn-muted flex-1 sm:flex-none justify-center px-4 sm:px-6 py-3 flex items-center gap-2 group"
                            >
                                <MagicWand size={18} weight="bold" className="group-hover:text-amber-500 transition-colors shrink-0" />
                                <span className="text-[10px] sm:text-xs font-black uppercase tracking-widest truncate">Auto</span>
                            </button>
                            <button
                                type="button"
                                onClick={handleClearLineup}
                                className="sim-btn-muted flex-1 sm:flex-none justify-center px-4 sm:px-6 py-3 flex items-center gap-2 group text-rose-300 hover:text-rose-200"
                            >
                                <Trash size={18} weight="bold" className="shrink-0" />
                                <span className="text-[10px] sm:text-xs font-black uppercase tracking-widest truncate">Clear</span>
                            </button>
                            <button 
                                type="submit"
                                disabled={processing || isReadOnly}
                                title={isReadOnly ? 'Kein Spiel geplant - Speichern deaktiviert' : ''}
                                className={`flex-[2] sm:flex-none justify-center px-6 sm:px-10 py-3 flex items-center gap-2 ${
                                    isReadOnly ? 'bg-slate-800 text-slate-500 border border-white/5 cursor-not-allowed' : 'sim-btn-primary'
                                }`}
                            >
                                <FloppyDisk size={18} weight={isReadOnly ? "thin" : "bold"} className="shrink-0" />
                                <span className="text-[10px] sm:text-xs font-black uppercase tracking-widest truncate">
                                    {isReadOnly ? 'GESPERRT' : 'Speichern'}
                                </span>
                            </button>
                        </div>
                    </div>

                    <div className="flex flex-col gap-6 lg:grid lg:grid-cols-[380px_1fr] lg:gap-8">
                        {/* Sidebar: Navigation Tabs */}
                        <aside className="order-2 flex flex-col gap-4 lg:order-1 lg:h-[800px] lg:gap-6">
                            {/* Tabs Navigation */}
                            <div className="no-scrollbar flex gap-1 overflow-x-auto rounded-2xl border border-white/5 bg-[#0c1222]/80 p-1 backdrop-blur-xl">
                                {[
                                    { id: 'kader', label: 'Kader', icon: <Users size={14} weight="bold" /> },
                                    { id: 'taktik', label: 'Taktik', icon: <Strategy size={14} weight="bold" /> },
                                    { id: 'spezial', label: 'Spezial', icon: <Target size={14} weight="bold" /> }
                                ].map(tab => (
                                    <button
                                        key={tab.id}
                                        type="button"
                                        onClick={() => setActiveTab(tab.id)}
                                        className={`flex min-w-[120px] flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all sm:min-w-0 ${
                                            activeTab === tab.id
                                                ? 'bg-amber-500 text-black shadow-[0_0_15px_rgba(217,177,92,0.3)]'
                                                : 'text-slate-400 hover:text-white hover:bg-white/5'
                                        }`}
                                    >
                                        {tab.id === 'kader' ? <Users size={14} weight="bold" /> : tab.id === 'taktik' ? <Strategy size={14} weight="bold" /> : <Selection size={14} weight="bold" />}
                                        {tab.label}
                                    </button>
                                ))}
                            </div>

                            {/* Tab Content */}
                            <div className="lg:flex-1 lg:overflow-hidden">
                                {activeTab === 'kader' && (
                                    <div className="sim-card flex flex-col bg-[#0c1222]/80 border-[var(--border-muted)] p-4 sm:p-5 lg:h-full">
                                        <div className="relative mb-4">
                                            <MagnifyingGlass size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                                            <input 
                                                type="text" 
                                                placeholder="Spieler suchen..."
                                                value={searchTerm}
                                                onChange={e => setSearchTerm(e.target.value)}
                                                className="sim-input pl-10 py-2.5 text-xs w-full"
                                            />
                                        </div>

                                        <div className="mb-4 flex flex-wrap gap-1.5">
                                            <button
                                                type="button"
                                                onClick={() => setPositionFilter('ALL')}
                                                className={`rounded-xl border px-2.5 py-1.5 text-[9px] font-black uppercase tracking-widest transition-all ${
                                                    positionFilter === 'ALL'
                                                        ? 'border-amber-500/40 bg-amber-500/10 text-amber-300'
                                                        : 'border-white/5 bg-white/5 text-slate-500 hover:text-white'
                                                }`}
                                            >
                                                Alle
                                            </button>
                                            {POSITION_GROUPS.map((group) => (
                                                <button
                                                    key={group.key}
                                                    type="button"
                                                    onClick={() => setPositionFilter(group.key)}
                                                    className={`rounded-xl border px-2.5 py-1.5 text-[9px] font-black uppercase tracking-widest transition-all ${
                                                        positionFilter === group.key
                                                            ? 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200'
                                                            : 'border-white/5 bg-white/5 text-slate-500 hover:text-white'
                                                    }`}
                                                >
                                                    {group.label}
                                                </button>
                                            ))}
                                        </div>

                                        <div className="space-y-2 custom-scrollbar lg:flex-1 lg:overflow-y-auto lg:pr-1">
                                            {POSITION_GROUPS.map((group) => {
                                                const players = groupedPoolPlayers[group.key] ?? [];
                                                const isCollapsed = collapsedGroups[group.key];
                                                if (positionFilter !== 'ALL' && positionFilter !== group.key) return null;
                                                return (
                                                    <div key={group.key} className="rounded-2xl border border-white/5 bg-black/20">
                                                        <button
                                                            type="button"
                                                            onClick={() => toggleGroup(group.key)}
                                                            className="flex w-full items-center justify-between px-3 py-2 text-left"
                                                        >
                                                            <span className="text-[9px] font-black uppercase tracking-widest text-slate-400">{group.label} ({players.length})</span>
                                                            <CaretDown size={12} className={`text-slate-600 transition-transform ${isCollapsed ? '' : 'rotate-180'}`} />
                                                        </button>
                                                        {!isCollapsed && (
                                                            <div className="p-2 space-y-1.5">
                                                                {players.map(p => (
                                                                    <PlayerCard 
                                                                        key={p.id}
                                                                        player={p}
                                                                        isSelected={selectedPlayerIds.has(p.id)}
                                                                        instructions={data.player_instructions[p.id] || []}
                                                                        onDragStart={(e, id) => e.dataTransfer.setData('playerId', id)}
                                                                        onAddPitch={addPitchAuto}
                                                                        onAddBench={addBenchAuto}
                                                                        onRemove={removePlayer}
                                                                    />
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    </div>
                                )}

                                {activeTab === 'taktik' && (
                                    <div className="space-y-4 custom-scrollbar lg:h-full lg:overflow-y-auto lg:pr-1">
                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Strategy size={14} weight="bold" />
                                                Grundordnung
                                            </h4>
                                            <div className="grid gap-3">
                                                <div className="rounded-2xl border border-amber-400/10 bg-gradient-to-br from-amber-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                    <div className="mb-2 flex items-start justify-between gap-3">
                                                        <div>
                                                            <div className="text-[9px] font-black uppercase tracking-[0.28em] text-amber-500/70">Struktur am Ball</div>
                                                            <label className="mt-1 block text-[11px] font-black uppercase tracking-wider text-slate-100">Formation</label>
                                                        </div>
                                                        <div className="rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-amber-200">
                                                            {data.formation}
                                                        </div>
                                                    </div>
                                                    <select value={data.formation} onChange={e => setData('formation', e.target.value)} className="sim-select w-full border-amber-500/20 bg-[#08111f]/90 text-xs">
                                                        {formations.map(f => <option key={f} value={f}>{f}</option>)}
                                                    </select>
                                                </div>
                                                <div className="rounded-2xl border border-amber-400/10 bg-gradient-to-br from-amber-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                    <div className="mb-2 flex items-start justify-between gap-3">
                                                        <div>
                                                            <div className="text-[9px] font-black uppercase tracking-[0.28em] text-amber-500/70">Risiko & Haltung</div>
                                                            <label className="mt-1 block text-[11px] font-black uppercase tracking-wider text-slate-100">Mentalität</label>
                                                        </div>
                                                        <div className="rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-amber-200">
                                                            {MENTALITY_OPTIONS.find(option => option.value === data.mentality)?.label || 'Normal'}
                                                        </div>
                                                    </div>
                                                    <select value={data.mentality} onChange={e => setData('mentality', e.target.value)} className="sim-select w-full border-amber-500/20 bg-[#08111f]/90 text-xs">
                                                        {MENTALITY_OPTIONS.map(option => (
                                                            <option key={option.value} value={option.value}>{option.label}</option>
                                                        ))}
                                                    </select>
                                                    <div className="mt-2 text-[10px] text-amber-100/70">
                                                        {MENTALITY_OPTIONS.find(option => option.value === data.mentality)?.tone || 'Ausgewogen'}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-rose-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Target size={14} weight="bold" />
                                                Defensiv-Taktik
                                            </h4>
                                            <div className="space-y-4">
                                                <div className="rounded-2xl border border-rose-400/10 bg-gradient-to-br from-rose-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                    <div className="flex justify-between items-center mb-2">
                                                        <label className="text-[9px] font-black uppercase tracking-[0.28em] text-rose-300">Pressing-Intensität</label>
                                                        <span className="rounded-full border border-rose-400/20 bg-rose-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-rose-200">
                                                            {PRESSING_LABELS[data.pressing_intensity] || 'Normal'}
                                                        </span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        min="1"
                                                        max="4"
                                                        step="1"
                                                        value={{ low: 1, normal: 2, high: 3, extreme: 4 }[data.pressing_intensity] ?? 2}
                                                        onChange={e => setData('pressing_intensity', ({ 1: 'low', 2: 'normal', 3: 'high', 4: 'extreme' }[e.target.value] || 'normal'))}
                                                        className="w-full accent-rose-500"
                                                    />
                                                    <div className="mt-2 flex justify-between text-[9px] font-black uppercase tracking-widest text-slate-500">
                                                        <span>Passiv</span>
                                                        <span>Extrem</span>
                                                    </div>
                                                </div>

                                                <div className="rounded-2xl border border-rose-400/10 bg-gradient-to-br from-rose-500/[0.06] via-[#0f1728] to-[#0a1020] p-4">
                                                    <div className="mb-3">
                                                        <label className="text-[9px] font-black uppercase tracking-[0.28em] text-rose-300">Pressing-Auslöser</label>
                                                        <div className="mt-1 text-[10px] text-slate-400">Wann soll das Team intensiv pressen?</div>
                                                    </div>
                                                    <div className="grid grid-cols-1 gap-2">
                                                        {[
                                                            { id: 'backpass', label: 'Rückpässe', desc: 'Presst, wenn der Gegner nach hinten spielt.' },
                                                            { id: 'ball_reception', label: 'Ballannahme', desc: 'Presst sofort bei Ballannahme des Gegners.' },
                                                            { id: 'wings', label: 'Am Flügel', desc: 'Gezieltes Pressing an den Seitenlinien.' }
                                                        ].map(trigger => {
                                                            const isActive = (data.pressing_triggers || []).includes(trigger.id);
                                                            return (
                                                                <button
                                                                    key={trigger.id}
                                                                    type="button"
                                                                    onClick={() => {
                                                                        const current = data.pressing_triggers || [];
                                                                        const next = isActive 
                                                                            ? current.filter(id => id !== trigger.id)
                                                                            : [...current, trigger.id];
                                                                        setData('pressing_triggers', next);
                                                                    }}
                                                                    className={`flex items-start gap-3 rounded-xl border p-3 text-left transition-all ${
                                                                        isActive 
                                                                            ? 'border-rose-500/40 bg-rose-500/10 text-rose-200 shadow-[0_0_15px_rgba(244,63,94,0.1)]' 
                                                                            : 'border-white/5 bg-white/[0.02] text-slate-500 hover:border-white/10 hover:bg-white/[0.04]'
                                                                    }`}
                                                                >
                                                                    <div className={`mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded border ${
                                                                        isActive ? 'border-rose-400 bg-rose-500 text-white' : 'border-white/20 bg-black/20'
                                                                    }`}>
                                                                        {isActive && <CheckCircle size={10} weight="bold" />}
                                                                    </div>
                                                                    <div>
                                                                        <div className={`text-[10px] font-black uppercase tracking-wider ${isActive ? 'text-rose-200' : 'text-slate-300'}`}>{trigger.label}</div>
                                                                        <div className="text-[9px] leading-tight text-slate-500 mt-0.5">{trigger.desc}</div>
                                                                    </div>
                                                                </button>
                                                            );
                                                        })}
                                                    </div>
                                                </div>

                                                <div className="rounded-2xl border border-amber-400/10 bg-gradient-to-br from-amber-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                    <div className="flex justify-between items-center mb-2">
                                                        <label className="text-[9px] font-black uppercase tracking-[0.28em] text-amber-300">Abwehrlinie</label>
                                                        <span className="rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-amber-200">
                                                            {LINE_HEIGHT_UI[String(data.line_height)] || `${data.line_height}%`}
                                                        </span>
                                                    </div>
                                                    <input
                                                        type="range"
                                                        min="20"
                                                        max="80"
                                                        step="10"
                                                        value={data.line_height}
                                                        onChange={e => setData('line_height', e.target.value)}
                                                        className="w-full accent-amber-500"
                                                    />
                                                    <div className="mt-2 flex justify-between text-[9px] font-black uppercase tracking-widest text-slate-500">
                                                        <span>Tief</span>
                                                        <span>Hoch</span>
                                                    </div>
                                                </div>

                                                <div className="grid grid-cols-2 gap-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => setData('offside_trap', !data.offside_trap)}
                                                        className={`rounded-2xl border px-3 py-3 text-left transition-all ${
                                                            data.offside_trap ? 'border-amber-500/40 bg-amber-500/10 text-amber-200' : 'border-white/6 bg-white/[0.04] text-slate-500 hover:border-white/12'
                                                        }`}
                                                    >
                                                        <div className="text-[10px] font-black uppercase tracking-widest">Abseitsfalle</div>
                                                        <div className="mt-1 text-[10px] leading-4 opacity-80">Linie schiebt aggressiver nach vorne.</div>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => setData('time_wasting', !data.time_wasting)}
                                                        className={`rounded-2xl border px-3 py-3 text-left transition-all ${
                                                            data.time_wasting ? 'border-amber-500/40 bg-amber-500/10 text-amber-200' : 'border-white/6 bg-white/[0.04] text-slate-500 hover:border-white/12'
                                                        }`}
                                                    >
                                                        <div className="text-[10px] font-black uppercase tracking-widest">Zeitspiel</div>
                                                        <div className="mt-1 text-[10px] leading-4 opacity-80">Mehr Kontrolle, weniger offenes Tempo.</div>
                                                    </button>
                                                </div>

                                                <div className="rounded-2xl border border-white/6 bg-white/[0.03] p-3 sm:p-4">
                                                    <div className="mb-3">
                                                        <div className="text-[9px] font-black uppercase tracking-[0.28em] text-slate-500">Standards gegen uns</div>
                                                        <div className="mt-1 text-[11px] font-black uppercase tracking-wider text-slate-100">Manndeckung & Varianten</div>
                                                    </div>
                                                    <div className="space-y-3">
                                                        {[
                                                            { key: 'corner_marking_strategy', label: 'Eckball-Verteidigung' },
                                                            { key: 'free_kick_marking_strategy', label: 'Freistoß-Verteidigung' },
                                                        ].map(section => (
                                                            <div key={section.key}>
                                                                <div className="mb-2 flex items-center justify-between gap-3">
                                                                    <div className="text-[10px] font-black uppercase tracking-widest text-slate-300">{section.label}</div>
                                                                    <div className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-emerald-300">
                                                                        {MARKING_STRATEGY_OPTIONS.find(option => option.value === data[section.key])?.label || 'Auto'}
                                                                    </div>
                                                                </div>
                                                                <div className="grid gap-2">
                                                                    {MARKING_STRATEGY_OPTIONS.map(option => {
                                                                        const active = data[section.key] === option.value;
                                                                        return (
                                                                            <button
                                                                                key={`${section.key}-${option.value}`}
                                                                                type="button"
                                                                                onClick={() => setData(section.key, option.value)}
                                                                                className={`flex w-full items-start justify-between gap-3 rounded-2xl border px-3 py-3 text-left transition-all ${
                                                                                    active
                                                                                        ? 'border-emerald-400/40 bg-emerald-400/10 shadow-[0_0_0_1px_rgba(52,211,153,0.12)]'
                                                                                        : 'border-white/6 bg-[#10182d]/80 hover:border-white/12 hover:bg-white/[0.05]'
                                                                                }`}
                                                                            >
                                                                                <div>
                                                                                    <div className={`text-[11px] font-black uppercase tracking-wide ${active ? 'text-emerald-200' : 'text-slate-100'}`}>
                                                                                        {option.label}
                                                                                    </div>
                                                                                    <div className={`mt-1 text-[10px] leading-4 ${active ? 'text-emerald-100/80' : 'text-slate-400'}`}>
                                                                                        {option.description}
                                                                                    </div>
                                                                                </div>
                                                                                <div className={`mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full border ${
                                                                                    active
                                                                                        ? 'border-emerald-300/40 bg-emerald-300/15 text-emerald-200'
                                                                                        : 'border-white/10 bg-white/[0.04] text-slate-500'
                                                                                }`}>
                                                                                    <CheckCircle size={14} weight={active ? 'fill' : 'regular'} />
                                                                                </div>
                                                                            </button>
                                                                        );
                                                                    })}
                                                                </div>
                                                            </div>
                                                        ))}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {activeTab === 'spezial' && (
                                    <div className="space-y-4 custom-scrollbar lg:h-full lg:overflow-y-auto lg:pr-1">
                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-cyan-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Target size={14} weight="bold" />
                                                Standardschützen
                                            </h4>
                                            <div className="grid gap-3">
                                                {[
                                                    { label: 'Elfmeter', key: 'penalty_taker_player_id' },
                                                    { label: 'Freistöße', key: 'free_kick_near_player_id' },
                                                    { label: 'Ecken', key: 'corner_left_taker_player_id' }
                                                ].map(role => (
                                                    <div key={role.key} className="rounded-2xl border border-cyan-400/10 bg-gradient-to-br from-cyan-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                        <div className="mb-2 flex items-start justify-between gap-3">
                                                            <div>
                                                                <div className="text-[9px] font-black uppercase tracking-[0.28em] text-cyan-500/70">
                                                                    {role.key === 'penalty_taker_player_id' ? 'Direkter Abschluss' : role.key === 'free_kick_near_player_id' ? 'Nahe Distanz' : 'Linke Seite'}
                                                                </div>
                                                                <label className="mt-1 block text-[11px] font-black uppercase tracking-wider text-slate-100">{role.label}</label>
                                                            </div>
                                                            <div className="max-w-[52%] truncate rounded-full border border-cyan-400/20 bg-cyan-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-cyan-200">
                                                                {data[role.key]
                                                                    ? (getPlayer(data[role.key])?.last_name || getPlayer(data[role.key])?.full_name || 'Auto')
                                                                    : 'Auto'}
                                                            </div>
                                                        </div>
                                                        <select value={data[role.key] || ''} onChange={e => setData(role.key, e.target.value)} className="sim-select w-full border-cyan-500/20 bg-[#08111f]/90 text-[10px]">
                                                            <option value="">- Auto -</option>
                                                            {Array.from(selectedPlayerIds).map(id => {
                                                                const p = getPlayer(id);
                                                                return <option key={id} value={id}>{p?.last_name || p?.full_name}</option>;
                                                            })}
                                                        </select>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-sky-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Target size={14} weight="bold" />
                                                Weitere Standards
                                            </h4>
                                            <div className="grid gap-3">
                                                {[
                                                    { label: 'Freistoss Fern', key: 'free_kick_far_player_id' },
                                                    { label: 'Ecke Rechts', key: 'corner_right_taker_player_id' },
                                                ].map(role => (
                                                    <div key={role.key} className="rounded-2xl border border-sky-400/10 bg-gradient-to-br from-sky-500/[0.06] via-[#0f1728] to-[#0a1020] p-3 sm:p-4">
                                                        <div className="mb-2 flex items-start justify-between gap-3">
                                                            <div>
                                                                <div className="text-[9px] font-black uppercase tracking-[0.28em] text-sky-500/70">
                                                                    {role.key === 'free_kick_far_player_id' ? 'Weite Distanz' : 'Rechte Seite'}
                                                                </div>
                                                                <label className="mt-1 block text-[11px] font-black uppercase tracking-wider text-slate-100">{role.label}</label>
                                                            </div>
                                                            <div className="max-w-[52%] truncate rounded-full border border-sky-400/20 bg-sky-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-sky-200">
                                                                {data[role.key]
                                                                    ? (getPlayer(data[role.key])?.last_name || getPlayer(data[role.key])?.full_name || 'Auto')
                                                                    : 'Auto'}
                                                            </div>
                                                        </div>
                                                        <select value={data[role.key] || ''} onChange={e => setData(role.key, e.target.value)} className="sim-select w-full border-sky-500/20 bg-[#08111f]/90 text-[10px]">
                                                            <option value="">- Auto -</option>
                                                            {Array.from(selectedPlayerIds).map(id => {
                                                                const p = getPlayer(id);
                                                                return <option key={id} value={id}>{p?.last_name || p?.full_name}</option>;
                                                            })}
                                                        </select>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>

                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Users size={14} weight="bold" />
                                                Rollen
                                            </h4>
                                            <div>
                                                <label className="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1 block">Kapitän</label>
                                                <select value={data.captain_player_id} onChange={e => setData('captain_player_id', e.target.value)} className="sim-select w-full text-[10px]">
                                                    <option value="">- Wählen -</option>
                                                    {Array.from(selectedPlayerIds).map(id => {
                                                        const p = getPlayer(id);
                                                        return <option key={id} value={id}>{p?.full_name}</option>;
                                                    })}
                                                </select>
                                            </div>
                                        </div>

                                        <div className="sim-card p-5 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                            <h4 className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                                                <Stack size={14} weight="bold" />
                                                Vorlagen
                                            </h4>
                                            <div className="space-y-3">
                                                <div className="flex flex-col gap-2 sm:flex-row">
                                                    <select value={selectedTemplateId} onChange={e => setSelectedTemplateId(e.target.value)} className="sim-select flex-1 text-[10px]">
                                                        <option value="">Laden...</option>
                                                        {templates.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                                    </select>
                                                    <button type="button" onClick={handleApplyTemplate} className="rounded-xl border border-cyan-500/20 bg-cyan-500/10 px-3 py-2 text-[10px] font-black uppercase text-cyan-400">OK</button>
                                                </div>
                                                <div className="flex flex-col gap-2 sm:flex-row">
                                                    <input value={data.template_name} onChange={e => setData('template_name', e.target.value)} placeholder="Name..." className="sim-input flex-1 text-[10px]" />
                                                    <button type="button" onClick={handleSaveTemplate} className="inline-flex items-center justify-center rounded-xl border border-amber-500/20 bg-amber-500/10 px-3 py-2 text-[10px] font-black uppercase text-amber-500"><FloppyDisk size={14} /></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </aside>

                        {/* Center/Right: The Pitch */}
                        <main className="order-1 flex flex-col space-y-6 lg:order-2 lg:space-y-8">
                            {/* Metrics Bar */}
                            <div className="flex flex-col gap-4 rounded-3xl border border-white/5 bg-[#0c1222]/80 p-4 backdrop-blur-xl sm:flex-row sm:items-center sm:justify-between">
                                <div className="flex flex-col gap-4 px-1 sm:flex-row sm:items-center sm:gap-6 sm:px-4">
                                    <div className="flex flex-col">
                                        <span className="text-[9px] font-black text-amber-500 uppercase tracking-widest">STÄRKE</span>
                                        <span className="text-2xl font-black text-white italic leading-none">{calculatedMetrics.overall}</span>
                                    </div>
                                    <div className="hidden h-8 w-px bg-white/5 sm:block" />
                                    <div className="grid grid-cols-3 gap-3 sm:flex sm:gap-4">
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-slate-500 uppercase tracking-widest">ANGRIFF</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.attack}</span>
                                        </div>
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-slate-500 uppercase tracking-widest">MITTE</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.midfield}</span>
                                        </div>
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-slate-500 uppercase tracking-widest">ABWEHR</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.defense}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center justify-center gap-2 rounded-2xl bg-amber-500/10 border border-amber-500/20 px-4 py-2 sm:justify-start">
                                    <Lightning size={16} weight="fill" className="text-amber-500" />
                                    <div className="flex flex-col">
                                        <span className="text-[9px] font-black text-amber-500 uppercase tracking-widest leading-none">CHEMIE</span>
                                        <span className="text-sm font-black text-white leading-none">{calculatedMetrics.chemistry}%</span>
                                    </div>
                                </div>
                            </div>

                            <div className="sim-card border-white/5 bg-[#0c1222]/80 p-4 sm:p-5">
                                <button
                                    type="button"
                                    onClick={() => setExplainExpanded((current) => !current)}
                                    className="flex w-full items-start justify-between gap-4 text-left"
                                >
                                    <div>
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Warum diese XI?</div>
                                        <div className="mt-1 text-sm font-black text-white">Chemie, Formation und Haupttreiber</div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Strategy size={16} weight="fill" className="text-cyan-300" />
                                        <div className="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/[0.03] text-slate-300">
                                            {explainExpanded ? <CaretUp size={16} weight="bold" /> : <CaretDown size={16} weight="bold" />}
                                        </div>
                                    </div>
                                </button>

                                {!explainExpanded && (
                                    <div className="mt-4 grid gap-3 sm:grid-cols-4">
                                        <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Basis</div>
                                            <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.explain.baseOverall}</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Faktor</div>
                                            <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.explain.formationFactor.toFixed(2)}</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Chemie</div>
                                            <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.chemistry}%</div>
                                        </div>
                                        <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                            <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Schwächster Fit</div>
                                            <div className="mt-1 text-lg font-black text-white">
                                                {calculatedMetrics.explain.weakestFits[0]?.fit?.toFixed(2) ?? '-'}
                                            </div>
                                        </div>
                                    </div>
                                )}

                                {explainExpanded && (
                                    <div className="mt-4 grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                                        <div>
                                            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                                <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Basisstärke</div>
                                                    <div className="mt-1 text-xl font-black text-white">{calculatedMetrics.explain.baseOverall}</div>
                                                    <div className="mt-1 text-[10px] font-bold text-white/60">vor Chemie und Kaderfaktor</div>
                                                </div>
                                                <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Formationsfaktor</div>
                                                    <div className="mt-1 text-xl font-black text-white">{calculatedMetrics.explain.formationFactor.toFixed(2)}</div>
                                                    <div className="mt-1 text-[10px] font-bold text-white/60">{calculatedMetrics.explain.starters} Starter aktiv</div>
                                                </div>
                                                <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3 sm:col-span-2 xl:col-span-1">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Chemie-Mix</div>
                                                    <div className="mt-1 text-xl font-black text-white">{calculatedMetrics.chemistry}%</div>
                                                    <div className="mt-1 text-[10px] font-bold text-white/60">
                                                        Moral {calculatedMetrics.explain.avgMorale} · Fitness {calculatedMetrics.explain.avgStamina}
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="mt-4 grid gap-3 sm:grid-cols-3">
                                                <div className="rounded-2xl border border-cyan-400/15 bg-cyan-500/[0.05] px-4 py-3">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-cyan-200">Ø Fit</div>
                                                    <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.explain.avgFit.toFixed(2)}</div>
                                                </div>
                                                <div className="rounded-2xl border border-amber-400/15 bg-amber-500/[0.05] px-4 py-3">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-amber-200">Fit-Modifikator</div>
                                                    <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.explain.fitModifier.toFixed(2)}</div>
                                                </div>
                                                <div className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                                    <div className="text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Größenbonus</div>
                                                    <div className="mt-1 text-lg font-black text-white">{calculatedMetrics.explain.sizeBonus.toFixed(1)}</div>
                                                </div>
                                            </div>

                                            <div className="mt-4">
                                                <div className="mb-2 text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">Größte Treiber</div>
                                                <div className="grid gap-2 sm:grid-cols-2">
                                                    {calculatedMetrics.explain.topDrivers.map((driver) => (
                                                        <div key={driver.attribute} className="flex items-center justify-between gap-3 rounded-xl border border-white/8 bg-black/20 px-3 py-2">
                                                            <span className="text-[10px] font-black text-white">{driver.label}</span>
                                                            <span className="text-[10px] font-black text-amber-300">{driver.value.toFixed(1)}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div className="mb-4 flex items-center justify-between gap-3">
                                                <div>
                                                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Schwächste Fits</div>
                                                    <div className="mt-1 text-sm font-black text-white">Die riskantesten Slots in der Startelf</div>
                                                </div>
                                                <Target size={16} weight="fill" className="text-amber-400" />
                                            </div>

                                            <div className="space-y-2.5">
                                                {calculatedMetrics.explain.weakestFits.map((entry) => (
                                                    <div key={entry.id} className="rounded-2xl border border-white/8 bg-white/[0.03] px-4 py-3">
                                                        <div className="flex items-center justify-between gap-3">
                                                            <div className="min-w-0">
                                                                <div className="truncate text-[11px] font-black text-white">{entry.name}</div>
                                                                <div className="mt-1 text-[9px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{entry.slot}</div>
                                                            </div>
                                                            <div className={`rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${
                                                                entry.fit < 0.8
                                                                    ? 'border-rose-400/20 bg-rose-500/10 text-rose-200'
                                                                    : entry.fit < 1
                                                                        ? 'border-amber-400/20 bg-amber-500/10 text-amber-200'
                                                                        : 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200'
                                                            }`}>
                                                                Fit {entry.fit.toFixed(2)}
                                                            </div>
                                                        </div>
                                                        <div className="mt-2 flex items-center justify-between gap-3 text-[10px] font-bold">
                                                            <span className="text-white/60">Effektiv</span>
                                                            <span className="text-white">{entry.effectiveOverall}</span>
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>

                            {/* Pitch Area */}
                            <div className="relative mx-auto aspect-[68/105] w-full max-w-[500px] overflow-hidden rounded-[1.5rem] border-4 border-[#1a1a1a] bg-[#0a0a0a] shadow-2xl sm:rounded-[2rem] sm:border-8">
                                <PitchMarkings />
                                <div className="absolute inset-0 bg-gradient-to-b from-amber-500/5 to-transparent z-10 pointer-events-none" />
                                
                                <div className="absolute inset-x-4 inset-y-8 z-20 sm:inset-x-8 sm:inset-y-12">
                                    {slots.map(slot => {
                                        const pId = data.starter_slots[slot.slot];
                                        const p = getPlayer(pId);
                                        const penalty = calculatePositionPenalty(p, slot.slot.split('-')[0]);

                                        return (
                                            <div 
                                                key={slot.slot}
                                                onDragOver={e => e.preventDefault()}
                                                onDrop={e => handleDrop(e, slot.slot)}
                                                className="absolute flex flex-col items-center group/slot"
                                                style={{ left: `${slot.x}%`, top: `${slot.y}%`, transform: 'translate(-50%, -50%)' }}
                                            >
                                                <div 
                                                    className={`relative flex h-11 w-11 items-center justify-center rounded-full border-2 transition-all duration-300 cursor-pointer sm:h-14 sm:w-14 ${
                                                        p ? 'bg-[#1a1c2e] border-amber-500/60 shadow-[0_0_20px_rgba(217,177,92,0.2)]' 
                                                          : 'bg-black/20 border-white/10 hover:border-white/30 border-dashed'
                                                    }`}
                                                    onClick={(e) => {
                                                        if (p) {
                                                            setRadialMenu({ isOpen: true, playerId: p.id, slot: slot.slot, x: e.clientX, y: e.clientY });
                                                        }
                                                    }}
                                                >
                                                    {p ? (
                                                        <>
                                                            <div className="flex flex-col items-center">
                                                                <span className="mb-0.5 text-[10px] font-black leading-none text-white sm:text-xs">{p.shirt_number}</span>
                                                                <div className="flex items-center gap-0.5">
                                                                    <span className={`text-[7px] font-black leading-none sm:text-[8px] ${penalty < 0 ? 'text-rose-400' : 'text-amber-500'}`}>
                                                                        {Math.round(p.overall * resolveFitFactor(p, slot, positionFit, positionMeta))}
                                                                    </span>
                                                                    {penalty < 0 && (
                                                                        <div className="w-1.5 h-1.5 rounded-full bg-rose-500" title={`Positions-Abzug: ${penalty}%`} />
                                                                    )}
                                                                </div>
                                                            </div>
                                                            {parseInt(data.captain_player_id) === p.id && (
                                                                <div className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full border border-slate-900 bg-amber-500 text-[8px] font-black text-black shadow-lg sm:h-5 sm:w-5 sm:border-2 sm:text-[10px]">C</div>
                                                            )}
                                                            <button 
                                                                type="button"
                                                                onClick={(e) => { e.stopPropagation(); removePlayer(p.id); }}
                                                                className="absolute -bottom-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-white opacity-100 transition-opacity sm:h-5 sm:w-5 sm:opacity-0 sm:group-hover/slot:opacity-100"
                                                            >
                                                                <X size={10} weight="bold" />
                                                            </button>
                                                        </>
                                                    ) : (
                                                        <span className="text-[7px] font-black text-white/30 uppercase tracking-tighter sm:text-[9px]">{slot.label}</span>
                                                    )}
                                                </div>
                                                {p && (
                                                    <div className="mt-1 rounded border border-white/5 bg-black/60 px-1.5 py-0.5 backdrop-blur-md sm:px-2">
                                                        <span className="block max-w-[46px] truncate text-center text-[7px] font-black leading-tight text-white uppercase sm:max-w-[60px] sm:text-[9px]">
                                                            {p.last_name}
                                                        </span>
                                                        {(data.player_instructions[p.id] || []).length > 0 && (
                                                            <div className="mt-1 flex max-w-[72px] flex-wrap justify-center gap-1">
                                                                {instructionLabelsForPlayer(data.player_instructions[p.id]).slice(0, 2).map((instruction) => (
                                                                    <span
                                                                        key={instruction.id}
                                                                        className="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-1 py-0.5 text-[6px] font-black uppercase tracking-[0.08em] text-cyan-200"
                                                                    >
                                                                        {instruction.label}
                                                                    </span>
                                                                ))}
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Bench */}
                            <div className="sim-card border-white/5 bg-[#0c1222]/80 p-4 sm:p-6">
                                <div className="mb-4 flex items-center justify-between gap-3">
                                    <h3 className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Auswechselbank</h3>
                                    <span className="text-[9px] font-bold text-slate-600">Max. {maxBenchPlayers}</span>
                                </div>
                                <div className="grid grid-cols-4 gap-2 sm:flex sm:flex-wrap sm:gap-4">
                                    {data.bench_slots.map((pId, idx) => {
                                        const p = getPlayer(pId);
                                        return (
                                            <div 
                                                key={idx}
                                                onDragOver={e => e.preventDefault()}
                                                onDrop={e => handleDrop(e, idx, true)}
                                                className={`relative flex h-14 w-full flex-col items-center justify-center rounded-2xl border-2 transition-all group/bench sm:h-14 sm:w-14 ${
                                                    p ? 'bg-[#1a1c2e] border-amber-600/40' 
                                                      : 'bg-black/20 border-white/5 border-dashed hover:border-white/10'
                                                }`}
                                            >
                                                {p ? (
                                                    <>
                                                        <span className="text-xs font-black text-white leading-none mb-1">{p.shirt_number}</span>
                                                        <span className="text-[8px] font-black text-slate-500 uppercase truncate max-w-[50px]">{p.last_name}</span>
                                                        {(data.player_instructions[p.id] || []).length > 0 && (
                                                            <span className="mt-1 rounded-full border border-cyan-400/20 bg-cyan-500/10 px-1.5 py-0.5 text-[6px] font-black uppercase tracking-[0.08em] text-cyan-200">
                                                                {instructionLabelsForPlayer(data.player_instructions[p.id])[0]?.label}
                                                            </span>
                                                        )}
                                                        <button 
                                                            type="button"
                                                            onClick={(e) => { e.stopPropagation(); removePlayer(p.id); }}
                                                            className="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-rose-500 text-white opacity-100 transition-opacity shadow-lg sm:opacity-0 sm:group-hover/bench:opacity-100"
                                                        >
                                                            <X size={10} weight="bold" />
                                                        </button>
                                                    </>
                                                ) : (
                                                    <span className="text-[10px] font-black text-white/10 italic">B-{idx+1}</span>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </main>
                    </div>
                </form>
            </div>

            <RadialMenu
                isOpen={radialMenu.isOpen}
                onClose={() => setRadialMenu({ ...radialMenu, isOpen: false })}
                playerId={radialMenu.playerId}
                activeInstructions={data.player_instructions[radialMenu.playerId] || []}
                onSelect={(instId) => handleInstructionToggle(radialMenu.playerId, instId)}
                playerPosition={slotByKey[radialMenu.slot]?.group ?? 'MID'}
            />
        </AuthenticatedLayout>
    );
}

