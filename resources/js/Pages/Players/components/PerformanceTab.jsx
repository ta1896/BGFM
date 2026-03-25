import React from 'react';
import { 
    Target, SoccerBall, ChartBar, ShieldCheck, 
    ArrowsLeftRight, Lightning, CheckCircle, Info 
} from '@phosphor-icons/react';
import ShotMap from './ShotMap';

export default function PerformanceTab({ player }) {
    const stats = player.seasonal_performance || {};
    const shots = player.shot_map || [];

    if (stats.matches === 0) {
        return (
            <div className="sim-card border border-dashed border-white/5 bg-black/20 p-20 text-center">
                <SoccerBall size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                <p className="text-sm font-bold uppercase tracking-widest text-slate-500 italic">Noch keine Leistungsdaten fuer diese Saison erfasst</p>
            </div>
        );
    }

    return (
        <div className="grid gap-8 xl:grid-cols-3">
            {/* Column 1: Advanced Metrics */}
            <div className="sim-card relative overflow-hidden border-cyan-500/20 bg-gradient-to-br from-[#0c1222] to-[#111827] p-8 shadow-2xl">
                <div className="absolute top-0 right-0 h-32 w-32 bg-cyan-500/5 blur-3xl animate-pulse" />
                
                <div className="relative z-10">
                    <div className="mb-10 flex items-center gap-4">
                        <div className="p-2.5 rounded-xl bg-cyan-400/10 border border-cyan-400/20 shadow-lg">
                            <Target size={24} weight="duotone" className="text-cyan-400" />
                        </div>
                        <div>
                            <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Scoring & Impact</h3>
                            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-500">xG, xGOT & Effizienz</p>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4">
                        <MetricCard label="Expected Goals" value={stats.xg} sub={`${stats.goals} Tore`} color="text-emerald-400" />
                        <MetricCard label="Expected GOT" value={stats.xgot} sub="Shot Precision" color="text-cyan-400" />
                        <MetricCard label="xG per 90" value={stats.xg_per_90} sub="Per Full Game" color="text-amber-400" />
                        <MetricCard label="Rating (AVG)" value={stats.rating} sub="Match Impact" color="text-purple-400" />
                    </div>

                    <div className="mt-8 space-y-4">
                        <ProgressBar label="Pass-Genauigkeit" value={stats.passing?.accuracy || 0} sub={`${stats.passing?.completed}/${stats.passing?.attempted}`} color="cyan" />
                        <ProgressBar label="Zweikampfquote" value={stats.duels?.accuracy || 0} sub={`${stats.duels?.won}/${stats.duels?.total}`} color="emerald" />
                        <ProgressBar label="Dribbling-Erfolg" value={stats.dribbling?.accuracy || 0} sub={`${stats.dribbling?.completed}/${stats.dribbling?.attempted}`} color="amber" />
                    </div>
                </div>
            </div>

            {/* Column 2: Seasonal Shot Map */}
            <div className="sim-card xl:col-span-2 relative overflow-hidden bg-black/40 p-8 shadow-2xl">
                <div className="mb-8 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <div className="p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 shadow-lg">
                            <SoccerBall size={24} weight="duotone" className="text-emerald-400" />
                        </div>
                        <div>
                            <h3 className="text-xl font-black uppercase tracking-tighter text-white italic">Shot Analytics</h3>
                            <p className="text-[10px] font-bold uppercase tracking-widest text-slate-500">Position & Abschluss-Qualität</p>
                        </div>
                    </div>
                    
                    <div className="flex items-center gap-6">
                        <div className="text-right">
                             <p className="text-[9px] font-black uppercase tracking-widest text-slate-500">Goals/Shots</p>
                             <p className="text-lg font-black text-white italic">{stats.goals} / {shots.length}</p>
                        </div>
                        <div className="h-8 w-px bg-white/5" />
                        <div className="text-right">
                             <p className="text-[9px] font-black uppercase tracking-widest text-slate-500">xG Delta</p>
                             <p className={`text-lg font-black italic ${(stats.goals - stats.xg) >= 0 ? 'text-emerald-400' : 'text-rose-400'}`}>
                                {(stats.goals - stats.xg) >= 0 ? '+' : ''}{(stats.goals - stats.xg).toFixed(2)}
                             </p>
                        </div>
                    </div>
                </div>

                <ShotMap shots={shots} />
            </div>

            {/* Column 3: Detailed Categories (Fills second row if needed, but we keep it tight) */}
            <div className="sim-card p-6 bg-black/20 border-l border-indigo-500/20">
                <div className="mb-6 flex items-center gap-3">
                    <Lightning size={18} weight="duotone" className="text-indigo-400" />
                    <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Kreation</h4>
                </div>
                <div className="space-y-4">
                    <CompactRow label="Chancen kreiert" value={stats.creation?.chances || 0} />
                    <CompactRow label="Großchancen" value={stats.creation?.big_chances || 0} />
                    <CompactRow label="Long Balls (%)" value={`${stats.long_balls?.accuracy || 0}%`} />
                </div>
            </div>

            <div className="sim-card p-6 bg-black/20 border-l border-rose-500/20">
                <div className="mb-6 flex items-center gap-3">
                    <ShieldCheck size={18} weight="duotone" className="text-rose-400" />
                    <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Defensive</h4>
                </div>
                <div className="space-y-4">
                    <CompactRow label="Tackles (Won)" value={stats.defending?.tackles_won || 0} />
                    <CompactRow label="Interceptions" value={stats.defending?.interceptions || 0} />
                    <CompactRow label="Recoveries" value={stats.defending?.recoveries || 0} />
                    <CompactRow label="Clearances" value={stats.defending?.clearances || 0} />
                </div>
            </div>

            <div className="sim-card p-6 bg-black/20 border-l border-amber-500/20">
                 <div className="mb-6 flex items-center gap-3">
                    <Info size={18} weight="duotone" className="text-amber-400" />
                    <h4 className="text-sm font-black uppercase tracking-tighter text-white italic">Engagement</h4>
                </div>
                <div className="space-y-4">
                    <CompactRow label="Einsaetze" value={stats.matches} />
                    <CompactRow label="Minuten/Spiel" value="~74'" />
                    <CompactRow label="Luftzweikaempfe (%)" value={`${stats.aerials?.accuracy || 0}%`} />
                </div>
            </div>
        </div>
    );
}

