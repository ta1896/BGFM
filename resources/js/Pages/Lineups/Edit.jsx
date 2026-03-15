import React, { useState, useEffect, useMemo, useCallback } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import { 
    SoccerBall, 
    ArrowLeft, 
    Lightning, 
    ShieldCheck, 
    Users, 
    Strategy, 
    CaretDown,
    Calendar,
    Checks,
    Warning,
    Target,
    Clock,
    UserCircle,
    Plus,
    X,
    Trash,
    FloppyDisk,
    MagicWand,
    MagnifyingGlass,
    TrendUp
} from '@phosphor-icons/react';

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

const PlayerCard = ({ player, isSelected, onDragStart, onAddPitch, onAddBench, onRemove }) => {
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
                            player.selection_warning === 'rehab'
                                ? 'border-rose-400/20 bg-rose-500/10 text-rose-300'
                                : player.selection_warning === 'risk'
                                    ? 'border-amber-400/20 bg-amber-500/10 text-amber-300'
                                    : 'border-cyan-400/20 bg-cyan-500/10 text-cyan-300'
                        }`}>
                            {player.selection_warning === 'rehab' ? 'Reha' : player.selection_warning === 'risk' ? 'Medical Risk' : 'Promise Druck'}
                        </p>
                    )}
                </div>
            </div>

            <div className="flex items-center gap-1">
                {!isSelected ? (
                    <>
                        <button 
                            onClick={() => onAddPitch(player.id)}
                            className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-amber-500 hover:border-amber-500/30 transition-all flex items-center justify-center"
                        >
                            <Plus size={12} weight="bold" />
                        </button>
                        <button 
                            onClick={() => onAddBench(player.id)}
                            className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-amber-600 hover:border-amber-600/30 transition-all flex items-center justify-center"
                        >
                            <span className="text-[10px] font-black">B</span>
                        </button>
                    </>
                ) : (
                    <button 
                        onClick={() => onRemove(player.id)}
                        className="w-6 h-6 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] text-rose-500 hover:text-rose-400 transition-all flex items-center justify-center"
                    >
                        <Trash size={12} />
                    </button>
                )}
            </div>
        </div>
    );
};

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
    metrics: initialMetrics,
    positionFit
}) {
    const { data, setData, post, put, patch, processing, errors } = useForm({
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
        starter_slots: starterDraft,
        // Pad bench to maxBenchPlayers so all slot dropzones render correctly
        bench_slots: Array.from({ length: maxBenchPlayers }, (_, i) => benchDraft[i] ?? null),
        action: 'save',
        template_name: ''
    });

    const [searchTerm, setSearchTerm] = useState('');
    const [metrics, setMetrics] = useState(initialMetrics);

    // Selected Player IDs for quick checking
    const selectedPlayerIds = useMemo(() => {
        const ids = [];
        Object.values(data.starter_slots).forEach(id => id && ids.push(parseInt(id)));
        data.bench_slots.forEach(id => id && ids.push(parseInt(id)));
        return new Set(ids);
    }, [data.starter_slots, data.bench_slots]);

    const getPlayer = (id) => clubPlayers.find(p => p.id === parseInt(id));

    // Helper for position grouping (simplified JS version of PlayerPositionService)
    const getGroupFromPosition = (pos) => {
        if (!pos) return null;
        const p = pos.toUpperCase();
        if (['TW', 'GK'].includes(p)) return 'GK';
        if (['LV', 'IV', 'RV', 'LWB', 'RWB', 'DEF'].includes(p)) return 'DEF';
        if (['LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM', 'MID'].includes(p)) return 'MID';
        if (['LS', 'MS', 'RS', 'ST', 'LW', 'RW', 'LF', 'RF', 'HS', 'FWD'].includes(p)) return 'FWD';
        if (p.startsWith('IV')) return 'DEF';
        if (p.startsWith('ZM') || p.startsWith('DM')) return 'MID';
        if (p.startsWith('ST')) return 'FWD';
        return null;
    };

    // Client-side Strength Calculation
    const calculatedMetrics = useMemo(() => {
        const starterIds = Object.entries(data.starter_slots).filter(([slot, id]) => id !== null);
        if (starterIds.length === 0) return { overall: 0, attack: 0, midfield: 0, defense: 0, chemistry: 0 };

        const entries = starterIds.map(([slotKey, pId]) => {
            const p = getPlayer(pId);
            if (!p) return null; // player not found in pool — skip
            const slot = slots.find(s => s.slot === slotKey);
            const slotGroup = slot ? slot.group : null;
            
            // Simplified Fit Factor
            let fit = positionFit.foreign;
            const pPos = (p.position_main || p.position).toUpperCase();
            const pSec = (p.position_second || '').toUpperCase();
            const pThird = (p.position_third || '').toUpperCase();

            const pGroup = getGroupFromPosition(pPos);
            const sGroup = slotGroup;

            if (pGroup === sGroup) fit = positionFit.main;
            else if (getGroupFromPosition(pSec) === sGroup) fit = positionFit.second;
            else if (getGroupFromPosition(pThird) === sGroup) fit = positionFit.third;
            else if (pGroup === 'GK' || sGroup === 'GK') fit = positionFit.foreign_gk;

            return { player: p, group: sGroup, fit };
        }).filter(Boolean); // remove any null entries (player not found in pool)

        const calculateScore = (players, type) => {
            if (players.length === 0) return 0;
            const sum = players.reduce((acc, { player: p, fit }) => {
                let base = 0;
                if (type === 'attack') base = (p.shooting * 0.4) + (p.pace * 0.2) + (p.physical * 0.15) + (p.overall * 0.25);
                else if (type === 'midfield') base = (p.passing * 0.35) + (p.pace * 0.15) + (p.defending * 0.2) + (p.overall * 0.3);
                else base = (p.defending * 0.4) + (p.physical * 0.2) + (p.passing * 0.1) + (p.overall * 0.3);

                const condition = ((p.stamina + p.morale) / 200) + 0.5;
                return acc + Math.min(99, base * condition * fit);
            }, 0);
            return sum / players.length;
        };

        const attScore = calculateScore(entries.filter(e => e.group === 'FWD'), 'attack');
        const midScore = calculateScore(entries.filter(e => e.group === 'MID'), 'midfield');
        const defScore = calculateScore(entries.filter(e => ['DEF', 'GK'].includes(e.group)), 'defense');

        const baseOverall = (attScore + midScore + defScore) / 3;
        
        // Chemistry
        const avgMorale = entries.length ? entries.reduce((a, b) => a + b.player.morale, 0) / entries.length : 0;
        const avgStamina = entries.length ? entries.reduce((a, b) => a + b.player.stamina, 0) / entries.length : 0;
        const avgFit = entries.length ? entries.reduce((a, b) => a + b.fit, 0) / entries.length : 0;
        const chemistry = Math.min(100, (((avgMorale + avgStamina) / 2) + (Math.min(10, entries.length) / 2)) * Math.max(0.82, Math.min(1, avgFit)));

        const formationFactor = ['4-3-3', '4-4-2', '3-5-2', '4-2-3-1', '5-3-2'].includes(data.formation) ? 1.0 : 0.95;
        const countFactor = entries.length < 8 ? 0.8 : 1.0;
        const overall = Math.round(Math.min(99, baseOverall * formationFactor * countFactor * (chemistry / 100)));

        return {
            overall,
            attack: Math.round(attScore),
            midfield: Math.round(midScore),
            defense: Math.round(defScore),
            chemistry: Math.round(chemistry)
        };
    }, [data.starter_slots, data.formation, clubPlayers, slots, positionFit]);

    // Handle Drop to Slot
    const handleDrop = (e, slotKey, isBench = false) => {
        e.preventDefault();
        const playerId = parseInt(e.dataTransfer.getData('playerId'));
        if (!playerId) return;

        assignPlayer(playerId, slotKey, isBench);
    };

    const assignPlayer = (playerId, targetSlot, isBench = false) => {
        // 1. Remove from anywhere else
        const newStarters = { ...data.starter_slots };
        const newBench = [...data.bench_slots];

        Object.keys(newStarters).forEach(k => {
            if (parseInt(newStarters[k]) === playerId) newStarters[k] = null;
        });
        
        const benchIndex = newBench.indexOf(playerId);
        if (benchIndex !== -1) newBench[benchIndex] = null;

        // 2. Assign to target
        if (isBench) {
            newBench[targetSlot] = playerId;
        } else {
            // Check if slot already occupied
            const oldOccupant = newStarters[targetSlot];
            newStarters[targetSlot] = playerId;
            
            // If it was occupied, we could swap? For now just assign.
        }

        setData(prev => ({
            ...prev,
            starter_slots: newStarters,
            bench_slots: newBench
        }));
    };

    const removePlayer = (playerId) => {
        const newStarters = { ...data.starter_slots };
        const newBench = [...data.bench_slots];

        Object.keys(newStarters).forEach(k => {
            if (parseInt(newStarters[k]) === playerId) newStarters[k] = null;
        });
        
        const benchIndex = newBench.indexOf(playerId);
        if (benchIndex !== -1) newBench[benchIndex] = null;

        setData(prev => ({
            ...prev,
            starter_slots: newStarters,
            bench_slots: newBench
        }));
    };

    const addPitchAuto = (playerId) => {
        // Find first empty slot
        const emptySlot = Object.keys(data.starter_slots).find(k => !data.starter_slots[k]);
        if (emptySlot) assignPlayer(playerId, emptySlot);
    };

    const addBenchAuto = (playerId) => {
        const newBench = [...data.bench_slots];
        // Find first null/empty slot
        const firstEmpty = newBench.findIndex(id => !id);
        if (firstEmpty !== -1) {
            newBench[firstEmpty] = playerId;
            // Also remove from starters if there
            const newStarters = { ...data.starter_slots };
            Object.keys(newStarters).forEach(k => {
                if (parseInt(newStarters[k]) === playerId) newStarters[k] = null;
            });
            setData(prev => ({
                ...prev,
                starter_slots: newStarters,
                bench_slots: newBench
            }));
        }
    };

    // Auto Fill Action — use form's put so it submits correctly with CSRF
    const handleAutoFill = (e) => {
        e.preventDefault();
        put(route('lineups.update', lineup.id), {
            data: { ...data, action: 'auto_pick' },
            onBefore: () => setData('action', 'auto_pick'),
        });
    };

    const handleSave = (e) => {
        e.preventDefault();
        put(route('lineups.update', lineup.id));
    };

    // Effects
    useEffect(() => {
        if (data.formation !== formation) {
            router.get(window.location.pathname, { formation: data.formation }, { preserveState: true });
        }
    }, [data.formation]);

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
                                                            {m.match_date} vs {m.home_club_id === club.id ? m.away_club.short_name : m.home_club.short_name}
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
                                type="submit"
                                disabled={processing}
                                className="sim-btn-primary flex-[2] sm:flex-none justify-center px-6 sm:px-10 py-3 flex items-center gap-2"
                            >
                                <FloppyDisk size={18} weight="bold" className="shrink-0" />
                                <span className="text-[10px] sm:text-xs font-black uppercase tracking-widest truncate">Speichern</span>
                            </button>
                        </div>
                    </div>

                    <div className="flex flex-col lg:grid lg:grid-cols-[320px_1fr_320px] gap-8">
                        {/* Left Sidebar: Tactics */}
                        <aside className="space-y-6 order-3 lg:order-1">
                            <div className="sim-card p-6 bg-[#0c1222]/80 backdrop-blur-xl border-[var(--border-muted)]">
                                <h3 className="text-xs font-black text-amber-500 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <Strategy size={16} weight="bold" />
                                    STRATEGIE
                                </h3>
                                
                                <div className="space-y-6">
                                    <div>
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2 block">Formation</label>
                                        <select 
                                            value={data.formation}
                                            onChange={e => setData('formation', e.target.value)}
                                            className="sim-select w-full"
                                        >
                                            {formations.map(f => <option key={f} value={f}>{f}</option>)}
                                        </select>
                                    </div>

                                    <div>
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2 block">Mentalität</label>
                                        <select 
                                            value={data.mentality}
                                            onChange={e => setData('mentality', e.target.value)}
                                            className="sim-select w-full"
                                        >
                                            <option value="defensive">Defensiv</option>
                                            <option value="counter">Konter</option>
                                            <option value="normal">Normal</option>
                                            <option value="offensive">Offensiv</option>
                                            <option value="all_out">Brechstange</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2 block">Aggressivität</label>
                                        <select 
                                            value={data.aggression}
                                            onChange={e => setData('aggression', e.target.value)}
                                            className="sim-select w-full"
                                        >
                                            <option value="cautious">Vorsichtig</option>
                                            <option value="normal">Normal</option>
                                            <option value="aggressive">Aggressiv</option>
                                        </select>
                                    </div>

                                    <div className="pt-4 space-y-3">
                                        {/* Abseitsfalle */}
                                        <button
                                            type="button"
                                            onClick={() => setData('offside_trap', !data.offside_trap)}
                                            className={`w-full flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 group ${
                                                data.offside_trap
                                                    ? 'bg-amber-500/10 border-amber-500/40 shadow-[0_0_12px_rgba(217,177,92,0.08)]'
                                                    : 'bg-[var(--bg-pillar)]/60 border-[var(--border-pillar)] hover:border-[var(--border-pillar)]'
                                            }`}
                                        >
                                            <span className={`text-xs font-black uppercase tracking-wider transition-colors ${data.offside_trap ? 'text-amber-500' : 'text-[var(--text-muted)] group-hover:text-slate-300'}`}>
                                                Abseitsfalle
                                            </span>
                                            <div className={`relative w-10 h-5 rounded-full transition-all duration-300 ${data.offside_trap ? 'bg-amber-500' : 'bg-slate-700'}`}>
                                                <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow-md transition-all duration-300 ${data.offside_trap ? 'left-5' : 'left-0.5'}`} />
                                            </div>
                                        </button>

                                        {/* Zeitspiel */}
                                        <button
                                            type="button"
                                            onClick={() => setData('time_wasting', !data.time_wasting)}
                                            className={`w-full flex items-center justify-between px-4 py-3 rounded-2xl border transition-all duration-200 group ${
                                                data.time_wasting
                                                    ? 'bg-amber-500/10 border-amber-500/40 shadow-[0_0_12px_rgba(245,158,11,0.08)]'
                                                    : 'bg-[var(--bg-pillar)]/60 border-[var(--border-pillar)] hover:border-[var(--border-pillar)]'
                                            }`}
                                        >
                                            <span className={`text-xs font-black uppercase tracking-wider transition-colors ${data.time_wasting ? 'text-amber-300' : 'text-[var(--text-muted)] group-hover:text-slate-300'}`}>
                                                Zeitspiel
                                            </span>
                                            <div className={`relative w-10 h-5 rounded-full transition-all duration-300 ${data.time_wasting ? 'bg-amber-500' : 'bg-slate-700'}`}>
                                                <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow-md transition-all duration-300 ${data.time_wasting ? 'left-5' : 'left-0.5'}`} />
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div className="sim-card p-6 bg-[#0c1222]/80 border-[var(--border-muted)]">
                                <h3 className="text-xs font-black text-amber-600 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <Target size={16} weight="bold" />
                                    ROLLEN
                                </h3>

                                <div className="space-y-4">
                                    <div>
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2 block">Kapitän</label>
                                        <select 
                                            value={data.captain_player_id}
                                            onChange={e => setData('captain_player_id', e.target.value)}
                                            className="sim-select w-full py-1.5 text-xs"
                                        >
                                            <option value="">- Wählen -</option>
                                            {Array.from(selectedPlayerIds).map(id => {
                                                const p = getPlayer(id);
                                                return <option key={id} value={id}>{p?.full_name}</option>
                                            })}
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </aside>

                        {/* Center: The Pitch */}
                        <main className="space-y-8 order-1 lg:order-2">
                            {/* Metrics Bar */}
                            <div className="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 rounded-3xl bg-[var(--bg-pillar)]/60 border border-white/5">
                                <div className="flex items-center gap-6 px-4">
                                    <div className="flex flex-col">
                                        <span className="text-[9px] font-black text-amber-500 uppercase tracking-widest">STÄRKE</span>
                                        <span className="text-2xl font-black text-white italic leading-none">{calculatedMetrics.overall}</span>
                                    </div>
                                    <div className="h-8 w-px bg-[var(--bg-content)]" />
                                    <div className="flex gap-4">
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">ANGRIFF</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.attack}</span>
                                        </div>
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">MITTE</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.midfield}</span>
                                        </div>
                                        <div className="flex flex-col">
                                            <span className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">ABWEHR</span>
                                            <span className="text-sm font-black text-white">{calculatedMetrics.defense}</span>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                                    <Lightning size={16} weight="fill" className="text-amber-500" />
                                    <div className="flex flex-col">
                                        <span className="text-[9px] font-black text-amber-500 uppercase tracking-widest leading-none">CHEMIE</span>
                                        <span className="text-sm font-black text-white leading-none">{calculatedMetrics.chemistry}%</span>
                                    </div>
                                </div>
                            </div>

                            {/* Pitch Area */}
                            <div className="relative aspect-[68/105] w-full max-w-[500px] mx-auto bg-[#0a0a0a] rounded-[2rem] shadow-2xl overflow-hidden border-8 border-[#1a1a1a]">
                                <div className="absolute inset-0 bg-gradient-to-b from-amber-900/5 to-transparent z-10 pointer-events-none" />
                                <PitchMarkings />
                                
                                {/* Grass Texture */}
                                <div className="absolute inset-0 z-0 opacity-20 pointer-events-none" 
                                     style={{ backgroundImage: 'repeating-linear-gradient(90deg, transparent, transparent 10%, rgba(0,0,0,0.1) 10%, rgba(0,0,0,0.1) 20%)' }} />

                                <div className="absolute inset-x-8 inset-y-12 z-20">
                                    {slots.map(slot => {
                                        const pId = data.starter_slots[slot.slot];
                                        const p = getPlayer(pId);

                                        return (
                                            <div 
                                                key={slot.slot}
                                                onDragOver={e => e.preventDefault()}
                                                onDrop={e => handleDrop(e, slot.slot)}
                                                className="absolute -translate-x-1/2 -translate-y-1/2 flex flex-col items-center group/slot"
                                                style={{ left: `${slot.x}%`, top: `${slot.y}%` }}
                                            >
                                                <div className={`w-14 h-14 rounded-full border-2 transition-all duration-300 flex items-center justify-center relative ${
                                                    p ? 'bg-[var(--bg-pillar)] border-amber-500/60 shadow-[0_0_20px_rgba(217,177,92,0.2)]' 
                                                      : 'bg-black/20 border-white/10 hover:border-white/30 hover:bg-white/5 border-dashed'
                                                }`}>
                                                    {p ? (
                                                        <>
                                                            <div className="flex flex-col items-center">
                                                                <span className="text-xs font-black text-white leading-none mb-0.5">{p.shirt_number}</span>
                                                                <span className="text-[8px] font-black text-amber-500 leading-none">{p.overall}</span>
                                                            </div>
                                                            {parseInt(data.captain_player_id) === p.id && (
                                                                <div className="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-amber-500 border-2 border-slate-900 flex items-center justify-center text-[10px] font-black text-black shadow-lg">C</div>
                                                            )}
                                                            <button 
                                                                type="button"
                                                                onClick={() => removePlayer(p.id)}
                                                                className="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center opacity-0 group-hover/slot:opacity-100 transition-opacity"
                                                            >
                                                                <X size={10} weight="bold" />
                                                            </button>
                                                        </>
                                                    ) : (
                                                        <span className="text-[9px] font-black text-white/30 uppercase tracking-tighter">{slot.label}</span>
                                                    )}
                                                </div>
                                                {p && (
                                                    <div className="mt-1 px-2 py-0.5 rounded bg-black/60 backdrop-blur-md border border-white/5">
                                                        <span className="text-[9px] font-black text-white uppercase truncate max-w-[60px] block">{p.last_name}</span>
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>

                            {/* Bench */}
                            <div className="sim-card p-6 bg-[var(--bg-pillar)]/40 border-[var(--border-pillar)]/40">
                                <div className="flex items-center justify-between mb-4">
                                    <h3 className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">Auswechselbank</h3>
                                    <span className="text-[9px] font-bold text-slate-600">Max. {maxBenchPlayers}</span>
                                </div>
                                <div className="flex flex-wrap gap-4">
                                    {data.bench_slots.map((pId, idx) => {
                                        const p = getPlayer(pId);
                                        return (
                                            <div 
                                                key={idx}
                                                onDragOver={e => e.preventDefault()}
                                                onDrop={e => handleDrop(e, idx, true)}
                                                className={`w-16 h-16 rounded-2xl border-2 transition-all flex flex-col items-center justify-center group/bench relative ${
                                                    p ? 'bg-[var(--bg-pillar)] border-amber-600/40' 
                                                      : 'bg-black/20 border-white/5 border-dashed hover:border-white/10'
                                                }`}
                                            >
                                                {p ? (
                                                    <>
                                                        <span className="text-xs font-black text-white leading-none mb-1">{p.shirt_number}</span>
                                                        <span className="text-[8px] font-black text-[var(--text-muted)] uppercase truncate max-w-[50px]">{p.last_name}</span>
                                                        <button 
                                                            type="button"
                                                            onClick={() => removePlayer(p.id)}
                                                            className="absolute -top-2 -right-2 w-5 h-5 rounded-full bg-rose-500 text-white flex items-center justify-center opacity-0 group-hover/bench:opacity-100 transition-opacity shadow-lg"
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

                        {/* Right Sidebar: Player Pool */}
                        <aside className="space-y-6 flex flex-col h-[500px] lg:h-full overflow-hidden min-h-[500px] lg:min-h-[800px] order-2 lg:order-3">
                            <div className="sim-card p-4 sm:p-6 bg-[#0c1222]/80 border-[var(--border-muted)] flex flex-col h-full">
                                <h3 className="text-xs font-black text-slate-300 uppercase tracking-widest mb-4">SPIELER-POOL</h3>
                                
                                <div className="relative mb-6">
                                    <MagnifyingGlass size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                                    <input 
                                        type="text" 
                                        placeholder="Suchen..."
                                        value={searchTerm}
                                        onChange={e => setSearchTerm(e.target.value)}
                                        className="sim-input pl-10 py-2.5 text-xs w-full"
                                    />
                                </div>

                                <div className="flex-1 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                    {clubPlayers
                                        .filter(p => !searchTerm || p.full_name.toLowerCase().includes(searchTerm.toLowerCase()))
                                        .map(p => (
                                            <PlayerCard 
                                                key={p.id}
                                                player={p}
                                                isSelected={selectedPlayerIds.has(p.id)}
                                                onDragStart={(e, id) => e.dataTransfer.setData('playerId', id)}
                                                onAddPitch={addPitchAuto}
                                                onAddBench={addBenchAuto}
                                                onRemove={removePlayer}
                                            />
                                        ))
                                    }
                                </div>
                            </div>
                        </aside>
                    </div>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-pitch {
                    background: radial-gradient(circle at center, #1a1a1a 0%, #0a0a0a 100%);
                }
                .custom-scrollbar::-webkit-scrollbar {
                    width: 4px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    @apply bg-transparent;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    @apply bg-[var(--bg-content)] rounded-full;
                }
            `}} />
        </AuthenticatedLayout>
    );
}

