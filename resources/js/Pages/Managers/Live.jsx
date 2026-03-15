import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Broadcast, Clock, PlayCircle, UsersThree } from '@phosphor-icons/react';

function LiveMatchCard({ match }) {
    return (
        <Link
            href={route('matches.show', match.id)}
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
                <div className="flex items-center gap-3 min-w-0">
                    <img src={match.home_club?.logo_url} alt={match.home_club?.name} className="h-9 w-9 rounded-xl border border-white/10 object-contain p-1" />
                    <div className="truncate text-sm font-black uppercase tracking-[0.06em] text-white">{match.home_club?.name}</div>
                </div>
                <div className="text-xs font-black uppercase tracking-[0.18em] text-emerald-200">VS</div>
                <div className="flex items-center justify-end gap-3 min-w-0">
                    <div className="truncate text-right text-sm font-black uppercase tracking-[0.06em] text-white">{match.away_club?.name}</div>
                    <img src={match.away_club?.logo_url} alt={match.away_club?.name} className="h-9 w-9 rounded-xl border border-white/10 object-contain p-1" />
                </div>
            </div>
        </Link>
    );
}

function ManagerCard({ manager }) {
    return (
        <div className="rounded-2xl border border-white/10 bg-white/[0.03] p-4">
            <div className="flex items-start justify-between gap-3">
                <div className="flex items-center gap-3 min-w-0">
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
                <div className="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-[0.14em] text-emerald-200">
                    <Clock size={11} weight="fill" />
                    {manager.last_seen_label}
                </div>
            </div>

            <div className="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-cyan-300/15 bg-cyan-300/5 px-3 py-3">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">Aktuelle Aktion</div>
                    <div className="mt-1 text-sm font-bold text-white">{manager.activity_label}</div>
                </div>
            </div>
        </div>
    );
}

export default function Live({ onlineManagers = [], liveMatches = [], onlineWindowMinutes = 5 }) {
    return (
        <AuthenticatedLayout>
            <Head title="Manager Live" />

            <div className="space-y-8">
                <PageHeader
                    eyebrow="Manager Live"
                    title="Wer ist gerade online?"
                />
                <p className="max-w-3xl text-sm leading-relaxed text-[var(--text-muted)]">
                    {`Zeigt aktive Manager der letzten ${onlineWindowMinutes} Minuten und alle laufenden Live-Matches.`}
                </p>

                <div className="grid grid-cols-1 gap-8 xl:grid-cols-12">
                    <SectionCard className="xl:col-span-7">
                        <div className="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <div className="text-xs font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Online Manager</div>
                                <div className="mt-1 text-2xl font-black uppercase tracking-tight text-white">{onlineManagers.length}</div>
                            </div>
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200">
                                <UsersThree size={12} weight="fill" />
                                Live Uebersicht
                            </div>
                        </div>

                        <div className="space-y-3">
                            {onlineManagers.length > 0 ? onlineManagers.map((manager) => (
                                <ManagerCard key={manager.id} manager={manager} />
                            )) : (
                                <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-8 text-center text-sm text-[var(--text-muted)]">
                                    Aktuell ist kein Manager online.
                                </div>
                            )}
                        </div>
                    </SectionCard>

                    <SectionCard className="xl:col-span-5">
                        <div className="mb-5 flex items-center justify-between gap-3">
                            <div>
                                <div className="text-xs font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Live Matches</div>
                                <div className="mt-1 text-2xl font-black uppercase tracking-tight text-white">{liveMatches.length}</div>
                            </div>
                            <div className="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-200">
                                <PlayCircle size={12} weight="fill" />
                                Gerade aktiv
                            </div>
                        </div>

                        <div className="space-y-3">
                            {liveMatches.length > 0 ? liveMatches.map((match) => (
                                <LiveMatchCard key={match.id} match={match} />
                            )) : (
                                <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-8 text-center text-sm text-[var(--text-muted)]">
                                    Derzeit laeuft kein Match live.
                                </div>
                            )}
                        </div>
                    </SectionCard>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
