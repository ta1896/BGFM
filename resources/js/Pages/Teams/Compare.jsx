import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';

export default function Compare({ auth, activeClub, unreadCount, clubs, club1Id, club2Id, comparisonData }) {
    const [selectedClub1, setSelectedClub1] = useState(club1Id || '');
    const [selectedClub2, setSelectedClub2] = useState(club2Id || '');

    const handleCompare = () => {
        if (selectedClub1 && selectedClub2) {
            router.get(route('teams.compare'), { club1: selectedClub1, club2: selectedClub2 }, { preserveState: true });
        }
    };

    const stats = comparisonData ? [
        { label: 'Punkte', key: 'points' },
        { label: 'Tore Erziel', key: 'goals_for' },
        { label: 'Tore Kassiert', key: 'goals_against' },
        { label: 'Tordifferenz', key: 'goal_difference' },
        { label: 'Siege', key: 'wins' },
        { label: 'Unentschieden', key: 'draws' },
        { label: 'Niederlagen', key: 'losses' },
        { label: 'Ø Bewertung', key: 'average_rating', suffix: '' },
        { label: 'Gesamtwert', key: 'total_value', prefix: '€', format: (v) => new Intl.NumberFormat('de-DE').format(v) },
    ] : [];

    const getWinnerClass = (val1, val2, key) => {
        if (!val1 && !val2) return '';
        const num1 = parseFloat(val1);
        const num2 = parseFloat(val2);
        
        if (key === 'goals_against' || key === 'losses') {
            if (num1 < num2) return 'text-emerald-400 font-black';
            if (num2 < num1) return 'text-rose-400';
        } else {
            if (num1 > num2) return 'text-emerald-400 font-black';
            if (num2 > num1) return 'text-rose-400';
        }
        return 'text-amber-500'; // Draw
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            activeClub={activeClub}
            unreadCount={unreadCount}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Team-Vergleich</h2>}
        >
            <Head title="Team-Vergleich" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Selectors */}
                    <div className="bg-slate-900 border border-slate-800 shadow-xl sm:rounded-2xl p-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                            <div>
                                <label className="block text-sm font-medium text-slate-400 mb-2 uppercase tracking-widest font-black">Team 1</label>
                                <select 
                                    className="w-full bg-slate-950 border-slate-800 rounded-xl text-white focus:ring-cyan-500 focus:border-cyan-500"
                                    value={selectedClub1}
                                    onChange={(e) => setSelectedClub1(e.target.value)}
                                >
                                    <option value="">Bitte wählen...</option>
                                    {clubs.map(c => (
                                        <option key={c.id} value={c.id} disabled={c.id.toString() === selectedClub2?.toString()}>{c.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="flex justify-center pb-2">
                                <button 
                                    onClick={handleCompare}
                                    disabled={!selectedClub1 || !selectedClub2}
                                    className="px-8 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white rounded-xl font-black uppercase tracking-widest transition-all shadow-[0_0_20px_rgba(8,145,178,0.3)] hover:shadow-[0_0_30px_rgba(8,145,178,0.5)] disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-1"
                                >
                                    Vergleichen
                                </button>
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-400 mb-2 uppercase tracking-widest font-black">Team 2</label>
                                <select 
                                    className="w-full bg-slate-950 border-slate-800 rounded-xl text-white focus:ring-cyan-500 focus:border-cyan-500"
                                    value={selectedClub2}
                                    onChange={(e) => setSelectedClub2(e.target.value)}
                                >
                                    <option value="">Bitte wählen...</option>
                                    {clubs.map(c => (
                                        <option key={c.id} value={c.id} disabled={c.id.toString() === selectedClub1?.toString()}>{c.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Comparison Results */}
                    {comparisonData && (
                        <div className="bg-slate-900 border border-slate-800 shadow-xl sm:rounded-2xl overflow-hidden">
                            {/* Headers */}
                            <div className="grid grid-cols-3 bg-slate-950/50 p-6 border-b border-slate-800">
                                <div className="flex flex-col items-center gap-4">
                                    <div className="w-24 h-24 p-3 bg-slate-900 rounded-2xl border border-slate-800 shadow-xl">
                                        <img loading="lazy" src={comparisonData.club1.logo_url} className="w-full h-full object-contain" alt={comparisonData.club1.name} />
                                    </div>
                                    <div className="text-xl font-black uppercase tracking-tighter text-center">{comparisonData.club1.name}</div>
                                </div>
                                <div className="flex items-center justify-center">
                                    <div className="w-12 h-12 rounded-full bg-slate-800 border-2 border-slate-700 flex items-center justify-center text-slate-400 font-black italic">VS</div>
                                </div>
                                <div className="flex flex-col items-center gap-4">
                                    <div className="w-24 h-24 p-3 bg-slate-900 rounded-2xl border border-slate-800 shadow-xl">
                                        <img loading="lazy" src={comparisonData.club2.logo_url} className="w-full h-full object-contain" alt={comparisonData.club2.name} />
                                    </div>
                                    <div className="text-xl font-black uppercase tracking-tighter text-center">{comparisonData.club2.name}</div>
                                </div>
                            </div>

                            {/* Stat Rows */}
                            <div className="divide-y divide-slate-800/50">
                                {stats.map((stat, idx) => (
                                    <div key={idx} className="grid grid-cols-3 p-4 hover:bg-white/[0.02] transition-colors">
                                        <div className={`text-center font-bold text-lg ${getWinnerClass(comparisonData.club1[stat.key], comparisonData.club2[stat.key], stat.key)}`}>
                                            {stat.prefix}{stat.format ? stat.format(comparisonData.club1[stat.key]) : comparisonData.club1[stat.key]}{stat.suffix}
                                        </div>
                                        <div className="text-center text-xs font-black uppercase tracking-widest text-slate-500 py-1">
                                            {stat.label}
                                        </div>
                                        <div className={`text-center font-bold text-lg ${getWinnerClass(comparisonData.club2[stat.key], comparisonData.club1[stat.key], stat.key)}`}>
                                            {stat.prefix}{stat.format ? stat.format(comparisonData.club2[stat.key]) : comparisonData.club2[stat.key]}{stat.suffix}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
