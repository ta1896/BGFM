import React, { Suspense, lazy, useState, useEffect, useRef } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear, 
    Scroll,
    ArrowRight,
    Play,
    ChartBar,
    Atom,
    Trophy,
    Brain,
    Cloud,
    Users,
    Heartbeat,
    CheckCircle,
    XCircle,
    CaretRight,
    Warning
} from '@phosphor-icons/react';
import { motion, AnimatePresence } from 'framer-motion';
import axios from 'axios';

const ApexChart = lazy(() => import('react-apexcharts'));

export default function Lab({ clubs }) {
    const [mode, setMode] = useState('single');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState(null);
    const resultsRef = useRef(null);

    const [forms, setForms] = useState({
        single: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '' },
        batch: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '', iterations: 50 },
        ab: { 
            home_club_id: clubs[0]?.id || '', 
            away_club_id: clubs[1]?.id || clubs[0]?.id || '',
            config_a: { aggression: 'normal' },
            config_b: { aggression: 'high' }
        },
        season: {},
        tactics: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '' }
    });

    const handleFormChange = (mode, field, value) => {
        setForms(prev => ({
            ...prev,
            [mode]: {
                ...prev[mode],
                [field]: value
            }
        }));
    };

    const handleNestedChange = (mode, parent, field, value) => {
        setForms(prev => ({
            ...prev,
            [mode]: {
                ...prev[mode],
                [parent]: {
                    ...prev[mode][parent],
                    [field]: value
                }
            }
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setResult(null);

        try {
            const response = await axios.post(route('admin.monitoring.lab.run'), {
                mode,
                ...forms[mode]
            });

            if (response.data.success) {
                setResult({ mode, data: response.data.data });
                // Smooth scroll to results
                setTimeout(() => {
                    resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                alert('Fehler: ' + response.data.message);
            }
        } catch (error) {
            console.error(error);
            alert('Ein Fehler ist aufgetreten: ' + (error.response?.data?.message || error.message));
        } finally {
            setLoading(false);
        }
    };

    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab'), active: true },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler') },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals') },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs') },
    ];

    const modes = [
        { id: 'single', label: 'Single', color: 'emerald' },
        { id: 'batch', label: 'Batch', color: 'indigo' },
        { id: 'ab', label: 'A/B', color: 'pink' },
        { id: 'season', label: 'Season', color: 'amber' },
        { id: 'tactics', label: 'Tactics', color: 'indigo' },
    ];

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-emerald-400">System Monitoring</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Match Lab (Sandbox)</h1>
                        <p className="mt-2 text-sm text-slate-300">Testumgebung für die Match-Engine Logik.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zur Übersicht</Link>
                </div>
            }
        >
            <Head title="Match Lab" />

            <div className="space-y-8">
                {/* Sub Navigation */}
                <div className="flex flex-wrap gap-4 mb-2">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm font-bold border ${
                                item.active 
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 border-emerald-500' 
                                : 'bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700 border-[var(--border-pillar)]'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                <div className="flex flex-col lg:flex-row gap-8 items-start">
                    {/* Config Side */}
                    <aside className="w-full lg:w-80 shrink-0">
                        <div className="sim-card p-6 lg:sticky lg:top-8">
                            <h3 className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-6 pb-2 border-b border-white/5">
                                Konfiguration
                            </h3>

                            {/* Mode Switcher */}
                            <div className="flex p-1 bg-[var(--sim-shell-bg)]/50 rounded-xl mb-6">
                                {modes.map(m => (
                                    <button
                                        key={m.id}
                                        onClick={() => setMode(m.id)}
                                        className={`flex-1 py-2 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all ${
                                            mode === m.id 
                                            ? 'bg-[var(--bg-content)] text-white shadow-lg ring-1 ring-white/10' 
                                            : 'text-[var(--text-muted)] hover:text-slate-300'
                                        }`}
                                    >
                                        {m.label}
                                    </button>
                                ))}
                            </div>

                            <form onSubmit={handleSubmit} className="space-y-6">
                                {mode === 'single' && (
                                    <>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Heimteam</label>
                                            <select 
                                                value={forms.single.home_club_id}
                                                onChange={e => handleFormChange('single', 'home_club_id', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-emerald-500/50 transition"
                                            >
                                                {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Gastteam</label>
                                            <select 
                                                value={forms.single.away_club_id}
                                                onChange={e => handleFormChange('single', 'away_club_id', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-emerald-500/50 transition"
                                            >
                                                {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                            </select>
                                        </div>
                                    </>
                                )}

                                {mode === 'batch' && (
                                    <>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Team A</label>
                                            <select 
                                                value={forms.batch.home_club_id}
                                                onChange={e => handleFormChange('batch', 'home_club_id', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition"
                                            >
                                                {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Team B</label>
                                            <select 
                                                value={forms.batch.away_club_id}
                                                onChange={e => handleFormChange('batch', 'away_club_id', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-xs text-white p-3 focus:ring-indigo-500/50 transition"
                                            >
                                                {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                            </select>
                                        </div>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Iterationen</label>
                                            <input 
                                                type="number"
                                                value={forms.batch.iterations}
                                                onChange={e => handleFormChange('batch', 'iterations', e.target.value)}
                                                min="10" max="250"
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
                                                    onChange={e => handleFormChange('ab', 'home_club_id', e.target.value)}
                                                    className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-pink-500/50 transition truncate"
                                                >
                                                    {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                                </select>
                                                <select 
                                                    value={forms.ab.away_club_id}
                                                    onChange={e => handleFormChange('ab', 'away_club_id', e.target.value)}
                                                    className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-pink-500/50 transition truncate"
                                                >
                                                    {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                                </select>
                                            </div>
                                        </div>
                                        <div className="p-3 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5">
                                            <h4 className="text-[9px] font-black text-white uppercase tracking-wider mb-2">Variante A (Kontrolle)</h4>
                                            <select 
                                                value={forms.ab.config_a.aggression}
                                                onChange={e => handleNestedChange('ab', 'config_a', 'aggression', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)] border-white/10 rounded-lg text-xs text-[var(--text-muted)] p-2"
                                            >
                                                <option value="normal">Aggression: Normal</option>
                                                <option value="high">Aggression: Hoch</option>
                                                <option value="low">Aggression: Niedrig</option>
                                            </select>
                                        </div>
                                        <div className="p-3 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5">
                                            <h4 className="text-[9px] font-black text-pink-400 uppercase tracking-wider mb-2">Variante B (Test)</h4>
                                            <select 
                                                value={forms.ab.config_b.aggression}
                                                onChange={e => handleNestedChange('ab', 'config_b', 'aggression', e.target.value)}
                                                className="w-full bg-[var(--sim-shell-bg)] border-white/10 rounded-lg text-xs text-pink-300 p-2 focus:ring-pink-500"
                                            >
                                                <option value="normal">Aggression: Normal</option>
                                                <option value="high">Aggression: Hoch</option>
                                                <option value="low">Aggression: Niedrig</option>
                                            </select>
                                        </div>
                                    </>
                                )}

                                {mode === 'season' && (
                                    <div className="p-4 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5 text-center">
                                        <div className="text-4xl mb-4">🏆</div>
                                        <h4 className="text-[10px] font-black text-amber-500 uppercase tracking-widest mb-2">Saison-Simulation</h4>
                                        <p className="text-[10px] text-[var(--text-muted)] leading-relaxed">Simuliert eine komplette Saison (Hin- & Rückrunde) für 18 Teams. Dauer: ca. 5-10 Sekunden.</p>
                                    </div>
                                )}

                                {mode === 'tactics' && (
                                    <>
                                        <div>
                                            <label className="block text-[10px] font-black text-[var(--text-muted)] uppercase mb-2 tracking-widest">Test-Teams</label>
                                            <div className="flex gap-2">
                                                <select 
                                                    value={forms.tactics.home_club_id}
                                                    onChange={e => handleFormChange('tactics', 'home_club_id', e.target.value)}
                                                    className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-indigo-500/50 transition truncate"
                                                >
                                                    {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                                </select>
                                                <select 
                                                    value={forms.tactics.away_club_id}
                                                    onChange={e => handleFormChange('tactics', 'away_club_id', e.target.value)}
                                                    className="w-1/2 bg-[var(--sim-shell-bg)]/50 border-white/10 rounded-xl text-[10px] text-white p-2 focus:ring-indigo-500/50 transition truncate"
                                                >
                                                    {clubs.map(club => <option key={club.id} value={club.id}>{club.name}</option>)}
                                                </select>
                                            </div>
                                        </div>
                                        <div className="p-4 bg-[var(--bg-pillar)]/50 rounded-xl border border-white/5 text-center">
                                            <div className="text-4xl mb-4">🧠</div>
                                            <h4 className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2">Taktik-Analyse</h4>
                                            <p className="text-[10px] text-[var(--text-muted)] leading-relaxed">Simuliert alle Formations-Kombinationen. Dauer: ca. 10 Sekunden.</p>
                                        </div>
                                    </>
                                )}

                                <button 
                                    type="submit" 
                                    disabled={loading}
                                    className={`w-full py-4 font-black rounded-xl shadow-lg transition-all text-xs uppercase tracking-widest flex items-center justify-center gap-2 ${
                                        loading 
                                        ? 'bg-slate-700 text-[var(--text-muted)] cursor-not-allowed' 
                                        : `bg-${modes.find(m => m.id === mode).color}-600 text-white hover:bg-${modes.find(m => m.id === mode).color}-500 hover:-translate-y-0.5 shadow-${modes.find(m => m.id === mode).color}-500/20`
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
                                            {mode === 'single' ? 'Simulation starten' : 
                                             mode === 'batch' ? 'Batch Run Starten' :
                                             mode === 'ab' ? 'A/B Vergleich Starten' :
                                             mode === 'season' ? 'Saison Starten' : 'Meta-Report Generieren'}
                                        </>
                                    )}
                                </button>
                            </form>
                        </div>
                    </aside>

                    {/* Results Area */}
                    <main className="flex-1 min-w-0 w-full space-y-8" ref={resultsRef}>
                        {!result && !loading && (
                            <div className="sim-card p-16 text-center border-2 border-dashed border-white/5 bg-[var(--bg-pillar)]/20 rounded-[3rem]">
                                <div className="text-8xl mb-10 opacity-10 filter grayscale transform -rotate-12">🧪</div>
                                <h3 className="text-3xl font-black text-white mb-4 tracking-tight uppercase">Experimentelle Sandbox</h3>
                                <p className="text-[var(--text-muted)] max-w-xl mx-auto leading-relaxed text-sm font-medium">
                                    Hier können Simulationen durchgeführt werden, ohne Daten in die Datenbank zu schreiben.
                                    Ideal zum Testen von Engine-Updates, Taktik-Einflüssen oder neuen Match-Events.
                                </p>
                            </div>
                        )}

                        {loading && (
                            <div className="sim-card p-20 flex flex-col items-center justify-center space-y-6">
                                <div className="relative">
                                    <div className="w-20 h-20 border-4 border-indigo-500/20 rounded-full"></div>
                                    <div className="w-20 h-20 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin absolute top-0"></div>
                                    <Atom size={40} className="absolute inset-0 m-auto text-indigo-400 animate-pulse" />
                                </div>
                                <div className="text-center">
                                    <h3 className="text-xl font-black text-white uppercase tracking-widest">Simulation läuft</h3>
                                    <p className="text-xs text-[var(--text-muted)] mt-2 font-mono">Engine is processing tactical algorithms...</p>
                                </div>
                            </div>
                        )}

                        <AnimatePresence>
                            {result && (
                                <motion.div 
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    exit={{ opacity: 0, y: -20 }}
                                    className="space-y-8"
                                >
                                    {result.mode === 'single' && <SingleResult data={result.data} />}
                                    {result.mode === 'batch' && <BatchResult data={result.data} />}
                                    {result.mode === 'ab' && <ABResult data={result.data} />}
                                    {result.mode === 'season' && <SeasonResult data={result.data} />}
                                    {result.mode === 'tactics' && <TacticsResult data={result.data} />}
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </main>
                </div>
            </div>
        </AdminLayout>
    );
}

function LazyChart(props) {
    return (
        <Suspense fallback={<ChartSkeleton />}>
            <ApexChart {...props} />
        </Suspense>
    );
}

function ChartSkeleton() {
    return (
        <div className="flex h-full w-full items-center justify-center rounded-2xl border border-white/5 bg-[var(--bg-content)]/20 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
            Lade Diagramm...
        </div>
    );
}

function SingleResult({ data }) {
    return (
        <div className="space-y-6">
            {/* Score Card */}
            <div className="sim-card relative overflow-hidden bg-[var(--bg-pillar)] border-white/10 shadow-2xl rounded-[2.5rem]">
                <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-blue-500/10"></div>
                <div className="relative p-8 px-10 flex flex-col sm:flex-row items-center justify-between gap-12">
                    <div className="flex flex-col items-center gap-4 text-center group">
                        <div className="w-24 h-24 bg-[var(--bg-content)] rounded-[3rem] border border-white/10 flex items-center justify-center text-5xl shadow-2xl transition-all group-hover:scale-110 group-hover:rotate-6 duration-500">🏠</div>
                        <div className="space-y-2">
                            <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">Heimteam</div>
                            <h4 className="text-xl font-black text-white uppercase tracking-tighter leading-none">{data.home_club.name}</h4>
                        </div>
                    </div>

                    <div className="flex flex-col items-center">
                        <div className="mb-6 flex flex-col items-center">
                            <div className="text-[10px] font-black text-emerald-400 uppercase tracking-[0.5em] mb-4 drop-shadow-[0_0_10px_rgba(16,185,129,0.5)]">Live Simulation</div>
                            <div className="bg-[var(--sim-shell-bg)] px-10 py-6 rounded-[3rem] border border-white/10 shadow-[0_20px_50px_rgba(0,0,0,0.7)] ring-1 ring-inset ring-white/5">
                                <span className="text-8xl font-black text-white tracking-tighter tabular-nums leading-none drop-shadow-2xl">
                                    {data.home_score}:{data.away_score}
                                </span>
                            </div>
                        </div>
                        <div className="px-6 py-2 bg-emerald-500/10 text-emerald-400 text-xs font-black uppercase tracking-[0.3em] rounded-full border border-emerald-500/20 backdrop-blur-md">
                            Abgeschlossen
                        </div>
                    </div>

                    <div className="flex flex-col items-center gap-4 text-center group">
                        <div className="w-24 h-24 bg-[var(--bg-content)] rounded-[3rem] border border-white/10 flex items-center justify-center text-5xl shadow-2xl transition-all group-hover:scale-110 group-hover:-rotate-6 duration-500">🚌</div>
                        <div className="space-y-2">
                            <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">Gastteam</div>
                            <h4 className="text-xl font-black text-white uppercase tracking-tighter leading-none">{data.away_club.name}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                {/* Event List */}
                <div className="xl:col-span-2 space-y-4">
                    <div className="sim-card p-4 rounded-[2rem]">
                        <div className="p-6 pb-2">
                            <h4 className="text-[11px] font-black border-b border-white/5 pb-5 mb-5 uppercase text-[var(--text-muted)] tracking-[0.3em] flex items-center gap-3">
                                <span className="relative flex h-3 w-3">
                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                                </span>
                                Ereignis-Protokoll
                            </h4>
                        </div>
                        <div className="space-y-3 p-2 max-h-[800px] overflow-y-auto custom-scrollbar pr-4">
                            {data.events.map((event, idx) => (
                                <motion.div 
                                    key={idx}
                                    initial={{ opacity: 0, x: -10 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: idx * 0.03 }}
                                    className={`flex items-start gap-6 p-6 rounded-[2rem] border transition-all duration-300 group ${
                                        (!event.narrative || event.narrative.includes('[') || event.narrative.includes(']')) 
                                        ? 'bg-red-500/5 border-red-500/20 hover:bg-red-500/10' 
                                        : 'bg-[var(--bg-pillar)]/40 border-white/5 hover:border-emerald-500/30 hover:bg-[var(--bg-pillar)]/60'
                                    }`}
                                >
                                    <div className={`w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center text-xs border border-white/10 font-black shadow-lg ring-1 ring-inset ring-white/5 ${
                                         (!event.narrative || event.narrative.includes('[') || event.narrative.includes(']')) ? 'bg-red-500/10 text-red-400' : 'bg-emerald-500/10 text-emerald-400'
                                    }`}>
                                        {event.minute}'
                                    </div>
                                    <div className="flex-1 min-w-0">
                                        <div className="flex items-center justify-between gap-3 mb-2">
                                            <div className="flex items-center gap-3 min-w-0">
                                                <span className="text-xl drop-shadow">{getEventIcon(event.event_type)}</span>
                                                <h5 className="text-[11px] font-black uppercase text-slate-300 truncate tracking-[0.15em]">
                                                    {event.club_name || 'Unbekannt'}
                                                </h5>
                                            </div>
                                            <span className="text-xs font-mono font-black text-[var(--text-muted)] bg-black/40 px-3 py-1 rounded-xl border border-white/5 tabular-nums">{event.score || ''}</span>
                                        </div>
                                        <p className={`text-sm ${
                                            (!event.narrative || event.narrative.includes('[') || event.narrative.includes(']')) ? 'text-red-400/90' : 'text-slate-200'
                                        } font-medium leading-relaxed tracking-tight`}>
                                            {event.narrative || 'Spielereignis ohne Kommentar.'}
                                        </p>
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Sidebar */}
                <div className="space-y-6">
                    <div className="sim-card p-6 overflow-hidden relative group rounded-[2rem]">
                        <div className="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <Cloud size={80} weight="fill" />
                        </div>
                        <h4 className="text-[11px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-[var(--text-muted)] tracking-widest relative">Atmosphäre</h4>
                        <div className="space-y-4 relative">
                            <AtmosphereItem label="Wetter" value={data.weather} icon={<Cloud size={16} />} />
                            <AtmosphereItem label="Zuschauer" value={data.attendance} icon={<Users size={16} />} />
                        </div>
                    </div>

                    <div className="sim-card p-6 relative group overflow-hidden rounded-[2rem]">
                        <div className="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <Brain size={80} weight="fill" />
                        </div>
                        <h4 className="text-[11px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-[var(--text-muted)] tracking-widest relative">Engine Metadata</h4>
                        <div className="text-[11px] text-[var(--text-muted)] font-mono space-y-3 leading-relaxed relative">
                            <MetadataItem label="Performance" value={`${data.duration_ms}ms`} sub={`${data.memory_usage_mb}mb`} />
                            <MetadataItem label="Integrity" value={data.health.is_stable ? 'PERFECT' : 'AUDIT REQUIRED'} color={data.health.is_stable ? 'emerald' : 'amber'} />
                             <div className="mt-6 pt-6 border-t border-white/10">
                                <h5 className="text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-4">Deep Simulation Audit</h5>
                                <div className="space-y-2">
                                    <AuditRow label="Score Validation" status={data.health.audit.score_validated} />
                                    <AuditRow label="Timeline Integrity" status={data.health.audit.timeline_validated} />
                                    <AuditRow label="Squad Consistency" status={data.health.audit.players_validated} />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function BatchResult({ data }) {
    const chartOptions = {
        chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
        series: [{ name: 'Ø Tore', data: [data.stats.avg_home_goals, data.stats.avg_away_goals] }],
        xaxis: {
            categories: [data.home_club.name, data.away_club.name],
            labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter', fontWeight: 700 } },
            axisBorder: { show: false }, axisTicks: { show: false }
        },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'Inter' } } },
        grid: { borderColor: 'rgba(255,255,255,0.05)' },
        colors: ['#10b981', '#3b82f6'],
        plotOptions: { bar: { borderRadius: 8, columnWidth: '40%', distributed: true } },
        dataLabels: { enabled: true, style: { fontSize: '12px', fontFamily: 'Inter', fontWeight: 900 } },
        legend: { show: false },
        theme: { mode: 'dark' },
        tooltip: { theme: 'dark' }
    };

    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6">
            <div className="text-center space-y-3">
                <div className="text-[11px] font-black uppercase tracking-[0.3em] text-indigo-400">Batch Simulation Report</div>
                <h2 className="text-3xl font-black text-white uppercase tracking-tighter">Stress Test Analysis</h2>
                <div className="inline-flex items-center gap-3 px-4 py-1.5 bg-[var(--bg-content)]/80 rounded-full border border-white/10 text-xs font-bold text-[var(--text-muted)] backdrop-blur-md">
                    <Heartbeat size={16} />
                    <span>{data.iterations} Iterationen</span>
                    <span className="opacity-20">|</span>
                    <span>{data.home_club.name} vs {data.away_club.name}</span>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <StatCard label="Heim-Siege" value={`${data.stats.home_win_rate}%`} color="emerald" />
                <StatCard label="Unentschieden" value={`${data.stats.draw_rate}%`} color="slate" />
                <StatCard label="Gast-Siege" value={`${data.stats.away_win_rate}%`} color="blue" />
            </div>

            <div className="sim-card p-8 bg-gradient-to-br from-slate-900 to-slate-950 rounded-[2.5rem] border border-white/5">
                <div className="flex items-center justify-between mb-8">
                    <h4 className="text-[11px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] flex items-center gap-3">
                        <ChartBar size={20} className="text-indigo-400" />
                        Tore pro Spiel (Durchschnitt)
                    </h4>
                </div>
                <div className="h-64">
                    <LazyChart options={chartOptions} series={chartOptions.series} type="bar" height="100%" />
                </div>
            </div>
        </div>
    );
}

function ABResult({ data }) {
    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6 tabular-nums">
            <div className="text-center space-y-3">
                <div className="text-[11px] font-black uppercase tracking-[0.3em] text-pink-500">A/B Engine Comparison</div>
                <h2 className="text-3xl font-black text-white uppercase tracking-tighter">Variant Analysis</h2>
                <div className="inline-flex items-center gap-3 px-4 py-1.5 bg-[var(--bg-content)]/80 rounded-full border border-white/10 text-xs font-bold text-[var(--text-muted)] backdrop-blur-md">
                    <Atom size={16} />
                    <span>250 Simulationen p. Variante</span>
                </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">
                {/* Variant Overviews */}
                <ABVariantCard title="Variante A" sub="Kontrollgruppe" stats={data.variant_a.stats} color="slate" config={data.variant_a.config} />
                <ABVariantCard title="Variante B" sub="Testgruppe" stats={data.variant_b.stats} color="pink" config={data.variant_b.config} />
            </div>

            <div className="sim-card p-8 rounded-[3rem] bg-gradient-to-br from-slate-900 to-black border border-white/5">
                <h4 className="text-[11px] font-black uppercase tracking-[0.3em] text-[var(--text-muted)] mb-8 border-b border-white/5 pb-4">Statistischer Delta (Impact)</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <DeltaBox 
                        label="Ø Tore" 
                        value={`${data.diff.home_goals > 0 ? '+' : ''}${data.diff.home_goals.toFixed(2)}`} 
                        color={data.diff.home_goals > 0 ? 'emerald' : 'red'} 
                    />
                    <DeltaBox 
                        label="Ø Karten" 
                        value={`${data.diff.cards > 0 ? '+' : ''}${data.diff.cards.toFixed(2)}`} 
                        color={data.diff.cards > 0 ? 'amber' : 'emerald'} 
                    />
                     <DeltaBox 
                        label="Win-Rate Shift" 
                        value={`${(data.variant_b.stats.win_rate_home - data.variant_a.stats.win_rate_home) > 0 ? '+' : ''}${(data.variant_b.stats.win_rate_home - data.variant_a.stats.win_rate_home).toFixed(1)}%`} 
                        color={(data.variant_b.stats.win_rate_home - data.variant_a.stats.win_rate_home) > 0 ? 'emerald' : 'red'} 
                    />
                </div>
            </div>
        </div>
    );
}

function SeasonResult({ data }) {
    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6">
            <div className="text-center space-y-3">
                <div className="text-[11px] font-black uppercase tracking-[0.3em] text-amber-500">Season Simulation Report</div>
                <h2 className="text-3xl font-black text-white uppercase tracking-tighter">Virtual League Table</h2>
                <div className="inline-flex items-center gap-3 px-4 py-1.5 bg-[var(--bg-content)]/80 rounded-full border border-white/10 text-xs font-bold text-[var(--text-muted)] backdrop-blur-md">
                    <Trophy size={16} className="text-amber-400" />
                    <span>{data.total_matches} Spiele</span>
                    <span className="opacity-20">|</span>
                    <span>{data.duration}s Berechnungszeit</span>
                </div>
            </div>

            <div className="sim-card overflow-hidden bg-[var(--bg-pillar)]/50 rounded-[3rem] border border-white/5 shadow-2xl">
                <div className="overflow-x-auto custom-scrollbar">
                    <table className="w-full text-left text-xs tabular-nums">
                        <thead className="bg-black/40 text-[10px] uppercase tracking-widest text-[var(--text-muted)] font-bold border-b border-white/10">
                            <tr>
                                <th className="py-6 px-6">#</th>
                                <th className="py-6 px-6">Club</th>
                                <th className="py-6 px-6 text-center">Sp</th>
                                <th className="py-6 px-6 text-center">S</th>
                                <th className="py-6 px-6 text-center">U</th>
                                <th className="py-6 px-6 text-center">N</th>
                                <th className="py-6 px-6 text-center">Tore</th>
                                <th className="py-6 px-6 text-center">Diff</th>
                                <th className="py-6 px-6 text-center">Punkte</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                            {data.standings.map((team, index) => (
                                <tr key={index} className="border-b border-white/5 hover:bg-white/5 transition group">
                                    <td className="py-5 px-6 font-mono text-[var(--text-muted)] font-bold">
                                        {index < 4 ? <span className="text-emerald-400">0{index + 1}</span> : index < 9 ? `0${index + 1}` : index + 1}
                                    </td>
                                    <td className="py-5 px-6 font-black text-white uppercase tracking-tight flex items-center gap-3">
                                        <div className={`w-2 h-2 rounded-full ${index < 4 ? 'bg-emerald-500 animate-pulse' : index >= data.standings.length - 3 ? 'bg-red-500' : 'bg-slate-700'}`}></div>
                                        {team.club}
                                    </td>
                                    <td className="py-5 px-6 text-center text-[var(--text-muted)] font-bold">{team.p}</td>
                                    <td className="py-5 px-6 text-center text-emerald-500/80 font-black">{team.w}</td>
                                    <td className="py-5 px-6 text-center text-[var(--text-muted)] font-bold">{team.d}</td>
                                    <td className="py-5 px-6 text-center text-red-500/80 font-black">{team.l}</td>
                                    <td className="py-5 px-6 text-center text-slate-300 font-mono text-xs">{team.gf}:{team.ga}</td>
                                    <td className={`py-5 px-6 text-center font-black ${team.gd > 0 ? 'text-emerald-500' : (team.gd < 0 ? 'text-red-500' : 'text-[var(--text-muted)]')}`}>
                                        {team.gd > 0 ? '+' : ''}{team.gd}
                                    </td>
                                    <td className="py-5 px-6 text-center font-black text-white text-xl tabular-nums">{team.pts}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}

function TacticsResult({ data }) {
    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6">
            <div className="text-center space-y-3">
                <div className="text-[11px] font-black uppercase tracking-[0.3em] text-indigo-400">Tactics Meta Report</div>
                <h2 className="text-3xl font-black text-white uppercase tracking-tighter">Formation Advantage Matrix</h2>
                <div className="inline-flex items-center gap-3 px-4 py-1.5 bg-[var(--bg-content)]/80 rounded-full border border-white/10 text-xs font-bold text-[var(--text-muted)] backdrop-blur-md">
                    <Brain size={16} />
                    <span>{data.iterations_per_pairing} Iterationen p. Paar</span>
                    <span className="opacity-20">|</span>
                    <span>{data.home_team} vs {data.away_team}</span>
                </div>
            </div>

            <div className="sim-card p-10 overflow-hidden bg-[var(--bg-pillar)]/50 rounded-[3rem] border border-white/5 shadow-2xl">
                 <div className="overflow-x-auto custom-scrollbar">
                    <table className="border-collapse mx-auto tabular-nums">
                        <thead>
                            <tr>
                                <th className="p-4"></th>
                                {data.formations.map(f => (
                                    <th key={f} className="py-4 px-6 text-center text-[10px] uppercase font-black text-[var(--text-muted)] tracking-widest min-w-[100px]">
                                        {f} <span className="block text-[8px] font-bold opacity-30 mt-1">Away</span>
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {data.formations.map(homeF => (
                                <tr key={homeF}>
                                    <th className="py-6 px-6 text-right text-[10px] uppercase font-black text-[var(--text-muted)] border-r border-white/10 tracking-widest min-w-[100px]">
                                        {homeF} <span className="block text-[8px] font-bold opacity-30 mt-1">Home</span>
                                    </th>
                                    {data.formations.map(awayF => {
                                        const res = data.matrix[homeF][awayF];
                                        let bgClass = 'bg-[var(--bg-content)]/20';
                                        let borderClass = 'border-white/5';
                                        let textClass = 'text-[var(--text-muted)]';

                                        if (res.win_rate > 55) { 
                                            bgClass = 'bg-emerald-500/10'; 
                                            borderClass = 'border-emerald-500/20';
                                            textClass = 'text-emerald-400 font-black'; 
                                        }
                                        else if (res.win_rate < 25) { 
                                            bgClass = 'bg-red-500/10'; 
                                            borderClass = 'border-red-500/20';
                                            textClass = 'text-red-400 font-black'; 
                                        }
                                        else if (res.win_rate >= 40 && res.win_rate <= 55) { 
                                            bgClass = 'bg-amber-500/10'; 
                                            borderClass = 'border-amber-500/20';
                                            textClass = 'text-amber-400 font-bold'; 
                                        }

                                        return (
                                            <td key={awayF} className={`p-4 text-center border ${borderClass} ${bgClass} transition-all hover:scale-110 hover:z-10 hover:shadow-2xl cursor-help group`} title={`${homeF} vs ${awayF}`}>
                                                <div className={`${textClass} text-base`}>{res.win_rate}%</div>
                                                <div className="text-[9px] text-[var(--text-muted)] font-bold opacity-50 uppercase group-hover:opacity-100 italic transition-opacity">Ø {res.avg_goals}</div>
                                            </td>
                                        );
                                    })}
                                </tr>
                            ))}
                        </tbody>
                    </table>
                 </div>
            </div>
            
            <div className="flex flex-wrap justify-center gap-8 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] pt-4">
                <LegendItem color="emerald" label="Advantage (>55%)" />
                <LegendItem color="amber" label="Balanced" />
                <LegendItem color="red" label="Disadvantage (<25%)" />
            </div>
        </div>
    );
}

// Helper Components
function AtmosphereItem({ label, value, icon }) {
    return (
        <div className="flex justify-between items-center bg-[var(--sim-shell-bg)]/40 p-4 rounded-2xl border border-white/5 hover:bg-[var(--sim-shell-bg)]/60 transition group">
            <div className="flex items-center gap-3">
                <span className="text-[var(--text-muted)] group-hover:text-emerald-400 transition-colors">{icon}</span>
                <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">{label}</span>
            </div>
            <span className="text-xs font-black text-white capitalize">{value}</span>
        </div>
    );
}

function MetadataItem({ label, value, sub, color = 'white' }) {
    const colors = {
        white: 'text-white',
        emerald: 'text-emerald-400',
        amber: 'text-amber-400',
    };
    return (
        <div className="flex justify-between items-center bg-black/40 p-4 rounded-2xl border border-white/5">
            <span className="text-[var(--text-muted)] uppercase tracking-tighter text-[10px] font-bold">{label}</span>
            <span className={`font-black ${colors[color]}`}>
                {value} {sub && <span className="text-slate-600 text-[10px] lowercase font-normal ml-1">({sub})</span>}
            </span>
        </div>
    );
}

function AuditRow({ label, status }) {
    return (
        <div className="flex items-center justify-between text-[10px]">
            <span className="text-[var(--text-muted)] font-bold">{label}</span>
            <div className="flex items-center gap-2">
                <span className={`font-black tracking-tighter ${status ? 'text-emerald-500' : 'text-red-500'}`}>
                    {status ? 'PASSED' : 'FAILED'}
                </span>
                {status ? <CheckCircle size={14} className="text-emerald-500" /> : <XCircle size={14} className="text-red-500" />}
            </div>
        </div>
    );
}

function StatCard({ label, value, color }) {
    const colors = {
        emerald: 'border-l-emerald-500 text-emerald-400',
        slate: 'border-l-slate-700 text-[var(--text-muted)]',
        blue: 'border-l-blue-500 text-blue-400',
    };
    return (
        <div className={`sim-card p-6 flex flex-col items-center justify-center bg-[var(--bg-pillar)]/50 border-l-4 ${colors[color]} rounded-2xl`}>
            <span className="text-[10px] uppercase tracking-widest text-[var(--text-muted)] mb-2 font-black">{label}</span>
            <span className="text-4xl font-black tabular-nums tracking-tighter">{value}</span>
        </div>
    );
}

function ABVariantCard({ title, sub, stats, color, config }) {
    const colors = {
        slate: 'border-l-slate-600 text-[var(--text-muted)] shadow-slate-900/40',
        pink: 'border-l-pink-500 text-pink-400 shadow-pink-900/20',
    };
    return (
        <div className={`sim-card p-8 border-l-8 ${colors[color]} bg-gradient-to-br from-slate-900/80 to-slate-950 rounded-[3rem]`}>
            <div className="flex justify-between items-start mb-8 pb-4 border-b border-white/5">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.2em] opacity-40 mb-1">{sub}</div>
                    <h4 className="text-2xl font-black text-white">{title}</h4>
                </div>
                <div className="bg-black/60 px-4 py-2 rounded-2xl border border-white/5 text-[10px] font-bold text-[var(--text-muted)]">
                    Aggression: <span className="text-white uppercase px-1">{config.aggression}</span>
                </div>
            </div>
            <div className="grid grid-cols-2 gap-8">
                <div>
                    <span className="text-[10px] text-[var(--text-muted)] block mb-1 uppercase font-black">Win Rate</span>
                    <span className="text-3xl font-black text-white">{stats.win_rate_home}%</span>
                </div>
                <div>
                     <span className="text-[10px] text-[var(--text-muted)] block mb-1 uppercase font-black">Avg Goals</span>
                     <span className="text-3xl font-black text-white">{stats.avg_home_goals.toFixed(2)}</span>
                </div>
            </div>
        </div>
    );
}

function DeltaBox({ label, value, color }) {
    const colors = {
        emerald: 'text-emerald-400 bg-emerald-400/5 border-emerald-400/20',
        red: 'text-red-400 bg-red-400/5 border-red-400/20',
        amber: 'text-amber-400 bg-amber-400/5 border-amber-400/20',
    };
    return (
        <div className={`text-center p-6 rounded-[2rem] border ${colors[color]} relative group overflow-hidden`}>
            <div className={`absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-current`}></div>
            <div className="relative">
                <p className="text-[10px] uppercase tracking-widest font-black opacity-60 mb-2">{label}</p>
                <p className="text-4xl font-black tabular-nums">{value}</p>
            </div>
        </div>
    );
}

function LegendItem({ color, label }) {
    const colors = {
        emerald: 'bg-emerald-500/20 border-emerald-500/50',
        amber: 'bg-amber-500/20 border-amber-500/50',
        red: 'bg-red-500/20 border-red-500/50',
    };
    return (
        <div className="flex items-center gap-3">
            <div className={`w-4 h-4 rounded-lg border ${colors[color]}`}></div>
            <span>{label}</span>
        </div>
    );
}

function getEventIcon(type) {
    switch(type) {
        case 'goal': return '⚽';
        case 'yellow_card': return '🟨';
        case 'red_card': return '🟥';
        case 'substitution': return '🔄';
        case 'injury': return '🚑';
        case 'foul': return '🚨';
        case 'chance': return '🔥';
        case 'corner': return '🚩';
        default: return '⚽';
    }
}
