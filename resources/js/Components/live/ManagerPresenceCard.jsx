import React from 'react';
import { Clock, UsersThree } from '@phosphor-icons/react';

export default function ManagerPresenceCard({ manager }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="flex min-w-0 items-center gap-3">
                    {manager.club?.logo_url ? (
                        <img src={manager.club.logo_url} alt={manager.club.name} className="h-10 w-10 rounded-xl border border-white/10 object-contain p-1" />
                    ) : (
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04]">
                            <UsersThree size={18} className="text-[var(--text-muted)]" />
                        </div>
                    )}
                    <div className="min-w-0">
                        <div className="truncate text-sm font-black uppercase tracking-[0.06em] text-white">{manager.manager}</div>
                        <div className="truncate text-[11px] font-bold uppercase tracking-[0.12em] text-[var(--text-muted)]">
                            {manager.club?.name || 'Ohne Verein'}
                        </div>
                    </div>
                </div>
                <div className="inline-flex shrink-0 items-center gap-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-200">
                    <Clock size={11} weight="fill" />
                    {manager.last_seen_label}
                </div>
            </div>

            <div className="mt-4 rounded-2xl border border-cyan-300/15 bg-cyan-300/5 px-3 py-3">
                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">Aktuelle Aktion</div>
                <div className="mt-1 text-sm font-bold text-white">{manager.activity_label}</div>
            </div>
        </div>
    );
}
