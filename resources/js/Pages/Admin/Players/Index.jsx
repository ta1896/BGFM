import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { 
    Users, UserPlus, MagnifyingGlass, Funnel, 
    ChartBar, TrendUp, Warning, FirstAid,
    IdentificationBadge, CaretDown, CaretUp, Robot, Lightning
} from '@phosphor-icons/react';
import SyncJournal from '@/Components/SyncJournal';

const StatCard = ({ title, value, subtext, color = 'cyan' }) => (
    <div className={`sim-card p-4 border-l-4 border-l-${color}-500/50`}>
        <p className="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest">{title}</p>
        <p className="text-xl font-black text-white mt-1 leading-none">{value}</p>
        {subtext && <p className="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest mt-1.5">{subtext}</p>}
    </div>
);

export default function Index({ players, groupedPlayers, squadStats, clubs, activeClubId, bulkSyncLogs }) {
    const [isFilterOpen, setIsFilterOpen] = useState(false);

    const handleClubChange = (e) => {
        router.get(route('admin.players.index'), { club: e.target.value });
    };

    return (
        <AdminLayout>
            <Head title="Spielerverwaltung" />

            <div className="space-y-8 pb-20">
                <div className="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Dashboard Spieler</h2>
                        <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">Globale Kader- und Talentverwaltung</p>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <div className="relative">
                            <select 
                                className="sim-select pl-10 pr-8 py-2.5 text-xs font-bold w-64 appearance-none"
                                value={activeClubId || ''}
                                onChange={handleClubChange}
                            >
                                <option value="">Alle Vereine (Global)</option>
                                {clubs.map(c => (
                                    <option key={c.id} value={c.id}>
                                        {c.name} ({c.user?.name || 'CPU'})
                                    </option>
                                ))}
                            </select>
                            <Funnel size={16} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                        </div>

                        <Link 
                            href={route('admin.players.bulk-sync')}
                            method="post"
                            as="button"
                            className="bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20 px-4 py-2 flex items-center gap-2 rounded-xl transition-colors font-black uppercase text-xs tracking-widest border border-cyan-500/20"
                        >
                            <Lightning size={18} weight="duotone" />
                            Bulk Sync
                        </Link>

                        <Link 
                            href={route('admin.players.create')}
                            className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                        >
                            <UserPlus size={18} weight="bold" />
                            Spieler erstellen
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                    <div className="lg:col-span-3 space-y-8">
                        {/* Squad Analysis */}
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <StatCard 
                                title=" Ø Stärke / Alter" 
                                value={`${squadStats.avg_rating} / 99`} 
                                subtext={`Alter: ${squadStats.avg_age} Jahre`}
                                color="cyan"
                            />
                             <StatCard 
                                title="Gesamtmarktwert" 
                                value={`${new Intl.NumberFormat('de-DE').format(squadStats.total_value)} €`} 
                                subtext={`Ø ${new Intl.NumberFormat('de-DE').format(squadStats.avg_value)} €`}
                                color="emerald"
                            />
                            <StatCard 
                                title="Verfügbarkeit" 
                                value={`${squadStats.count} Spieler`} 
                                subtext={`${squadStats.injured_count} Verletzt / ${squadStats.suspended_count} Gesperrt`}
                                color="amber"
                            />
                        </div>

                        {groupedPlayers ? (
                            <div className="space-y-8">
                                {Object.entries(groupedPlayers).map(([group, groupPlayers]) => (
                                    <div key={group} className="space-y-4">
                                        <div className="flex items-center gap-3 px-2">
                                            <div className="h-0.5 flex-1 bg-gradient-to-r from-cyan-500/50 to-transparent"></div>
                                            <h3 className="text-[10px] font-black text-cyan-400 uppercase tracking-[0.3em]">{group} ({groupPlayers.length})</h3>
                                            <div className="h-0.5 flex-1 bg-gradient-to-l from-cyan-500/50 to-transparent"></div>
                                        </div>

                                        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                            {groupPlayers.map(player => (
                                                <Link 
                                                    key={player.id}
                                                    href={route('admin.players.edit', player.id)}
                                                    className="sim-card p-3 relative group hover:border-cyan-500/30 transition-all overflow-hidden"
                                                >
                                                     <div className="absolute -bottom-2 -right-2 text-4xl font-black text-white/[0.03] italic transition-colors group-hover:text-cyan-500/10 active:opacity-0 pointer-events-none">
                                                        {player.position}
                                                    </div>

                                                    <div className="flex items-center gap-3">
                                                        <div className="relative">
                                                            <img src={player.photo_url} className="h-10 w-10 rounded-xl object-cover border border-[var(--border-pillar)] bg-[var(--bg-pillar)]" alt="" />
                                                            <div className="absolute -bottom-1 -right-1 bg-[var(--sim-shell-bg)] px-1 rounded border border-[var(--border-pillar)] text-[8px] font-black text-cyan-400 uppercase">
                                                                {player.position}
                                                                {player.is_imported && (
                                                                    <div className="absolute -top-1 -right-1 bg-cyan-500 text-black px-1 rounded border border-cyan-400 text-[7px] font-black uppercase z-10">
                                                                        <Robot size={8} weight="fill" />
                                                                    </div>
                                                                )}
                                                            </div>
                                                        </div>
                                                        <div className="flex-1 min-w-0">
                                                            <p className="font-bold text-white text-sm truncate group-hover:text-cyan-400 transition-colors uppercase italic">{player.last_name}</p>
                                                            <p className="text-[9px] text-[var(--text-muted)] font-bold uppercase truncate">{player.first_name}</p>
                                                        </div>
                                                        <div className="text-right">
                                                            <p className="text-xl font-black text-white leading-none">{player.overall}</p>
                                                            <p className="text-[8px] text-slate-600 font-black uppercase mt-0.5 tracking-tighter">Rating</p>
                                                        </div>
                                                    </div>
                                                </Link>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="sim-card overflow-hidden">
                                <div className="overflow-x-auto">
                                    <table className="w-full text-left">
                                        <thead>
                                            <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Spieler</th>
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Verein</th>
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Pos</th>
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">OVR</th>
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Alter</th>
                                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-right">Wert</th>
                                                <th className="px-6 py-4"></th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-800/50">
                                            {players.data.map((player) => (
                                                <tr key={player.id} className="hover:bg-[var(--bg-content)]/20 transition-colors group">
                                                    <td className="px-6 py-4">
                                                        <div className="flex items-center gap-3">
                                                            <span className="font-bold text-white group-hover:text-cyan-400 transition-colors">{player.full_name}</span>
                                                            {player.is_imported && (
                                                                <span className="bg-cyan-500/10 text-cyan-500 text-[8px] font-black px-1 rounded border border-cyan-500/20 flex items-center gap-0.5 uppercase ml-2">
                                                                    <Robot size={10} weight="fill" />
                                                                    IMP
                                                                </span>
                                                            )}
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        {player.club ? (
                                                             <div className="flex items-center gap-2">
                                                                <img src={player.club.logo_url} className="h-5 w-5 object-contain" alt="" />
                                                                <span className="text-xs text-[var(--text-muted)] hover:text-white transition-colors cursor-default">{player.club.name}</span>
                                                            </div>
                                                        ) : (
                                                            <span className="text-[10px] font-black text-slate-600 uppercase tracking-widest italic">Vereinslos</span>
                                                        )}
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="bg-[var(--bg-content)] px-1.5 py-0.5 rounded text-[10px] font-black text-cyan-400 border border-[var(--border-pillar)]">{player.position}</span>
                                                    </td>
                                                    <td className="px-6 py-4 text-center font-black text-white">{player.overall}</td>
                                                    <td className="px-6 py-4 text-center text-xs text-[var(--text-muted)]">{player.age}</td>
                                                    <td className="px-6 py-4 text-right font-black text-emerald-400 text-xs tabular-nums">
                                                        {new Intl.NumberFormat('de-DE').format(player.market_value)} €
                                                    </td>
                                                    <td className="px-6 py-4 text-right">
                                                        <Link 
                                                            href={route('admin.players.edit', player.id)}
                                                            className="p-2 text-slate-600 hover:text-cyan-400 transition-colors"
                                                        >
                                                            <IdentificationBadge size={18} weight="bold" />
                                                        </Link>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>

                                {/* Pagination for Global View */}
                                <div className="flex items-center justify-between px-6 py-4 bg-[var(--bg-pillar)]/50 border-t border-[var(--border-pillar)]">
                                     <div className="text-[10px] font-black text-slate-600 uppercase tracking-widest">
                                        Zeige {players.from}-{players.to} von {players.total}
                                    </div>
                                    <div className="flex gap-2">
                                        {players.links.map((link, idx) => (
                                            <PaginationLink
                                                key={idx}
                                                link={link}
                                                className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest transition-all border ${
                                                    link.active 
                                                    ? 'bg-cyan-500 border-cyan-400 text-white' 
                                                    : 'bg-[var(--bg-content)] border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-white'
                                                }`}
                                                disabledClassName="px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border bg-[var(--bg-pillar)] border-[var(--border-pillar)] text-slate-700 cursor-default opacity-50"
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>

                    <div className="space-y-8 sticky top-8">
                        <SyncJournal logs={bulkSyncLogs} />
                        
                        <div className="sim-card p-4 space-y-3">
                            <h4 className="text-[10px] font-black text-white uppercase tracking-widest flex items-center gap-2">
                                <TrendUp size={14} className="text-emerald-400" />
                                Marktwert-Update
                            </h4>
                            <p className="text-[10px] text-[var(--text-muted)] leading-relaxed italic">
                                Der Bulk Sync aktualisiert automatisch die Marktwert-Trends basierend auf den neuesten Sofascore-Daten.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(34,211,238,0.2)];
                }
            `}} />
        </AdminLayout>
    );
}
