import React from 'react';
import { Heartbeat, Play } from '@phosphor-icons/react';

export const LAB_MODES = [
    { id: 'single', label: 'Single', buttonClassName: 'bg-emerald-600 text-white hover:bg-emerald-500 hover:-translate-y-0.5 shadow-emerald-500/20' },
    { id: 'batch', label: 'Batch', buttonClassName: 'bg-indigo-600 text-white hover:bg-indigo-500 hover:-translate-y-0.5 shadow-indigo-500/20' },
    { id: 'ab', label: 'A/B', buttonClassName: 'bg-pink-600 text-white hover:bg-pink-500 hover:-translate-y-0.5 shadow-pink-500/20' },
    { id: 'season', label: 'Season', buttonClassName: 'bg-amber-600 text-white hover:bg-amber-500 hover:-translate-y-0.5 shadow-amber-500/20' },
    { id: 'tactics', label: 'Tactics', buttonClassName: 'bg-indigo-600 text-white hover:bg-indigo-500 hover:-translate-y-0.5 shadow-indigo-500/20' },
];

function renderClubOptions(clubs) {
    return clubs.map((club) => (
        <option key={club.id} value={club.id}>
            {club.name}
        </option>
    ));
}

export default function LabConfigPanel({
    clubs,
    mode,
    setMode,
    forms,
    loading,
    onFormChange,
    onNestedChange,
    onSubmit,
}) {
    const activeMode = LAB_MODES.find((entry) => entry.id === mode) ?? LAB_MODES[0];

    return (
        <aside className="w-full lg:w-80 shrink-0">
            <div className="sim-card p-6 lg:sticky lg:top-8">
                <h3 className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                    Konfiguration
                </h3>

                <div className="flex p-1 bg-[var(--sim-shell-bg)]/50 rounded-xl mb-6">
                    {LAB_MODES.map((entry) => (
                        <button
                            key={entry.id}
                            type="button"
                            onClick={() => setMode(entry.id)}
                            className={`flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all ${
                                mode === entry.id
                                    ? 'bg-[var(--bg-content)] text-white shadow-lg ring-1 ring-white/10'
                                    : 'text-[var(--text-muted)] hover:text-slate-300'
                            }`}
                        >
                            {entry.label}
                        </button>
                    ))}
                </div>

                <form onSubmit={onSubmit} className="space-y-6">
                    {mode === 'single' && (
                        <>
                            <SelectField
                                label="Heimteam"
                                value={forms.single.home_club_id}
                                onChange={(event) => onFormChange('single', 'home_club_id', event.target.value)}
                                className="focus:ring-emerald-500/50"
                            >
                                {renderClubOptions(clubs)}
                            </SelectField>
                            <SelectField
                                label="Gastteam"
                                value={forms.single.away_club_id}
                                onChange={(event) => onFormChange('single', 'away_club_id', event.target.value)}
                                className="focus:ring-emerald-500/50"
                            >
                                {renderClubOptions(clubs)}
                            </SelectField>
                        </>
                    )}

                    {mode === 'batch' && (
                        <>
                            <SelectField
                                label="Team A"
                                value={forms.batch.home_club_id}
                                onChange={(event) => onFormChange('batch', 'home_club_id', event.target.value)}
                                className="focus:ring-indigo-500/50"
                            >
                                {renderClubOptions(clubs)}
                            </SelectField>
                            <SelectField
                                label="Team B"
                                value={forms.batch.away_club_id}
                                onChange={(event) => onFormChange('batch', 'away_club_id', event.target.value)}
                                className="focus:ring-indigo-500/50"
                            >
                                {renderClubOptions(clubs)}
                            </SelectField>
                            <div>
                                <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Iterationen</label>
                                <input
                                    type="number"
                                    value={forms.batch.iterations}
                                    onChange={(event) => onFormChange('batch', 'iterations', event.target.value)}
                                    min="10"
                                    max="250"
                                    className="w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition text-center font-mono"
                                />
                            </div>
                        </>
                    )}

                    {mode === 'ab' && (
                        <>
                            <div>
                                <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Test-Paarung</label>
                                <div className="flex gap-2">
                                    <select
                                        value={forms.ab.home_club_id}
                                        onChange={(event) => onFormChange('ab', 'home_club_id', event.target.value)}
                                        className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-pink-500/50 transition truncate"
                                    >
                                        {renderClubOptions(clubs)}
                                    </select>
                                    <select
                                        value={forms.ab.away_club_id}
                                        onChange={(event) => onFormChange('ab', 'away_club_id', event.target.value)}
                                        className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-pink-500/50 transition truncate"
                                    >
                                        {renderClubOptions(clubs)}
                                    </select>
                                </div>
                            </div>

                            <VariantCard
                                title="Variante A (Kontrolle)"
                                titleClassName="text-white"
                                value={forms.ab.config_a.aggression}
                                onChange={(event) => onNestedChange('ab', 'config_a', 'aggression', event.target.value)}
                                className="text-[var(--text-muted)]"
                            />

                            <VariantCard
                                title="Variante B (Test)"
                                titleClassName="text-pink-400"
                                value={forms.ab.config_b.aggression}
                                onChange={(event) => onNestedChange('ab', 'config_b', 'aggression', event.target.value)}
                                className="text-pink-300 focus:ring-pink-500"
                            />
                        </>
                    )}

                    {mode === 'season' && <InfoCard icon="T" title="Saison-Simulation" text="Simuliert eine komplette Saison (Hin- & Rueckrunde) fuer 18 Teams. Dauer: ca. 5-10 Sekunden." accentClassName="text-amber-500" />}

                    {mode === 'tactics' && (
                        <>
                            <div>
                                <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Test-Teams</label>
                                <div className="flex gap-2">
                                    <select
                                        value={forms.tactics.home_club_id}
                                        onChange={(event) => onFormChange('tactics', 'home_club_id', event.target.value)}
                                        className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-indigo-500/50 transition truncate"
                                    >
                                        {renderClubOptions(clubs)}
                                    </select>
                                    <select
                                        value={forms.tactics.away_club_id}
                                        onChange={(event) => onFormChange('tactics', 'away_club_id', event.target.value)}
                                        className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-indigo-500/50 transition truncate"
                                    >
                                        {renderClubOptions(clubs)}
                                    </select>
                                </div>
                            </div>

                            <InfoCard icon="M" title="Taktik-Analyse" text="Simuliert alle Formations-Kombinationen. Dauer: ca. 10 Sekunden." accentClassName="text-indigo-400" />
                        </>
                    )}

                    <button
                        type="submit"
                        disabled={loading}
                        className={`w-full py-4 font-black rounded-xl shadow-lg transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2 ${
                            loading
                                ? 'bg-slate-700 text-[var(--text-muted)] cursor-not-allowed'
                                : activeMode.buttonClassName
                        }`}
                    >
                        {loading ? (
                            <>
                                <Heartbeat size={18} className="animate-spin" />
                                Simuliere...
                            </>
                        ) : (
                            <>
                                <Play size={16} weight="fill" />
                                {getSubmitLabel(mode)}
                            </>
                        )}
                    </button>
                </form>
            </div>
        </aside>
    );
}

