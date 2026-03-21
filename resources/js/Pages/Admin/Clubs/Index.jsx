import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { 
    Shield, User, Trophy, Users, 
    PencilSimple, UserPlus, Robot 
} from '@phosphor-icons/react';

export default function Index({ clubs }) {
    return (
        <AdminLayout>
            <Head title="Vereinsverwaltung" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Vereine</h2>
                        <p className="text-[var(--text-muted)] text-sm font-bold uppercase tracking-widest mt-1">Globale Vereinsverwaltung</p>
                    </div>
                    <Link 
                        href={route('admin.clubs.create')}
                        className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                    >
                        <UserPlus size={18} weight="bold" />
                        Verein erstellen
                    </Link>
                </div>

                <div className="sim-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Verein</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Owner</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Liga</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Stats</th>
                                    <th className="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {clubs.data.map((club) => (
                                    <tr key={club.id} className="hover:bg-[var(--bg-content)]/20 transition-colors group">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-10 rounded-xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)] p-1 flex-shrink-0">
                                                    <img src={club.logo_url} className="h-full w-full object-contain" alt={club.name} />
                                                </div>
                                                <div>
                                                    <div className="flex items-center gap-2">
                                                        <span className="font-bold text-white group-hover:text-cyan-400 transition-colors">{club.name}</span>
                                                        {club.is_cpu && (
                                                            <span className="bg-amber-500/10 text-amber-500 text-[9px] font-black px-1.5 py-0.5 rounded border border-amber-500/20 flex items-center gap-1 uppercase tracking-widest">
                                                                <Robot size={10} weight="fill" />
                                                                CPU
                                                            </span>
                                                        )}
                                                        {club.is_imported && (
                                                            <span className="bg-cyan-500/10 text-cyan-500 text-[9px] font-black px-1.5 py-0.5 rounded border border-cyan-500/20 flex items-center gap-1 uppercase tracking-widest">
                                                                <Robot size={10} weight="fill" />
                                                                Imported
                                                            </span>
                                                        )}
                                                    </div>
                                                    <p className="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest">{club.short_name || '-'}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-sm text-slate-300 font-medium">
                                                <User size={14} className="text-[var(--text-muted)]" />
                                                {club.user ? (
                                                    <span>{club.user.name}</span>
                                                ) : (
                                                    <span className="text-slate-600 italic">Kein Owner</span>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-sm text-[var(--text-muted)]">
                                                <Trophy size={14} className="text-slate-600" />
                                                {club.league || '-'}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center justify-center gap-4 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest leading-none">
                                                <div className="text-center">
                                                    <p className="text-slate-600 mb-1">Players</p>
                                                    <p className="text-white text-xs">{club.players_count}</p>
                                                </div>
                                                <div className="text-center border-l border-[var(--border-pillar)] pl-4">
                                                    <p className="text-slate-600 mb-1">Lineups</p>
                                                    <p className="text-white text-xs">{club.lineups_count}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link 
                                                href={route('admin.clubs.edit', club.id)}
                                                className="sim-btn-muted px-4 py-1.5 inline-flex items-center gap-2 text-xs"
                                            >
                                                <PencilSimple size={14} />
                                                Bearbeiten
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Pagination */}
                <div className="flex justify-center gap-2 mt-6">
                    {clubs.links.map((link, idx) => (
                        <PaginationLink
                            key={idx}
                            link={link}
                            className={`px-4 py-2 rounded-xl text-sm font-bold transition-all border ${
                                link.active 
                                ? 'bg-cyan-500 border-cyan-400 text-white' 
                                : 'bg-[var(--bg-content)]/50 border-[var(--border-muted)] text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                            }`}
                            disabledClassName="px-4 py-2 rounded-xl text-sm font-bold border bg-[var(--bg-pillar)]/50 border-[var(--border-pillar)]/30 text-slate-600 cursor-default opacity-50"
                        />
                    ))}
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(34,211,238,0.2)];
                }
                .sim-btn-muted {
                    @apply bg-[var(--bg-content)]/50 border border-[var(--border-muted)] text-slate-300 font-bold rounded-xl hover:bg-[var(--bg-content)] hover:text-white transition-all;
                }
            `}} />
        </AdminLayout>
    );
}
