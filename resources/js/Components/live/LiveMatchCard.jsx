import React from 'react';
import { Link } from '@inertiajs/react';
import { Broadcast } from '@phosphor-icons/react';

export default function LiveMatchCard({ match, href }) {
    return (
        <Link
            href={href}
            className="rounded-2xl border border-emerald-400/15 bg-emerald-400/5 p-4 transition-colors hover:border-emerald-300/30"
        >
            <div className="mb-3 flex items-center justify-between gap-3">
                <div className="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-200">
                    <Broadcast size={12} weight="fill" />
                    Live {match.live_minute}'
                </div>
                <span className="text-xs font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                    {match.home_score}:{match.away_score}
                </span>
            </div>

            <div className="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                <div className="flex min-w-0 items-center gap-3">
                    <img src={match.home_club?.logo_url} alt={match.home_club?.name} className="h-9 w-9 rounded-xl border border-white/10 object-contain p-1" />
                    <div className="truncate text-sm font-black uppercase tracking-[0.06em] text-white">{match.home_club?.name}</div>
                </div>
                <div className="text-xs font-black uppercase tracking-[0.18em] text-emerald-200">VS</div>
                <div className="flex min-w-0 items-center justify-end gap-3">
                    <div className="truncate text-right text-sm font-black uppercase tracking-[0.06em] text-white">{match.away_club?.name}</div>
                    <img src={match.away_club?.logo_url} alt={match.away_club?.name} className="h-9 w-9 rounded-xl border border-white/10 object-contain p-1" />
                </div>
            </div>
        </Link>
    );
}