function SelectField({ label, value, onChange, className, children }) {
    return (
        <div>
            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">{label}</label>
            <select
                value={value}
                onChange={onChange}
                className={`w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 transition ${className}`}
            >
                {children}
            </select>
        </div>
    );
}

function VariantCard({ title, titleClassName, value, onChange, className }) {
    return (
        <div className="p-3 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5">
            <h4 className={`text-[9px] font-black uppercase tracking-wider mb-2 ${titleClassName}`}>{title}</h4>
            <select
                value={value}
                onChange={onChange}
                className={`w-full bg-[var(--sim-shell-bg)] border-white/10 rounded-lg text-xs p-2 ${className}`}
            >
                <option value="normal">Aggression: Normal</option>
                <option value="high">Aggression: Hoch</option>
                <option value="low">Aggression: Niedrig</option>
            </select>
        </div>
    );
}

function InfoCard({ icon, title, text, accentClassName }) {
    return (
        <div className="p-4 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5 text-center">
            <div className="text-4xl mb-4">{icon}</div>
            <h4 className={`text-[10px] font-black uppercase tracking-widest mb-2 ${accentClassName}`}>{title}</h4>
            <p className="text-[10px] text-[var(--text-muted)] leading-relaxed">{text}</p>
        </div>
    );
}

function getSubmitLabel(mode) {
    switch (mode) {
        case 'single':
            return 'Simulation starten';
        case 'batch':
            return 'Batch Run starten';
        case 'ab':
            return 'A/B Vergleich starten';
        case 'season':
            return 'Saison starten';
        default:
            return 'Meta-Report generieren';
    }
}
