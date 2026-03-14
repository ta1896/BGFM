import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Shield, Users, Trophy, ChartBar, Calendar, Star, CaretRight, Info } from '@phosphor-icons/react';

export default function Show({ auth, club, seasons, activeSeason, overallStats, seasonStats, players, isOwner }) {
    const [activeTab, setActiveTab] = useState('overview');

    const handleSeasonChange = (e) => {
        router.get(route('clubs.show', club.id), { season_id: e.target.value }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between w-full">
                    <div className="flex items-center space-x-4">
                        <div className="w-12 h-12 bg-white/10 rounded-lg p-2.5 flex items-center justify-center">
                            {club.logo_path ? (
                                <img src={`/storage/${club.logo_path.replace('public/', '')}`} alt="" className="max-w-full max-h-full object-contain" />
                            ) : (
                                <Shield size={48} weight="fill" className="text-white/20" />
                            )}
                        </div>
                        <div>
                            <h2 className="font-black text-2xl text-white leading-tight uppercase tracking-tight">{club.name}</h2>
                            <p className="text-[var(--text-muted)] text-xs font-bold uppercase tracking-widest">{club.short_name} • {club.country}</p>
                        </div>
                    </div>
                    {isOwner && (
                        <Link 
                            href={route('clubs.edit', club.id)}
                            className="bg-[var(--bg-content)] hover:bg-slate-700 text-white px-4 py-2 rounded-lg text-xs font-black uppercase tracking-widest transition-all border border-[var(--border-pillar)]"
                        >
                            Einstellungen
                        </Link>
                    )}
                </div>
            }
        >
            <Head title={club.name} />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    {/* Navigation Tabs */}
                    <div className="flex items-center space-x-1 bg-[var(--bg-pillar)] p-1 rounded-xl border border-[var(--border-pillar)] w-fit">
                        {['overview', 'squad', 'stats', 'history'].map(tab => (
                            <button
                                key={tab}
                                onClick={() => setActiveTab(tab)}
                                className={`px-6 py-2.5 rounded-lg text-xs font-black uppercase tracking-widest transition-all ${
                                    activeTab === tab 
                                    ? 'bg-amber-600 text-black shadow-lg shadow-amber-900/40' 
                                    : 'text-[var(--text-muted)] hover:text-white hover:bg-[var(--bg-content)]'
                                }`}
                            >
                                {tab === 'overview' && 'Übersicht'}
                                {tab === 'squad' && 'Kader'}
                                {tab === 'stats' && 'Statistiken'}
                                {tab === 'history' && 'Erfolge'}
                            </button>
                        ))}
                    </div>

                    {activeTab === 'overview' && (
                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-in fade-in duration-500">
                            {/* Stats Summary Column */}
                            <div className="space-y-6">
                                <div className="bg-[var(--bg-pillar)] border border-[var(--border-pillar)] rounded-2xl overflow-hidden shadow-sm">
                                    <div className="p-4 bg-[var(--bg-content)]/50 border-b border-[var(--border-pillar)] flex items-center justify-between">
                                        <h4 className="text-white text-[10px] font-black uppercase tracking-widest flex items-center">
                                            <ChartBar size={14} weight="fill" className="mr-2 text-amber-500" /> Saison-Zusammenfassung
                                        </h4>
                                        <select 
                                            value={activeSeason?.id} 
                                            onChange={handleSeasonChange}
                                            className="bg-[var(--bg-pillar)] border-none text-[10px] text-amber-500 font-black py-0 pl-0 pr-6 focus:ring-0 cursor-pointer uppercase"
                                        >
                                            {seasons.map(s => (
                                                <option key={s.id} value={s.id}>{s.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="p-6 grid grid-cols-2 gap-4">
                                        <StatBox label="Spiele" value={seasonStats?.played || 0} color="amber" />
                                        <StatBox label="Punkte" value={seasonStats?.points || 0} color="green" />
                                        <StatBox label="Tore" value={seasonStats?.goals_for || 0} color="slate" />
                                        <StatBox label="Gegentore" value={seasonStats?.goals_against || 0} color="red" />
                                    </div>
                                </div>

                                <div className="bg-[var(--bg-pillar)] border border-[var(--border-pillar)] rounded-2xl p-6 shadow-sm">
                                    <h4 className="text-white text-[10px] font-black uppercase tracking-widest mb-6 flex items-center">
                                        <Info size={14} weight="bold" className="mr-2 text-[var(--text-muted)]" /> Vereinsinfos
                                    </h4>
                                    <div className="space-y-4">
                                        <InfoRow label="Stadion" value={club.stadium?.name || 'Kein Stadion'} />
                                        <InfoRow label="Kapazität" value={(club.stadium?.capacity || 0).toLocaleString()} />
                                        <InfoRow label="Manager" value={club.user?.name || 'CPU'} />
                                        <InfoRow label="Prestige" value={`${club.reputation} / 99`} />
                                    </div>
                                </div>
                            </div>

                            {/* Top Players Table */}
                            <div className="lg:col-span-2 bg-[var(--bg-pillar)] border border-[var(--border-pillar)] rounded-2xl overflow-hidden shadow-sm">
                                <div className="p-4 bg-[var(--bg-content)]/50 border-b border-[var(--border-pillar)] flex items-center justify-between">
                                    <h4 className="text-white text-[10px] font-black uppercase tracking-widest flex items-center">
                                        <Users size={14} weight="fill" className="mr-2 text-amber-500" /> Schlüsselspieler
                                    </h4>
                                    <button onClick={() => setActiveTab('squad')} className="text-amber-500 text-[10px] font-black uppercase hover:underline">Gesamter Kader</button>
                                </div>
                                <div className="overflow-x-auto">
                                    <table className="w-full text-left border-collapse">
                                        <thead>
                                            <tr className="bg-[var(--bg-content)]/20">
                                                <th className="px-6 py-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">Spieler</th>
                                                <th className="px-6 py-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest text-center">Pos</th>
                                                <th className="px-6 py-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest text-center">Alter</th>
                                                <th className="px-6 py-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest text-center">Stärke</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-slate-800">
                                            {players.slice(0, 10).map(player => (
                                                <tr key={player.id} className="hover:bg-[var(--bg-content)]/30 transition-colors cursor-pointer group" onClick={() => router.get(route('players.show', player.id))}>
                                                    <td className="px-6 py-4">
                                                        <div className="flex items-center space-x-3">
                                                            <div className="w-9 h-9 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)] overflow-hidden">
                                                                <img src={player.photo_url} className="w-full h-full object-cover" alt="" />
                                                            </div>
                                                            <span className="text-white font-bold text-sm group-hover:text-amber-500 transition-colors">{player.first_name} {player.last_name}</span>
                                                        </div>
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="text-[10px] font-black bg-[var(--bg-content)] text-[var(--text-muted)] px-1.5 py-0.5 rounded border border-[var(--border-pillar)]">{player.position}</span>
                                                    </td>
                                                    <td className="px-6 py-4 text-center text-slate-300 text-sm font-bold">{player.age}</td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className="text-amber-500 font-black text-sm italic">{player.overall}</span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Simple implementation for other tabs for now */}
                    {activeTab !== 'overview' && (
                        <div className="py-20 bg-[var(--bg-pillar)] rounded-2xl border border-[var(--border-pillar)] flex flex-col items-center justify-center text-[var(--text-muted)] animate-in fade-in transition-all">
                             <p className="font-bold uppercase tracking-widest text-sm mb-4">Bereich in Vorbereitung</p>
                             <button onClick={() => setActiveTab('overview')} className="text-amber-500 underline text-xs font-bold uppercase">Zurück zur Übersicht</button>
                        </div>
                    )}

                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function StatBox({ label, value, color }) {
    const colorMap = {
        amber: 'text-amber-500',
        green: 'text-green-500',
        red: 'text-red-500',
        slate: 'text-slate-300'
    };
    return (
        <div className="bg-[var(--bg-content)]/30 p-4 rounded-xl border border-[var(--border-muted)]">
            <p className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">{label}</p>
            <p className={`text-2xl font-black italic ${colorMap[color] || 'text-white'}`}>{value}</p>
        </div>
    );
}

function InfoRow({ label, value }) {
    return (
        <div className="flex items-center justify-between py-2 border-b border-[var(--border-muted)] last:border-0">
            <span className="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-tight">{label}</span>
            <span className="text-white text-xs font-black">{value}</span>
        </div>
    );
}
