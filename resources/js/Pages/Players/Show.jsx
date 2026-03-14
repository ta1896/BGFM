import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    User, 
    CalendarBlank,
    SoccerBall, 
    ChartBar, 
    Clock, 
    ShieldCheck, 
    TrendUp,
    ArrowLeft,
    IdentificationBadge,
    Selection,
    Camera,
    FloppyDisk,
    Info,
    Trophy,
    ClockCounterClockwise,
    Warning,
    Crown,
    Smiley,
    Lightning,
    Target
} from '@phosphor-icons/react';

const StatRing = ({ value, max = 99, label, color = "emerald" }) => {
    const radius = 24;
    const circumference = 2 * Math.PI * radius;
    const offset = circumference - (value / max) * circumference;

    const colors = {
        emerald: "text-emerald-500",
        amber: "text-amber-500",
        gold: "text-[#d9b15c]",
        bronze: "text-amber-800"
    };

    return (
        <div className="flex flex-col items-center">
            <div className="relative flex items-center justify-center w-16 h-16">
                <svg className="w-full h-full transform -rotate-90">
                    <circle cx="32" cy="32" r={radius} stroke="currentColor" strokeWidth="4" fill="transparent" className="text-slate-800" />
                    <motion.circle 
                        initial={{ strokeDashoffset: circumference }}
                        animate={{ strokeDashoffset: offset }}
                        transition={{ duration: 1, ease: "easeOut" }}
                        cx="32" cy="32" r={radius} stroke="currentColor" strokeWidth="4" fill="transparent" 
                        strokeDasharray={circumference} className={colors[color]} 
                    />
                </svg>
                <span className="absolute text-sm font-black text-white italic">{value}</span>
            </div>
            <span className="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">{label}</span>
        </div>
    );
};

const TabButton = ({ active, onClick, children, icon: Icon }) => (
    <button 
        onClick={onClick}
        className={`flex items-center gap-2.5 px-6 py-4 border-b-2 transition-all text-xs font-black uppercase tracking-widest ${
            active 
                ? 'border-amber-500 text-amber-500 bg-amber-500/5' 
                : 'border-transparent text-slate-500 hover:text-slate-300 hover:bg-white/5'
        }`}
    >
        {Icon && <Icon size={18} weight={active ? "fill" : "bold"} />}
        {children}
    </button>
);

