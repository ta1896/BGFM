import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Shield, Users, ClipboardText, Plus, CaretRight } from '@phosphor-icons/react';

export default function Index({ auth, clubs }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Meine Vereine</h2>}
        >
            <Head title="Meine Vereine" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Header Action */}
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-3 text-[var(--text-muted)]">
                            <Shield size={20} weight="fill" />
                            <span className="text-sm font-medium">Alle deine aktiven Engagements</span>
                        </div>
                        <Link
                            href={route('clubs.create')}
                            className="bg-gradient-to-r from-[#d9b15c] to-[#b69145] text-black px-4 py-2 rounded-lg text-sm font-black flex items-center space-x-2 transition-all shadow-lg shadow-amber-900/40 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <Plus size={12} weight="bold" />
                            <span>Verein gründen</span>
                        </Link>
                    </div>

                    {/* Clubs List */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {clubs.length > 0 ? (
                            clubs.map((club) => (
                                <Link 
                                    key={club.id} 
                                    href={route('clubs.show', club.id)}
                                    className="bg-[var(--bg-pillar)] border border-[var(--border-pillar)] rounded-xl overflow-hidden hover:border-amber-500/50 transition-all duration-300 group shadow-sm flex flex-col"
                                >
                                    <div className="p-6 flex-1">
                                        <div className="flex justify-between items-start mb-6">
                                            <div className="w-16 h-16 bg-[var(--bg-content)] rounded-lg border border-[var(--border-pillar)] flex items-center justify-center overflow-hidden">
                                                {club.logo_path ? (
                                                    <img src={`/storage/${club.logo_path.replace('public/', '')}`} alt="" className="max-w-full max-h-full object-contain" />
                                                ) : (
                                                    <Shield size={32} weight="fill" className="text-slate-700" />
                                                )}
                                            </div>
                                            <div className="bg-amber-600/10 text-amber-500 text-[10px] font-black px-3 py-1.5 rounded-lg border border-amber-500/20 uppercase tracking-[0.1em]">
                                                Manager
                                            </div>
                                        </div>

                                        <h3 className="text-white font-black text-xl mb-1 group-hover:text-amber-500 transition-colors uppercase italic">
                                            {club.name}
                                        </h3>
                                        <p className="text-[var(--text-muted)] text-xs font-medium uppercase tracking-widest mb-6">
                                            {club.short_name} • {club.country || 'Global'}
                                        </p>

                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="bg-[var(--bg-content)]/40 p-3 rounded-lg border border-[var(--border-pillar)]/30">
                                                <div className="flex items-center text-[var(--text-muted)] mb-1">
                                                    <Users size={12} weight="fill" className="mr-2" />
                                                    <span className="text-[9px] font-bold uppercase tracking-tight">Kader</span>
                                                </div>
                                                <p className="text-white font-black text-lg">{club.players_count || 0}</p>
                                            </div>
                                            <div className="bg-[var(--bg-content)]/40 p-3 rounded-lg border border-[var(--border-pillar)]/30">
                                                <div className="flex items-center text-[var(--text-muted)] mb-1">
                                                    <ClipboardText size={12} weight="fill" className="mr-2" />
                                                    <span className="text-[9px] font-bold uppercase tracking-tight">Taktiken</span>
                                                </div>
                                                <p className="text-white font-black text-lg">{club.lineups_count || 0}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="px-6 py-4 bg-[var(--bg-content)]/30 border-t border-[var(--border-pillar)] flex items-center justify-between text-amber-500 text-[10px] font-black uppercase tracking-[0.2em] group-hover:bg-amber-500/5 transition-colors">
                                        <span>Zum Vereinszentrum</span>
                                        <CaretRight size={12} weight="bold" />
                                    </div>
                                </Link>
                            ))
                        ) : (
                            <div className="col-span-full py-20 bg-[var(--bg-pillar)] rounded-xl border border-[var(--border-pillar)] border-dashed flex flex-col items-center justify-center text-[var(--text-muted)]">
                                <Shield size={48} weight="fill" className="mb-4 opacity-20" />
                                <p className="font-medium">Du leitest aktuell keinen Verein.</p>
                                <Link 
                                    href={route('clubs.free')} 
                                    className="mt-4 text-amber-500 hover:text-amber-400 font-black uppercase tracking-widest text-[10px] flex items-center gap-2 group"
                                >
                                    <span>Jetzt einen freien Verein suchen</span>
                                    <CaretRight size={10} className="group-hover:translate-x-1 transition-transform" />
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
