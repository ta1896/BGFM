import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Trophy, Calendar, Star, Users, ArrowRight, Funnel } from '@phosphor-icons/react';

export default function Index({ auth, teams, activeTeam, entries, competitionSeasons }) {
    const [selectedTeam, setSelectedTeam] = useState(activeTeam?.id || '');

    const handleTeamChange = (e) => {
        const id = e.target.value;
        setSelectedTeam(id);
        router.get(route('team-of-the-day.index'), { totd: id });
    };

    // Helper to group entries by position (simplified for pitch display)
    const pitchPositions = {
        'GK': entries.filter(e => e.position_code === 'GK'),
        'DEF': entries.filter(e => ['LB', 'CB', 'RB', 'LWB', 'RWB', 'LV', 'IV', 'RV'].includes(e.position_code)),
        'MID': entries.filter(e => ['CDM', 'CM', 'CAM', 'LM', 'RM', 'DM', 'ZM', 'OM'].includes(e.position_code)),
        'FWD': entries.filter(e => ['ST', 'CF', 'LW', 'RW', 'LF', 'HS', 'MS', 'RF'].includes(e.position_code)),
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Team der Woche</h2>}
        >
            <Head title="Team der Woche" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    {/* Header & Filter */}
                    <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-sm">
                        <div className="flex items-center space-x-4">
                            <div className="p-3 bg-yellow-600/20 rounded-lg text-yellow-500">
                                <Trophy size={24} weight="fill" />
                            </div>
                            <div>
                                <h3 className="text-xl font-bold text-white uppercase tracking-tight">Ehrung der Besten</h3>
                                <p className="text-slate-400 text-sm">Die herausragenden Leistungen des Spieltags auf einen Blick.</p>
                            </div>
                        </div>

                        <div className="flex items-center space-x-3">
                            <Funnel className="text-slate-500" size={16} weight="fill" />
                            <select 
                                value={selectedTeam}
                                onChange={handleTeamChange}
                                className="bg-slate-800 border-slate-700 text-white rounded-lg text-sm focus:ring-yellow-500 focus:border-yellow-500 min-w-[240px]"
                            >
                                {teams.map(team => (
                                    <option key={team.id} value={team.id}>
                                        {team.for_date ? new Date(team.for_date).toLocaleDateString('de-DE') : 'Auswahl'} - {team.competition_season?.competition?.name || 'Globale Wertung'}
                                    </option>
                                ))}
                            </select>
                        </div>
                    </div>

                    {/* Main Layout: Pitch and List */}
                    <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
                        
                        {/* Pitch View */}
                        <div className="xl:col-span-2">
                            <div className="bg-green-900/20 border border-slate-800 rounded-2xl overflow-hidden relative aspect-[4/3] max-h-[600px] shadow-2xl">
                                {/* Pitch Background Pattern */}
                                <div className="absolute inset-0 opacity-10 pointer-events-none">
                                    {[...Array(10)].map((_, i) => (
                                        <div key={i} className={`h-1/10 border-b border-white ${i % 2 === 0 ? 'bg-green-800/10' : ''}`}></div>
                                    ))}
                                    <div className="absolute inset-0 border-4 border-white m-4 rounded-sm"></div>
                                    <div className="absolute top-1/2 left-0 right-0 h-0.5 bg-white -translate-y-1/2"></div>
                                    <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 border-4 border-white rounded-full"></div>
                                </div>

                                {/* Players on Pitch */}
                                <div className="absolute inset-0 p-8 flex flex-col justify-between">
                                    {/* Forwards */}
                                    <div className="flex justify-around items-center h-1/4">
                                        {pitchPositions.FWD.map(entry => (
                                            <PitchPlayer key={entry.id} entry={entry} />
                                        ))}
                                    </div>
                                    {/* Midfield */}
                                    <div className="flex justify-around items-center h-1/4">
                                        {pitchPositions.MID.map(entry => (
                                            <PitchPlayer key={entry.id} entry={entry} />
                                        ))}
                                    </div>
                                    {/* Defense */}
                                    <div className="flex justify-around items-center h-1/4">
                                        {pitchPositions.DEF.map(entry => (
                                            <PitchPlayer key={entry.id} entry={entry} />
                                        ))}
                                    </div>
                                    {/* Goalkeeper */}
                                    <div className="flex justify-around items-center h-1/4">
                                        {pitchPositions.GK.map(entry => (
                                            <PitchPlayer key={entry.id} entry={entry} />
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Summary List */}
                        <div className="space-y-6">
                            <div className="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
                                <div className="p-4 bg-slate-800/50 border-b border-slate-700 flex items-center justify-between">
                                    <h4 className="text-white font-bold uppercase text-xs tracking-widest flex items-center">
                                        <Star className="text-yellow-500 mr-2" weight="fill" size={14} /> Details
                                    </h4>
                                    <span className="bg-slate-700 text-slate-300 text-[10px] font-black px-2 py-0.5 rounded uppercase">
                                        {entries.length} Spieler
                                    </span>
                                </div>
                                <div className="divide-y divide-slate-800">
                                    {entries.map(entry => (
                                        <div 
                                            key={entry.id} 
                                            className="p-4 flex items-center justify-between hover:bg-slate-800/30 transition-colors cursor-pointer group"
                                            onClick={() => router.get(route('players.show', entry.player.id))}
                                        >
                                            <div className="flex items-center space-x-3">
                                                <div className="w-10 h-10 rounded-lg bg-slate-800 overflow-hidden border border-slate-700">
                                                    <img 
                                                        src={entry.player?.photo_url} 
                                                        className="w-full h-full object-cover"
                                                        alt="" 
                                                    />
                                                </div>
                                                <div>
                                                    <p className="text-white font-medium text-sm group-hover:text-yellow-500 transition-colors">
                                                        {entry.player?.last_name}
                                                    </p>
                                                    <p className="text-slate-500 text-[10px] uppercase font-bold tracking-tighter">
                                                        {entry.player?.position} • {entry.player?.club?.name}
                                                    </p>
                                                </div>
                                            </div>
                                            <div className="text-right">
                                                <div className="text-yellow-500 text-sm font-black italic">
                                                    {entry.player?.overall}
                                                </div>
                                                <div className="text-slate-500 text-[10px] uppercase font-bold">
                                                    OVR
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Info Card */}
                            <div className="bg-yellow-600/5 border border-yellow-600/20 rounded-xl p-6">
                                <h5 className="text-yellow-500 font-bold mb-2 flex items-center text-sm">
                                    <Calendar className="mr-2" weight="fill" size={16} /> Wie wird gewählt?
                                </h5>
                                <p className="text-slate-400 text-xs leading-relaxed">
                                    Das "Team der Woche" wird basierend auf den Match-Ratings, Toren, Assists und Clean Sheets des aktuellen Spieltags automatisch berechnet.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function PitchPlayer({ entry }) {
    if (!entry.player) return null;
    return (
        <div className="flex flex-col items-center group cursor-pointer" onClick={() => router.get(route('players.show', entry.player.id))}>
            <div className="relative mb-2">
                <div className="w-16 h-16 md:w-20 md:h-20 rounded-full border-2 border-yellow-500 overflow-hidden bg-slate-800 shadow-xl group-hover:scale-110 transition-transform duration-300">
                    <img src={entry.player.photo_url} className="w-full h-full object-cover" alt="" />
                </div>
                <div className="absolute -bottom-1 -right-1 bg-yellow-500 text-slate-900 text-xs font-black px-1.5 py-0.5 rounded border border-slate-900 shadow-lg">
                    {entry.player.overall}
                </div>
            </div>
            <div className="bg-slate-900/90 backdrop-blur-sm border border-slate-700 px-3 py-1 rounded-full shadow-md">
                <span className="text-white font-black text-[10px] uppercase tracking-tighter whitespace-nowrap">
                    {entry.player.last_name}
                </span>
            </div>
        </div>
    );
}
