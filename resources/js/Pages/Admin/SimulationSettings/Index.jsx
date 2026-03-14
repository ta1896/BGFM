import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { 
    Gear, Timer, ArrowsLeftRight, Users, 
    ToggleLeft, ToggleRight, FloppyDisk, ChartBar
} from '@phosphor-icons/react';

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
        <label className="flex items-start gap-3 p-3 rounded-xl border border-slate-700/40 bg-slate-800/20 hover:bg-slate-800/40 cursor-pointer transition">
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
                {desc && <span className="block text-[10px] text-slate-500 mt-0.5 leading-snug">{desc}</span>}
            </div>
        </label>
    );
}

export default function Index({ simulationSettings: s }) {
    const { data, setData, put, processing, errors } = useForm({
        simulation: {
            scheduler: {
                interval_minutes:          s?.scheduler?.interval_minutes ?? 1,
                default_limit:             s?.scheduler?.default_limit ?? 0,
                max_concurrency:           s?.scheduler?.max_concurrency ?? 5,
                default_minutes_per_run:   s?.scheduler?.default_minutes_per_run ?? 5,
                default_types:             s?.scheduler?.default_types ?? ['friendly', 'league', 'cup'],
                claim_stale_after_seconds: s?.scheduler?.claim_stale_after_seconds ?? 180,
                runner_lock_seconds:       s?.scheduler?.runner_lock_seconds ?? 120,
            },
            position_fit: {
                main:       s?.position_fit?.main       ?? 1.0,
                second:     s?.position_fit?.second     ?? 0.9,
                third:      s?.position_fit?.third      ?? 0.8,
                foreign:    s?.position_fit?.foreign    ?? 0.7,
                foreign_gk: s?.position_fit?.foreign_gk ?? 0.5,
            },
            live_changes: {
                planned_substitutions: {
                    max_per_club:         s?.live_changes?.planned_substitutions?.max_per_club         ?? 3,
                    min_minutes_ahead:    s?.live_changes?.planned_substitutions?.min_minutes_ahead    ?? 2,
                    min_interval_minutes: s?.live_changes?.planned_substitutions?.min_interval_minutes ?? 3,
                }
            },
            lineup: {
                max_bench_players: s?.lineup?.max_bench_players ?? 5,
            },
            observers: {
                match_finished: {
                    enabled:                              s?.observers?.match_finished?.enabled                              ?? true,
                    rebuild_match_player_stats:           s?.observers?.match_finished?.rebuild_match_player_stats           ?? true,
                    aggregate_player_competition_stats:   s?.observers?.match_finished?.aggregate_player_competition_stats   ?? true,
                    apply_match_availability:             s?.observers?.match_finished?.apply_match_availability             ?? true,
                    update_competition_after_match:       s?.observers?.match_finished?.update_competition_after_match       ?? true,
                    settle_match_finance:                 s?.observers?.match_finished?.settle_match_finance                 ?? true,
                }
            }
        }
    });

    const setNested = (path, value) => {
        const keys = path.split('.');
        setData(prev => {
            const next = JSON.parse(JSON.stringify(prev));
            let obj = next;
            for (let i = 0; i < keys.length - 1; i++) obj = obj[keys[i]];
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
    const ob = data.simulation.observers.match_finished;

    return (
        <AdminLayout>
            <Head title="Simulation Settings" />

            <form onSubmit={handleSubmit} className="space-y-8 pb-20">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Simulation Setup</h2>
                        <p className="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mt-1">Engine-Konfiguration</p>
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
                    <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 mb-5 flex items-center gap-2">
                        <Timer size={14} /> Scheduler Konfiguration
                    </h3>
                    <p className="text-xs text-slate-500 mb-6">Steuert die automatische Berechnung von Spielen im Hintergrund.</p>
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[
                            { key: 'interval_minutes', label: 'Intervall (Min)', min: 1, max: 60, step: 1, desc: 'Wie oft der Scheduler läuft' },
                            { key: 'default_limit', label: 'Max Matches / Lauf', min: 0, max: 500, step: 1, desc: '0 = Keine Begrenzung' },
                            { key: 'default_minutes_per_run', label: 'Spielminuten / Lauf', min: 1, max: 90, step: 1, desc: 'Simulierte Spielminuten pro Intervall' },
                            { key: 'max_concurrency', label: 'Max. Worker', min: 1, max: 50, step: 1, desc: 'Parallele Worker-Prozesse' },
                        ].map(f => (
                            <div key={f.key}>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">{f.label}</label>
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

                    <div className="mt-6 pt-5 border-t border-slate-800">
                        <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-3">Automatische Match-Typen</label>
                        <div className="flex flex-wrap gap-3">
                            {[['friendly', 'Freundschaftsspiele'], ['league', 'Ligaspiele'], ['cup', 'Pokalspiele']].map(([t, l]) => (
                                <button
                                    key={t} type="button"
                                    onClick={() => toggleType(t)}
                                    className={`px-4 py-2 rounded-xl text-sm font-bold border transition-all ${
                                        sc.default_types.includes(t)
                                            ? 'bg-cyan-500/20 border-cyan-500/50 text-cyan-300'
                                            : 'bg-slate-800 border-slate-700 text-slate-500 hover:text-white'
                                    }`}
                                >{l}</button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Position Fit */}
                <div className="sim-card p-6">
                    <h3 className="text-xs font-black uppercase tracking-widest text-indigo-400 mb-5 flex items-center gap-2">
                        <ChartBar size={14} /> Position Fit Multipliers
                    </h3>
                    <p className="text-xs text-slate-500 mb-6">Einfluss der Positionstreue auf die Spielstärke (1.0 = 100%).</p>
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

                {/* Live Changes & Lineup */}
                <div className="grid gap-6 lg:grid-cols-2">
                    <div className="sim-card p-6">
                        <h3 className="text-xs font-black uppercase tracking-widest text-emerald-400 mb-5 flex items-center gap-2">
                            <ArrowsLeftRight size={14} /> Live Changes
                        </h3>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Geplante Wechsel (Max/Club)</label>
                                <input type="number" min={1} max={5}
                                    value={lc.max_per_club}
                                    onChange={e => setNested('simulation.live_changes.planned_substitutions.max_per_club', parseInt(e.target.value))}
                                    className="sim-input w-full"
                                />
                            </div>
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Vorlauf (Min)</label>
                                    <input type="number" min={1} max={30}
                                        value={lc.min_minutes_ahead}
                                        onChange={e => setNested('simulation.live_changes.planned_substitutions.min_minutes_ahead', parseInt(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Intervall (Min)</label>
                                    <input type="number" min={1} max={30}
                                        value={lc.min_interval_minutes}
                                        onChange={e => setNested('simulation.live_changes.planned_substitutions.min_interval_minutes', parseInt(e.target.value))}
                                        className="sim-input w-full"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="sim-card p-6">
                        <h3 className="text-xs font-black uppercase tracking-widest text-amber-400 mb-5 flex items-center gap-2">
                            <Users size={14} /> Lineup Limits
                        </h3>
                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Maximale Bankspieler</label>
                            <input type="number" min={1} max={10}
                                value={data.simulation.lineup.max_bench_players}
                                onChange={e => setNested('simulation.lineup.max_bench_players', parseInt(e.target.value))}
                                className="sim-input w-full"
                            />
                            <p className="text-[10px] text-slate-600 mt-2">Größe der Ersatzbank für alle Wettbewerbe (1–10).</p>
                        </div>
                    </div>
                </div>

                {/* Observers */}
                <div className="sim-card p-6">
                    <div className="flex items-center justify-between mb-6">
                        <div>
                            <h3 className="text-xs font-black uppercase tracking-widest text-rose-400 flex items-center gap-2">
                                <Gear size={14} /> Post-Match Pipeline (Observers)
                            </h3>
                            <p className="text-xs text-slate-500 mt-1">Aktionen, die nach jedem Spiel automatisch ausgeführt werden.</p>
                        </div>
                        <button
                            type="button"
                            onClick={() => setNested('simulation.observers.match_finished.enabled', !ob.enabled)}
                            className={`flex items-center gap-2 px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest border transition-all ${
                                ob.enabled
                                    ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-400'
                                    : 'bg-slate-800 border-slate-700 text-slate-500'
                            }`}
                        >
                            {ob.enabled ? <ToggleRight size={16} /> : <ToggleLeft size={16} />}
                            Pipeline {ob.enabled ? 'Aktiv' : 'Inaktiv'}
                        </button>
                    </div>

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
            </form>
        </AdminLayout>
    );
}
