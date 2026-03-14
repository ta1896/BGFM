import React from 'react';
import {
    Atom,
    Brain,
    ChartBar,
    CheckCircle,
    Cloud,
    Heartbeat,
    Trophy,
    Users,
    XCircle,
} from '@phosphor-icons/react';

export default function LabResultViews({ result }) {
    if (!result) {
        return null;
    }

    if (result.mode === 'single') {
        return <SingleResult data={result.data} />;
    }

    if (result.mode === 'batch') {
        return <BatchResult data={result.data} />;
    }

    if (result.mode === 'ab') {
        return <ABResult data={result.data} />;
    }

    if (result.mode === 'season') {
        return <SeasonResult data={result.data} />;
    }

    if (result.mode === 'tactics') {
        return <TacticsResult data={result.data} />;
    }

    return null;
}

function SingleResult({ data }) {
    return (
        <div className="space-y-6">
            <div className="sim-card relative overflow-hidden bg-[var(--bg-pillar)] border-white/10 shadow-2xl rounded-[2.5rem]">
                <div className="absolute inset-0 bg-gradient-to-br from-emerald-500/10 via-slate-900 to-blue-500/10" />
                <div className="relative p-8 px-10 flex flex-col sm:flex-row items-center justify-between gap-12">
                    <TeamScoreCard sideLabel="Heimteam" teamName={data.home_club.name} badgeText="H" badgeClassName="group-hover:rotate-6" />

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

                    <TeamScoreCard sideLabel="Gastteam" teamName={data.away_club.name} badgeText="A" badgeClassName="group-hover:-rotate-6" />
                </div>
            </div>

            <div className="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
                <div className="xl:col-span-2 space-y-4">
                    <div className="sim-card p-4 rounded-[2rem]">
                        <div className="p-6 pb-2">
                            <h4 className="text-[11px] font-black border-b border-white/5 pb-5 mb-5 uppercase text-[var(--text-muted)] tracking-[0.3em] flex items-center gap-3">
                                <span className="relative flex h-3 w-3">
                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75" />
                                    <span className="relative inline-flex rounded-full h-3 w-3 bg-emerald-500" />
                                </span>
                                Ereignis-Protokoll
                            </h4>
                        </div>
                        <div className="space-y-3 p-2 max-h-[800px] overflow-y-auto custom-scrollbar pr-4">
                            {data.events.map((event, index) => {
                                const isBrokenNarrative = !event.narrative || event.narrative.includes('[') || event.narrative.includes(']');

                                return (
                                    <div
                                        key={`${event.minute}-${event.event_type}-${index}`}
                                        className={`flex items-start gap-6 p-6 rounded-[2rem] border transition-all duration-300 group ${
                                            isBrokenNarrative
                                                ? 'bg-red-500/5 border-red-500/20 hover:bg-red-500/10'
                                                : 'bg-[var(--bg-pillar)]/40 border-white/5 hover:border-emerald-500/30 hover:bg-[var(--bg-pillar)]/60'
                                        }`}
                                    >
                                        <div className={`w-12 h-12 shrink-0 rounded-2xl flex items-center justify-center text-xs border border-white/10 font-black shadow-lg ring-1 ring-inset ring-white/5 ${
                                            isBrokenNarrative ? 'bg-red-500/10 text-red-400' : 'bg-emerald-500/10 text-emerald-400'
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
                                                <span className="text-xs font-mono font-black text-[var(--text-muted)] bg-black/40 px-3 py-1 rounded-xl border border-white/5 tabular-nums">
                                                    {event.score || ''}
                                                </span>
                                            </div>
                                            <p className={`${isBrokenNarrative ? 'text-red-400/90' : 'text-slate-200'} text-sm font-medium leading-relaxed tracking-tight`}>
                                                {event.narrative || 'Spielereignis ohne Kommentar.'}
                                            </p>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                </div>

                <div className="space-y-6">
                    <div className="sim-card p-6 overflow-hidden relative group rounded-[2rem]">
                        <div className="absolute top-0 right-0 p-8 opacity-5 group-hover:opacity-10 transition-opacity">
                            <Cloud size={80} weight="fill" />
                        </div>
                        <h4 className="text-[11px] font-black border-b border-white/5 pb-4 mb-6 uppercase text-[var(--text-muted)] tracking-widest relative">Atmosphaere</h4>
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

function TeamScoreCard({ sideLabel, teamName, badgeText, badgeClassName }) {
    return (
        <div className="flex flex-col items-center gap-4 text-center group">
            <div className={`w-24 h-24 bg-[var(--bg-content)] rounded-[3rem] border border-white/10 flex items-center justify-center text-5xl shadow-2xl transition-all group-hover:scale-110 duration-500 ${badgeClassName}`}>
                {badgeText}
            </div>
            <div className="space-y-2">
                <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">{sideLabel}</div>
                <h4 className="text-xl font-black text-white uppercase tracking-tighter leading-none">{teamName}</h4>
            </div>
        </div>
    );
}

function BatchResult({ data }) {
    const chartData = [
        { label: data.home_club.name, value: data.stats.avg_home_goals, colorClassName: 'from-emerald-500 to-emerald-300' },
        { label: data.away_club.name, value: data.stats.avg_away_goals, colorClassName: 'from-blue-500 to-blue-300' },
    ];

    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6">
            <ResultHeading eyebrow="Batch Simulation Report" title="Stress Test Analysis">
                <Heartbeat size={16} />
                <span>{data.iterations} Iterationen</span>
                <span className="opacity-20">|</span>
                <span>{data.home_club.name} vs {data.away_club.name}</span>
            </ResultHeading>

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
                    <MiniBarChart items={chartData} />
                </div>
            </div>
        </div>
    );
}

function ABResult({ data }) {
    return (
        <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6 tabular-nums">
            <ResultHeading eyebrow="A/B Engine Comparison" title="Variant Analysis">
                <Atom size={16} />
                <span>250 Simulationen p. Variante</span>
            </ResultHeading>

            <div className="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <ABVariantCard title="Variante A" sub="Kontrollgruppe" stats={data.variant_a.stats} color="slate" config={data.variant_a.config} />
                <ABVariantCard title="Variante B" sub="Testgruppe" stats={data.variant_b.stats} color="pink" config={data.variant_b.config} />
            </div>

            <div className="sim-card p-8 rounded-[3rem] bg-gradient-to-br from-slate-900 to-black border border-white/5">
                <h4 className="text-[11px] font-black uppercase tracking-[0.3em] text-[var(--text-muted)] mb-8 border-b border-white/5 pb-4">Statistischer Delta (Impact)</h4>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <DeltaBox label="Avg Tore" value={`${data.diff.home_goals > 0 ? '+' : ''}${data.diff.home_goals.toFixed(2)}`} color={data.diff.home_goals > 0 ? 'emerald' : 'red'} />
                    <DeltaBox label="Avg Karten" value={`${data.diff.cards > 0 ? '+' : ''}${data.diff.cards.toFixed(2)}`} color={data.diff.cards > 0 ? 'amber' : 'emerald'} />
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
            <ResultHeading eyebrow="Season Simulation Report" title="Virtual League Table">
                <Trophy size={16} className="text-amber-400" />
                <span>{data.total_matches} Spiele</span>
                <span className="opacity-20">|</span>
                <span>{data.duration}s Berechnungszeit</span>
            </ResultHeading>

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
                                <tr key={`${team.club}-${index}`} className="border-b border-white/5 hover:bg-white/5 transition group">
                                    <td className="py-5 px-6 font-mono text-[var(--text-muted)] font-bold">
                                        {index < 4 ? <span className="text-emerald-400">0{index + 1}</span> : index < 9 ? `0${index + 1}` : index + 1}
                                    </td>
                                    <td className="py-5 px-6 font-black text-white uppercase tracking-tight flex items-center gap-3">
                                        <div className={`w-2 h-2 rounded-full ${index < 4 ? 'bg-emerald-500 animate-pulse' : index >= data.standings.length - 3 ? 'bg-red-500' : 'bg-slate-700'}`} />
                                        {team.club}
                                    </td>
                                    <td className="py-5 px-6 text-center text-[var(--text-muted)] font-bold">{team.p}</td>
                                    <td className="py-5 px-6 text-center text-emerald-500/80 font-black">{team.w}</td>
                                    <td className="py-5 px-6 text-center text-[var(--text-muted)] font-bold">{team.d}</td>
                                    <td className="py-5 px-6 text-center text-red-500/80 font-black">{team.l}</td>
                                    <td className="py-5 px-6 text-center text-slate-300 font-mono text-xs">{team.gf}:{team.ga}</td>
                                    <td className={`py-5 px-6 text-center font-black ${team.gd > 0 ? 'text-emerald-500' : team.gd < 0 ? 'text-red-500' : 'text-[var(--text-muted)]'}`}>
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
            <ResultHeading eyebrow="Tactics Meta Report" title="Formation Advantage Matrix">
                <Brain size={16} />
                <span>{data.iterations_per_pairing} Iterationen p. Paar</span>
                <span className="opacity-20">|</span>
                <span>{data.home_team} vs {data.away_team}</span>
            </ResultHeading>

            <div className="sim-card p-10 overflow-hidden bg-[var(--bg-pillar)]/50 rounded-[3rem] border border-white/5 shadow-2xl">
                <div className="overflow-x-auto custom-scrollbar">
                    <table className="border-collapse mx-auto tabular-nums">
                        <thead>
                            <tr>
                                <th className="p-4" />
                                {data.formations.map((formation) => (
                                    <th key={formation} className="py-4 px-6 text-center text-[10px] uppercase font-black text-[var(--text-muted)] tracking-widest min-w-[100px]">
                                        {formation}
                                        <span className="block text-[8px] font-bold opacity-30 mt-1">Away</span>
                                    </th>
                                ))}
                            </tr>
                        </thead>
                        <tbody>
                            {data.formations.map((homeFormation) => (
                                <tr key={homeFormation}>
                                    <th className="py-6 px-6 text-right text-[10px] uppercase font-black text-[var(--text-muted)] border-r border-white/10 tracking-widest min-w-[100px]">
                                        {homeFormation}
                                        <span className="block text-[8px] font-bold opacity-30 mt-1">Home</span>
                                    </th>
                                    {data.formations.map((awayFormation) => {
                                        const result = data.matrix[homeFormation][awayFormation];
                                        const styles = getMatrixCellStyles(result.win_rate);

                                        return (
                                            <td
                                                key={awayFormation}
                                                className={`p-4 text-center border ${styles.borderClass} ${styles.bgClass} transition-all hover:scale-110 hover:z-10 hover:shadow-2xl cursor-help group`}
                                                title={`${homeFormation} vs ${awayFormation}`}
                                            >
                                                <div className={`${styles.textClass} text-base`}>{result.win_rate}%</div>
                                                <div className="text-[9px] text-[var(--text-muted)] font-bold opacity-50 uppercase group-hover:opacity-100 italic transition-opacity">
                                                    Avg {result.avg_goals}
                                                </div>
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

function ResultHeading({ eyebrow, title, children }) {
    return (
        <div className="text-center space-y-3">
            <div className="text-[11px] font-black uppercase tracking-[0.3em] text-indigo-400">{eyebrow}</div>
            <h2 className="text-3xl font-black text-white uppercase tracking-tighter">{title}</h2>
            <div className="inline-flex items-center gap-3 px-4 py-1.5 bg-[var(--bg-content)]/80 rounded-full border border-white/10 text-xs font-bold text-[var(--text-muted)] backdrop-blur-md">
                {children}
            </div>
        </div>
    );
}

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
                {value}
                {sub && <span className="text-slate-600 text-[10px] lowercase font-normal ml-1">({sub})</span>}
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
            <div className="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-current" />
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
            <div className={`w-4 h-4 rounded-lg border ${colors[color]}`} />
            <span>{label}</span>
        </div>
    );
}

function MiniBarChart({ items }) {
    const maxValue = Math.max(...items.map((item) => item.value), 1);

    return (
        <div className="flex h-full items-end justify-around gap-6 rounded-2xl border border-white/5 bg-[var(--bg-content)]/10 px-6 py-5">
            {items.map((item) => {
                const height = `${Math.max((item.value / maxValue) * 100, 12)}%`;

                return (
                    <div key={item.label} className="flex h-full flex-1 flex-col items-center justify-end gap-3">
                        <span className="text-sm font-black text-white tabular-nums">{item.value.toFixed(2)}</span>
                        <div className="flex h-full w-full max-w-24 items-end rounded-2xl bg-white/5 p-2">
                            <div
                                className={`w-full rounded-xl bg-gradient-to-t ${item.colorClassName} shadow-lg`}
                                style={{ height }}
                            />
                        </div>
                        <span className="text-center text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">
                            {item.label}
                        </span>
                    </div>
                );
            })}
        </div>
    );
}

function getMatrixCellStyles(winRate) {
    if (winRate > 55) {
        return {
            bgClass: 'bg-emerald-500/10',
            borderClass: 'border-emerald-500/20',
            textClass: 'text-emerald-400 font-black',
        };
    }

    if (winRate < 25) {
        return {
            bgClass: 'bg-red-500/10',
            borderClass: 'border-red-500/20',
            textClass: 'text-red-400 font-black',
        };
    }

    if (winRate >= 40 && winRate <= 55) {
        return {
            bgClass: 'bg-amber-500/10',
            borderClass: 'border-amber-500/20',
            textClass: 'text-amber-400 font-bold',
        };
    }

    return {
        bgClass: 'bg-[var(--bg-content)]/20',
        borderClass: 'border-white/5',
        textClass: 'text-[var(--text-muted)]',
    };
}

function getEventIcon(type) {
    switch (type) {
        case 'goal':
            return 'G';
        case 'yellow_card':
            return 'Y';
        case 'red_card':
            return 'R';
        case 'substitution':
            return 'S';
        case 'injury':
            return 'I';
        case 'foul':
            return 'F';
        case 'chance':
            return 'C';
        case 'corner':
            return 'K';
        default:
            return 'E';
    }
}
