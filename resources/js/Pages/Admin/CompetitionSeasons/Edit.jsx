import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FloppyDisk, Trophy, CheckCircle } from '@phosphor-icons/react';

export default function Edit({ competitionSeason: cs, clubs }) {
    const { data, setData, put, processing, errors } = useForm({
        league_winner_club_id:       cs.league_winner_club_id       ?? '',
        national_cup_winner_club_id: cs.national_cup_winner_club_id ?? '',
        intl_cup_winner_club_id:     cs.intl_cup_winner_club_id     ?? '',
        is_finished:                 cs.is_finished                 ?? false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('admin.competition-seasons.update', cs.id));
    };

    const competition = cs.competition;
    const season = cs.season;

    return (
        <AdminLayout
            header={
                <div className="flex items-center gap-4">
                    <Link href={route('admin.competitions.edit', cs.competition_id)} className="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-widest text-cyan-400">Wettbewerb-Saison</p>
                        <h1 className="text-xl font-bold text-white">{competition?.name} – {season?.name}</h1>
                    </div>
                </div>
            }
        >
            <Head title="Wettbewerb-Saison bearbeiten" />

            <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">

                {/* Winner Settings */}
                <div className="sim-card p-6">
                    <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 mb-5 flex items-center gap-2">
                        <Trophy size={14} /> Gewinner & Ergebnis
                    </h3>

                    <div className="space-y-5">
                        {competition?.type === 'league' && (
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Liga-Meister</label>
                                <select className="sim-select w-full" value={data.league_winner_club_id}
                                    onChange={e => setData('league_winner_club_id', e.target.value || null)}>
                                    <option value="">— Automatisch (aus Tabelle) —</option>
                                    {clubs.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                </select>
                                <p className="text-[10px] text-slate-600 mt-1">Leer lassen = automatisch aus der Abschlusstabelle ermittelt.</p>
                            </div>
                        )}

                        {competition?.type === 'cup' && (
                            <>
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Nationaler Pokalsieger</label>
                                    <select className="sim-select w-full" value={data.national_cup_winner_club_id}
                                        onChange={e => setData('national_cup_winner_club_id', e.target.value || null)}>
                                        <option value="">— Keiner —</option>
                                        {clubs.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Internationaler Pokalsieger</label>
                                    <select className="sim-select w-full" value={data.intl_cup_winner_club_id}
                                        onChange={e => setData('intl_cup_winner_club_id', e.target.value || null)}>
                                        <option value="">— Keiner —</option>
                                        {clubs.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                                    </select>
                                </div>
                            </>
                        )}
                    </div>
                </div>

                {/* Finish Season */}
                <div className={`sim-card p-6 border-l-4 ${data.is_finished ? 'border-l-emerald-500' : 'border-l-slate-700'}`}>
                    <h3 className="text-xs font-black uppercase tracking-widest text-slate-400 mb-4">Saison abschließen</h3>
                    <label className="flex items-start gap-4 cursor-pointer">
                        <div onClick={() => setData('is_finished', !data.is_finished)}
                            className={`mt-0.5 w-10 h-5 rounded-full transition-colors relative flex-shrink-0 ${data.is_finished ? 'bg-emerald-500' : 'bg-slate-700'}`}>
                            <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${data.is_finished ? 'translate-x-5' : 'translate-x-0.5'}`} />
                        </div>
                        <div>
                            <p className="text-sm font-bold text-white">Saison als abgeschlossen markieren</p>
                            <p className="text-[10px] text-slate-500 mt-1 leading-relaxed">
                                Setzt `is_finished = true`, vergibt finale Tabellenränge und schreibt Achievements für alle Gewinner. Diese Aktion kann nicht rückgängig gemacht werden.
                            </p>
                        </div>
                    </label>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-4">
                    <button type="submit" disabled={processing} className="sim-btn-primary px-8 py-3 flex items-center gap-2">
                        <FloppyDisk size={18} weight="bold" />
                        Speichern
                    </button>
                    <Link href={route('admin.competitions.edit', cs.competition_id)} className="sim-btn-muted px-6 py-3">
                        Abbrechen
                    </Link>
                </div>
            </form>
        </AdminLayout>
    );
}
