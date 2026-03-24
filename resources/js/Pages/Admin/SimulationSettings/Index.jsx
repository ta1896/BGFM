import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { 
    Gear, Timer, ArrowsLeftRight, Users, 
    ToggleLeft, ToggleRight, FloppyDisk, ChartBar,
    CaretDown, CaretUp
} from '@phosphor-icons/react';
import { useState } from 'react';

const ATTRIBUTE_LABELS = {
    overall: 'Overall',
    shooting: 'Shooting',
    passing: 'Passing',
    defending: 'Defending',
    pace: 'Pace',
    physical: 'Physical',
    stamina: 'Stamina',
    morale: 'Morale',
    attr_attacking: 'Attacking',
    attr_technical: 'Technical',
    attr_tactical: 'Tactical',
    attr_defending: 'Defending Attr.',
    attr_creativity: 'Creativity',
    attr_market: 'Market Value',
    potential: 'Potential',
};

const TEAM_STRENGTH_GROUPS = [
    {
        key: 'attack',
        title: 'Attack Weighting',
        fields: ['shooting', 'pace', 'physical', 'overall', 'attr_attacking', 'attr_technical', 'attr_tactical', 'attr_creativity', 'attr_market', 'potential'],
    },
    {
        key: 'midfield',
        title: 'Midfield Weighting',
        fields: ['passing', 'pace', 'defending', 'overall', 'attr_technical', 'attr_tactical', 'attr_creativity', 'attr_defending', 'attr_attacking', 'attr_market', 'potential'],
    },
    {
        key: 'defense',
        title: 'Defense Weighting',
        fields: ['defending', 'physical', 'passing', 'overall', 'attr_defending', 'attr_tactical', 'attr_technical', 'attr_creativity', 'attr_market', 'potential'],
    },
];

const MATCH_STRENGTH_FIELDS = ['overall', 'shooting', 'passing', 'defending', 'stamina', 'morale', 'attr_attacking', 'attr_technical', 'attr_tactical', 'attr_defending', 'attr_creativity', 'attr_market', 'potential'];
const SLOT_SCORE_BONUS_FIELDS = [
    ['main', 'Hauptposition'],
    ['second', 'Nebenposition'],
    ['third', 'Dritte Position'],
    ['group_fallback', 'Gruppen-Fallback'],
];

