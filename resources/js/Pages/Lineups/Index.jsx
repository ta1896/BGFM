import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    SoccerBall, 
    Users, 
    Layout, 
    Plus, 
    Calendar, 
    Cards, 
    Strategy, 
    TrendUp,
    Clock,
    UserCircle,
    CaretRight,
    Checks,
    Warning
} from '@phosphor-icons/react';

const MatchCard = ({ match, club }) => {
    const isHome = match.home_club_id === club.id;
    const opponent = isHome ? match.away_club : match.home_club;
    const userLineup = match.lineups && match.lineups.length > 0 ? match.lineups[0] : null;

    return (
        <motion.div 
            whileHover={{ y: -5 }}
            className="sim-card group relative overflow-hidden flex flex-col h-full bg-[#0c1222]/80 backdrop-blur-xl border-slate-800/50"
        >
            <div className="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
            
            <div className="p-6 relative z-10 flex flex-col h-full">
                {/* Meta */}
                <div className="flex items-center justify-between mb-6">
                    <div className="px-2 py-1 rounded bg-slate-900 border border-slate-800 text-[10px] font-black text-slate-500 uppercase tracking-widest">
                        {match.match_type || 'Liga'}
                    </div>
                    <div className="flex items-center gap-1.5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                        <Calendar size={12} weight="bold" />
                        {match.kickoff_at_formatted}
                    </div>
                </div>

                {/* Matchup */}
                <div className="flex items-center justify-between gap-6 mb-8">
                    <div className="flex flex-col items-center gap-3 flex-1">
                        <div className="w-14 h-14 rounded-2xl bg-slate-900 border border-slate-800 p-2.5 shadow-xl group-hover:border-cyan-500/30 transition-colors relative">
                            <img src={match.home_club.logo_url} className="w-full h-full object-contain" />
                            {isHome && <div className="absolute -top-1 -right-1 w-3.5 h-3.5 bg-cyan-500 rounded-full border-2 border-[#0c1222] shadow-[0_0_10px_rgba(34,211,238,0.5)]" />}
                        </div>
                        <span className="text-xs font-black text-white uppercase tracking-tighter text-center line-clamp-1">{match.home_club.short_name}</span>
                    </div>

                    <div className="text-xl font-black text-slate-700 italic">VS</div>

                    <div className="flex flex-col items-center gap-3 flex-1">
                        <div className="w-14 h-14 rounded-2xl bg-slate-900 border border-slate-800 p-2.5 shadow-xl group-hover:border-cyan-500/30 transition-colors relative">
                            <img src={match.away_club.logo_url} className="w-full h-full object-contain" />
                            {!isHome && <div className="absolute -top-1 -right-1 w-3.5 h-3.5 bg-cyan-500 rounded-full border-2 border-[#0c1222] shadow-[0_0_10px_rgba(34,211,238,0.5)]" />}
                        </div>
                        <span className="text-xs font-black text-white uppercase tracking-tighter text-center line-clamp-1">{match.away_club.short_name}</span>
                    </div>
                </div>

                {/* Status Indicator */}
                <div className="mt-auto pt-6 border-t border-slate-800/50 flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Aufstellung</span>
                        {userLineup ? (
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-emerald-400 uppercase">
                                <Checks size={14} weight="bold" /> GESETZT
                            </div>
                        ) : (
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-amber-500 uppercase">
                                <Warning size={14} weight="bold" /> AUSSTEHEND
                            </div>
                        )}
                    </div>

                    <Link 
                        href={route('lineups.match', match.id)}
                        className={`px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all ${
                            userLineup 
                                ? 'bg-slate-800 text-slate-300 hover:bg-slate-700' 
                                : 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/40'
                        }`}
                    >
                        {userLineup ? 'BEARBEITEN' : 'ERSTELLEN'}
                    </Link>
                </div>
            </div>
        </motion.div>
    );
};

