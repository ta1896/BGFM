import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import {
    Plus,
    MagnifyingGlass,
    IdentificationBadge,
    Heartbeat,
    CurrencyEur,
    ChartLineUp,
    SortAscending,
} from '@phosphor-icons/react';

const PlayerListItem = ({ player }) => (
    <div className="sim-card-soft p-4 bg-[var(--bg-pillar)]/40 border-[var(--border-muted)] hover:border-[var(--accent-primary)]/30 transition-all group relative overflow-hidden hover:-translate-y-0.5">
        <div className="flex items-center gap-4 relative z-10">
            <div className="relative shrink-0">
                {player.photo_url ? (
                    <div className="h-14 w-14 rounded-2xl overflow-hidden border-2 border-[var(--border-pillar)] ring-4 ring-[var(--accent-glow)]">
                        <img loading="lazy" src={player.photo_url} className="w-full h-full object-cover" alt={player.full_name} />
                    </div>
                ) : (
                    <div className="h-14 w-14 rounded-2xl bg-[var(--bg-content)] border-2 border-[var(--border-pillar)] flex items-center justify-center">
                        <span className="text-sm font-black text-[var(--text-muted)] uppercase">
                            {player.first_name[0]}{player.last_name[0]}
                        </span>
                    </div>
                )}
                <div className="absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-[var(--bg-pillar)] border border-[var(--border-pillar)] flex items-center justify-center text-[10px] font-black text-[var(--accent-primary)] shadow-xl">
                    {player.overall}
                </div>
            </div>

            <div className="flex-1 min-w-0">
                <Link href={route('players.show', player.id)} className="block">
                    <h4 className="font-black text-[var(--text-main)] group-hover:text-[var(--accent-primary)] transition-colors uppercase tracking-tight truncate leading-none mb-1">
                        {player.last_name}
                    </h4>
                    <div className="flex items-center gap-2">
                        <span className="text-[10px] font-black text-[var(--accent-primary)] uppercase tracking-widest">{player.display_position}</span>
                        <span className="text-[10px] text-[var(--text-muted)]">•</span>
                        <span className="text-[10px] text-[var(--text-muted)] font-bold">{player.age} JAHRE</span>
                    </div>
                </Link>
            </div>

            <div className="text-right shrink-0">
                <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">Marktwert</div>
                <div className="text-xs font-black text-emerald-400">{player.market_value_formatted}</div>
            </div>
        </div>

        <div className="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/5 opacity-40 group-hover:opacity-100 transition-opacity">
            <Metric label="PAC" value={player.pace} />
            <Metric label="SHO" value={player.shooting} />
            <Metric label="DEF" value={player.defending} />
        </div>

        <div className="absolute -bottom-4 -right-1 text-7xl font-black text-white/[0.02] select-none pointer-events-none italic">
            {player.display_position}
        </div>
    </div>
);

