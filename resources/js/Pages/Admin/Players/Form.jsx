import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { 
    User, IdentificationBadge, ChartBar, 
    Coins, ArrowLeft, FloppyDisk, Image,
    Trash, Warning, Info, SoccerBall,
    Sneaker, Lightning, ShieldCheck, Heart,
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
        potential: player?.potential || 70,
        market_value: player?.market_value || 1000000,
        attr_market: player?.attr_market || 50,
        salary: player?.salary || 15000,
        is_imported: player ? !!player.is_imported : false,
        player_style: player?.player_style || 'Allrounder',
        transfermarkt_id: player?.transfermarkt_id || '',
        sofascore_id: player?.sofascore_id || '',
        attr_attacking: player?.attr_attacking || 50,
        attr_technical: player?.attr_technical || 50,
        attr_tactical: player?.attr_tactical || 50,
        attr_defending: player?.attr_defending || 50,
        attr_creativity: player?.attr_creativity || 50,
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

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <IdentificationBadge size={10} />
                                        Transfermarkt ID
                                    </label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full font-mono text-cyan-400"
                                        value={data.transfermarkt_id}
                                        onChange={e => setData('transfermarkt_id', e.target.value)}
                                        placeholder="z.B. 12345"
                                    />
                                    {errors.transfermarkt_id && <p className="text-rose-500 text-[9px] font-bold">{errors.transfermarkt_id}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <ChartBar size={10} />
                                        Sofascore ID
                                    </label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full font-mono text-indigo-400"
                                        value={data.sofascore_id}
                                        onChange={e => setData('sofascore_id', e.target.value)}
                                        placeholder="z.B. 70996"
                                    />
                                    {errors.sofascore_id && <p className="text-rose-500 text-[9px] font-bold">{errors.sofascore_id}</p>}
                                </div>
                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Robot size={10} />
                                        Spieler-Typ (Auto)
                                    </label>
                                    <input 
                                        type="text"
                                        className="sim-input w-full bg-indigo-500/5 border-indigo-500/20 text-indigo-400 font-black italic"
                                        value={data.player_style}
                                        onChange={e => setData('player_style', e.target.value)}
                                        placeholder="Wird automatisch berechnet..."
                                    />
                                </div>
                                <div className="flex items-end pb-1.5 ">
                                    <label className="flex items-center gap-3 cursor-pointer group p-3 rounded-xl bg-[var(--bg-pillar)] overflow-hidden border border-[var(--border-pillar)] w-full active:scale-95 transition">
                                        <div className={`w-9 h-5 rounded-full p-0.5 transition-colors ${data.is_imported ? 'bg-cyan-500' : 'bg-slate-700'}`}>
                                            <div className={`w-4 h-4 bg-white rounded-full transition-transform ${data.is_imported ? 'translate-x-4' : 'translate-x-0'}`} />
                                        </div>
                                        <input 
                                            type="checkbox" 
                                            className="hidden" 
                                            checked={data.is_imported}
                                            onChange={e => setData('is_imported', e.target.checked)}
                                        />
                                        <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] group-hover:text-white transition-colors flex items-center gap-1">
                                            <Robot size={12} />
                                            Importiert
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </Card>

                        <Card title="Attribute & Skills" icon={ChartBar} color="violet">
                            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-6 gap-y-8">
                                <div className="col-span-full space-y-4 mb-2">
                                    <p className="text-[10px] font-black text-cyan-500 uppercase tracking-[0.2em]">Hauptattribute</p>
                                    
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

                                    <div className="p-6 rounded-2xl bg-amber-500/5 border border-amber-500/10 flex items-center justify-between group">
                                         <div>
                                            <p className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">Marktwert-Stärke</p>
                                            <p className="text-[9px] text-slate-600 font-bold uppercase tracking-widest">Basiert auf dem Marktwert (€)</p>
                                         </div>
                                         <input 
                                            type="number"
                                            className="bg-transparent border-none text-4xl font-black text-amber-500/80 w-24 text-right focus:ring-0 group-hover:text-amber-400 transition-colors"
                                            value={data.attr_market}
                                            onChange={e => setData('attr_market', e.target.value)}
                                            required
                                         />
                                    </div>
                                </div>


                                <div className="col-span-full mt-4 pt-4 border-t border-[var(--border-muted)] border-dashed">
                                    <p className="text-[10px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-4">Sofascore Attribute (Performance)</p>
                                    <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-6">
                                        <AttributeInput label="Attacking" value={data.attr_attacking} onChange={e => setData('attr_attacking', e.target.value)} color="indigo" />
                                        <AttributeInput label="Technical" value={data.attr_technical} onChange={e => setData('attr_technical', e.target.value)} color="indigo" />
                                        <AttributeInput label="Tactical" value={data.attr_tactical} onChange={e => setData('attr_tactical', e.target.value)} color="indigo" />
                                        <AttributeInput label="Defending" value={data.attr_defending} onChange={e => setData('attr_defending', e.target.value)} color="indigo" />
                                        <AttributeInput label="Creativity" value={data.attr_creativity} onChange={e => setData('attr_creativity', e.target.value)} color="indigo" />
                                    </div>
                                </div>
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
