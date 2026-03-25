import React, { useEffect, useMemo, useState } from 'react';
import RadialMenu, { INSTRUCTION_LABELS } from '@/Components/RadialMenu';
import { ArrowsClockwise, ArrowsLeftRight, Crown, CursorClick, FloppyDisk, Lightning } from '@phosphor-icons/react';

const POSITION_GROUPS = {
    GK: 'GK', TW: 'GK',
    LV: 'DEF', LB: 'DEF', IV: 'DEF', CB: 'DEF', RV: 'DEF', RB: 'DEF', LWB: 'DEF', RWB: 'DEF',
    DM: 'MID', CDM: 'MID', ZM: 'MID', CM: 'MID', LM: 'MID', RM: 'MID', OM: 'MID', CAM: 'MID', LAM: 'MID', RAM: 'MID', ZOM: 'MID',
    LF: 'FWD', LW: 'FWD', RF: 'FWD', RW: 'FWD', HS: 'FWD', MS: 'FWD', ST: 'FWD',
};

const fieldTone = 'rounded-xl border border-white/10 bg-white/[0.03] px-3 py-2 text-sm text-white outline-none transition focus:border-amber-400/30 focus:bg-white/[0.05]';

const buildDraft = (lineup, editor) => ({
    formation: lineup?.formation || editor.formations?.[0] || '4-4-2',
    mentality: lineup?.mentality || 'normal',
    aggression: lineup?.aggression || 'normal',
    line_height: lineup?.line_height || 'normal',
    attack_focus: lineup?.attack_focus || 'center',
    offside_trap: Boolean(lineup?.offside_trap),
    time_wasting: Boolean(lineup?.time_wasting),
    pressing_intensity: lineup?.pressing_intensity || 'normal',
    line_of_engagement: lineup?.line_of_engagement || 'normal',
    pressing_trap: lineup?.pressing_trap || 'none',
    cross_engagement: lineup?.cross_engagement || 'none',
    corner_marking_strategy: lineup?.corner_marking_strategy || 'zonal',
    free_kick_marking_strategy: lineup?.free_kick_marking_strategy || 'zonal',
    captain_player_id: lineup?.captain_player_id || 0,
    penalty_taker_player_id: lineup?.set_pieces?.penalty_taker_player_id || 0,
    free_kick_near_player_id: lineup?.set_pieces?.free_kick_near_player_id || 0,
    free_kick_far_player_id: lineup?.set_pieces?.free_kick_far_player_id || 0,
    corner_left_taker_player_id: lineup?.set_pieces?.corner_left_taker_player_id || 0,
    corner_right_taker_player_id: lineup?.set_pieces?.corner_right_taker_player_id || 0,
    starter_slots: Object.fromEntries((lineup?.starters || []).map((player) => [player.slot, player.id])),
    bench_slots: (lineup?.bench || []).map((player) => player.id),
    player_instructions: Object.fromEntries(
        [...(lineup?.starters || []), ...(lineup?.bench || [])]
            .filter((player) => (player.instructions || []).length > 0)
            .map((player) => [player.id, player.instructions || []])
    ),
});

const instructionItems = (instructions = []) => instructions.map((instruction) => INSTRUCTION_LABELS[instruction] || instruction);
const playerLabel = (player) => `${player.name} (${player.position}, ${player.overall})`;
const slotPositionGroup = (slot) => POSITION_GROUPS[String(slot?.label || slot?.slot || '').toUpperCase()] || 'MID';