export default function Index({ groupedPlayers, squadStats, clubs, activeClubId }) {
    const { auth } = usePage().props;
    const [searchTerm, setSearchTerm] = useState('');

    const hasResults = Object.values(groupedPlayers).some((group) =>
        group.some((player) => !searchTerm || player.full_name.toLowerCase().includes(searchTerm.toLowerCase())),
    );

    return (
        <AuthenticatedLayout>
            <Head title="Kaderuebersicht" />

            <div className="max-w-[1600px] mx-auto space-y-12">
                <div className="border-b border-white/5 pb-12">
                    <PageHeader
                        eyebrow="Squad Management"
                        title="Kader & Analyse"
                        actions={<div className="flex items-center gap-4">
                        <div className="relative group">
                            <MagnifyingGlass size={20} className="absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)] group-focus-within:text-[var(--accent-primary)] transition-colors" />
                            <input
                                type="text"
                                placeholder="SPIELER SUCHEN..."
                                value={searchTerm}
                                onChange={(event) => setSearchTerm(event.target.value)}
                                className="sim-input pl-12 py-4 text-xs font-black uppercase tracking-widest min-w-[300px]"
                            />
                        </div>

                        <select
                            value={activeClubId || ''}
                            onChange={(event) => router.get(route('players.index'), { club: event.target.value })}
                            className="sim-select py-4 text-xs font-black uppercase tracking-widest min-w-[200px]"
                        >
                            <option value="">ALLE VEREINE</option>
                            {clubs.map((club) => <option key={club.id} value={club.id}>{club.name}</option>)}
                        </select>

                        {auth.user.is_admin && (
                            <Link href={route('admin.players.create')} className="sim-btn-primary p-4 rounded-2xl flex items-center justify-center">
                                <Plus size={24} weight="bold" />
                            </Link>
                        )}
                        </div>}
                    />
                </div>

                <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <AnalyticsCard icon={IdentificationBadge} label="Kadergroesse" value={squadStats.count} subLabel="Profis" accent="amber" />
                    <AnalyticsCard icon={ChartLineUp} label="Ø Staerke / Alter" value={squadStats.avg_rating} subLabel={`/ ${squadStats.avg_age} J`} accent="cyan" />
                    <AnalyticsCard icon={CurrencyEur} label="Kaderwert" value={squadStats.total_value_formatted} subLabel={`Ø ${squadStats.avg_value_formatted}`} accent="emerald" />
                    <div className="sim-card p-6 bg-gradient-to-br from-[#0c1222] to-slate-900/50 border-rose-500/20">
                        <div className="flex items-center gap-4 mb-4">
                            <div className="p-2 rounded-xl bg-rose-500/10 text-rose-400">
                                <Heartbeat size={24} weight="duotone" />
                            </div>
                            <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">Verfuegbarkeit</span>
                        </div>
                        <div className="flex items-center gap-4">
                            <div className="flex flex-col">
                                <span className="text-2xl font-black text-[var(--text-main)] leading-none">{squadStats.injured_count}</span>
                                <span className="text-[8px] font-black text-rose-400 uppercase tracking-widest">Verletzt</span>
                            </div>
                            <div className="w-px h-8 bg-[var(--bg-content)]" />
                            <div className="flex flex-col">
                                <span className="text-2xl font-black text-[var(--text-main)] leading-none">{squadStats.suspended_count}</span>
                                <span className="text-[8px] font-black text-amber-500 uppercase tracking-widest">Gesperrt</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="space-y-16">
                    {Object.entries(groupedPlayers).map(([group, players]) => {
                        const filteredPlayers = players.filter((player) =>
                            !searchTerm || player.full_name.toLowerCase().includes(searchTerm.toLowerCase()),
                        );

                        if (filteredPlayers.length === 0) {
                            return null;
                        }

                        return (
                            <section key={group} className="space-y-6">
                                <div className="flex items-center justify-between pb-4 border-b border-white/5">
                                    <div className="flex items-center gap-4">
                                        <div className="w-2 h-8 bg-[var(--accent-primary)] rounded-full" />
                                        <h2 className="text-2xl font-black text-[var(--text-main)] uppercase italic tracking-tighter">
                                            {group} <span className="text-[var(--text-muted)] ml-2">[{filteredPlayers.length}]</span>
                                        </h2>
                                    </div>
                                    <div className="flex items-center gap-1 opacity-40">
                                        <SortAscending size={16} />
                                        <span className="text-[10px] font-black uppercase tracking-widest">Nach Staerke</span>
                                    </div>
                                </div>

                                <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                                    {filteredPlayers.map((player) => (
                                        <PlayerListItem key={player.id} player={player} />
                                    ))}
                                </div>
                            </section>
                        );
                    })}
                </div>

                {!hasResults && (
                    <div className="py-32 text-center">
                        <MagnifyingGlass size={64} weight="thin" className="mx-auto text-slate-800 mb-6" />
                        <h3 className="text-2xl font-black text-slate-600 uppercase tracking-widest">Keine Spieler gefunden</h3>
                        <p className="text-[var(--text-muted)] mt-2 font-bold uppercase tracking-widest text-xs">Pass deine Suche oder Filter an</p>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}

function Metric({ label, value }) {
    return (
        <div className="flex flex-col items-center">
            <span className="text-[8px] font-black text-[var(--text-muted)] uppercase">{label}</span>
            <span className="text-[10px] font-black text-[var(--text-main)]">{value || '-'}</span>
        </div>
    );
}

function AnalyticsCard({ icon: Icon, label, value, subLabel, accent }) {
    const toneMap = {
        amber: 'bg-amber-500/10 text-amber-500 border-amber-500/20',
        cyan: 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
        emerald: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    };

    return (
        <div className="sim-card p-6 bg-gradient-to-br from-[#0c1222] to-slate-900/50 border-[var(--border-pillar)]">
            <div className="flex items-center gap-4 mb-4">
                <div className={`p-2 rounded-xl border ${toneMap[accent]}`}>
                    <Icon size={24} weight="duotone" />
                </div>
                <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-[0.2em]">{label}</span>
            </div>
            <div className="flex flex-col">
                <span className="text-2xl font-black text-[var(--text-main)] leading-none truncate">{value}</span>
                <span className="text-[10px] font-bold text-[var(--text-muted)] uppercase mt-1">{subLabel}</span>
            </div>
        </div>
    );
}
