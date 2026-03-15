import React, { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { Broadcast } from '@phosphor-icons/react';
import { subscribeToLiveOverview } from '@/lib/liveOverviewBus';

export default function LiveMatchesIndicator({ count = 0 }) {
    const [liveCount, setLiveCount] = useState(count);
    const isActive = liveCount > 0;

    useEffect(() => {
        setLiveCount(count);
    }, [count]);

    useEffect(() => {
        return subscribeToLiveOverview((event) => {
            setLiveCount(Number(event.liveMatchesCount || 0));
        });
    }, []);

    return (
        <Link
            href={route('live-ticker.index')}
            className={`group relative flex items-center gap-2 overflow-hidden rounded-xl border px-3 py-2 transition-all ${
                isActive
                    ? 'border-rose-500/30 bg-gradient-to-br from-rose-700/90 via-rose-800/95 to-red-950 text-white shadow-[0_8px_24px_-12px_rgba(244,63,94,0.75)]'
                    : 'border-white/10 bg-white/[0.03] text-[var(--text-muted)] hover:border-white/20 hover:text-white'
            }`}
            title={`${liveCount} Live-Spiele`}
        >
            <div className={`flex h-8 w-8 items-center justify-center rounded-lg ${isActive ? 'bg-black/25' : 'bg-white/[0.04]'}`}>
                <Broadcast size={16} weight="fill" className={isActive ? 'text-amber-200' : ''} />
            </div>

            <div className="min-w-0">
                <div className={`text-[9px] font-black uppercase tracking-[0.18em] ${isActive ? 'text-amber-100/80' : 'text-[var(--text-muted)] group-hover:text-white/70'}`}>
                    Live
                </div>
                <div className="text-xs font-black uppercase tracking-[0.08em]">
                    {liveCount} {liveCount === 1 ? 'Spiel' : 'Spiele'}
                </div>
            </div>

            {isActive && <div className="absolute left-2 top-2 h-2 w-2 rounded-full bg-amber-300 shadow-[0_0_10px_rgba(252,211,77,0.8)]" />}
        </Link>
    );
}
