import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { 
    FastForward, Target, Sword, Shield, 
    Lightning, Warning, WarningCircle, House, 
    FloppyDisk 
} from '@phosphor-icons/react';

function SettingSlider({ label, desc, name, value, min, max, step = 0.01, onChange, icon: Icon }) {
    return (
        <div className="sim-card p-4">
            <div className="flex items-center justify-between mb-3">
                <div className="flex items-center gap-2">
                    {Icon && <Icon size={16} className="text-cyan-400" />}
                    <label className="text-[10px] font-black uppercase tracking-widest text-slate-400">{label}</label>
                </div>
                <span className="text-xs font-mono text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">{value}</span>
            </div>
            <input
                type="range" min={min} max={max} step={step}
                value={value}
                onChange={e => onChange(name, parseFloat(e.target.value))}
                className="w-full h-1.5 bg-slate-700 rounded-full appearance-none cursor-pointer accent-cyan-500"
            />
            {desc && <p className="text-[10px] text-slate-600 mt-2 leading-tight">{desc}</p>}
        </div>
    );
}

export default function Settings({ settings }) {
    const { data, setData, put, processing, errors } = useForm({
        settings: {
            match_engine_duration:               settings['match_engine.duration'] ?? 90,
            match_engine_chance_probability:     settings['match_engine.chance_probability'] ?? 0.08,
            match_engine_goal_conversion:        settings['match_engine.goal_conversion'] ?? 0.35,
            match_engine_tactic_attack_bonus:    settings['match_engine.tactic_attack_bonus'] ?? 1.25,
            match_engine_tactic_defense_penalty: settings['match_engine.tactic_defense_penalty'] ?? 0.85,
            match_engine_counter_attack_bonus:   settings['match_engine.counter_attack_bonus'] ?? 1.50,
            match_engine_yellow_card_chance:     settings['match_engine.yellow_card_chance'] ?? 0.045,
            match_engine_red_card_chance:        settings['match_engine.red_card_chance'] ?? 0.005,
            match_engine_home_advantage:         settings['match_engine.home_advantage'] ?? 1.05,
        }
    });

    const updateSetting = (name, val) => {
        const key = name.replace('settings.', '');
        setData('settings', { ...data.settings, [key]: val });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.match-engine.update'));
    };

    const s = data.settings;

    return (
        <AdminLayout>
            <Head title="Match Engine Settings" />

            <form onSubmit={handleSubmit} className="space-y-6 pb-20">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Match Engine</h2>
                        <p className="text-slate-500 text-[10px] font-black uppercase tracking-[0.2em] mt-1">Kern-Simulation & Wahrscheinlichkeiten</p>
                    </div>
                    <button
                        type="submit"
                        disabled={processing}
                        className="sim-btn-primary px-8 py-3 flex items-center gap-2"
                    >
                        <FloppyDisk size={18} weight="bold" />
                        Konfiguration Speichern
                    </button>
                </div>

                {errors && Object.keys(errors).length > 0 && (
                    <div className="sim-card p-4 border-red-500/30 bg-red-500/10 text-red-400 text-xs">
                        <p className="font-bold mb-1 uppercase tracking-widest">Validierungsfehler:</p>
                        <ul className="list-disc list-inside">
                            {Object.entries(errors).map(([k, v]) => <li key={k}>{v}</li>)}
                        </ul>
                    </div>
                )}

                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <SettingSlider
                        label="Spieldauer (Min)" desc="Minuten pro Spiel für die Simulation." icon={FastForward}
                        name="settings.match_engine_duration" value={s.match_engine_duration} min={10} max={120} step={1}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Basis Chance Wahrsch." desc="Grundwahrscheinlichkeit für ein Ereignis pro Minute." icon={Lightning}
                        name="settings.match_engine_chance_probability" value={s.match_engine_chance_probability} min={0.01} max={0.50}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Tor-Konvertierung" desc="Wahrscheinlichkeit, dass eine Chance zum Tor führt." icon={Target}
                        name="settings.match_engine_goal_conversion" value={s.match_engine_goal_conversion} min={0.01} max={1.00}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Taktik: Angriffsbonus" desc="Stärkebonus für offensive Taktiken." icon={Sword}
                        name="settings.match_engine_tactic_attack_bonus" value={s.match_engine_tactic_attack_bonus} min={1.0} max={2.0}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Taktik: Abwehrabzug" desc="Schwächung der Defensive bei extremen Offensivtaktiken." icon={Shield}
                        name="settings.match_engine_tactic_defense_penalty" value={s.match_engine_tactic_defense_penalty} min={0.1} max={1.0}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Konter-Bonus" desc="Multiplikator für erfolgreiche Konter-Situationen." icon={Lightning}
                        name="settings.match_engine_counter_attack_bonus" value={s.match_engine_counter_attack_bonus} min={1.0} max={3.0}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Gelbe Karte Chance" desc="Basis-Wahrscheinlichkeit pro Ereignis." icon={Warning}
                        name="settings.match_engine_yellow_card_chance" value={s.match_engine_yellow_card_chance} min={0.001} max={0.2}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Rote Karte Chance" desc="Basis-Wahrscheinlichkeit pro Ereignis." icon={WarningCircle}
                        name="settings.match_engine_red_card_chance" value={s.match_engine_red_card_chance} min={0.0001} max={0.1}
                        onChange={updateSetting}
                    />
                    <SettingSlider
                        label="Heimvorteil Bonus" desc="Stärkemultiplikator für das Heimteam." icon={House}
                        name="settings.match_engine_home_advantage" value={s.match_engine_home_advantage} min={1.0} max={1.5}
                        onChange={updateSetting}
                    />
                </div>
            </form>
        </AdminLayout>
    );
}