function buildInitialSimulation(settings, moduleSettingsSections) {
    const simulation = {
        scheduler: {
            interval_minutes:          settings?.scheduler?.interval_minutes ?? 1,
            default_limit:             settings?.scheduler?.default_limit ?? 0,
            max_concurrency:           settings?.scheduler?.max_concurrency ?? 5,
            default_minutes_per_run:   settings?.scheduler?.default_minutes_per_run ?? 5,
            default_types:             settings?.scheduler?.default_types ?? ['friendly', 'league', 'cup'],
            claim_stale_after_seconds: settings?.scheduler?.claim_stale_after_seconds ?? 180,
            runner_lock_seconds:       settings?.scheduler?.runner_lock_seconds ?? 120,
        },
        position_fit: {
            main:       settings?.position_fit?.main       ?? 1.0,
            second:     settings?.position_fit?.second     ?? 0.9,
            third:      settings?.position_fit?.third      ?? 0.8,
            foreign:    settings?.position_fit?.foreign    ?? 0.7,
            foreign_gk: settings?.position_fit?.foreign_gk ?? 0.5,
        },
        live_changes: {
            planned_substitutions: {
                max_per_club:         settings?.live_changes?.planned_substitutions?.max_per_club         ?? 3,
                min_minutes_ahead:    settings?.live_changes?.planned_substitutions?.min_minutes_ahead    ?? 2,
                min_interval_minutes: settings?.live_changes?.planned_substitutions?.min_interval_minutes ?? 3,
            }
        },
        lineup: {
            max_bench_players: settings?.lineup?.max_bench_players ?? 5,
        },
        lineup_scoring: {
            slot_score_bonuses: {
                main: settings?.lineup_scoring?.slot_score_bonuses?.main ?? 120,
                second: settings?.lineup_scoring?.slot_score_bonuses?.second ?? 70,
                third: settings?.lineup_scoring?.slot_score_bonuses?.third ?? 35,
                group_fallback: settings?.lineup_scoring?.slot_score_bonuses?.group_fallback ?? 20,
            },
            fit_weight: settings?.lineup_scoring?.fit_weight ?? 260,
            role_weight: settings?.lineup_scoring?.role_weight ?? 3,
            low_fit_penalty: settings?.lineup_scoring?.low_fit_penalty ?? 220,
        },
        team_strength: {
            weights: {
                attack: Object.fromEntries(TEAM_STRENGTH_GROUPS[0].fields.map((field) => [field, settings?.team_strength?.weights?.attack?.[field] ?? 0])),
                midfield: Object.fromEntries(TEAM_STRENGTH_GROUPS[1].fields.map((field) => [field, settings?.team_strength?.weights?.midfield?.[field] ?? 0])),
                defense: Object.fromEntries(TEAM_STRENGTH_GROUPS[2].fields.map((field) => [field, settings?.team_strength?.weights?.defense?.[field] ?? 0])),
            },
            formation_factor: {
                complete_lineup: settings?.team_strength?.formation_factor?.complete_lineup ?? 1,
                incomplete_lineup: settings?.team_strength?.formation_factor?.incomplete_lineup ?? 0.8,
                minimum_players: settings?.team_strength?.formation_factor?.minimum_players ?? 8,
            },
            chemistry: {
                size_bonus_cap: settings?.team_strength?.chemistry?.size_bonus_cap ?? 10,
                fit_modifier_min: settings?.team_strength?.chemistry?.fit_modifier_min ?? 0.82,
                fit_modifier_max: settings?.team_strength?.chemistry?.fit_modifier_max ?? 1,
            },
        },
        match_strength: {
            weights: Object.fromEntries(MATCH_STRENGTH_FIELDS.map((field) => [field, settings?.match_strength?.weights?.[field] ?? 0])),
            home_bonus: settings?.match_strength?.home_bonus ?? 3.5,
        },
        features: {
            player_conversations_enabled: settings?.features?.player_conversations_enabled ?? false,
        },
        observers: {
            match_finished: {
                enabled:                              settings?.observers?.match_finished?.enabled                              ?? true,
                rebuild_match_player_stats:           settings?.observers?.match_finished?.rebuild_match_player_stats           ?? true,
                aggregate_player_competition_stats:   settings?.observers?.match_finished?.aggregate_player_competition_stats   ?? true,
                apply_match_availability:             settings?.observers?.match_finished?.apply_match_availability             ?? true,
                update_competition_after_match:       settings?.observers?.match_finished?.update_competition_after_match       ?? true,
                settle_match_finance:                 settings?.observers?.match_finished?.settle_match_finance                 ?? true,
            }
        }
    };

    (moduleSettingsSections ?? []).forEach((section) => {
        (section?.fields ?? []).forEach((field) => {
            if (typeof field?.key !== 'string' || !field.key.startsWith('simulation.')) {
                return;
            }

            const path = field.key.split('.');
            let cursor = { simulation };

            path.forEach((segment, index) => {
                if (index === path.length - 1) {
                    cursor[segment] = field.value ?? field.default ?? false;
                    return;
                }

                if (!cursor[segment] || typeof cursor[segment] !== 'object') {
                    cursor[segment] = {};
                }

                cursor = cursor[segment];
            });
        });
    });

    return simulation;
}

function SettingSlider({ label, desc, name, value, min, max, step = 0.01, onChange }) {
    return (
        <div>
            <div className="flex items-center justify-between mb-1">
                <label className="text-xs font-bold text-slate-300">{label}</label>
                <span className="text-xs font-mono text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">{value}</span>
            </div>
            <input
                type="range" min={min} max={max} step={step}
                value={value}
                onChange={e => onChange(name, parseFloat(e.target.value))}
                className="w-full h-2 bg-slate-700 rounded-full appearance-none cursor-pointer accent-cyan-500"
            />
            {desc && <p className="text-[10px] text-slate-600 mt-1">{desc}</p>}
        </div>
    );
}