const PitchMarkings = () => (
    <svg className="pointer-events-none absolute inset-0 h-full w-full opacity-40" viewBox="0 0 680 1050" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
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

const fitTone = (fit) => (
    fit < 0.8 ? 'border-rose-400/20 bg-rose-500/10 text-rose-200'
        : fit < 1 ? 'border-amber-400/20 bg-amber-500/10 text-amber-200'
            : 'border-emerald-400/20 bg-emerald-500/10 text-emerald-200'
);

const PlayerNode = ({ player, slot, isCaptain, instructions, onInstructionOpen, onDragStart, onDrop, pendingSubstitutionPlayer, onTapSubstitution }) => (
    <div
        className="absolute flex -translate-x-1/2 -translate-y-1/2 flex-col items-center"
        style={{ left: `${slot.x}%`, top: `${slot.y}%` }}
        onDragOver={(event) => event.preventDefault()}
        onDrop={(event) => onDrop(event, slot.slot)}
    >
        <button
            type="button"
            draggable={Boolean(player)}
            onDragStart={(event) => player && onDragStart(event, player.id)}
            onClick={() => {
                if (!player) return;
                if (pendingSubstitutionPlayer) {
                    onTapSubstitution(player, slot);
                    return;
                }
                onInstructionOpen(player, slot);
            }}
            className={`relative flex h-12 w-12 items-center justify-center rounded-full border-2 transition-all sm:h-14 sm:w-14 ${player ? 'border-amber-500/60 bg-[#1a1c2e] shadow-[0_0_20px_rgba(217,177,92,0.2)]' : 'border-dashed border-white/15 bg-black/20'}`}
        >
            {player ? (
                <>
                    <div className="flex flex-col items-center">
                        <span className="text-[10px] font-black leading-none text-white sm:text-xs">{player.overall}</span>
                        <span className={`mt-1 rounded-full px-1.5 py-0.5 text-[7px] font-black uppercase tracking-[0.12em] ${fitTone(Number(player.fit_factor))}`}>Fit {Number(player.fit_factor).toFixed(2)}</span>
                    </div>
                    {isCaptain && (
                        <div className="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full border border-slate-900 bg-amber-500 text-black shadow-lg">
                            <Crown size={11} weight="fill" />
                        </div>
                    )}
                    {pendingSubstitutionPlayer && (
                        <div className="absolute -left-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full border border-cyan-900 bg-cyan-400 px-1 text-[8px] font-black uppercase tracking-[0.08em] text-black shadow-lg">
                            IN
                        </div>
                    )}
                </>
            ) : (
                <span className="text-[8px] font-black uppercase tracking-[0.14em] text-white/30">{slot.label}</span>
            )}
        </button>
        <div className="mt-1 max-w-[86px] rounded-xl border border-white/8 bg-black/55 px-2 py-1 text-center backdrop-blur">
            <div className="truncate text-[8px] font-black uppercase tracking-[0.08em] text-white">{player ? player.name.split(' ').slice(-1)[0] : slot.slot}</div>
            {player && instructions.length > 0 && (
                <div className="mt-1 flex flex-wrap justify-center gap-1">
                    {instructions.slice(0, 2).map((label) => (
                        <span key={label} className="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-1.5 py-0.5 text-[6px] font-black uppercase tracking-[0.08em] text-cyan-200">{label}</span>
                    ))}
                </div>
            )}
        </div>
    </div>
);

const BenchCard = ({ player, instructions, onInstructionOpen, onDragStart, onDrop, onSelectSubstitution, isPendingSubstitution }) => (
    <div
        className={`rounded-2xl border px-3 py-3 transition ${player ? 'border-white/10 bg-black/15 hover:border-amber-400/20' : 'border-dashed border-white/10 bg-black/10'}`}
        onDragOver={(event) => event.preventDefault()}
        onDrop={(event) => onDrop(event)}
    >
        {player ? (
            <>
                <button type="button" draggable onDragStart={(event) => onDragStart(event, player.id)} onClick={() => onInstructionOpen(player, { slot: player.position, label: player.position })} className="w-full text-left">
                    <div className="flex items-center justify-between gap-3">
                        <div className="min-w-0">
                            <div className="truncate text-sm font-semibold text-white">{player.name}</div>
                            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-white/45">{player.position} · {player.overall}</div>
                        </div>
                        <div className={`rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.12em] ${fitTone(Number(player.fit_factor))}`}>{Number(player.fit_factor).toFixed(2)}</div>
                    </div>
                </button>
                {instructions.length > 0 && (
                    <div className="mt-2 flex flex-wrap gap-1">
                        {instructions.slice(0, 2).map((label) => (
                            <span key={label} className="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-2 py-1 text-[9px] font-black uppercase tracking-[0.12em] text-cyan-200">{label}</span>
                        ))}
                    </div>
                )}
                <button
                    type="button"
                    onClick={() => onSelectSubstitution(player)}
                    className={`mt-2 inline-flex items-center justify-center rounded-xl border px-3 py-2 text-[9px] font-black uppercase tracking-[0.14em] transition ${isPendingSubstitution ? 'border-cyan-400/30 bg-cyan-500/15 text-cyan-100' : 'border-white/10 bg-white/[0.04] text-white/75 hover:border-cyan-400/20 hover:text-white'}`}
                >
                    {isPendingSubstitution ? 'Ziel waehlen' : 'Mobil einwechseln'}
                </button>
            </>
        ) : (
            <div className="py-3 text-center text-[10px] font-black uppercase tracking-[0.16em] text-white/20">Hier ablegen</div>
        )}
    </div>
);

const SelectField = ({ label, value, onChange, options }) => (
    <label className="space-y-1.5 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
        {label}
        <select value={value} onChange={(event) => onChange(event.target.value)} className={fieldTone}>
            {options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
        </select>
    </label>
);

const LiveClubEditor = ({ club, lineup, editor, teamState, onSync, onSubstitute, busy, feedback }) => {
    const [draft, setDraft] = useState(() => buildDraft(lineup, editor));
    const [radialMenu, setRadialMenu] = useState({ open: false, playerId: null, position: 'MID' });
    const [draggedPlayerId, setDraggedPlayerId] = useState(null);
    const [pendingSubstitutionPlayer, setPendingSubstitutionPlayer] = useState(null);
    const [substitution, setSubstitution] = useState({ player_out_id: '', player_in_id: '' });

    useEffect(() => {
        setDraft(buildDraft(lineup, editor));
        setPendingSubstitutionPlayer(null);
    }, [lineup, editor]);

    const formationSlots = useMemo(() => editor.slots_by_formation?.[draft.formation] || [], [draft.formation, editor.slots_by_formation]);
    const starterPlayers = lineup?.starters || [];
    const benchPlayers = lineup?.bench || [];
    const substitutionsUsed = Number(teamState?.substitutions_used || 0);
    const substitutionsRemaining = Math.max(0, 5 - substitutionsUsed);
    const playersById = useMemo(() => new Map([...starterPlayers, ...benchPlayers].map((player) => [player.id, player])), [starterPlayers, benchPlayers]);
    const allPlayers = [...starterPlayers, ...benchPlayers];

    const startersForPitch = useMemo(() => formationSlots.map((slot) => {
        const playerId = Number(draft.starter_slots?.[slot.slot] || 0);
        const basePlayer = playersById.get(playerId);
        return { slot, player: basePlayer ? { ...basePlayer, slot: slot.slot } : null };
    }), [formationSlots, draft.starter_slots, playersById]);

    const benchForView = useMemo(() => draft.bench_slots.map((playerId) => playersById.get(Number(playerId)) || null), [draft.bench_slots, playersById]);

    const setValue = (key, value) => setDraft((current) => ({ ...current, [key]: value }));

    const handleSlotChange = (slotKey, playerId) => {
        const nextPlayerId = Number(playerId || 0);
        setDraft((current) => {
            const starters = { ...current.starter_slots };
            const currentHolder = Object.entries(starters).find(([, id]) => Number(id) === nextPlayerId)?.[0];
            const previousPlayerId = Number(starters[slotKey] || 0);
            if (currentHolder && currentHolder !== slotKey) starters[currentHolder] = previousPlayerId || null;
            starters[slotKey] = nextPlayerId || null;
            return { ...current, starter_slots: starters };
        });
    };

    const movePlayerToBench = (playerId) => {
        setDraft((current) => {
            const starters = { ...current.starter_slots };
            const slotEntry = Object.entries(starters).find(([, id]) => Number(id) === Number(playerId));
            if (slotEntry) starters[slotEntry[0]] = null;
            const benchSlots = [...current.bench_slots];
            if (!benchSlots.includes(Number(playerId))) {
                const freeIndex = benchSlots.findIndex((id) => !id);
                if (freeIndex >= 0) benchSlots[freeIndex] = Number(playerId);
            }
            return { ...current, starter_slots: starters, bench_slots: benchSlots };
        });
    };

    const handleInstructionToggle = (playerId, instructionId) => {
        setDraft((current) => {
            const existing = current.player_instructions[playerId] || [];
            const next = existing.includes(instructionId) ? existing.filter((entry) => entry !== instructionId) : [...existing, instructionId];
            const playerInstructions = { ...current.player_instructions };
            if (next.length > 0) playerInstructions[playerId] = next;
            else delete playerInstructions[playerId];
            return { ...current, player_instructions: playerInstructions };
        });
    };

    const openInstructionMenu = (player, slot) => setRadialMenu({ open: true, playerId: player.id, position: slotPositionGroup(slot) });
    const onDragStart = (event, playerId) => {
        event.dataTransfer.setData('playerId', String(playerId));
        setDraggedPlayerId(playerId);
    };

    const handlePitchDrop = (event, targetSlot) => {
        event.preventDefault();
        const sourceId = Number(event.dataTransfer.getData('playerId') || draggedPlayerId || 0);
        if (!sourceId) return;
        const droppedBenchPlayer = benchPlayers.find((player) => player.id === sourceId);
        const targetPlayerId = Number(draft.starter_slots?.[targetSlot] || 0);
        if (droppedBenchPlayer && targetPlayerId) {
            onSubstitute(club.id, { player_out_id: targetPlayerId, player_in_id: sourceId, target_slot: targetSlot });
            setDraggedPlayerId(null);
            return;
        }
        handleSlotChange(targetSlot, sourceId);
        setDraggedPlayerId(null);
    };

    const handleBenchDrop = (event) => {
        event.preventDefault();
        const sourceId = Number(event.dataTransfer.getData('playerId') || draggedPlayerId || 0);
        if (!sourceId) return;
        movePlayerToBench(sourceId);
        setDraggedPlayerId(null);
    };

    const handleTapSubstitution = (playerOut, slot) => {
        if (!pendingSubstitutionPlayer || busy) return;
        onSubstitute(club.id, {
            player_out_id: playerOut.id,
            player_in_id: pendingSubstitutionPlayer.id,
            target_slot: slot.slot,
        });
        setPendingSubstitutionPlayer(null);
    };

    const applySubstitution = () => {
        if (!substitution.player_out_id || !substitution.player_in_id) return;
        const targetSlot = starterPlayers.find((player) => player.id === Number(substitution.player_out_id))?.slot || '';
        onSubstitute(club.id, { player_out_id: Number(substitution.player_out_id), player_in_id: Number(substitution.player_in_id), target_slot: targetSlot });
        setSubstitution({ player_out_id: '', player_in_id: '' });
        setPendingSubstitutionPlayer(null);
    };

    return (
        <div className="sim-card space-y-6 p-5">
            <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div className="flex items-center gap-3">
                    <img loading="lazy" src={club?.logo_url} alt={club?.name} className="h-11 w-11 object-contain" />
                    <div>
                        <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Live-Aufstellung</div>
                        <div className="text-lg font-black text-white">{club?.name}</div>
                    </div>
                </div>
                <button onClick={() => onSync(club.id, draft)} disabled={busy} className="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-500/25 bg-amber-500/15 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-amber-200 transition hover:bg-amber-500/25 disabled:cursor-not-allowed disabled:opacity-50">
                    <FloppyDisk size={16} weight="fill" />
                    Live anwenden
                </button>
            </div>

            <div className="grid gap-3 lg:grid-cols-[0.75fr_0.25fr]">
                <div className={`rounded-2xl border px-4 py-3 text-sm ${
                    feedback?.type === 'error'
                        ? 'border-rose-400/20 bg-rose-500/10 text-rose-100'
                        : 'border-emerald-400/20 bg-emerald-500/10 text-emerald-100'
                }`}>
                    {feedback?.message || 'Live-Aenderungen werden direkt auf die Match-Aufstellung angewendet.'}
                </div>
                <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-center">
                    <div className="text-[9px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">Wechsel uebrig</div>
                    <div className="mt-1 text-xl font-black text-white">{substitutionsRemaining}</div>
                    <div className="text-[10px] text-white/45">{substitutionsUsed} genutzt</div>
                </div>
            </div>

            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <SelectField label="Formation" value={draft.formation} onChange={(value) => setValue('formation', value)} options={editor.formations.map((formation) => ({ value: formation, label: formation }))} />
                <SelectField label="Mentalitaet" value={draft.mentality} onChange={(value) => setValue('mentality', value)} options={[{ value: 'defensive', label: 'Defensiv' }, { value: 'counter', label: 'Konter' }, { value: 'normal', label: 'Normal' }, { value: 'offensive', label: 'Offensiv' }, { value: 'all_out', label: 'All-In' }]} />
                <SelectField label="Aggression" value={draft.aggression} onChange={(value) => setValue('aggression', value)} options={[{ value: 'cautious', label: 'Vorsichtig' }, { value: 'normal', label: 'Normal' }, { value: 'aggressive', label: 'Aggressiv' }]} />
                <SelectField label="Angriffsfokus" value={draft.attack_focus} onChange={(value) => setValue('attack_focus', value)} options={[{ value: 'center', label: 'Zentrum' }, { value: 'left', label: 'Links' }, { value: 'right', label: 'Rechts' }, { value: 'both_wings', label: 'Beide Fluegel' }]} />
                <SelectField label="Linienhoehe" value={draft.line_height} onChange={(value) => setValue('line_height', value)} options={[{ value: 'deep', label: 'Tief' }, { value: 'normal', label: 'Normal' }, { value: 'high', label: 'Hoch' }, { value: 'very_high', label: 'Sehr hoch' }]} />
                <SelectField label="Pressing" value={draft.pressing_intensity} onChange={(value) => setValue('pressing_intensity', value)} options={[{ value: 'low', label: 'Niedrig' }, { value: 'normal', label: 'Normal' }, { value: 'high', label: 'Hoch' }, { value: 'extreme', label: 'Extrem' }]} />
                <SelectField label="Pressinglinie" value={draft.line_of_engagement} onChange={(value) => setValue('line_of_engagement', value)} options={[{ value: 'deep', label: 'Tief' }, { value: 'normal', label: 'Normal' }, { value: 'high', label: 'Hoch' }]} />
                <SelectField label="Pressingfalle" value={draft.pressing_trap} onChange={(value) => setValue('pressing_trap', value)} options={[{ value: 'none', label: 'Keine' }, { value: 'inside', label: 'Innen' }, { value: 'outside', label: 'Aussen' }]} />
                <SelectField label="Flanken-Verhalten" value={draft.cross_engagement} onChange={(value) => setValue('cross_engagement', value)} options={[{ value: 'none', label: 'Neutral' }, { value: 'stop', label: 'Stoppen' }, { value: 'invite', label: 'Einladen' }]} />
            </div>

            <div className="grid gap-3 md:grid-cols-2">
                <label className="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white">
                    <input type="checkbox" checked={draft.offside_trap} onChange={(event) => setValue('offside_trap', event.target.checked)} />
                    Abseitsfalle
                </label>
                <label className="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-white">
                    <input type="checkbox" checked={draft.time_wasting} onChange={(event) => setValue('time_wasting', event.target.checked)} />
                    Zeitspiel
                </label>
            </div>

            <div className="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
                <div className="space-y-4">
                    <div className="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                        <ArrowsClockwise size={14} weight="bold" />
                        Live-Pitch
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-[#08111d] p-3 sm:p-4">
                        <div className="mb-3 flex flex-wrap items-center gap-2 text-[9px] font-black uppercase tracking-[0.16em] text-white/45">
                            <span className="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1">
                                <CursorClick size={11} weight="fill" />
                                Tippen fuer Instruktionen
                            </span>
                            <span className="inline-flex items-center gap-1 rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1">
                                Drag & Drop fuer Slots
                            </span>
                        </div>
                        {pendingSubstitutionPlayer && (
                            <div className="mb-3 rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-cyan-100">
                                {pendingSubstitutionPlayer.name} ist zur Einwechslung markiert. Waehle jetzt einen Feldspieler als Ziel.
                            </div>
                        )}

                        <div className="relative mx-auto aspect-[68/105] w-full max-w-[520px] overflow-hidden rounded-[1.6rem] border-4 border-[#121826] bg-[#0a0f18] shadow-2xl sm:rounded-[2rem] sm:border-8" onDragOver={(event) => event.preventDefault()}>
                            <PitchMarkings />
                            <div className="pointer-events-none absolute inset-0 bg-gradient-to-b from-amber-500/6 via-transparent to-cyan-500/6" />
                            <div className="absolute inset-x-4 inset-y-8 sm:inset-x-8 sm:inset-y-12">
                                {startersForPitch.map(({ slot, player }) => (
                                    <PlayerNode
                                        key={slot.slot}
                                        player={player}
                                        slot={slot}
                                        isCaptain={Number(draft.captain_player_id) === Number(player?.id || 0)}
                                        instructions={instructionItems(draft.player_instructions[player?.id] || [])}
                                        onInstructionOpen={openInstructionMenu}
                                        onDragStart={onDragStart}
                                        onDrop={handlePitchDrop}
                                        pendingSubstitutionPlayer={pendingSubstitutionPlayer}
                                        onTapSubstitution={handleTapSubstitution}
                                    />
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div className="mb-3 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Mobile / Feintuning</div>
                        <div className="grid gap-3 md:grid-cols-2">
                            {formationSlots.map((slot) => (
                                <label key={slot.slot} className="space-y-1.5 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    {slot.slot} · {slot.label}
                                    <select value={draft.starter_slots[slot.slot] || ''} onChange={(event) => handleSlotChange(slot.slot, event.target.value)} className={`${fieldTone} w-full`}>
                                        <option value="">Kein Spieler</option>
                                        {starterPlayers.map((player) => <option key={player.id} value={player.id}>{playerLabel(player)}</option>)}
                                    </select>
                                </label>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="space-y-4">
                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div className="mb-3 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            <ArrowsLeftRight size={14} weight="bold" />
                            Live-Wechsel
                        </div>
                        {substitutionsRemaining === 0 && (
                            <div className="mb-3 rounded-xl border border-rose-400/20 bg-rose-500/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.12em] text-rose-100">
                                Kein Wechsel mehr verfuegbar.
                            </div>
                        )}
                        <div className="space-y-3">
                            <select value={substitution.player_out_id} onChange={(event) => setSubstitution((current) => ({ ...current, player_out_id: event.target.value }))} className={`${fieldTone} w-full`}>
                                <option value="">Spieler raus</option>
                                {starterPlayers.map((player) => <option key={player.id} value={player.id}>{playerLabel(player)}</option>)}
                            </select>
                            <select value={substitution.player_in_id} onChange={(event) => setSubstitution((current) => ({ ...current, player_in_id: event.target.value }))} className={`${fieldTone} w-full`}>
                                <option value="">Spieler rein</option>
                                {benchPlayers.map((player) => <option key={player.id} value={player.id}>{playerLabel(player)}</option>)}
                            </select>
                            <button onClick={applySubstitution} disabled={busy || substitutionsRemaining === 0 || !substitution.player_out_id || !substitution.player_in_id} className="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-cyan-400/25 bg-cyan-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-cyan-100 transition hover:bg-cyan-500/20 disabled:cursor-not-allowed disabled:opacity-50">
                                <Lightning size={16} weight="fill" />
                                Wechsel ausfuehren
                            </button>
                        </div>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div className="mb-3 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Bank</div>
                        <div className="space-y-2">
                            {benchForView.map((player, index) => (
                                <BenchCard
                                    key={player?.id || `empty-${index}`}
                                    player={player}
                                    instructions={instructionItems(draft.player_instructions[player?.id] || [])}
                                    onInstructionOpen={openInstructionMenu}
                                    onDragStart={onDragStart}
                                    onDrop={handleBenchDrop}
                                    onSelectSubstitution={setPendingSubstitutionPlayer}
                                    isPendingSubstitution={pendingSubstitutionPlayer?.id === player?.id}
                                />
                            ))}
                        </div>
                    </div>

                    <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
                        <div className="mb-3 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Rollen und Standards</div>
                        <div className="space-y-3">
                            {[
                                ['captain_player_id', 'Kapitaen'],
                                ['penalty_taker_player_id', 'Elfmeter'],
                                ['free_kick_near_player_id', 'Freistoss Nah'],
                                ['free_kick_far_player_id', 'Freistoss Fern'],
                                ['corner_left_taker_player_id', 'Ecke Links'],
                                ['corner_right_taker_player_id', 'Ecke Rechts'],
                            ].map(([key, label]) => (
                                <label key={key} className="space-y-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    {label}
                                    <select value={draft[key] || ''} onChange={(event) => setValue(key, Number(event.target.value || 0))} className={`${fieldTone} w-full`}>
                                        <option value="">Kein Spieler</option>
                                        {allPlayers.map((player) => <option key={player.id} value={player.id}>{playerLabel(player)}</option>)}
                                    </select>
                                </label>
                            ))}
                            <SelectField label="Eckball-Verteidigung" value={draft.corner_marking_strategy} onChange={(value) => setValue('corner_marking_strategy', value)} options={[{ value: 'zonal', label: 'Raumdeckung' }, { value: 'player', label: 'Manndeckung' }, { value: 'hybrid', label: 'Hybrid' }]} />
                            <SelectField label="Freistoss-Verteidigung" value={draft.free_kick_marking_strategy} onChange={(value) => setValue('free_kick_marking_strategy', value)} options={[{ value: 'zonal', label: 'Raumdeckung' }, { value: 'player', label: 'Manndeckung' }, { value: 'hybrid', label: 'Hybrid' }]} />
                        </div>
                    </div>
                </div>
            </div>

            <RadialMenu
                isOpen={radialMenu.open}
                onClose={() => setRadialMenu({ open: false, playerId: null, position: 'MID' })}
                onToggleInstruction={(instructionId) => handleInstructionToggle(radialMenu.playerId, instructionId)}
                selectedInstructions={draft.player_instructions[radialMenu.playerId] || []}
                playerPosition={radialMenu.position}
                position={radialMenu.position}
            />
        </div>
    );
};

export default function LiveLineupEditorTab({ clubs = [], lineups = {}, manageableClubIds = [], liveLineupEditor, teamStates = {}, onSync, onSubstitute, busy = false, feedback = null }) {
    const editableClubs = clubs.filter((club) => manageableClubIds.includes(club.id));
    if (editableClubs.length === 0) return null;

    return (
        <div className="space-y-6">
            {editableClubs.map((club) => (
                <LiveClubEditor
                    key={club.id}
                    club={club}
                    lineup={lineups?.[String(club.id)]}
                    editor={liveLineupEditor}
                    teamState={teamStates?.[String(club.id)]}
                    onSync={onSync}
                    onSubstitute={onSubstitute}
                    busy={busy}
                    feedback={feedback}
                />
            ))}
        </div>
    );
}
