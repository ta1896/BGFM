import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Globe, Users, ArrowRight, Shield } from '@phosphor-icons/react';

export default function Free({ auth, freeClubs, hasOwnedClub }) {
    
    const handleClaim = (clubId) => {
        if (confirm('Möchtest du diesen Verein wirklich übernehmen?')) {
            router.post(route('clubs.claim', clubId));
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Freie Vereine</h2>}
        >
            <Head title="Freie Vereine" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Info Header */}
                    <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
                        <div className="flex items-center space-x-4">
                            <div className="p-3 bg-green-600/20 rounded-lg text-green-500">
                                <Shield size={24} weight="fill" />
                            </div>
                            <div>
                                <h3 className="text-lg font-bold text-white uppercase tracking-tight">Nimm dein Schicksal in die Hand</h3>
                                <p className="text-slate-400 text-sm">Diese Vereine suchen aktuell einen neuen Manager. Jede Legende beginnt irgendwo.</p>
                            </div>
                        </div>
                    </div>

                    {/* Clubs Grid */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        {freeClubs.data.length > 0 ? (
                            freeClubs.data.map((club) => (
                                <div key={club.id} className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-green-500/50 transition-all duration-300 group flex flex-col">
                                    <div className="p-6 flex-1">
                                        <div className="flex justify-between items-start mb-4">
                                            <div className="bg-slate-800 p-3 rounded-lg border border-slate-700 w-16 h-16 flex items-center justify-center">
                                                {club.logo_url ? (
                                                    <img src={club.logo_url} alt={club.name} className="max-w-full max-h-full object-contain" />
                                                ) : (
                                                    <Shield size={32} weight="fill" className="text-slate-600" />
                                                )}
                                            </div>
                                            <div className="inline-flex items-center px-2 py-1 rounded bg-slate-800 border border-slate-700 text-[10px] font-bold text-slate-400 uppercase">
                                                ID: {club.id}
                                            </div>
                                        </div>

                                        <h4 className="text-white font-bold text-lg mb-1 group-hover:text-green-400 transition-colors">
                                            {club.name}
                                        </h4>
                                        <div className="flex items-center text-slate-400 text-xs mb-4">
                                            <Globe className="mr-1.5 text-slate-500" size={12} weight="fill" />
                                            <span>{club.country} • {club.league}</span>
                                        </div>

                                        <div className="grid grid-cols-2 gap-3 mb-6">
                                            <div className="bg-slate-800/40 p-2.5 rounded-lg border border-slate-700/30">
                                                <p className="text-[9px] text-slate-500 uppercase font-black tracking-widest mb-0.5">Prestige</p>
                                                <p className="text-white font-bold text-sm tracking-tight">{club.reputation} / 99</p>
                                            </div>
                                            <div className="bg-slate-800/40 p-2.5 rounded-lg border border-slate-700/30">
                                                <p className="text-[9px] text-slate-500 uppercase font-black tracking-widest mb-0.5">Kader</p>
                                                <div className="flex items-center">
                                                    <Users className="text-slate-500 mr-1.5" size={12} weight="fill" />
                                                    <span className="text-white font-bold text-sm tracking-tight">{club.players_count}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="p-4 bg-slate-800/30 border-t border-slate-800">
                                        {hasOwnedClub ? (
                                            <div className="w-full text-center py-2.5 text-slate-500 text-xs font-medium italic">
                                                Bereits aktiv
                                            </div>
                                        ) : (
                                            <button
                                                onClick={() => handleClaim(club.id)}
                                                className="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-2.5 rounded-lg flex items-center justify-center space-x-2 transition-all duration-300 shadow-lg shadow-green-900/20"
                                            >
                                                <span>Diesen Verein führen</span>
                                                <ArrowRight size={12} weight="bold" />
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-full py-20 bg-slate-900 rounded-xl border border-slate-800 flex flex-col items-center justify-center text-slate-500 italic">
                                Aktuell sind alle Vereine unter Vertrag.
                            </div>
                        )}
                    </div>

                    {/* Pagination Placeholder (if needed) */}
                    {freeClubs.links && freeClubs.links.length > 3 && (
                        <div className="flex justify-center mt-8 space-x-2">
                             {freeClubs.links.map((link, i) => (
                                <button
                                    key={i}
                                    disabled={!link.url || link.active}
                                    onClick={() => router.get(link.url)}
                                    className={`px-4 py-2 rounded-lg text-sm font-bold border ${
                                        link.active 
                                        ? 'bg-green-600 border-green-500 text-white' 
                                        : 'bg-slate-900 border-slate-800 text-slate-400 hover:bg-slate-800'
                                    } disabled:opacity-50 transition-all`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                             ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