function Toggle({ label, desc, checked, onChange }) {
    return (
        <label className="flex items-start gap-3 p-3 rounded-xl border border-[var(--border-pillar)]/40 bg-[var(--bg-content)]/20 hover:bg-[var(--bg-content)]/40 cursor-pointer transition">
            <div className="pt-0.5 flex-shrink-0">
                <div
                    onClick={onChange}
                    className={`w-10 h-5 rounded-full transition-colors relative cursor-pointer ${checked ? 'bg-cyan-500' : 'bg-slate-700'}`}
                >
                    <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${checked ? 'translate-x-5' : 'translate-x-0.5'}`} />
                </div>
            </div>
            <div>
                <span className="block text-sm font-bold text-slate-200">{label}</span>
                {desc && <span className="block text-[10px] text-[var(--text-muted)] mt-0.5 leading-snug">{desc}</span>}
            </div>
        </label>
    );
}

function SectionHeader({ title, icon: Icon, colorClass, isOpen, onToggle, description }) {
    return (
        <div 
            className="flex items-center justify-between cursor-pointer group"
            onClick={onToggle}
        >
            <div className="flex items-center gap-3">
                <div className={`p-2 rounded-lg bg-white/5 border border-white/10 ${colorClass}`}>
                    <Icon size={18} weight="duotone" />
                </div>
                <div>
                    <h3 className="text-sm font-black uppercase tracking-widest text-white">{title}</h3>
                    {description && <p className="text-[10px] text-[var(--text-muted)] mt-0.5 font-bold uppercase tracking-wider">{description}</p>}
                </div>
            </div>
            <div className={`transition-transform duration-300 ${isOpen ? 'rotate-180' : ''}`}>
                <CaretDown size={18} className="text-[var(--text-muted)] group-hover:text-white" />
            </div>
        </div>
    );
}

function fieldValueFromData(data, key) {
    return key.split('.').reduce((carry, segment) => carry?.[segment], data);
}

export default function Index({ simulationSettings: s, moduleSettingsSections = [] }) {
    const { data, setData, put, processing } = useForm({
        simulation: buildInitialSimulation(s, moduleSettingsSections),
    });

    const [openSections, setOpenSections] = useState({
        scheduler: true,
        positionFit: true,
        liveChanges: true,
        lineupLimits: true,
        lineupScoring: true,
        teamStrength: true,
        matchStrength: true,
        features: true,
        modules: true,
        observers: true
    });

    const toggleSection = (section) => {
        setOpenSections(prev => ({ ...prev, [section]: !prev[section] }));
    };

    const setNested = (path, value) => {
        const keys = path.split('.');
        setData(prev => {
            const next = JSON.parse(JSON.stringify(prev));
            let obj = next;
            for (let i = 0; i < keys.length - 1; i++) {
                if (!obj[keys[i]] || typeof obj[keys[i]] !== 'object') {
                    obj[keys[i]] = {};
                }

                obj = obj[keys[i]];
            }
            obj[keys[keys.length - 1]] = value;
            return next;
        });
    };

    const toggleType = (type) => {
        const types = data.simulation.scheduler.default_types;
        const next = types.includes(type) ? types.filter(t => t !== type) : [...types, type];
        setNested('simulation.scheduler.default_types', next);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.simulation.settings.update'));
    };

    const sc = data.simulation.scheduler;
    const pf = data.simulation.position_fit;
    const lc = data.simulation.live_changes.planned_substitutions;
    const ls = data.simulation.lineup_scoring.slot_score_bonuses;
    const ft = data.simulation.features;
    const ob = data.simulation.observers.match_finished;
    const ts = data.simulation.team_strength.weights;
    const tsFormation = data.simulation.team_strength.formation_factor;
    const tsChemistry = data.simulation.team_strength.chemistry;
    const ms = data.simulation.match_strength;

    return (
        <AdminLayout>
            <Head title="Simulation Settings" />

            <form onSubmit={handleSubmit} className="space-y-8 pb-20">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Simulation Setup</h2>
                        <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">Engine-Konfiguration</p>
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="sim-btn-primary px-8 py-3 flex items-center gap-2"
                    >
                        <FloppyDisk size={18} weight="bold" />
                        Speichern
                    </button>
                </div>

                {/* Scheduler */}
                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Scheduler Konfiguration" 
                        icon={Timer} 
                        colorClass="text-cyan-400"
                        isOpen={openSections.scheduler}
                        onToggle={() => toggleSection('scheduler')}
                        description="Steuerung der automatischen Berechnung"
                    />

                    {openSections.scheduler && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 space-y-8 animate-in fade-in slide-in-from-top-2 duration-300">
                            <p className="text-xs text-[var(--text-muted)]">Steuert die automatische Berechnung von Spielen im Hintergrund.</p>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                {[
                                    { key: 'interval_minutes', label: 'Intervall (Min)', min: 1, max: 60, step: 1, desc: 'Wie oft der Scheduler läuft' },
                                    { key: 'default_limit', label: 'Max Matches / Lauf', min: 0, max: 500, step: 1, desc: '0 = Keine Begrenzung' },
                                    { key: 'default_minutes_per_run', label: 'Spielminuten / Lauf', min: 1, max: 90, step: 1, desc: 'Simulierte Spielminuten pro Intervall' },
                                    { key: 'max_concurrency', label: 'Max. Worker', min: 1, max: 50, step: 1, desc: 'Parallele Worker-Prozesse' },
                                ].map(f => (
                                    <div key={f.key}>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">{f.label}</label>
                                        <input
                                            type="number" min={f.min} max={f.max} step={f.step}
                                            value={sc[f.key]}
                                            onChange={e => setNested(`simulation.scheduler.${f.key}`, parseInt(e.target.value))}
                                            className="sim-input w-full"
                                        />
                                        <p className="text-[10px] text-slate-600 mt-1">{f.desc}</p>
                                    </div>
                                ))}
                            </div>

                            <div className="pt-5 border-t border-[var(--border-pillar)]">
                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-3">Automatische Match-Typen</label>
                                <div className="flex flex-wrap gap-3">
                                    {[['friendly', 'Freundschaftsspiele'], ['league', 'Ligaspiele'], ['cup', 'Pokalspiele']].map(([t, l]) => (
                                        <button
                                            key={t} type="button"
                                            onClick={() => toggleType(t)}
                                            className={`px-4 py-2 rounded-xl text-sm font-bold border transition-all ${
                                                sc.default_types.includes(t)
                                                    ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-300'
                                                    : 'bg-[var(--bg-content)] border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-white'
                                            }`}
                                        >{l}</button>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* Position Fit */}
                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Position Fit Multipliers" 
                        icon={ChartBar} 
                        colorClass="text-indigo-400"
                        isOpen={openSections.positionFit}
                        onToggle={() => toggleSection('positionFit')}
                        description="Einfluss der Positionstreue auf die Spielstärke"
                    />

                    {openSections.positionFit && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 space-y-8 animate-in fade-in slide-in-from-top-2 duration-300">
                            <p className="text-xs text-[var(--text-muted)]">Einfluss der Positionstreue auf die Spielstärke (1.0 = 100%).</p>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                {[
                                    { key: 'main', label: 'Hauptposition', desc: 'Perfekter Fit', min: 0.50, max: 1.20 },
                                    { key: 'second', label: 'Nebenposition', desc: 'Leichter Abzug', min: 0.50, max: 1.20 },
                                    { key: 'third', label: 'Dritte Position', desc: 'Spürbarer Abzug', min: 0.50, max: 1.20 },
                                    { key: 'foreign', label: 'Fremdposition', desc: 'Starker Abzug', min: 0.30, max: 1.20 },
                                    { key: 'foreign_gk', label: 'Feldspieler im Tor', desc: 'Extremer Abzug', min: 0.20, max: 1.20 },
                                ].map(f => (
                                    <SettingSlider
                                        key={f.key}
                                        label={f.label} desc={f.desc}
                                        name={`simulation.position_fit.${f.key}`}
                                        value={pf[f.key]} min={f.min} max={f.max}
                                        onChange={(name, val) => setNested(name, val)}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                {/* Live Changes & Lineup */}
                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="sim-card p-6">
                        <SectionHeader 
                            title="Live Changes" 
                            icon={ArrowsLeftRight} 
                            colorClass="text-emerald-400"
                            isOpen={openSections.liveChanges}
                            onToggle={() => toggleSection('liveChanges')}
                            description="Geplante Auswechslungen & Intervalle"
                        />

                        {openSections.liveChanges && (
                            <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Geplante Wechsel (Max/Club)</label>
                                    <input type="number" min={1} max={5}
                                        value={lc.max_per_club}
                                        onChange={e => setNested('simulation.live_changes.planned_substitutions.max_per_club', parseInt(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Vorlauf (Min)</label>
                                        <input type="number" min={1} max={30}
                                            value={lc.min_minutes_ahead}
                                            onChange={e => setNested('simulation.live_changes.planned_substitutions.min_minutes_ahead', parseInt(e.target.value))}
                                            className="sim-input w-full"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Intervall (Min)</label>
                                        <input type="number" min={1} max={30}
                                            value={lc.min_interval_minutes}
                                            onChange={e => setNested('simulation.live_changes.planned_substitutions.min_interval_minutes', parseInt(e.target.value))}
                                            className="sim-input w-full"
                                        />
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="sim-card p-6">
                        <SectionHeader 
                            title="Lineup Limits" 
                            icon={Users} 
                            colorClass="text-amber-400"
                            isOpen={openSections.lineupLimits}
                            onToggle={() => toggleSection('lineupLimits')}
                            description="Kaderregeln & Bankgröße"
                        />

                        {openSections.lineupLimits && (
                            <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 space-y-4 animate-in fade-in slide-in-from-top-2 duration-300">
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Maximale Bankspieler</label>
                                    <input type="number" min={1} max={10}
                                        value={data.simulation.lineup.max_bench_players}
                                        onChange={e => setNested('simulation.lineup.max_bench_players', parseInt(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                    <p className="text-[10px] text-slate-600 mt-2">Größe der Ersatzbank für alle Wettbewerbe (1–10).</p>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Lineup Scoring" 
                        icon={ChartBar} 
                        colorClass="text-amber-300"
                        isOpen={openSections.lineupScoring}
                        onToggle={() => toggleSection('lineupScoring')}
                        description="Bonusse fuer Auto-Selection und Slot-Fit"
                    />

                    {openSections.lineupScoring && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                {SLOT_SCORE_BONUS_FIELDS.map(([key, label]) => (
                                    <div key={key}>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                            {label}
                                        </label>
                                        <input
                                            type="number"
                                            min={0}
                                            max={500}
                                            step={1}
                                            value={ls[key]}
                                            onChange={(e) => setNested(`simulation.lineup_scoring.slot_score_bonuses.${key}`, Number(e.target.value))}
                                            className="sim-input w-full"
                                        />
                                    </div>
                                ))}
                            </div>

                            <div className="mt-6 grid gap-4 md:grid-cols-3">
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                        Fit Weight
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        max={1000}
                                        step={1}
                                        value={data.simulation.lineup_scoring.fit_weight}
                                        onChange={(e) => setNested('simulation.lineup_scoring.fit_weight', Number(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>

                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                        Role Weight
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        max={25}
                                        step={0.1}
                                        value={data.simulation.lineup_scoring.role_weight}
                                        onChange={(e) => setNested('simulation.lineup_scoring.role_weight', Number(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>

                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                        Low Fit Penalty
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        max={1000}
                                        step={1}
                                        value={data.simulation.lineup_scoring.low_fit_penalty}
                                        onChange={(e) => setNested('simulation.lineup_scoring.low_fit_penalty', Number(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Team Strength Weighting" 
                        icon={ChartBar} 
                        colorClass="text-sky-400"
                        isOpen={openSections.teamStrength}
                        onToggle={() => toggleSection('teamStrength')}
                        description="Gewichte fuer Angriff, Mitte und Abwehr"
                    />

                    {openSections.teamStrength && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            <p className="text-xs text-[var(--text-muted)] mb-8">Diese Gewichte steuern die Teamstaerke-Anzeige im Lineup-Editor. 0 deaktiviert ein Attribut, 1 gibt ihm volles Gewicht.</p>
                            <div className="grid gap-6 xl:grid-cols-3">
                                {TEAM_STRENGTH_GROUPS.map((group) => (
                                    <div key={group.key} className="rounded-2xl border border-[var(--border-pillar)]/50 bg-[var(--bg-content)]/20 p-5">
                                        <div className="flex items-center justify-between gap-4">
                                            <h4 className="text-sm font-black uppercase tracking-widest text-white">{group.title}</h4>
                                            <span className="rounded-full border border-cyan-500/20 bg-cyan-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-cyan-300">
                                                Summe {(group.fields.reduce((sum, field) => sum + Number(ts?.[group.key]?.[field] ?? 0), 0)).toFixed(2)}
                                            </span>
                                        </div>
                                        <div className="mt-5 space-y-3">
                                            {group.fields.map((field) => (
                                                <div key={field}>
                                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                                        {ATTRIBUTE_LABELS[field] ?? field}
                                                    </label>
                                                    <input
                                                        type="number"
                                                        min={0}
                                                        max={1}
                                                        step={0.01}
                                                        value={ts?.[group.key]?.[field] ?? 0}
                                                        onChange={(e) => setNested(`simulation.team_strength.weights.${group.key}.${field}`, Number(e.target.value))}
                                                        className="sim-input w-full"
                                                    />
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="mt-6 grid gap-6 lg:grid-cols-2">
                                <div className="rounded-2xl border border-[var(--border-pillar)]/50 bg-[var(--bg-content)]/20 p-5">
                                    <h4 className="text-sm font-black uppercase tracking-widest text-white">Formation Factor</h4>
                                    <div className="mt-5 grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Complete</label>
                                            <input
                                                type="number"
                                                min={0.1}
                                                max={2}
                                                step={0.01}
                                                value={tsFormation.complete_lineup}
                                                onChange={(e) => setNested('simulation.team_strength.formation_factor.complete_lineup', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Incomplete</label>
                                            <input
                                                type="number"
                                                min={0.1}
                                                max={2}
                                                step={0.01}
                                                value={tsFormation.incomplete_lineup}
                                                onChange={(e) => setNested('simulation.team_strength.formation_factor.incomplete_lineup', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Min Players</label>
                                            <input
                                                type="number"
                                                min={1}
                                                max={11}
                                                step={1}
                                                value={tsFormation.minimum_players}
                                                onChange={(e) => setNested('simulation.team_strength.formation_factor.minimum_players', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-[var(--border-pillar)]/50 bg-[var(--bg-content)]/20 p-5">
                                    <h4 className="text-sm font-black uppercase tracking-widest text-white">Chemistry</h4>
                                    <div className="mt-5 grid gap-4 md:grid-cols-3">
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Size Bonus Cap</label>
                                            <input
                                                type="number"
                                                min={0}
                                                max={25}
                                                step={1}
                                                value={tsChemistry.size_bonus_cap}
                                                onChange={(e) => setNested('simulation.team_strength.chemistry.size_bonus_cap', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Fit Min</label>
                                            <input
                                                type="number"
                                                min={0.1}
                                                max={1.5}
                                                step={0.01}
                                                value={tsChemistry.fit_modifier_min}
                                                onChange={(e) => setNested('simulation.team_strength.chemistry.fit_modifier_min', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Fit Max</label>
                                            <input
                                                type="number"
                                                min={0.1}
                                                max={1.5}
                                                step={0.01}
                                                value={tsChemistry.fit_modifier_max}
                                                onChange={(e) => setNested('simulation.team_strength.chemistry.fit_modifier_max', Number(e.target.value))}
                                                className="sim-input w-full"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Match Strength Weighting" 
                        icon={ChartBar} 
                        colorClass="text-violet-400"
                        isOpen={openSections.matchStrength}
                        onToggle={() => toggleSection('matchStrength')}
                        description="Gewichte fuer die eigentliche Match-Berechnung"
                    />

                    {openSections.matchStrength && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            <p className="text-xs text-[var(--text-muted)] mb-8">Diese Werte fliessen in die Match-Staerke der Simulation ein. Der Heimbonus wird separat addiert.</p>
                            <div className="mb-6 flex justify-end">
                                <span className="rounded-full border border-violet-500/20 bg-violet-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-violet-300">
                                    Summe {(MATCH_STRENGTH_FIELDS.reduce((sum, field) => sum + Number(ms.weights?.[field] ?? 0), 0)).toFixed(2)}
                                </span>
                            </div>
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                {MATCH_STRENGTH_FIELDS.map((field) => (
                                    <div key={field}>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                            {ATTRIBUTE_LABELS[field] ?? field}
                                        </label>
                                        <input
                                            type="number"
                                            min={0}
                                            max={1}
                                            step={0.01}
                                            value={ms.weights?.[field] ?? 0}
                                            onChange={(e) => setNested(`simulation.match_strength.weights.${field}`, Number(e.target.value))}
                                            className="sim-input w-full"
                                        />
                                    </div>
                                ))}
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                        Home Bonus
                                    </label>
                                    <input
                                        type="number"
                                        min={0}
                                        max={25}
                                        step={0.1}
                                        value={ms.home_bonus}
                                        onChange={(e) => setNested('simulation.match_strength.home_bonus', Number(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                <div className="sim-card p-6">
                    <SectionHeader 
                        title="Gameplay Features" 
                        icon={Gear} 
                        colorClass="text-fuchsia-400"
                        isOpen={openSections.features}
                        onToggle={() => toggleSection('features')}
                        description="Modulare Gameplay-Elemente"
                    />

                    {openSections.features && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            <div className="grid gap-3 md:grid-cols-2">
                                <Toggle
                                    label="Spielergespraeche aktivieren"
                                    desc="Nur wenn aktiv, wirken Gespraeche in Hierarchie, Spielerprofil und Manager-Historie."
                                    checked={ft.player_conversations_enabled}
                                    onChange={() => setNested('simulation.features.player_conversations_enabled', !ft.player_conversations_enabled)}
                                />
                            </div>
                        </div>
                    )}
                </div>

                {moduleSettingsSections.length > 0 && (
                    <div className="sim-card p-6">
                        <SectionHeader 
                            title="Module Sections" 
                            icon={Gear} 
                            colorClass="text-cyan-300"
                            isOpen={openSections.modules}
                            onToggle={() => toggleSection('modules')}
                            description="Zusätzliche Erweiterungen & Widgets"
                        />

                        {openSections.modules && (
                            <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                                <p className="text-xs text-[var(--text-muted)] mb-8">Steuert modulare Dashboard-Widgets, Matchcenter-Panels und weitere Erweiterungen.</p>
                                
                                <div className="grid gap-6 xl:grid-cols-2">
                                    {moduleSettingsSections.map((section) => (
                                        <div key={section.key} className="rounded-2xl border border-[var(--border-pillar)]/50 bg-[var(--bg-content)]/20 p-5">
                                            <div className="mb-4">
                                                <p className="text-[10px] font-black uppercase tracking-[0.24em] text-cyan-300/80">
                                                    {section.module_name}
                                                </p>
                                                <h4 className="mt-1 text-lg font-black text-white">{section.title}</h4>
                                                {section.description && (
                                                    <p className="mt-1 text-xs text-[var(--text-muted)]">{section.description}</p>
                                                )}
                                            </div>

                                            <div className="space-y-3">
                                                {(section.fields ?? []).map((field) => {
                                                    const fieldValue = fieldValueFromData(data, field.key);

                                                    if ((field.type ?? 'boolean') === 'boolean') {
                                                        return (
                                                            <Toggle
                                                                key={field.key}
                                                                label={field.label}
                                                                desc={field.description}
                                                                checked={Boolean(fieldValue)}
                                                                onChange={() => setNested(field.key, !fieldValue)}
                                                            />
                                                        );
                                                    }

                                                    if (field.type === 'select') {
                                                        return (
                                                            <div key={field.key}>
                                                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                                                    {field.label}
                                                                </label>
                                                                <select
                                                                    value={fieldValue ?? field.default ?? ''}
                                                                    onChange={(e) => setNested(field.key, e.target.value)}
                                                                    className="sim-select w-full"
                                                                >
                                                                    {(field.options ?? []).map((option) => (
                                                                        <option key={option} value={option}>
                                                                            {option}
                                                                        </option>
                                                                    ))}
                                                                </select>
                                                                {field.description && (
                                                                    <p className="text-[10px] text-slate-600 mt-1">{field.description}</p>
                                                                )}
                                                            </div>
                                                        );
                                                    }

                                                    if (field.type === 'text') {
                                                        return (
                                                            <div key={field.key}>
                                                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                                                    {field.label}
                                                                </label>
                                                                <input
                                                                    type="text"
                                                                    maxLength={field.max_length ?? 255}
                                                                    value={fieldValue ?? field.default ?? ''}
                                                                    onChange={(e) => setNested(field.key, e.target.value)}
                                                                    className="sim-input w-full"
                                                                />
                                                                {field.description && (
                                                                    <p className="text-[10px] text-slate-600 mt-1">{field.description}</p>
                                                                )}
                                                            </div>
                                                        );
                                                    }

                                                    return (
                                                        <div key={field.key}>
                                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">
                                                                {field.label}
                                                            </label>
                                                            <input
                                                                type="number"
                                                                min={field.min ?? 0}
                                                                max={field.max ?? 1000}
                                                                step={field.step ?? 1}
                                                                value={fieldValue ?? field.default ?? 0}
                                                                onChange={(e) => setNested(field.key, Number(e.target.value))}
                                                                className="sim-input w-full"
                                                            />
                                                            {field.description && (
                                                                <p className="text-[10px] text-slate-600 mt-1">{field.description}</p>
                                                            )}
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* Observers */}
                <div className="sim-card p-6">
                    <div className="flex items-center justify-between">
                        <SectionHeader 
                            title="Post-Match Pipeline (Observers)" 
                            icon={Gear} 
                            colorClass="text-rose-400"
                            isOpen={openSections.observers}
                            onToggle={() => toggleSection('observers')}
                            description="Aktionen nach Spielende"
                        />
                        <button
                            type="button"
                            onClick={() => setNested('simulation.observers.match_finished.enabled', !ob.enabled)}
                            className={`flex items-center gap-2 px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest border transition-all ${
                                ob.enabled
                                    ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400'
                                    : 'bg-[var(--bg-content)] border-[var(--border-pillar)] text-[var(--text-muted)]'
                            }`}
                        >
                            {ob.enabled ? <ToggleRight size={16} /> : <ToggleLeft size={16} />}
                            Pipeline {ob.enabled ? 'Aktiv' : 'Inaktiv'}
                        </button>
                    </div>

                    {openSections.observers && (
                        <div className="mt-8 pt-8 border-t border-[var(--border-pillar)]/50 animate-in fade-in slide-in-from-top-2 duration-300">
                            <p className="text-xs text-[var(--text-muted)] mb-6">Aktionen, die nach jedem Spiel automatisch ausgeführt werden.</p>
                            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                {[
                                    { key: 'rebuild_match_player_stats', label: 'Statistiken berechnen', desc: 'Goals, Assists & Cards aus Event-Log' },
                                    { key: 'aggregate_player_competition_stats', label: 'Wettbewerbs-Stats', desc: 'Summierte Saison-Statistiken' },
                                    { key: 'apply_match_availability', label: 'Sperren & Fitness', desc: 'Gelbsperren und Erschöpfung' },
                                    { key: 'update_competition_after_match', label: 'Tabellen & Runden', desc: 'Ligatabelle neu berechnen' },
                                    { key: 'settle_match_finance', label: 'Finanzen buchen', desc: 'Prämien und Ticketeinnahmen' },
                                ].map(f => (
                                    <Toggle
                                        key={f.key}
                                        label={f.label} desc={f.desc}
                                        checked={ob[f.key]}
                                        onChange={() => setNested(`simulation.observers.match_finished.${f.key}`, !ob[f.key])}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </form>
        </AdminLayout>
    );
}
