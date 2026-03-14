import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, FloppyDisk, Users, PencilSimple } from '@phosphor-icons/react';

const FORMATIONS = ['4-3-3', '4-4-2', '4-2-3-1', '3-5-2', '3-4-3', '5-3-2', '5-4-1', '4-5-1', '4-1-4-1', '4-3-2-1'];

export default function Form({ lineup, players, clubs }) {
    const isEdit = !!lineup;

    const { data, setData, post, put, processing, errors } = useForm({
        club_id:          lineup?.club_id          ?? '',
        name:             lineup?.name             ?? '',
        formation:        lineup?.formation        ?? '4-3-3',
        notes:            lineup?.notes            ?? '',
        is_active:        lineup?.is_active        ?? false,
        selected_players: lineup?.players?.map(p => p.id) ?? [],
        pitch_positions:  lineup?.players?.reduce((acc, p) => {
            acc[p.id] = p.pivot?.pitch_position ?? '';
            return acc;
        }, {}) ?? {},
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.lineups.update', lineup.id));
        } else {
            post(route('admin.lineups.store'));
        }
    };

    const togglePlayer = (playerId) => {
        setData(prev => {
            const selected = prev.selected_players.includes(playerId)
                ? prev.selected_players.filter(id => id !== playerId)
                : [...prev.selected_players, playerId];
            return { ...prev, selected_players: selected };
        });
    };

    const setPitchPosition = (playerId, position) => {
        setData(prev => ({
            ...prev,
            pitch_positions: { ...prev.pitch_positions, [playerId]: position }
        }));
    };

    return (
        <AdminLayout
            header={
                <div className="flex items-center gap-4">
                    <Link href={route('admin.lineups.index')} className="p-2 text-[var(--text-muted)] hover:text-white hover:bg-slate-700 rounded-lg transition">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-widest text-cyan-400">Aufstellungen</p>
                        <h1 className="text-xl font-bold text-white">{isEdit ? lineup.name : 'Neue Aufstellung'}</h1>
                    </div>
                </div>
            }
        >
            <Head title={isEdit ? 'Aufstellung bearbeiten' : 'Neue Aufstellung'} />

            <form onSubmit={handleSubmit} className="space-y-6 max-w-4xl">
                {/* Basic Info */}
                <div className="sim-card p-6">
                    <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 mb-5">Allgemein</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {!isEdit && (
                            <div className="md:col-span-2">
                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Verein *</label>
                                <select className={`sim-select w-full ${errors.club_id ? 'border-red-500' : ''}`}
                                    value={data.club_id} onChange={e => setData('club_id', e.target.value)}>
                                    <option value="">— Verein wählen —</option>
                                    {clubs?.map(c => (
                                        <option key={c.id} value={c.id}>{c.name} ({c.user?.name ?? 'CPU'})</option>
                                    ))}
                                </select>
                                {errors.club_id && <p className="text-red-400 text-xs mt-1">{errors.club_id}</p>}
                            </div>
                        )}

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Name *</label>
                            <input type="text" className={`sim-input w-full ${errors.name ? 'border-red-500' : ''}`}
                                value={data.name} onChange={e => setData('name', e.target.value)} placeholder="z.B. Standardaufstellung" />
                            {errors.name && <p className="text-red-400 text-xs mt-1">{errors.name}</p>}
                        </div>

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Formation *</label>
                            <select className="sim-select w-full" value={data.formation} onChange={e => setData('formation', e.target.value)}>
                                {FORMATIONS.map(f => <option key={f} value={f}>{f}</option>)}
                            </select>
                        </div>

                        <div className="md:col-span-2">
                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Notizen</label>
                            <textarea rows={2} className="sim-input w-full" value={data.notes} onChange={e => setData('notes', e.target.value)}
                                placeholder="Optionale Anmerkungen..." />
                        </div>

                        <div>
                            <label className="flex items-center gap-3 cursor-pointer">
                                <div onClick={() => setData('is_active', !data.is_active)}
                                    className={`w-10 h-5 rounded-full transition-colors relative ${data.is_active ? 'bg-cyan-500' : 'bg-slate-700'}`}>
                                    <div className={`absolute top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform ${data.is_active ? 'translate-x-5' : 'translate-x-0.5'}`} />
                                </div>
                                <span className="text-sm font-bold text-slate-300">Als aktive Aufstellung setzen</span>
                            </label>
                        </div>
                    </div>
                </div>

                {/* Players */}
                {isEdit && players && (
                    <div className="sim-card p-6">
                        <div className="flex items-center justify-between mb-5">
                            <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 flex items-center gap-2">
                                <Users size={14} /> Spieler ({data.selected_players.length}/11)
                            </h3>
                            {errors.selected_players && <p className="text-red-400 text-xs">{errors.selected_players}</p>}
                        </div>

                        <div className="space-y-2">
                            {players.map(player => {
                                const isSelected = data.selected_players.includes(player.id);
                                return (
                                    <div key={player.id} className={`flex items-center gap-3 p-3 rounded-xl border transition-all ${isSelected ? 'bg-cyan-500/10 border-cyan-500/30' : 'bg-[var(--bg-content)]/20 border-[var(--border-muted)] hover:bg-[var(--bg-content)]/40'}`}>
                                        <input type="checkbox" checked={isSelected} onChange={() => togglePlayer(player.id)}
                                            className="w-4 h-4 accent-cyan-500 rounded cursor-pointer" />
                                        <img src={player.photo_url} className="h-8 w-8 rounded-lg object-cover border border-[var(--border-pillar)] bg-[var(--bg-pillar)]" alt="" />
                                        <div className="flex-1 min-w-0">
                                            <p className="text-sm font-bold text-white truncate">{player.full_name}</p>
                                            <p className="text-[10px] text-[var(--text-muted)]">{player.position} · OVR {player.overall}</p>
                                        </div>
                                        {isSelected && (
                                            <input
                                                type="text"
                                                placeholder="Pitch-Position (z.B. ST)"
                                                value={data.pitch_positions[player.id] ?? ''}
                                                onChange={e => setPitchPosition(player.id, e.target.value)}
                                                className="sim-input w-28 text-xs py-1"
                                            />
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                    </div>
                )}

                {/* Actions */}
                <div className="flex items-center gap-4">
                    <button type="submit" disabled={processing} className="sim-btn-primary px-8 py-3 flex items-center gap-2">
                        <FloppyDisk size={18} weight="bold" />
                        {isEdit ? 'Änderungen speichern' : 'Aufstellung erstellen'}
                    </button>
                    <Link href={route('admin.lineups.index')} className="sim-btn-muted px-6 py-3">Abbrechen</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
