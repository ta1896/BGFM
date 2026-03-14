import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { ListNumbers, CheckCircle, XCircle, ChartBar } from '@phosphor-icons/react';

export default function Show({ lineup, metrics }) {
    return (
        <AdminLayout>
            <Head title={`Aufstellung: ${lineup.name}`} />

            <div className="space-y-6 max-w-4xl">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-widest text-cyan-400 mb-1">{lineup.club?.name}</p>
                        <h2 className="text-2xl font-black text-white uppercase italic">{lineup.name}</h2>
                        <p className="text-slate-500 text-sm mt-1">Formation: <span className="text-indigo-400 font-bold">{lineup.formation}</span></p>
                    </div>
                    <div className="flex gap-3">
                        <Link href={route('admin.lineups.edit', lineup.id)} className="sim-btn-muted px-5 py-2 flex items-center gap-2 text-sm">
                            Bearbeiten
                        </Link>
                        <Link href={route('admin.lineups.index')} className="sim-btn-muted px-5 py-2 text-sm">
                            Zurück
                        </Link>
                    </div>
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {[
                        { label: 'Gesamtstärke', value: metrics?.total_strength?.toFixed(1) ?? '—' },
                        { label: 'Angriff', value: metrics?.attack?.toFixed(1) ?? '—' },
                        { label: 'Mittelfeld', value: metrics?.midfield?.toFixed(1) ?? '—' },
                        { label: 'Abwehr', value: metrics?.defense?.toFixed(1) ?? '—' },
                    ].map(m => (
                        <div key={m.label} className="sim-card p-4 text-center">
                            <p className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">{m.label}</p>
                            <p className="text-2xl font-black text-cyan-400">{m.value}</p>
                        </div>
                    ))}
                </div>

                <div className="sim-card overflow-hidden">
                    <div className="p-4 border-b border-slate-800 flex items-center gap-2">
                        <ListNumbers size={16} className="text-cyan-400" />
                        <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400">Spieler ({lineup.players?.length ?? 0})</h3>
                    </div>
                    <div className="divide-y divide-slate-800/50">
                        {lineup.players?.map(player => (
                            <div key={player.id} className="flex items-center gap-4 px-5 py-3 hover:bg-slate-800/20 transition">
                                <img src={player.photo_url} className="h-9 w-9 rounded-lg object-cover border border-slate-700 bg-slate-900" alt="" />
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-bold text-white truncate">{player.full_name}</p>
                                    <p className="text-[10px] text-slate-500">{player.position} · {player.pivot?.pitch_position || '—'}</p>
                                </div>
                                <span className="text-xl font-black text-white">{player.overall}</span>
                            </div>
                        ))}
                        {!lineup.players?.length && (
                            <p className="px-5 py-8 text-center text-slate-500 italic text-sm">Keine Spieler zugeordnet.</p>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