export default function Show({ player, currentSeasonStats, careerStats, recentMatches, isOwner, positions }) {
    const [activeTab, setActiveTab] = useState('overview');
    
    const { data, setData, patch, processing, errors } = useForm({
        market_value: player.market_value,
        position: player.position,
        position_second: player.position_second || '',
        position_third: player.position_third || '',
        photo_url: '',
    });

    const handleUpdate = (e) => {
        e.preventDefault();
        patch(route('players.update', player.id), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={player.full_name} />

            <div className="max-w-[1400px] mx-auto space-y-8">
                {/* Breadcrumbs / Navigation */}
                <div className="flex items-center justify-between">
                    <Link 
                        href={route('players.index')}
                        className="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-widest hover:text-amber-500 transition-colors"
                    >
                        <ArrowLeft size={14} weight="bold" />
                        Zurück zum Kader
                    </Link>
                    
                    <div className="flex items-center gap-3">
                        {isOwner && (
                            <div className="px-3 py-1 rounded-full bg-cyan-500/10 border border-cyan-500/20 text-[9px] font-black text-cyan-400 uppercase tracking-widest">
                                Dein Spieler
                            </div>
                        )}
                        <span className="px-3 py-1 rounded-full bg-slate-900 border border-slate-800 text-[9px] font-black text-slate-500 uppercase tracking-widest italic">
                            ID: #{player.id}
                        </span>
                    </div>
                </div>

                {/* Hero Profile Card */}
                <div className="sim-card p-0 overflow-hidden bg-gradient-to-br from-[#0c1222] to-[#161e32] border-slate-800/50 shadow-2xl relative">
                    {/* Background Visuals */}
                    <div className="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-cyan-500/5 to-transparent pointer-events-none" />
                    <div className="absolute -bottom-24 -left-24 w-64 h-64 bg-indigo-600/10 blur-[100px] rounded-full pointer-events-none" />
                    
                    <div className="p-8 md:p-12 relative z-10">
                        <div className="flex flex-col lg:flex-row gap-12 items-center lg:items-end">
                            {/* Player Photo */}
                            <div className="relative group">
                                <div className="absolute inset-0 bg-cyan-500/20 blur-2xl rounded-full opacity-0 group-hover:opacity-100 transition-opacity" />
                                <div className="w-48 h-48 md:w-56 md:h-56 rounded-full p-2 bg-gradient-to-br from-slate-800 to-slate-950 border border-slate-700/50 shadow-2xl relative">
                                    <img loading="lazy" 
                                        src={player.photo_url} 
                                        alt={player.full_name}
                                        className="w-full h-full object-cover rounded-full mix-blend-luminosity hover:mix-blend-normal transition-all duration-500"
                                    />
                                    {isOwner && (
                                        <div className="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-amber-500 text-black flex items-center justify-center border-4 border-[#0c1222] shadow-xl">
                                            <Crown size={20} weight="fill" />
                                        </div>
                                    )}
                                </div>
                            </div>

                            {/* Player Identity */}
                            <div className="flex-1 text-center lg:text-left">
                                <div className="flex flex-wrap items-center justify-center lg:justify-start gap-3 mb-4">
                                    <span className="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[10px] font-black text-amber-500 uppercase tracking-widest italic">
                                        {player.position}
                                    </span>
                                    <span className="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                        {player.age} Jahre
                                    </span>
                                </div>
                                <h1 className="text-5xl md:text-7xl font-black text-white tracking-tighter uppercase italic leading-none mb-6">
                                    {player.first_name} <span className="text-amber-500">{player.last_name}</span>
                                </h1>
                                
                                <div className="flex flex-wrap items-center justify-center lg:justify-start gap-8">
                                    {player.club ? (
                                        <Link href={route('clubs.show', player.club.id)} className="flex items-center gap-4 group">
                                            <div className="w-12 h-12 p-2 rounded-xl bg-white/5 border border-white/10 group-hover:border-cyan-500/30 transition-all">
                                                <img loading="lazy" src={player.club.logo_url} className="w-full h-full object-contain" alt={player.club.name} />
                                            </div>
                                            <div>
                                                <p className="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Aktueller Verein</p>
                                                <p className="text-lg font-black text-white group-hover:text-cyan-400 transition-colors leading-none uppercase italic tracking-tighter">{player.club.name}</p>
                                            </div>
                                        </Link>
                                    ) : (
                                        <div className="flex items-center gap-4 opacity-50">
                                            <div className="w-12 h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-600 italic font-black">?</div>
                                            <div>
                                                <p className="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Status</p>
                                                <p className="text-lg font-black text-slate-400 leading-none uppercase italic tracking-tighter">Vereinslos</p>
                                            </div>
                                        </div>
                                    )}

                                    <div className="h-10 w-px bg-slate-800 hidden md:block" />

                                    <div>
                                        <p className="text-[10px] font-black text-slate-500 uppercase tracking-widest leading-none mb-1">Marktwert</p>
                                        <p className="text-3xl font-black text-white italic tracking-tighter leading-none">
                                            {player.market_value_formatted}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {/* Main Metrics */}
                            <div className="flex gap-8 px-8 py-6 rounded-3xl bg-slate-900/50 border border-white/5 backdrop-blur-md self-center">
                                <StatRing value={player.overall} max={99} label="Stärke" color="emerald" />
                                <StatRing value={player.potential} max={99} label="Potenzial" color="amber" />
                            </div>
                        </div>
                    </div>

                    {/* Navigation Tabs */}
                    <nav className="flex items-center border-t border-slate-800/50 px-8 bg-black/20 overflow-x-auto no-scrollbar">
                        <TabButton active={activeTab === 'overview'} onClick={() => setActiveTab('overview')} icon={ChartBar}>Übersicht</TabButton>
                        <TabButton active={activeTab === 'career'} onClick={() => setActiveTab('career')} icon={Trophy}>Karriere</TabButton>
                        <TabButton active={activeTab === 'matches'} onClick={() => setActiveTab('matches')} icon={SoccerBall}>Spiele</TabButton>
                        <TabButton active={activeTab === 'history'} onClick={() => setActiveTab('history')} icon={ClockCounterClockwise}>Historie</TabButton>
                        {isOwner && (
                            <TabButton active={activeTab === 'customize'} onClick={() => setActiveTab('customize')} icon={IdentificationBadge}>Anpassen</TabButton>
                        )}
                    </nav>
                </div>

                <div className="min-h-[500px]">
                    <AnimatePresence mode="wait">
                        {activeTab === 'overview' && (
                            <motion.div 
                                key="overview"
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -10 }}
                                className="grid lg:grid-cols-3 gap-8"
                            >
                                {/* Core Stats */}
                                <div className="lg:col-span-2 space-y-8">
                                    <div className="sim-card p-8">
                                        <div className="flex items-center gap-4 mb-8">
                                            <ChartBar size={24} weight="duotone" className="text-cyan-400" />
                                            <h3 className="text-xl font-black text-white uppercase tracking-tighter italic">Physische & Technische Profile</h3>
                                        </div>
                                        <div className="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
                                            {[
                                                { label: 'Tempo', val: player.pace, icon: Lightning, color: 'text-amber-500' },
                                                { label: 'Schuss', val: player.shooting, icon: Target, color: 'text-rose-400' },
                                                { label: 'Passen', val: player.passing, icon: ChartBar, color: 'text-amber-600' },
                                                { label: 'Dribbling', val: player.dribbling || 70, icon: SoccerBall, color: 'text-amber-500' },
                                                { label: 'Defensive', val: player.defending, icon: ShieldCheck, color: 'text-emerald-400' },
                                                { label: 'Physis', val: player.physical, icon: TrendUp, color: 'text-purple-400' },
                                            ].map(stat => (
                                                <div key={stat.label} className="space-y-3">
                                                    <div className="flex justify-between items-center px-1">
                                                        <div className="flex items-center gap-2">
                                                            <stat.icon size={14} className={stat.color} weight="bold" />
                                                            <span className="text-[10px] font-black text-slate-400 uppercase tracking-widest">{stat.label}</span>
                                                        </div>
                                                        <span className="text-xs font-black text-white italic">{stat.val}</span>
                                                    </div>
                                                    <div className="h-2 bg-slate-900 rounded-full overflow-hidden p-0.5 border border-slate-800">
                                                        <motion.div 
                                                            initial={{ width: 0 }}
                                                            animate={{ width: `${stat.val}%` }}
                                                            className={`h-full rounded-full bg-gradient-to-r ${stat.color.replace('text-', 'from-')}/60 to-transparent`}
                                                        />
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>

                                    {/* Additional Positions */}
                                    <div className="sim-card p-8">
                                        <div className="flex items-center gap-4 mb-8">
                                            <Selection size={24} weight="duotone" className="text-indigo-400" />
                                            <h3 className="text-xl font-black text-white uppercase tracking-tighter italic">Positionen</h3>
                                        </div>
                                        <div className="flex flex-wrap gap-4">
                                            <div className="flex-1 min-w-[200px] p-6 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-center text-amber-500">
                                                <span className="text-[10px] font-black uppercase tracking-widest block mb-2 text-center w-full opacity-60">Hauptposition</span>
                                                <span className="text-3xl font-black text-white">{player.position}</span>
                                            </div>
                                            {player.position_second && (
                                                <div className="flex-1 min-w-[200px] p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center">
                                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2 text-center w-full">Nebenposition</span>
                                                    <span className="text-2xl font-black text-slate-300">{player.position_second}</span>
                                                </div>
                                            )}
                                            {player.position_third && (
                                                <div className="flex-1 min-w-[200px] p-6 rounded-3xl bg-slate-900 border border-slate-800 text-center">
                                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest block mb-2 text-center w-full">Alternativ</span>
                                                    <span className="text-2xl font-black text-slate-300">{player.position_third}</span>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* Condition Sidebar */}
                                <div className="space-y-8">
                                    <div className="sim-card p-8">
                                        <div className="flex items-center gap-4 mb-8">
                                            <Smiley size={24} weight="duotone" className="text-emerald-400" />
                                            <h3 className="text-xl font-black text-white uppercase tracking-tighter italic">Kondition</h3>
                                        </div>
                                        <div className="space-y-8">
                                            <div>
                                                <div className="flex justify-between mb-4 px-1">
                                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Fitness</span>
                                                    <span className={`text-xs font-black italic ${player.stamina > 80 ? 'text-emerald-400' : 'text-amber-400'}`}>{player.stamina}%</span>
                                                </div>
                                                <div className="h-6 bg-slate-900 rounded-xl overflow-hidden p-1 border border-slate-800 shadow-inner relative">
                                                    <motion.div 
                                                        initial={{ width: 0 }}
                                                        animate={{ width: `${player.stamina}%` }}
                                                        className={`h-full rounded-lg bg-gradient-to-r ${player.stamina > 80 ? 'from-emerald-600 to-emerald-400' : 'from-amber-600 to-amber-400'} shadow-lg`}
                                                    />
                                                </div>
                                            </div>

                                            <div>
                                                <div className="flex justify-between mb-4 px-1">
                                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Moral</span>
                                                    <span className="text-xs font-black text-amber-500 italic">{player.morale}%</span>
                                                </div>
                                                <div className="h-6 bg-slate-900 rounded-xl overflow-hidden p-1 border border-slate-800 shadow-inner">
                                                    <motion.div 
                                                        initial={{ width: 0 }}
                                                        animate={{ width: `${player.morale}%` }}
                                                        className="h-full rounded-lg bg-gradient-to-r from-amber-600 to-amber-500 shadow-lg"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Quick Info Card */}
                                    <div className="sim-card p-8 bg-slate-900/40 border-slate-800/80">
                                        <div className="flex items-center gap-4 mb-6">
                                            <Info size={24} weight="duotone" className="text-indigo-400" />
                                            <h3 className="text-xl font-black text-white uppercase tracking-tighter italic">Vertrag</h3>
                                        </div>
                                        <div className="space-y-4">
                                            <div className="flex justify-between items-center py-2 border-b border-slate-800">
                                                <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Gehalt</span>
                                                <span className="text-xs font-black text-white">{new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 }).format(player.salary || 0)}</span>
                                            </div>
                                            <div className="flex justify-between items-center py-2 border-b border-slate-800">
                                                <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Marktwert</span>
                                                <span className="text-xs font-black text-white">{player.market_value_formatted}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </motion.div>
                        )}

                        {activeTab === 'career' && (
                            <motion.div 
                                key="career"
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: -20 }}
                                className="space-y-8"
                            >
                                <div className="sim-card p-0 overflow-hidden">
                                     <div className="overflow-x-auto">
                                        <table className="w-full text-left">
                                            <thead>
                                                <tr className="bg-slate-900/50">
                                                    <th className="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Saison</th>
                                                    <th className="px-8 py-5 text-[10px] font-black text-slate-500 uppercase tracking-widest">Wettbewerb</th>
                                                    <th className="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Spiele</th>
                                                    <th className="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Tore</th>
                                                    <th className="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Vorl.</th>
                                                    <th className="px-8 py-5 text-center text-[10px] font-black text-slate-500 uppercase tracking-widest">Gelb/Rot</th>
                                                    <th className="px-8 py-5 text-right text-[10px] font-black text-slate-500 uppercase tracking-widest">Rating</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-slate-800">
                                                {careerStats.length > 0 ? careerStats.map((stat, idx) => (
                                                    <tr key={idx} className="hover:bg-white/5 transition-colors group">
                                                        <td className="px-8 py-5 text-xs font-black text-white italic">{stat.season?.name || '-'}</td>
                                                        <td className="px-8 py-5">
                                                            <div className="flex items-center gap-3">
                                                                <Trophy size={16} className="text-amber-400" />
                                                                <span className="text-xs font-bold text-slate-300 uppercase tracking-tight">
                                                                    {stat.competition_context === 'league' ? 'LIGA' : stat.competition_context === 'cup_national' ? 'POKAL' : 'INTERNATIONAL'}
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td className="px-8 py-5 text-center text-xs font-black text-white">{stat.appearances}</td>
                                                        <td className="px-8 py-5 text-center text-xs font-bold text-emerald-400 group-hover:scale-110 transition-transform">{stat.goals}</td>
                                                        <td className="px-8 py-5 text-center text-xs font-bold text-amber-500 group-hover:scale-110 transition-transform">{stat.assists}</td>
                                                        <td className="px-8 py-5 text-center">
                                                            <div className="flex items-center justify-center gap-2 text-xs font-bold">
                                                                <span className="text-amber-500">{stat.yellow_cards}</span>
                                                                <span className="text-slate-600">/</span>
                                                                <span className="text-rose-500">{stat.red_cards}</span>
                                                            </div>
                                                        </td>
                                                        <td className="px-8 py-5 text-right">
                                                            <span className={`px-2 py-1 rounded text-xs font-black italic ${stat.average_rating >= 7.0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400'}`}>
                                                                {stat.average_rating > 0 ? parseFloat(stat.average_rating).toFixed(2) : '-'}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                )) : (
                                                    <tr>
                                                        <td colSpan="7" className="px-8 py-20 text-center text-slate-500 italic text-sm">Keine Karrieredaten gefunden.</td>
                                                    </tr>
                                                )}
                                            </tbody>
                                        </table>
                                     </div>
                                </div>
                            </motion.div>
                        )}

                        {activeTab === 'matches' && (
                            <motion.div 
                                key="matches"
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                exit={{ opacity: 0, x: 20 }}
                                className="space-y-8"
                            >
                                <div className="grid gap-6">
                                    {recentMatches.length > 0 ? recentMatches.map((stat, idx) => (
                                        <div key={idx} className="sim-card p-6 flex flex-wrap lg:flex-nowrap items-center gap-8 hover:border-cyan-500/30 transition-all group">
                                            <div className="flex flex-col gap-1 w-32 border-r border-slate-800 pr-6 shrink-0">
                                                <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">{stat.match?.kickoff_date_formatted}</span>
                                                <span className="text-[9px] font-black text-indigo-400 uppercase tracking-[0.2em]">{stat.match?.competition_season?.competition?.code || 'LG'}</span>
                                            </div>

                                            <div className="flex-1 flex items-center justify-center lg:justify-start gap-12 min-w-[300px]">
                                                <div className="flex items-center gap-4 flex-1 justify-end">
                                                    <span className={`text-xs font-black uppercase text-right line-clamp-1 ${stat.match?.home_club_id === player.club_id ? 'text-white' : 'text-slate-500'}`}>{stat.match?.home_club?.short_name}</span>
                                                    <img loading="lazy" src={stat.match?.home_club?.logo_url} className="w-8 h-8 object-contain opacity-80" />
                                                </div>
                                                
                                                <div className="px-4 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-lg font-black text-white italic min-w-[60px] text-center">
                                                    {stat.match?.home_score} : {stat.match?.away_score}
                                                </div>

                                                <div className="flex items-center gap-4 flex-1">
                                                    <img loading="lazy" src={stat.match?.away_club?.logo_url} className="w-8 h-8 object-contain opacity-80" />
                                                    <span className={`text-xs font-black uppercase line-clamp-1 ${stat.match?.away_club_id === player.club_id ? 'text-white' : 'text-slate-500'}`}>{stat.match?.away_club?.short_name}</span>
                                                </div>
                                            </div>

                                            <div className="flex items-center gap-8 lg:border-l border-slate-800 lg:pl-8 shrink-0">
                                                <div className="text-center">
                                                    <p className="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Einsatz</p>
                                                    <p className="text-xs font-black text-white italic">{stat.minutes_played}'</p>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">S/A</p>
                                                    <p className="text-xs font-black text-white italic">
                                                        <span className="text-emerald-400">{stat.goals}</span>
                                                        <span className="text-slate-700 mx-1">/</span>
                                                        <span className="text-amber-500">{stat.assists}</span>
                                                    </p>
                                                </div>
                                                <div className="text-center">
                                                    <p className="text-[9px] font-black text-slate-500 uppercase tracking-widest mb-1">Rating</p>
                                                    <span className={`px-2 py-0.5 rounded text-xs font-black italic ${stat.rating >= 7.0 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-400'}`}>
                                                        {parseFloat(stat.rating || 0).toFixed(1)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    )) : (
                                        <div className="sim-card p-20 text-center border-dashed border-slate-800 bg-slate-900/40">
                                            <SoccerBall size={48} weight="thin" className="text-slate-700 mx-auto mb-6" />
                                            <p className="text-slate-500 font-bold uppercase tracking-widest text-sm italic">Keine aktuellen Spieldaten erfasst</p>
                                        </div>
                                    )}
                                </div>
                            </motion.div>
                        )}

                        {activeTab === 'history' && (
                            <motion.div 
                                key="history"
                                initial={{ opacity: 0, scale: 0.95 }}
                                animate={{ opacity: 1, scale: 1 }}
                                className="sim-card p-20 text-center border-dashed border-2 border-slate-800 bg-slate-900/40"
                            >
                                <History size={48} weight="duotone" className="text-slate-700 mx-auto mb-6" />
                                <h3 className="text-xl font-black text-white uppercase tracking-tighter mb-2 italic">Entwicklungshistorie</h3>
                                <p className="text-slate-500 text-sm font-medium">Die vollständige Transfer- und Attributshistorie wird in Kürze freigeschaltet.</p>
                            </motion.div>
                        )}

                        {activeTab === 'customize' && isOwner && (
                            <motion.div 
                                key="customize"
                                initial={{ opacity: 0, y: 10 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="max-w-3xl mx-auto sim-card p-10 bg-[#0c1222]/80 backdrop-blur-xl border-slate-800/50"
                            >
                                <div className="flex items-center gap-4 mb-10 border-b border-slate-800 pb-6">
                                    <IdentificationBadge size={32} weight="duotone" className="text-cyan-400" />
                                    <div>
                                        <h3 className="text-2xl font-black text-white uppercase tracking-tighter italic">Spielerprofil Anpassen</h3>
                                        <p className="text-xs font-medium text-slate-500 uppercase tracking-widest">Änderungen werden nach Prüfung übernommen</p>
                                    </div>
                                </div>

                                <form onSubmit={handleUpdate} className="space-y-8">
                                    <div className="grid md:grid-cols-2 gap-8">
                                        <div className="space-y-6">
                                            <div>
                                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Marktwert (€)</label>
                                                <input 
                                                    type="number"
                                                    value={data.market_value}
                                                    onChange={e => setData('market_value', e.target.value)}
                                                    className="sim-input-indigo w-full text-white font-mono"
                                                />
                                            </div>
                                            <div>
                                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Sortitoutsi Bild-ID oder URL</label>
                                                <div className="relative">
                                                    <Camera size={18} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500" />
                                                    <input 
                                                        type="text"
                                                        placeholder="https://sortitoutsi.net/player/..."
                                                        value={data.photo_url}
                                                        onChange={e => setData('photo_url', e.target.value)}
                                                        className="sim-input flex-1 pl-12 w-full text-xs"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div className="space-y-6">
                                            <div>
                                                <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 block">Hauptposition</label>
                                                <select 
                                                    value={data.position}
                                                    onChange={e => setData('position', e.target.value)}
                                                    className="sim-select w-full uppercase font-black"
                                                >
                                                    {positions.map(pos => <option key={pos} value={pos}>{pos}</option>)}
                                                </select>
                                            </div>
                                            <div className="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Nebenposition 1</label>
                                                    <select 
                                                        value={data.position_second}
                                                        onChange={e => setData('position_second', e.target.value)}
                                                        className="sim-select w-full text-xs uppercase"
                                                    >
                                                        <option value="">- KEINE -</option>
                                                        {positions.map(pos => <option key={pos} value={pos}>{pos}</option>)}
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 block">Nebenposition 2</label>
                                                    <select 
                                                        value={data.position_third}
                                                        onChange={e => setData('position_third', e.target.value)}
                                                        className="sim-select w-full text-xs uppercase"
                                                    >
                                                        <option value="">- KEINE -</option>
                                                        {positions.map(pos => <option key={pos} value={pos}>{pos}</option>)}
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="pt-8 border-t border-slate-800 flex items-center justify-between gap-6">
                                        <div className="flex items-center gap-3 text-amber-500/60 flex-1">
                                            <Warning size={16} weight="bold" />
                                            <p className="text-[9px] font-black uppercase tracking-widest leading-relaxed max-w-xs">
                                                ACHTUNG: FALSCHDATEN KÖNNEN ZU KONSEQUENZEN FÜR DEINEN ACCOUNT FÜHREN.
                                            </p>
                                        </div>
                                        <button 
                                            type="submit" 
                                            disabled={processing}
                                            className="sim-btn-primary px-10 py-4 flex items-center gap-3 group"
                                        >
                                            <FloppyDisk size={20} weight="bold" className="group-hover:rotate-12 transition-transform" />
                                            <span className="font-black uppercase tracking-widest text-xs">Antrag Speichern</span>
                                        </button>
                                    </div>
                                </form>
                            </motion.div>
                        )}
                    </AnimatePresence>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                .sim-btn-primary {
                    @apply bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black shadow-[0_10px_40px_rgba(217,177,92,0.15)] hover:brightness-110 transition-all rounded-2xl border-none;
                }
                .sim-input-indigo {
                    @apply bg-amber-500/5 border border-amber-500/20 rounded-2xl px-5 py-3.5 text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
