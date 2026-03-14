import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Note, Calendar, CurrencyEur, User, CaretRight, FileText } from '@phosphor-icons/react';

export default function Index({ auth, clubs, activeClub, players, filters }) {
    const [selectedClub, setSelectedClub] = useState(activeClub?.id || '');
    const [isRenewing, setIsRenewing] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        salary: '',
        months: 12,
        release_clause: '',
    });

    const handleClubChange = (e) => {
        const id = e.target.value;
        setSelectedClub(id);
        router.get(route('contracts.index'), { club: id }, { preserveState: true });
    };

    const handleRenew = (player) => {
        setIsRenewing(player.id);
        setData({
            salary: player.renewal_info.current_salary,
            months: 12,
            release_clause: player.release_clause || '',
        });
    };

    const submitRenewal = (e, playerId) => {
        e.preventDefault();
        post(route('contracts.renew', playerId), {
            onSuccess: () => {
                setIsRenewing(null);
                reset();
            },
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-white leading-tight">Verträge</h2>}
        >
            <Head title="Verträge" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                    {/* Club Selector */}
                    <div className="bg-slate-900 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-slate-800">
                        <div className="flex items-center justify-between">
                            <div className="flex items-center space-x-3">
                                <div className="p-3 bg-blue-600/20 rounded-lg text-blue-500">
                                    <Note size={24} weight="fill" />
                                </div>
                                <div>
                                    <h3 className="text-lg font-medium text-white">Vertragsmanagement</h3>
                                    <p className="text-sm text-slate-400">Verwalte die Laufzeiten und Gehälter deiner Spieler.</p>
                                </div>
                            </div>
                            <div className="w-64">
                                <select
                                    value={selectedClub}
                                    onChange={handleClubChange}
                                    className="w-full bg-slate-800 border-slate-700 text-white rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    {clubs.map((club) => (
                                        <option key={club.id} value={club.id}>
                                            {club.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Players Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {players.length > 0 ? (
                            players.map((player) => (
                                <div key={player.id} className="bg-slate-900 rounded-xl border border-slate-800 overflow-hidden hover:border-blue-500/50 transition-all duration-300 group">
                                    <div className="p-5">
                                        <div className="flex items-start space-x-4">
                                            <div className="relative">
                                                <img 
                                                    src={player.photo_url} 
                                                    alt={player.last_name} 
                                                    className="w-16 h-16 rounded-lg object-cover bg-slate-800"
                                                />
                                                <div className="absolute -bottom-2 -right-2 bg-blue-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded border border-slate-900">
                                                    {player.overall}
                                                </div>
                                            </div>
                                            <div className="flex-1">
                                                <h4 className="text-white font-bold text-lg group-hover:text-blue-400 transition-colors">
                                                    {player.first_name} {player.last_name}
                                                </h4>
                                                <p className="text-slate-400 text-xs font-medium uppercase tracking-wider">{player.position}</p>
                                                <div className="mt-2 flex items-center space-x-2 text-sm text-slate-300">
                                                    <Calendar className="text-blue-500" size={12} weight="fill" />
                                                    <span>Läuft aus: {player.expires_on_formatted}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="mt-6 grid grid-cols-2 gap-4">
                                            <div className="bg-slate-800/50 p-3 rounded-lg border border-slate-700/50">
                                                <p className="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Gehalt</p>
                                                <p className="text-white font-semibold flex items-center">
                                                    {player.salary_formatted}
                                                </p>
                                            </div>
                                            <div className="bg-slate-800/50 p-3 rounded-lg border border-slate-700/50">
                                                <p className="text-[10px] text-slate-400 uppercase font-bold tracking-tight">Marktwert</p>
                                                <p className="text-white font-semibold">
                                                    {player.value_formatted}
                                                </p>
                                            </div>
                                        </div>

                                        {isRenewing === player.id ? (
                                            <form onSubmit={(e) => submitRenewal(e, player.id)} className="mt-6 p-4 bg-slate-800 rounded-lg border border-blue-500/30 animate-in fade-in slide-in-from-top-2">
                                                <h5 className="text-sm font-bold text-white mb-4 flex items-center">
                                                    <FileText className="mr-2 text-blue-500" weight="fill" /> Vertrag verlängern
                                                </h5>
                                                
                                                <div className="space-y-4">
                                                    <div>
                                                        <label className="block text-xs font-medium text-slate-400 mb-1">Gehalt / Monat</label>
                                                        <div className="relative">
                                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                                                <CurrencyEur size={12} weight="bold" />
                                                            </div>
                                                            <input
                                                                type="number"
                                                                value={data.salary}
                                                                onChange={e => setData('salary', e.target.value)}
                                                                className="block w-full pl-8 bg-slate-900 border-slate-700 rounded-md text-sm text-white focus:ring-blue-500 focus:border-blue-500"
                                                            />
                                                        </div>
                                                        {errors.salary && <p className="text-red-500 text-[10px] mt-1">{errors.salary}</p>}
                                                    </div>

                                                    <div>
                                                        <label className="block text-xs font-medium text-slate-400 mb-1">Laufzeit (Monate)</label>
                                                        <input
                                                            type="number"
                                                            value={data.months}
                                                            onChange={e => setData('months', e.target.value)}
                                                            className="block w-full bg-slate-900 border-slate-700 rounded-md text-sm text-white focus:ring-blue-500 focus:border-blue-500"
                                                            min="1"
                                                            max="84"
                                                        />
                                                        {errors.months && <p className="text-red-500 text-[10px] mt-1">{errors.months}</p>}
                                                    </div>

                                                    <div className="flex space-x-2 pt-2">
                                                        <button
                                                            type="submit"
                                                            disabled={processing}
                                                            className="flex-1 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 rounded-md transition-colors disabled:opacity-50"
                                                        >
                                                            {processing ? 'Verarbeite...' : 'Bestätigen'}
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => setIsRenewing(null)}
                                                            className="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-3 py-2 rounded-md transition-colors"
                                                        >
                                                            Abbrechen
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        ) : (
                                            <button
                                                onClick={() => handleRenew(player)}
                                                className="mt-6 w-full flex items-center justify-center space-x-2 bg-slate-800 hover:bg-blue-600 text-white text-sm font-bold py-3 rounded-lg border border-slate-700 hover:border-blue-500 transition-all duration-300"
                                            >
                                                <span>Vertrag verlängern</span>
                                                <CaretRight size={12} weight="bold" />
                                            </button>
                                        )}
                                    </div>
                                </div>
                            ))
                        ) : (
                            <div className="col-span-full py-20 bg-slate-900 rounded-xl border border-slate-800 flex flex-col items-center justify-center text-slate-500">
                                <User className="w-12 h-12 mb-4 opacity-20" weight="fill" />
                                <p>Keine Spieler in diesem Verein gefunden.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
