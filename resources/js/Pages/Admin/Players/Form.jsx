import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { 
    User, IdentificationBadge, ChartBar, 
    Coins, ArrowLeft, FloppyDisk, Image,
    Trash, Warning, Info, SoccerBall,
    Sneaker, Lightning, ShieldCheck, Heart,
    Robot, Broadcast, ArrowsLeftRight
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

const TabButton = ({ active, onClick, icon: Icon, children }) => (
    <button
        type="button"
        onClick={onClick}
        className={`flex items-center gap-2.5 border-b-2 px-6 py-4 text-[10px] font-black uppercase tracking-widest transition-all ${
            active 
                ? 'border-cyan-500 bg-cyan-500/5 text-cyan-400' 
                : 'border-transparent text-[var(--text-muted)] hover:bg-white/5 hover:text-slate-300'
        }`}
    >
        <Icon size={16} weight={active ? 'fill' : 'bold'} />
        {children}
    </button>
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
    const [activeTab, setActiveTab] = useState('general');
    
    const { data, setData, post, processing, errors } = useForm({
        club_id: player?.club_id || (clubs.length > 0 ? clubs[0].id : ''),
        first_name: player?.first_name || '',
        last_name: player?.last_name || '',
        photo: null,
        position: player?.position ?? 'ZM',
        age: player?.age ?? 22,
        birthday: player?.birthday ? player.birthday.split('T')[0] : '',
        height: player?.height || '',
        shirt_number: player?.shirt_number || '',
        preferred_foot: player?.preferred_foot || 'right',
        overall: player?.overall ?? 60,
        potential: player?.potential ?? 70,
        market_value: player?.market_value ?? 1000000,
        attr_market: player?.attr_market ?? 100,
        salary: player?.salary ?? 15000,
        is_imported: player ? !!player.is_imported : false,
        player_style: player?.player_style || 'Allrounder',
        transfermarkt_id: player?.transfermarkt_id || '',
        sofascore_id: player?.sofascore_id || '',
        sofascore_url: player?.sofascore_url || '',
        position_second: player?.position_second || '',
        position_third: player?.position_third ?? '',
        attr_attacking: player?.attr_attacking ?? 100,
        attr_technical: player?.attr_technical ?? 100,
        attr_tactical: player?.attr_tactical ?? 100,
        attr_defending: player?.attr_defending ?? 100,
        attr_creativity: player?.attr_creativity ?? 100,
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

    const handleSyncSofascore = () => {
        if (!player) return;
        router.post(route('admin.players.sync-sofascore', player.id), {}, { preserveScroll: true });
    };

    const handleSyncHistory = () => {
        if (!player) return;
        router.post(route('admin.players.sync-history', player.id), {}, { preserveScroll: true });
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

                <div className="sim-card no-scrollbar flex items-center overflow-x-auto bg-black/20 p-0 mb-8 border-[var(--border-muted)]">
                    <TabButton active={activeTab === 'general'} onClick={() => setActiveTab('general')} icon={User}>Stammdaten</TabButton>
                    <TabButton active={activeTab === 'skills'} onClick={() => setActiveTab('skills')} icon={ChartBar}>Simulation & Skills</TabButton>
                    {isEdit && (
                        <TabButton active={activeTab === 'sync'} onClick={() => setActiveTab('sync')} icon={Lightning}>Externe Synchronisation</TabButton>
                    )}
                </div>

                <form onSubmit={submit} className="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <div className="lg:col-span-8 lg:row-span-2 space-y-8">
                    {activeTab === 'general' && (
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
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">2. Position</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.position_second}
                                        onChange={e => setData('position_second', e.target.value)}
                                    >
                                        <option value="">- Keine -</option>
                                        {Object.entries(positions).map(([key, label]) => (
                                            <option key={key} value={key}>{label} ({key})</option>
                                        ))}
                                    </select>
                                    {errors.position_second && <p className="text-rose-500 text-[9px] font-bold">{errors.position_second}</p>}
                                </div>
                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">3. Position</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.position_third}
                                        onChange={e => setData('position_third', e.target.value)}
                                    >
                                        <option value="">- Keine -</option>
                                        {Object.entries(positions).map(([key, label]) => (
                                            <option key={key} value={key}>{label} ({key})</option>
                                        ))}
                                    </select>
                                    {errors.position_third && <p className="text-rose-500 text-[9px] font-bold">{errors.position_third}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Geburtsdatum</label>
                                    <input 
                                        type="date"
                                        className="sim-input w-full"
                                        value={data.birthday}
                                        onChange={e => {
                                            const val = e.target.value;
                                            setData('birthday', val);
                                            // Simple age calculation for UI
                                            if (val) {
                                                const birthDate = new Date(val);
                                                const today = new Date();
                                                let age = today.getFullYear() - birthDate.getFullYear();
                                                const m = today.getMonth() - birthDate.getMonth();
                                                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                                                    age--;
                                                }
                                                setData('age', age);
                                            }
                                        }}
                                    />
                                    {errors.birthday && <p className="text-rose-500 text-[9px] font-bold">{errors.birthday}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Alter</label>
                                    <input 
                                        type="number"
                                        min="15"
                                        max="45"
                                        className="sim-input w-full bg-slate-800/50 cursor-not-allowed"
                                        value={data.age}
                                        readOnly
                                    />
                                    {errors.age && <p className="text-rose-500 text-[9px] font-bold">{errors.age}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Größe (cm)</label>
                                    <input 
                                        type="number"
                                        min="150"
                                        max="220"
                                        className="sim-input w-full"
                                        value={data.height}
                                        onChange={e => setData('height', e.target.value)}
                                    />
                                    {errors.height && <p className="text-rose-500 text-[9px] font-bold">{errors.height}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Trikotnummer</label>
                                    <input 
                                        type="number"
                                        min="1"
                                        max="99"
                                        className="sim-input w-full"
                                        value={data.shirt_number}
                                        onChange={e => setData('shirt_number', e.target.value)}
                                    />
                                    {errors.shirt_number && <p className="text-rose-500 text-[9px] font-bold">{errors.shirt_number}</p>}
                                </div>

                                <div className="space-y-1">
                                    <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Starker Fuß</label>
                                    <select 
                                        className="sim-select w-full"
                                        value={data.preferred_foot}
                                        onChange={e => setData('preferred_foot', e.target.value)}
                                        required
                                    >
                                        <option value="right">Rechts</option>
                                        <option value="left">Links</option>
                                        <option value="both">Beidfüßig</option>
                                    </select>
                                    {errors.preferred_foot && <p className="text-rose-500 text-[9px] font-bold">{errors.preferred_foot}</p>}
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
                    )}

                    {activeTab === 'skills' && (
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
                    )}

                    {isEdit && activeTab === 'sync' && (
                        <div className="space-y-8">
                            <Card title="Schnittstellen & IDs" icon={IdentificationBadge}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                    </div>
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                            <Broadcast size={10} />
                                            Sofascore ID
                                        </label>
                                        <input 
                                            type="text"
                                            className="sim-input w-full font-mono text-indigo-400"
                                            value={data.sofascore_id}
                                            onChange={e => setData('sofascore_id', e.target.value)}
                                            placeholder="z.B. 70996"
                                        />
                                    </div>
                                    <div className="md:col-span-2 space-y-1">
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                            <Broadcast size={10} />
                                            Sofascore URL
                                        </label>
                                        <input 
                                            type="text"
                                            className="sim-input w-full font-mono text-indigo-400"
                                            value={data.sofascore_url}
                                            onChange={e => setData('sofascore_url', e.target.value)}
                                            placeholder="Full URL..."
                                        />
                                    </div>
                                </div>
                            </Card>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div className="sim-card p-6 border-cyan-500/20 bg-cyan-500/5">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                                            <Broadcast size={20} className="text-cyan-500" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="text-sm font-black text-white uppercase tracking-wider italic">Sofascore Sync</h3>
                                            <p className="text-[8px] text-[var(--text-muted)] uppercase font-bold">Synchronisiert Bio-Daten & Skills</p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        disabled={!data.sofascore_id}
                                        onClick={handleSyncSofascore}
                                        className="sim-btn-primary w-full py-3 flex items-center justify-center gap-2 text-[10px] disabled:opacity-30"
                                    >
                                        <Lightning size={16} weight="fill" />
                                        Attribute & Info laden
                                    </button>
                                </div>

                                <div className="sim-card p-6 border-amber-500/20 bg-amber-500/5">
                                    <div className="flex items-center gap-3 mb-4">
                                        <div className="p-2 rounded-lg bg-amber-500/10 border border-amber-500/20">
                                            <ArrowsLeftRight size={20} className="text-amber-500" />
                                        </div>
                                        <div className="flex-1">
                                            <h3 className="text-sm font-black text-white uppercase tracking-wider italic">History Sync</h3>
                                            <p className="text-[8px] text-[var(--text-muted)] uppercase font-bold">Transfer Historie von Transfermarkt</p>
                                        </div>
                                    </div>
                                    <button 
                                        type="button"
                                        disabled={!player?.tm_profile_url}
                                        onClick={handleSyncHistory}
                                        className="sim-btn-primary w-full py-3 from-amber-500 to-orange-600 shadow-[0_4px_25px_rgba(245,158,11,0.25)] flex items-center justify-center gap-2 text-[10px] disabled:opacity-30"
                                    >
                                        <SoccerBall size={16} weight="fill" />
                                        Transfers synchronisieren
                                    </button>
                                </div>
                            </div>

                            <Card title="Simulation Engine" icon={Robot} color="indigo">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-1">
                                        <label className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest px-1 flex items-center gap-1">
                                            <Robot size={10} />
                                            Spieler-Typ
                                        </label>
                                        <select 
                                            className="sim-select w-full bg-indigo-500/5 border-indigo-500/20 text-indigo-400 font-black italic"
                                            value={data.player_style}
                                            onChange={e => setData('player_style', e.target.value)}
                                        >
                                            <option value="Allrounder">Allrounder</option>
                                            <optgroup label="Torwart">
                                                <option value="Mitspielender Torwart">Mitspielender Torwart</option>
                                                <option value="Linien-Torwart">Linien-Torwart</option>
                                                <option value="Torwart-Spezialist">Torwart-Spezialist</option>
                                            </optgroup>
                                            <optgroup label="Verteidigung">
                                                <option value="Zweikampfmonster">Zweikampfmonster</option>
                                                <option value="Spielstarker IV">Spielstarker IV</option>
                                                <option value="Defensiv-Anker">Defensiv-Anker</option>
                                                <option value="Offensiv-Flitzer">Offensiv-Flitzer</option>
                                                <option value="Defensiv-Spezialist">Defensiv-Spezialist</option>
                                                <option value="Zweikampfstarker AV">Zweikampfstarker AV</option>
                                            </optgroup>
                                            <optgroup label="Mittelfeld">
                                                <option value="Box-to-Box">Box-to-Box</option>
                                                <option value="Regisseur">Regisseur</option>
                                                <option value="Abräumer">Abräumer</option>
                                                <option value="Strategischer DM">Strategischer DM</option>
                                                <option value="Dribbelkünstler">Dribbelkünstler</option>
                                                <option value="Spielgestalter">Spielgestalter</option>
                                                <option value="Flügel-Flitzer">Flügel-Flitzer</option>
                                            </optgroup>
                                            <optgroup label="Angriff">
                                                <option value="Knipser">Knipser</option>
                                                <option value="Zielspieler">Zielspieler</option>
                                                <option value="Dynamische Spitze">Dynamische Spitze</option>
                                            </optgroup>
                                        </select>
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
                                                Import-Markierung
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    )}
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
