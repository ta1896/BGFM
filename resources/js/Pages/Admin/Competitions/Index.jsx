import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { 
    Trophy, Globe, Plus, PencilSimple, 
    CheckCircle, XCircle 
} from '@phosphor-icons/react';

export default function Index({ competitions }) {
    return (
        <AdminLayout>
            <Head title="Ligen & Pokale" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Wettbewerbe</h2>
                        <p className="text-[var(--text-muted)] text-sm font-bold uppercase tracking-widest mt-1">Verwaltung der Ligen und Pokale</p>
                    </div>
                    <Link 
                        href={route('admin.competitions.create')}
                        className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                    >
                        <Plus size={18} weight="bold" />
                        Neu erstellen
                    </Link>
                </div>

                <div className="sim-card overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Wettbewerb</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Typ</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Stufe</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Region</th>
                                    <th className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Status</th>
                                    <th className="px-6 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {competitions.data.map((comp) => (
                                    <tr key={comp.id} className="hover:bg-[var(--bg-content)]/20 transition-colors group">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-10 w-10 rounded-xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)] p-1 flex-shrink-0">
                                                    <img src={comp.logo_url} className="h-full w-full object-contain" alt={comp.name} />
                                                </div>
                                                <span className="font-bold text-white group-hover:text-cyan-400 transition-colors">{comp.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className={`text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-widest border ${
                                                comp.type === 'league' ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20' : 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                            }`}>
                                                {comp.type === 'league' ? 'Liga' : 'Pokal'}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-sm font-bold text-slate-300">
                                            {comp.tier || '-'}
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-2 text-sm text-[var(--text-muted)]">
                                                <Globe size={16} />
                                                {comp.country?.name || (comp.scope === 'international' ? 'International' : '-')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            {comp.is_active ? (
                                                <CheckCircle size={20} weight="fill" className="text-emerald-500 mx-auto" />
                                            ) : (
                                                <XCircle size={20} weight="fill" className="text-slate-600 mx-auto" />
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link 
                                                href={route('admin.competitions.edit', comp.id)}
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

                {/* Pagination placeholder */}
                <div className="flex justify-center gap-2 mt-6">
                    {competitions.links.map((link, idx) => (
                        link.url ? (
                            <Link
                                key={idx}
                                href={link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-4 py-2 rounded-xl text-sm font-bold transition-all border ${
                                    link.active 
                                    ? 'bg-cyan-500 border-cyan-400 text-white' 
                                    : 'bg-[var(--bg-content)]/50 border-[var(--border-muted)] text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                                }`}
                            />
                        ) : (
                            <span
                                key={idx}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className="px-4 py-2 rounded-xl text-sm font-bold border bg-[var(--bg-pillar)]/50 border-[var(--border-pillar)]/30 text-slate-600 cursor-default opacity-50"
                            />
                        )
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
