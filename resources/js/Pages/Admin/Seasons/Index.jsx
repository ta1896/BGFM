import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { 
    Calendar, Plus, PencilSimple, Trash,
    CheckCircle, ClockClockwise
} from '@phosphor-icons/react';

export default function Index({ seasons }) {
    const deleteSeason = (id) => {
        if (confirm('Saison wirklich löschen?')) {
            router.delete(route('admin.seasons.destroy', id));
        }
    };

    return (
        <AdminLayout>
            <Head title="Saisons Verwalten" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Saisons</h2>
                        <p className="text-[var(--text-muted)] text-sm font-bold uppercase tracking-widest mt-1">Verwaltung der Spielzeiträume</p>
                    </div>
                    <Link 
                        href={route('admin.seasons.create')}
                        className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                    >
                        <Plus size={18} weight="bold" />
                        Neue Saison
                    </Link>
                </div>

                <div className="sim-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Saison Name</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Zeitraum</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Aktuelle Saison</th>
                                    <th className="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {seasons.map((season) => (
                                    <tr key={season.id} className="hover:bg-[var(--bg-content)]/20 transition-colors group">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-10 rounded-xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)] flex items-center justify-center text-cyan-400">
                                                    <Calendar size={24} weight="duotone" />
                                                </div>
                                                <span className="font-bold text-white group-hover:text-cyan-400 transition-colors text-lg">{season.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-sm text-slate-300 font-medium">
                                                <ClockClockwise size={16} className="text-[var(--text-muted)]" />
                                                {new Date(season.start_date).toLocaleDateString('de-DE')} - {new Date(season.end_date).toLocaleDateString('de-DE')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            {season.is_current ? (
                                                <span className="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase tracking-widest">
                                                    Aktiv
                                                </span>
                                            ) : (
                                                <span className="text-slate-700 font-bold">-</span>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Link 
                                                    href={route('admin.seasons.edit', season.id)}
                                                    className="sim-btn-muted px-4 py-1.5 inline-flex items-center gap-2 text-xs"
                                                >
                                                    <PencilSimple size={14} />
                                                    Edit
                                                </Link>
                                                <button 
                                                    onClick={() => deleteSeason(season.id)}
                                                    className="p-1.5 text-slate-600 hover:text-rose-500 transition-colors"
                                                >
                                                    <Trash size={18} />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {seasons.length === 0 && (
                    <div className="sim-card p-12 text-center">
                        <Calendar size={48} className="mx-auto text-slate-700 mb-4" />
                        <p className="text-[var(--text-muted)] font-bold italic">Keine Saisons angelegt.</p>
                    </div>
                )}
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
