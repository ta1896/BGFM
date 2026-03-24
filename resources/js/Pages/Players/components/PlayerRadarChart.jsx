import React from 'react';
import {
    Radar,
    RadarChart,
    PolarGrid,
    PolarAngleAxis,
    PolarRadiusAxis,
    ResponsiveContainer,
    Tooltip,
} from 'recharts';

function RadarTooltip({ active, payload }) {
    if (!active || !payload || !payload.length) {
        return null;
    }

    return (
        <div className="rounded-xl border border-amber-500/30 bg-slate-950/90 px-4 py-2 shadow-[0_0_20px_rgba(0,0,0,0.5)] backdrop-blur-md">
            <div className="flex items-center gap-3">
                <div className="h-6 w-1.5 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(245,158,11,0.5)]" />
                <div className="flex flex-col">
                    <span className="mb-1 text-nowrap text-[10px] font-black uppercase leading-none tracking-[0.2em] text-amber-500/70">
                        {payload[0].payload.fullLabel}
                    </span>
                    <span className="text-xl font-black italic leading-none text-white">
                        {payload[0].value}
                    </span>
                </div>
            </div>
        </div>
    );
}

export default function PlayerRadarChart({ stats }) {
    const data = stats.map((stat) => ({
        subject: stat.label.substring(0, 3).toUpperCase(),
        fullLabel: stat.label,
        value: stat.value,
        fullMark: 99,
    }));

    return (
        <div className="h-[300px] w-full">
            <ResponsiveContainer width="100%" height="100%">
                <RadarChart cx="50%" cy="50%" outerRadius="80%" data={data}>
                    <PolarGrid stroke="rgba(255,255,255,0.05)" />
                    <PolarAngleAxis
                        dataKey="subject"
                        tick={{ fill: 'rgba(255,255,255,0.5)', fontSize: 10, fontWeight: 900 }}
                    />
                    <PolarRadiusAxis
                        angle={30}
                        domain={[0, 99]}
                        tick={false}
                        axisLine={false}
                    />
                    <Tooltip content={<RadarTooltip />} cursor={false} />
                    <Radar
                        name="Player"
                        dataKey="value"
                        stroke="#f59e0b"
                        strokeWidth={3}
                        fill="#f59e0b"
                        fillOpacity={0.35}
                        dot={{ r: 3, fill: '#f59e0b', stroke: '#fff', strokeWidth: 2 }}
                        activeDot={{ r: 5, fill: '#f59e0b', stroke: '#fff', strokeWidth: 2 }}
                        animationDuration={1500}
                    />
                </RadarChart>
            </ResponsiveContainer>
        </div>
    );
}
