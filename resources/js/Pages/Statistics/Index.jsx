import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ auth, activeClub, unreadCount, seasons, activeSeasonId, topScorers, topAssists, topRatings, teamStats }) {
    const handleSeasonChange = (e) => {
        router.get(route('statistics.index'), { season_id: e.target.value }, { preserveState: true });
    };

    const PlayerList = ({ title, data, valueKey, label }) => (
        <div className="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl">
            <h3 className="text-lg font-black text-white uppercase tracking-wider mb-6 pb-4 border-b border-slate-800">{title}</h3>
            {data.length === 0 ? (
                <div className="text-center text-slate-500 italic py-8">Noch keine Daten vorhanden</div>
            ) : (
                <div className="space-y-4">
                    {data.map((item, index) => (
                        <div key={item.player_id} className="flex items-center gap-4 group">
                            <span className={`w-6 text-center font-black ${index < 3 ? 'text-amber-500' : 'text-slate-600'}`}>
                                {index + 1}.
                            </span>
                            <div className="w-10 h-10 rounded-full overflow-hidden border-2 border-slate-800 bg-slate-950">
                                <img loading="lazy" src={item.player?.photo_url || '/images/default-player.png'} className="w-full h-full object-cover" />
                            </div>
                            <div className="flex-1 min-w-0">
                                <Link href={route('players.show', item.player_id)} className="text-sm font-bold text-white hover:text-cyan-400 transition-colors truncate block">
                                    {item.player?.full_name}
                                </Link>
                                <div className="flex items-center gap-2 mt-0.5">
                                    <img loading="lazy" src={item.club?.logo_url} className="w-3.5 h-3.5 object-contain opacity-70" />
                                    <span className="text-[10px] text-slate-500 uppercase font-bold tracking-wider truncate">{item.club?.short_name}</span>
                                </div>
                            </div>
                            <div className="text-right">
                                <div className="text-lg font-black text-cyan-400">{item[valueKey]}</div>
                                <div className="text-[10px] text-slate-500 uppercase tracking-widest">{label}</div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </div>
    );

    return (
        <AuthenticatedLayout
            user={auth.user}
            activeClub={activeClub}
            unreadCount={unreadCount}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Saison-Statistiken</h2>}
        >
            <Head title="Statistiken" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                    
                    {/* Season Selector */}
                    <div className="flex justify-end">
                        <select 
                            className="bg-slate-900 border-slate-800 rounded-xl text-white focus:ring-cyan-500 focus:border-cyan-500 min-w-[250px] shadow-xl"
                            value={activeSeasonId || ''}
                            onChange={handleSeasonChange}
                        >
                            {seasons.map(s => (
                                <option key={s.id} value={s.id}>{s.name}</option>
                            ))}
                        </select>
                    </div>

                    {/* Top Players Grids */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <PlayerList title="Top Torjäger" data={topScorers} valueKey="total_goals" label="Tore" />
                        <PlayerList title="Top Vorlagen" data={topAssists} valueKey="total_assists" label="Assists" />
                        <PlayerList title="Beste Bewertung" data={topRatings} valueKey="avg_rating" label="Ø Note" />
                    </div>

                    {/* Team Stats Table if implemented later... */}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
