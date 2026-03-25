import React from 'react';
import { Target, SoccerBall } from '@phosphor-icons/react';

/**
 * ShotMap component for visualizing seasonal shots and goals on a pitch.
 */
export default function ShotMap({ shots = [] }) {
    if (!shots.length) {
        return (
            <div className="flex h-[300px] flex-col items-center justify-center rounded-3xl border border-white/[0.05] bg-black/20 text-slate-600">
                <Target size={48} weight="thin" className="mb-4 opacity-20" />
                <p className="text-[10px] font-black uppercase tracking-widest italic opacity-50">Keine Schussdaten fuer diese Saison</p>
            </div>
        );
    }

    return (
        <div className="relative aspect-[4/3] w-full overflow-hidden rounded-3xl border border-emerald-500/20 bg-emerald-950/20 shadow-2xl group">
            {/* Pitch Lines */}
            <svg viewBox="0 0 100 100" className="absolute inset-0 h-full w-full opacity-20">
                <rect x="0" y="0" width="100" height="100" fill="#064e3b" />
                {/* Halfway line (partially visible depending on zoom) */}
                <path d="M 0 0 H 100" stroke="white" strokeWidth="0.5" fill="none" />
                {/* Penalty Area */}
                <rect x="15" y="65" width="70" height="35" stroke="white" strokeWidth="0.5" fill="none" />
                {/* Goal Area */}
                <rect x="35" y="88" width="30" height="12" stroke="white" strokeWidth="0.5" fill="none" />
                {/* Penalty Arc */}
                <path d="M 35 65 A 15 15 0 0 1 65 65" stroke="white" strokeWidth="0.5" fill="none" />
                {/* Penalty Spot */}
                <circle cx="50" cy="80" r="0.5" fill="white" />
            </svg>

            {/* Shots */}
            <div className="absolute inset-0 z-10 p-4">
                {shots.map((shot, i) => {
                    // Normalize coordinates (assuming y is bottom-up in simulation, but we show top-down for the offensive end)
                    // Let's assume input x,y are 0-100 where y=100 is the goal line
                    const size = Math.max(8, (shot.xg || 0.1) * 35);
                    const isGoal = shot.is_goal;

                    return (
                        <div
                            key={i}
                            className={`absolute -translate-x-1/2 -translate-y-1/2 rounded-full cursor-help transition-all hover:scale-150 hover:z-30 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] ${
                                isGoal 
                                    ? 'bg-emerald-400 shadow-[0_0_10px_rgba(52,211,153,0.5)] ring-2 ring-emerald-500/50' 
                                    : 'bg-white/40 border border-white/20'
                            }`}
                            style={{ 
                                left: `${shot.x}%`, 
                                top: `${100 - shot.y}%`, // Invert Y since simulation likely uses 100 as goal line
                                width: `${size}px`, 
                                height: `${size}px` 
                            }}
                            title={`xG: ${shot.xg}${isGoal ? ' (TOR)' : ''}`}
                        >
                            {isGoal && (
                                <div className="absolute inset-0 flex items-center justify-center">
                                    <div className="h-1 w-1 rounded-full bg-white animate-ping" />
                                </div>
                            )}
                        </div>
                    );
                })}
            </div>

            {/* Legend */}
            <div className="absolute bottom-4 left-4 z-20 flex gap-4 rounded-xl bg-black/60 px-3 py-2 backdrop-blur-md border border-white/5">
                <div className="flex items-center gap-2">
                    <div className="h-2 w-2 rounded-full bg-emerald-400" />
                    <span className="text-[8px] font-black uppercase tracking-widest text-white">Tor</span>
                </div>
                <div className="flex items-center gap-2">
                    <div className="h-2 w-2 rounded-full bg-white/40" />
                    <span className="text-[8px] font-black uppercase tracking-widest text-white">Schuss</span>
                </div>
                <div className="ml-2 flex items-center gap-1 border-l border-white/10 pl-3">
                    <span className="text-[8px] font-black uppercase tracking-widest text-slate-400">Grosse = xG</span>
                </div>
            </div>
            
            <div className="absolute top-4 right-4 z-20">
                <div className="rounded-xl bg-emerald-500/10 px-3 py-1 border border-emerald-500/20 backdrop-blur-md">
                    <span className="text-[9px] font-black uppercase tracking-widest text-emerald-400 italic">Saison Shot Map</span>
                </div>
            </div>
        </div>
    );
}