function MetricCard({ label, value, sub, color }) {
    return (
        <div className="rounded-2xl border border-white/[0.03] bg-white/[0.02] p-4 transition-all hover:bg-white/[0.04]">
            <p className="mb-1 text-[8px] font-black uppercase tracking-widest text-slate-500">{label}</p>
            <p className={`text-2xl font-black italic tracking-tighter ${color}`}>{value}</p>
            <p className="text-[9px] font-bold text-slate-600">{sub}</p>
        </div>
    );
}

function ProgressBar({ label, value, sub, color }) {
    const colors = {
        cyan: 'from-cyan-600 to-cyan-400',
        emerald: 'from-emerald-600 to-emerald-400',
        amber: 'from-amber-600 to-amber-400',
    };

    return (
        <div className="space-y-2">
            <div className="flex justify-between px-1">
                <span className="text-[9px] font-black uppercase tracking-widest text-slate-400">{label}</span>
                <span className="text-[10px] font-black text-white italic">{value}% <span className="text-slate-600 text-[8px] ml-1">({sub})</span></span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-black/40 p-0.5 border border-white/[0.05]">
                <div 
                    className={`h-full rounded-full bg-gradient-to-r ${colors[color]} transition-all duration-1000 ease-out`} 
                    style={{ width: `${value}%` }} 
                />
            </div>
        </div>
    );
}

function CompactRow({ label, value }) {
    return (
        <div className="flex items-center justify-between border-b border-white/[0.03] pb-2 last:border-0 grow">
            <span className="text-[9px] font-black uppercase tracking-widest text-slate-500">{label}</span>
            <span className="text-xs font-black text-white italic">{value}</span>
        </div>
    );
}