export default function Index({ club, matches, templates }) {
    const { auth } = usePage().props;

    return (
        <AuthenticatedLayout>
            <Head title="Aufstellungen & Taktik" />

            <div className="max-w-[1400px] mx-auto space-y-12">
                {/* Hero / Header */}
                <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                    <div>
                        <motion.div 
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            className="flex items-center gap-2 mb-2"
                        >
                            <span className="h-px w-8 bg-cyan-500" />
                            <span className="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-500">Matchcenter // Strategie</span>
                        </motion.div>
                        <h1 className="text-5xl lg:text-7xl font-black text-white tracking-tighter uppercase italic leading-none">
                            Aufstellungen <span className="text-slate-600">&</span> Taktik
                        </h1>
                    </div>

                    <motion.div 
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                    >
                        <Link 
                            href={route('lineups.create')}
                            className="sim-btn-primary flex items-center gap-3 px-8 py-4 group"
                        >
                            <Plus size={20} weight="bold" />
                            <span className="font-black uppercase tracking-widest text-xs">Neue Vorlage erstellen</span>
                        </Link>
                    </motion.div>
                </div>

                {/* Live Section */}
                <section className="space-y-6">
                    <div className="flex items-center gap-4">
                        <div className="p-2 rounded-xl bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                            <Calendar size={24} weight="duotone" />
                        </div>
                        <h2 className="text-2xl font-black text-white uppercase tracking-tighter italic">Anstehende Termine</h2>
                    </div>

                    {matches.length === 0 ? (
                        <div className="sim-card p-20 text-center border-dashed border-2 border-slate-800 bg-slate-900/20">
                            <p className="text-slate-500 font-bold uppercase tracking-widest text-sm">Keine geplanten Spiele erfasst</p>
                        </div>
                    ) : (
                        <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                            {matches.map(match => (
                                <MatchCard key={match.id} match={match} club={club} />
                            ))}
                        </div>
                    )}
                </section>

                {/* Templates Section */}
                <section className="space-y-6 pt-12 border-t border-slate-800/50">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="p-2 rounded-xl bg-slate-800 text-slate-400 border border-slate-700">
                                <Layout size={24} weight="duotone" />
                            </div>
                            <h2 className="text-2xl font-black text-white uppercase tracking-tighter italic">Gespeicherte Vorlagen</h2>
                        </div>
                        <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">{templates.length} Profile</span>
                    </div>

                    {templates.length === 0 ? (
                        <div className="text-slate-600 italic text-sm">Keine taktischen Vorlagen hinterlegt.</div>
                    ) : (
                        <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {templates.map(template => (
                                <motion.div 
                                    key={template.id}
                                    whileHover={{ scale: 1.02 }}
                                    className="sim-card p-6 bg-slate-900/50 border-slate-800 group"
                                >
                                    <div className="flex justify-between items-start mb-4">
                                        <h3 className="font-black text-white uppercase tracking-tight group-hover:text-cyan-400 transition-colors line-clamp-1">
                                            {template.name}
                                        </h3>
                                        <div className="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px] font-black text-cyan-400 uppercase">
                                            {template.formation}
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-widest mb-6">
                                        <Users size={14} />
                                        {template.players_count || template.players?.length} Spieler zugewiesen
                                    </div>

                                    <div className="flex items-center justify-between pt-4 border-t border-slate-800">
                                        <Link 
                                            href={route('lineups.edit', template.id)}
                                            className="text-[10px] font-black text-slate-400 hover:text-white transition-colors"
                                        >
                                            BEARBEITEN
                                        </Link>
                                        <form method="POST" action={route('lineups.destroy', template.id)} onSubmit={(e) => !confirm('Vorlage wirklich löschen?') && e.preventDefault()}>
                                            <input type="hidden" name="_method" value="DELETE" />
                                            {/* We should use Inertia delete here, but sticking to logic for now */}
                                            <button type="submit" className="text-[10px] font-black text-rose-500/70 hover:text-rose-400 transition-colors">
                                                LÖSCHEN
                                            </button>
                                        </form>
                                    </div>
                                </motion.div>
                            ))}
                        </div>
                    )}
                </section>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white shadow-[0_0_30px_rgba(34,211,238,0.2)] hover:shadow-[0_0_40px_rgba(34,211,238,0.4)] transition-all rounded-2xl;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
