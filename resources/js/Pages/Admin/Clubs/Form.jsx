import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { 
    Shield, User, Coins, Money, 
    Crown, Sword, ArrowLeft, FloppyDisk,
    Trash, Warning, Info, Image,
    Robot
} from '@phosphor-icons/react';

const Card = ({ title, children, icon: Icon, color = 'cyan' }) => (
    <div className="sim-card p-6">
        <div className="flex items-center gap-3 mb-6">
            <div className={`p-2 rounded-lg bg-${color}-500/10 border border-${color}-500/20`}>
                <Icon size={20} className={`text-${color}-500`} />
            </div>
            <h3 className="text-lg font-bold text-white tracking-tight leading-none uppercase italic">{title}</h3>
        </div>
        {children}
    </div>
);

export default function Form({ club, users, clubs, rolePlayers }) {
    const isEdit = !!club;
    
    const { data, setData, post, processing, errors } = useForm({
        user_id: club?.user_id || (users.length > 0 ? users[0].id : ''),
        name: club?.name || '',
        short_name: club?.short_name || '',
        logo: null,
        country: club?.country || 'Deutschland',
        league: club?.league || 'Amateurliga',
        founded_year: club?.founded_year || '',
        budget: club?.budget || 500000,
        coins: club?.coins || 0,
        wage_budget: club?.wage_budget || 250000,
        reputation: club?.reputation || 50,
        fan_mood: club?.fan_mood || 50,
        season_objective: club?.season_objective || 'mid_table',
        captain_player_id: club?.captain_player_id || '',
        vice_captain_player_id: club?.vice_captain_player_id || '',
        is_cpu: club ? !!club.is_cpu : false,
        notes: club?.notes || '',
        rival_id_1: club?.rival_id_1 || '',
        rival_id_2: club?.rival_id_2 || '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            router.post(route('admin.clubs.update', club.id), {
                _method: 'PUT',
                ...data,
            }, {
                forceFormData: true,
            });
        } else {
            post(route('admin.clubs.store'));
        }
    };

    const deleteClub = () => {
        if (confirm('Möchtest du diesen Verein wirklich löschen? Alle zugehörigen Daten gehen verloren.')) {
            router.delete(route('admin.clubs.destroy', club.id));
        }
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? `${club.name} bearbeiten` : 'Verein erstellen'} />

            <div className="max-w-6xl mx-auto space-y-8 pb-20 px-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={route('admin.clubs.index')}
                            className="p-2 rounded-xl bg-[var(--bg-content)] text-[var(--text-muted)] hover:text-white transition"
                        >
                            <ArrowLeft size={20} weight="bold" />
                        </Link>
                        <div>
                            <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">
                                {isEdit ? 'Verein bearbeiten' : 'Neuer Verein'}
                            </h2>
                            <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">
                                {isEdit ? club.name : 'Manueller Vereins-Setup'}
                            </p>
                        </div>
                    </div>
                    {isEdit && club.logo_url && (
                        <div className="h-16 w-16 p-2 rounded-2xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)]">
                            <img src={club.logo_url} className="h-full w-full object-contain" alt="" />
                        </div>
                    )}
                </div>

                <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div className="lg:col-span-2 space-y-8">
                        <Card title="Stammdaten" icon={Shield}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-1 mt-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Owner User</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.user_id}
                                        onChange={e => setData('user_id', e.target.value)}
                                        required
                                    >
                                        {users.map(u => (
                                            <option key={u.id} value={u.id}>{u.name} ({u.email}) {u.is_admin ? '[ADMIN]' : ''}</option>
                                        ))}
                                    </select>
                                    {errors.user_id && <p className="text-rose-500 text-[9px] font-bold">{errors.user_id}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Vereinsname</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.name}
                                        onChange={e => setData('name', e.target.value)}
                                        required
                                    />
                                    {errors.name && <p className="text-rose-500 text-[9px] font-bold">{errors.name}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Kurzname (z.B. FCB)</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.short_name}
                                        onChange={e => setData('short_name', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Logo Upload</label>
                                    <div className="flex items-center gap-4 p-3 rounded-xl bg-[var(--sim-shell-bg)]/50 border border-[var(--border-pillar)] group relative">
                                        <input 
                                            type="file"
                                            className="absolute inset-0 opacity-0 cursor-pointer"
                                            onChange={e => setData('logo', e.target.files[0])}
                                        />
                                        <div className="p-1.5 rounded-lg bg-[var(--bg-content)] text-[var(--text-muted)] group-hover:text-cyan-400 transition">
                                            <Image size={16} />
                                        </div>
                                        <div className="text-[10px] text-[var(--text-muted)] font-bold truncate">
                                            {data.logo ? data.logo.name : 'Bild wählen...'}
                                        </div>
                                    </div>
                                    {errors.logo && <p className="text-rose-500 text-[9px] font-bold">{errors.logo}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Land</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.country}
                                        onChange={e => setData('country', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Liga Bezeichnung</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.league}
                                        onChange={e => setData('league', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Gründungsjahr</label>
                                    <input 
                                        type="number"
                                        className="sim-input w-full"
                                        value={data.founded_year}
                                        onChange={e => setData('founded_year', e.target.value)}
                                    />
                                </div>

                                <div className="flex items-end pb-1.5">
                                    <label className="flex items-center gap-3 cursor-pointer group p-3 rounded-xl bg-[var(--bg-pillar)] overflow-hidden border border-[var(--border-pillar)] w-full active:scale-95 transition">
                                        <div className={`w-9 h-5 rounded-full p-0.5 transition-colors ${data.is_cpu ? 'bg-amber-500' : 'bg-slate-700'}`}>
                                            <div className={`w-4 h-4 bg-white rounded-full transition-transform ${data.is_cpu ? 'translate-x-4' : 'translate-x-0'}`} />
                                        </div>
                                        <input 
                                            type="checkbox" 
                                            className="hidden" 
                                            checked={data.is_cpu}
                                            onChange={e => setData('is_cpu', e.target.checked)}
                                        />
                                        <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-white transition-colors">CPU-Gesteuert</span>
                                    </label>
                                </div>
                            </div>
                        </Card>

                        <Card title="Finanzen & Erfolg" icon={Coins} color="amber">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Money size={12} />
                                        Transferbudget (€)
                                    </label>
                                    <input 
                                        type="number"
                                        className="sim-input w-full text-amber-400"
                                        value={data.budget}
                                        onChange={e => setData('budget', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Money size={12} />
                                        Gehaltsbudget (€/Woche)
                                    </label>
                                    <input 
                                        type="number"
                                        className="sim-input w-full text-amber-400"
                                        value={data.wage_budget}
                                        onChange={e => setData('wage_budget', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Coins</label>
                                    <input 
                                        type="number"
                                        className="sim-input w-full text-cyan-400"
                                        value={data.coins}
                                        onChange={e => setData('coins', e.target.value)}
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Reputation (1-99)</label>
                                    <input 
                                        type="number"
                                        min="1"
                                        max="99"
                                        className="sim-input w-full"
                                        value={data.reputation}
                                        onChange={e => setData('reputation', e.target.value)}
                                        required
                                    />
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Saisonziel</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.season_objective}
                                        onChange={e => setData('season_objective', e.target.value)}
                                    >
                                        <option value="avoid_relegation">Klassenerhalt</option>
                                        <option value="mid_table">Mittelfeld</option>
                                        <option value="promotion">Aufstieg</option>
                                        <option value="title">Meisterschaft</option>
                                        <option value="cup_run">Pokalrunde</option>
                                    </select>
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Fan Stimmung (1-100)</label>
                                    <input 
                                        type="number"
                                        min="1"
                                        max="100"
                                        className="sim-input w-full"
                                        value={data.fan_mood}
                                        onChange={e => setData('fan_mood', e.target.value)}
                                        required
                                    />
                                </div>
                            </div>
                        </Card>
                    </div>

                    <div className="space-y-8">
                        <Card title="Rollen & Rivalen" icon={Crown} color="indigo">
                            <div className="space-y-6">
                                {isEdit && rolePlayers.length > 0 ? (
                                    <>
                                        <div className="space-y-1">
                                            <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Kapitän</label>
                                            <select 
                                                className="sim-select w-full text-xs"
                                                value={data.captain_player_id}
                                                onChange={e => setData('captain_player_id', e.target.value)}
                                            >
                                                <option value="">-- Kein Spieler --</option>
                                                {rolePlayers.map(p => (
                                                    <option key={p.id} value={p.id}>{p.full_name} ({p.position_main || p.position} | OVR {p.overall})</option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="space-y-1">
                                            <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Vize-Kapitän</label>
                                            <select 
                                                className="sim-select w-full text-xs"
                                                value={data.vice_captain_player_id}
                                                onChange={e => setData('vice_captain_player_id', e.target.value)}
                                            >
                                                <option value="">-- Kein Spieler --</option>
                                                {rolePlayers.map(p => (
                                                    <option key={p.id} value={p.id}>{p.full_name} ({p.position_main || p.position} | OVR {p.overall})</option>
                                                ))}
                                            </select>
                                        </div>
                                    </>
                                ) : isEdit && (
                                    <div className="p-3 rounded-xl bg-[var(--bg-pillar)]/50 border border-[var(--border-pillar)] text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest text-center">
                                        Keine Spieler im Verein
                                    </div>
                                )}

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Sword size={12} className="text-rose-500" />
                                        Erzrivale 1
                                    </label>
                                    <select 
                                        className="sim-select w-full text-xs"
                                        value={data.rival_id_1}
                                        onChange={e => setData('rival_id_1', e.target.value)}
                                    >
                                        <option value="">-- Kein Rivale --</option>
                                        {clubs.map(c => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Sword size={12} className="text-rose-500" />
                                        Erzrivale 2
                                    </label>
                                    <select 
                                        className="sim-select w-full text-xs"
                                        value={data.rival_id_2}
                                        onChange={e => setData('rival_id_2', e.target.value)}
                                    >
                                        <option value="">-- Kein Rivale --</option>
                                        {clubs.map(c => (
                                            <option key={c.id} value={c.id}>{c.name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                        </Card>

                        <div className="sim-card p-6 space-y-6">
                            <div className="space-y-1">
                                <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Spezielle Notizen</label>
                                <textarea 
                                    className="sim-textarea w-full text-xs h-32"
                                    value={data.notes}
                                    onChange={e => setData('notes', e.target.value)}
                                    placeholder="..."
                                />
                            </div>

                            <button 
                                type="submit" 
                                disabled={processing}
                                className="sim-btn-primary w-full py-4 flex items-center justify-center gap-3 text-sm"
                            >
                                <FloppyDisk size={20} weight="bold" />
                                {isEdit ? 'Änderungen speichern' : 'Verein anlegen'}
                            </button>
                        </div>

                        {isEdit && (
                             <button 
                                type="button"
                                onClick={deleteClub}
                                className="w-full p-4 rounded-2xl border border-rose-500/20 bg-rose-500/5 text-rose-500 text-[10px] font-black uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all shadow-sm"
                            >
                                <div className="flex items-center justify-center gap-2">
                                    <Trash size={16} />
                                    Verein Löschen
                                </div>
                            </button>
                        )}
                    </div>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black rounded-2xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_25px_rgba(34,211,238,0.25)] disabled:opacity-50 disabled:scale-100;
                }
            `}} />
        </AdminLayout>
    );
}
