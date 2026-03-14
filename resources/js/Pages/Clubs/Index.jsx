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
                        <div className="flex items-center space-x-3 text-slate-400">
                            <Shield size={20} weight="fill" />
                            <span className="text-sm font-medium">Alle deine aktiven Engagements</span>
                        </div>
                        <Link
                            href={route('clubs.create')}
                            className="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center space-x-2 transition-all shadow-lg shadow-blue-900/20"
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
                                    className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-blue-500/50 transition-all duration-300 group shadow-sm flex flex-col"
                                >
                                    <div className="p-6 flex-1">
                                        <div className="flex justify-between items-start mb-6">
                                            <div className="w-16 h-16 bg-slate-800 rounded-lg border border-slate-700 flex items-center justify-center overflow-hidden">
                                                {club.logo_path ? (
                                                    <img src={`/storage/${club.logo_path.replace('public/', '')}`} alt="" className="max-w-full max-h-full object-contain" />
                                                ) : (
                                                    <Shield size={32} weight="fill" className="text-slate-700" />
                                                )}
                                            </div>
                                            <div className="bg-blue-600/10 text-blue-500 text-[10px] font-black px-2 py-1 rounded uppercase tracking-wider">
                                                Manager
                                            </div>
                                        </div>

                                        <h3 className="text-white font-bold text-xl mb-1 group-hover:text-blue-400 transition-colors">
                                            {club.name}
                                        </h3>
                                        <p className="text-slate-500 text-xs font-medium uppercase tracking-widest mb-6">
                                            {club.short_name} • {club.country || 'Global'}
                                        </p>

                                        <div className="grid grid-cols-2 gap-4">
                                            <div className="bg-slate-800/40 p-3 rounded-lg border border-slate-700/30">
                                                <div className="flex items-center text-slate-400 mb-1">
                                                    <Users size={12} weight="fill" className="mr-2" />
                                                    <span className="text-[9px] font-bold uppercase tracking-tight">Kader</span>
                                                </div>
                                                <p className="text-white font-black text-lg">{club.players_count || 0}</p>
                                            </div>
                                            <div className="bg-slate-800/40 p-3 rounded-lg border border-slate-700/30">
                                                <div className="flex items-center text-slate-400 mb-1">
                                                    <ClipboardText size={12} weight="fill" className="mr-2" />
                                                    <span className="text-[9px] font-bold uppercase tracking-tight">Taktiken</span>
                                                </div>
                                                <p className="text-white font-black text-lg">{club.lineups_count || 0}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="px-6 py-4 bg-slate-800/30 border-t border-slate-800 flex items-center justify-between text-blue-500 text-xs font-bold uppercase tracking-widest group-hover:bg-blue-600/5 transition-colors">
                                        <span>Zum Vereinszentrum</span>
                                        <CaretRight size={12} weight="bold" />
                                    </div>
                                </Link>
                            ))
                        ) : (
                            <div className="col-span-full py-20 bg-slate-900 rounded-xl border border-slate-800 border-dashed flex flex-col items-center justify-center text-slate-500">
                                <Shield size={48} weight="fill" className="mb-4 opacity-20" />
                                <p className="font-medium">Du leitest aktuell keinen Verein.</p>
                                <Link 
                                    href={route('clubs.free')} 
                                    className="mt-4 text-blue-500 hover:text-blue-400 font-bold underline"
                                >
                                    Jetzt einen freien Verein suchen
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
