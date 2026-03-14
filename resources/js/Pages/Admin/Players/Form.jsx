import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { 
    User, IdentificationBadge, ChartBar, 
    Coins, ArrowLeft, FloppyDisk, Image,
    Trash, Warning, Info, SoccerBall,
    Sneaker, Lightning, ShieldCheck, Heart
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

const AttributeInput = ({ label, value, onChange, icon: Icon, error }) => (
    <div className="space-y-1.5">
        <label className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
            {Icon && <Icon size={10} />}
            {label}
        </label>
        <div className="relative group">
            <input 
                type="number"
                min="1"
                max="100"
                className="sim-input w-full text-center font-black group-hover:border-cyan-500/30 transition-all border-[var(--border-muted)]"
                value={value}
                onChange={onChange}
                required
            />
            <div 
                className="absolute inset-x-0 -bottom-0.5 h-0.5 rounded-full bg-cyan-500/30 opacity-0 group-focus-within:opacity-100 transition-opacity"
                style={{ width: `${value}%` }}
            />
        </div>
        {error && <p className="text-rose-500 text-[8px] font-bold uppercase">{error}</p>}
    </div>
);

export default function Form({ player, clubs, positions }) {
    const isEdit = !!player;
    
    const { data, setData, post, processing, errors } = useForm({
        club_id: player?.club_id || (clubs.length > 0 ? clubs[0].id : ''),
        first_name: player?.first_name || '',
        last_name: player?.last_name || '',
        photo: null,
        position: player?.position || 'ZM',
        age: player?.age || 22,
        overall: player?.overall || 60,
        pace: player?.pace || 60,
        shooting: player?.shooting || 60,
        passing: player?.passing || 60,
        defending: player?.defending || 60,
        physical: player?.physical || 60,
        stamina: player?.stamina || 80,
        morale: player?.morale || 60,
        market_value: player?.market_value || 1000000,
        salary: player?.salary || 15000,
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            router.post(route('admin.players.update', player.id), {
                _method: 'PUT',
                ...data,
            }, {
                forceFormData: true,
            });
        } else {
            post(route('admin.players.store'));
        }
    };

    const deletePlayer = () => {
        if (confirm('Möchtest du diesen Spieler wirklich löschen?')) {
            router.delete(route('admin.players.destroy', player.id));
        }
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? `${player.full_name} bearbeiten` : 'Spieler erstellen'} />

            <div className="max-w-6xl mx-auto space-y-8 pb-32 px-4">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={route('admin.players.index')}
                            className="p-2 rounded-xl bg-[var(--bg-content)] text-[var(--text-muted)] hover:text-white transition"
                        >
                            <ArrowLeft size={20} weight="bold" />
                        </Link>
                        <div>
                            <h2 className="text-2xl font-black text-white tracking-tight uppercase italic border-l-4 border-cyan-500 pl-4">
                                {isEdit ? 'Profil Editieren' : 'Neuer Spieler'}
                            </h2>
                            <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1 pl-4">
                                {isEdit ? player.full_name : 'Global Player Registry'}
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    {/* Left Column: Personal Data */}
                    <div className="lg:col-span-8 lg:row-span-2 space-y-8">
                        <Card title="Personalien & Info" icon={IdentificationBadge}>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Vorname</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.first_name}
                                        onChange={e => setData('first_name', e.target.value)}
                                        required
                                    />
                                    {errors.first_name && <p className="text-rose-500 text-[9px] font-bold">{errors.first_name}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Nachname</label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full"
                                        value={data.last_name}
                                        onChange={e => setData('last_name', e.target.value)}
                                        required
                                    />
                                    {errors.last_name && <p className="text-rose-500 text-[9px] font-bold">{errors.last_name}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Aktueller Verein</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.club_id}
                                        onChange={e => setData('club_id', e.target.value)}
                                        required
                                    >
                                        {clubs.map(c => (
                                            <option key={c.id} value={c.id}>{c.name} ({c.user?.name || 'CPU'})</option>
                                        ))}
                                    </select>
                                    {errors.club_id && <p className="text-rose-500 text-[9px] font-bold">{errors.club_id}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Primäre Position</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.position}
                                        onChange={e => setData('position', e.target.value)}
                                        required
                                    >
                                        {Object.entries(positions).map(([key, label]) => (
                                            <option key={key} value={key}>{label} ({key})</option>
                                        ))}
                                    </select>
                                    {errors.position && <p className="text-rose-500 text-[9px] font-bold">{errors.position}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Alter</label>
                                    <input 
                                        type="number"
                                        min="15"
                                        max="45"
                                        className="sim-input w-full"
                                        value={data.age}
                                        onChange={e => setData('age', e.target.value)}
                                        required
                                    />
                                    {errors.age && <p className="text-rose-500 text-[9px] font-bold">{errors.age}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Foto Upload</label>
                                    <div className="relative group p-2.5 rounded-xl bg-[var(--sim-shell-bg)]/50 border border-[var(--border-muted)] hover:border-cyan-500/30 transition-all flex items-center gap-3">
                                        <input 
                                            type="file" 
                                            className="absolute inset-0 opacity-0 cursor-pointer"
                                            onChange={e => setData('photo', e.target.files[0])}
                                        />
                                        <div className="p-1 rounded bg-[var(--bg-content)] text-[var(--text-muted)]">
                                            <Image size={16} />
                                        </div>
                                        <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-wider truncate">
                                            {data.photo ? data.photo.name : 'Neues Foto wählen...'}
                                        </span>
                                    </div>
                                    {errors.photo && <p className="text-rose-500 text-[8px] font-bold">{errors.photo}</p>}
                                </div>
                            </div>
                        </Card>

                        <Card title="Attribute & Skills" icon={ChartBar} color="violet">
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-6 gap-y-8">
                                <div className="col-span-full mb-2">
                                    <p className="text-[10px] font-black text-cyan-500 uppercase tracking-[0.2em] mb-4">Hauptattribute</p>
                                    <div className="p-6 rounded-2xl bg-cyan-500/5 border border-cyan-500/10 flex items-center justify-between group">
                                         <div>
                                            <p className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">Stärkerating (OVR)</p>
                                            <p className="text-[9px] text-slate-600 font-bold uppercase tracking-widest">Wichtigster Skill-Indicator</p>
                                         </div>
                                         <input 
                                            type="number"
                                            className="bg-transparent border-none text-4xl font-black text-white w-24 text-right focus:ring-0 group-hover:text-cyan-400 transition-colors"
                                            value={data.overall}
                                            onChange={e => setData('overall', e.target.value)}
                                            required
                                         />
                                    </div>
                                </div>

                                <AttributeInput label="Tempo" value={data.pace} onChange={e => setData('pace', e.target.value)} icon={Lightning} error={errors.pace} />
                                <AttributeInput label="Schuss" value={data.shooting} onChange={e => setData('shooting', e.target.value)} icon={SoccerBall} error={errors.shooting} />
                                <AttributeInput label="Passen" value={data.passing} onChange={e => setData('passing', e.target.value)} icon={Sneaker} error={errors.passing} />
                                <AttributeInput label="Defensive" value={data.defending} onChange={e => setData('defending', e.target.value)} icon={ShieldCheck} error={errors.defending} />
                                <AttributeInput label="Physis" value={data.physical} onChange={e => setData('physical', e.target.value)} icon={ChartBar} error={errors.physical} />
                                <AttributeInput label="Ausdauer" value={data.stamina} onChange={e => setData('stamina', e.target.value)} icon={Heart} error={errors.stamina} />
                                <AttributeInput label="Moral" value={data.morale} onChange={e => setData('morale', e.target.value)} icon={Info} error={errors.morale} />
                            </div>
                        </Card>
                    </div>

                    {/* Right Column: Financials & Meta */}
                    <div className="lg:col-span-4 space-y-8">
                        <Card title="Werte & Finanzen" icon={Coins} color="amber">
                            <div className="space-y-6">
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Marktwert (€)</label>
                                    <div className="relative group">
                                        <input 
                                            type="number"
                                            className="sim-input w-full text-lg font-black text-amber-400 pl-8"
                                            value={data.market_value}
                                            onChange={e => setData('market_value', e.target.value)}
                                            required
                                        />
                                        <Coins size={16} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-amber-500/50" />
                                    </div>
                                    {errors.market_value && <p className="text-rose-500 text-[9px] font-bold">{errors.market_value}</p>}
                                </div>

                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Gehalt / Woche (€)</label>
                                    <div className="relative group">
                                        <input 
                                            type="number"
                                            className="sim-input w-full text-lg font-black text-emerald-400 pl-8"
                                            value={data.salary}
                                            onChange={e => setData('salary', e.target.value)}
                                            required
                                        />
                                        <Coins size={16} className="absolute left-2.5 top-1/2 -translate-y-1/2 text-emerald-500/50" />
                                    </div>
                                    {errors.salary && <p className="text-rose-500 text-[9px] font-bold">{errors.salary}</p>}
                                </div>
                            </div>
                        </Card>

                        <div className="sim-card p-6 space-y-6">
                             <div className="p-4 rounded-xl bg-[var(--bg-pillar)]/50 border border-[var(--border-pillar)] border-dashed">
                                <h4 className="text-[10px] font-black text-white uppercase tracking-widest mb-4 flex items-center gap-2">
                                    <Info size={14} className="text-cyan-500" />
                                    Admin Info
                                </h4>
                                <p className="text-[10px] text-[var(--text-muted)] leading-relaxed font-bold uppercase tracking-tighter">
                                    Änderungen an den Attributen wirken sich unmittelbar auf die Simulation aus.
                                    Nutze den OVR als primären Balancer.
                                </p>
                             </div>

                             <button 
                                type="submit" 
                                disabled={processing}
                                className="sim-btn-primary w-full py-4 flex items-center justify-center gap-3 text-sm"
                            >
                                <FloppyDisk size={20} weight="bold" />
                                {isEdit ? 'Profil Aktualisieren' : 'Spieler Anlegen'}
                            </button>
                        </div>

                        {isEdit && (
                            <button 
                                type="button"
                                onClick={deletePlayer}
                                className="w-full p-4 rounded-2xl border border-rose-500/20 bg-rose-500/5 text-rose-500 text-[10px] font-black uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all"
                            >
                                Spieler Löschen
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
