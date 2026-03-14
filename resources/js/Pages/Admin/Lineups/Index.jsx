import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { 
    ListNumbers, Plus, CheckCircle, XCircle, 
    PencilSimple, Eye, Trash, ArrowsClockwise
} from '@phosphor-icons/react';

export default function Index({ lineups }) {
    const handleActivate = (id) => {
        router.post(route('admin.lineups.activate', id));
    };

    const handleDelete = (id) => {
        if (confirm('Aufstellung wirklich löschen?')) {
            router.delete(route('admin.lineups.destroy', id));
        }
    };

    return (
        <AdminLayout>
            <Head title="Aufstellungen" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Aufstellungen</h2>
                        <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">
                            {lineups.total} Aufstellungen im System
                        </p>
                    </div>
                    <Link
                        href={route('admin.lineups.create')}
                        className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                    >
                        <Plus size={18} weight="bold" />
                        Neue Aufstellung
                    </Link>
                </div>

                <div className="sim-card overflow-hidden">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Name</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Verein</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Formation</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Spieler</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Status</th>
                                <th className="px-5 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/50">
                            {lineups.data.map((lineup) => (
                                <tr key={lineup.id} className="hover:bg-[var(--bg-content)]/20 transition-colors group">
                                    <td className="px-5 py-3">
                                        <p className="font-bold text-white group-hover:text-cyan-400 transition-colors">{lineup.name}</p>
                                        {lineup.notes && (
                                            <p className="text-[10px] text-slate-600 mt-0.5 truncate max-w-xs">{lineup.notes}</p>
                                        )}
                                    </td>
                                    <td className="px-5 py-3">
                                        <div className="flex items-center gap-2">
                                            {lineup.club?.logo_url && (
                                                <img src={lineup.club.logo_url} className="h-6 w-6 object-contain" alt="" />
                                            )}
                                            <div>
                                                <p className="text-sm font-bold text-slate-300">{lineup.club?.name ?? '—'}</p>
                                                <p className="text-[10px] text-slate-600">{lineup.club?.user?.name ?? 'CPU'}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-5 py-3">
                                        <span className="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            {lineup.formation}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 text-center font-black text-white">
                                        {lineup.players_count ?? lineup.players?.length ?? '—'}
                                    </td>
                                    <td className="px-5 py-3 text-center">
                                        {lineup.is_active ? (
                                            <span className="flex items-center justify-center gap-1 text-emerald-400">
                                                <CheckCircle size={16} weight="fill" /> <span className="text-[10px] font-black uppercase">Aktiv</span>
                                            </span>
                                        ) : (
                                            <span className="flex items-center justify-center gap-1 text-slate-600">
                                                <XCircle size={16} /> <span className="text-[10px] font-black uppercase">Inaktiv</span>
                                            </span>
                                        )}
                                    </td>
                                    <td className="px-5 py-3 text-right">
                                        <div className="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Link href={route('admin.lineups.show', lineup.id)} className="p-1.5 text-[var(--text-muted)] hover:text-cyan-400 rounded-lg transition">
                                                <Eye size={16} weight="bold" />
                                            </Link>
                                            <Link href={route('admin.lineups.edit', lineup.id)} className="p-1.5 text-[var(--text-muted)] hover:text-indigo-400 rounded-lg transition">
                                                <PencilSimple size={16} weight="bold" />
                                            </Link>
                                            {!lineup.is_active && (
                                                <button onClick={() => handleActivate(lineup.id)} className="p-1.5 text-[var(--text-muted)] hover:text-emerald-400 rounded-lg transition">
                                                    <ArrowsClockwise size={16} weight="bold" />
                                                </button>
                                            )}
                                            <button onClick={() => handleDelete(lineup.id)} className="p-1.5 text-[var(--text-muted)] hover:text-red-400 rounded-lg transition">
                                                <Trash size={16} weight="bold" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {lineups.data.length === 0 && (
                                <tr><td colSpan="6" className="px-5 py-12 text-center text-[var(--text-muted)] italic">Keine Aufstellungen gefunden.</td></tr>
                            )}
                        </tbody>
                    </table>

                    <div className="flex justify-center gap-2 p-4 border-t border-[var(--border-muted)]">
                        {lineups.links?.map((link, idx) => (
                            link.url ? (
                                <Link key={idx} href={link.url} dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`px-4 py-2 rounded-xl text-sm font-bold transition-all border ${link.active ? 'bg-cyan-500 border-cyan-400 text-white' : 'bg-[var(--bg-content)]/50 border-[var(--border-muted)] text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'}`}
                                />
                            ) : (
                                <span key={idx} dangerouslySetInnerHTML={{ __html: link.label }}
                                    className="px-4 py-2 rounded-xl text-sm font-bold border bg-[var(--bg-pillar)]/50 border-[var(--border-pillar)]/30 text-slate-600 cursor-default opacity-50"
                                />
                            )
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
