import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    User, IdentificationCard, Target, 
    Lightning, Sword, Shield, 
    Heartbeat, TrendUp, Coins, 
    ArrowsClockwise, CaretLeft, Camera,
    FileText, UserCircle
} from '@phosphor-icons/react';

const Card = ({ title, children, icon: Icon }) => (
    <div className="sim-card p-6 border-slate-800/50 relative overflow-hidden h-full">
        <div className="absolute top-0 right-0 p-6 opacity-[0.03] pointer-events-none">
            {Icon && <Icon size={80} weight="fill" className="text-amber-500" />}
        </div>
        <div className="flex items-center gap-3 mb-6 relative z-10">
            <div className="p-2 bg-slate-800 rounded-lg">
                {Icon && <Icon size={20} className="text-amber-500" />}
            </div>
            <h3 className="text-sm font-black text-white uppercase tracking-[0.2em]">{title}</h3>
        </div>
        <div className="relative z-10 space-y-4">
            {children}
        </div>
    </div>
);

const InputGroup = ({ label, error, children }) => (
    <div className="space-y-1">
        <label className="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] px-1">{label}</label>
        {children}
        {error && <p className="text-rose-500 text-[10px] font-bold mt-1 px-1 uppercase tracking-widest">{error}</p>}
    </div>
);

const AttributeInput = ({ label, value, onChange, error, icon: Icon, min=1, max=99 }) => (
    <InputGroup label={label} error={error}>
        <div className="relative group">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                {Icon && <Icon size={16} className="text-slate-500 group-focus-within:text-amber-500 transition-colors" />}
            </div>
            <input 
                type="number" 
                min={min} max={max}
                value={value} 
                onChange={e => onChange(e.target.value)}
                className="sim-input-modern pl-10"
                required
            />
            <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span className="text-[10px] font-black text-slate-600">{value}</span>
            </div>
        </div>
    </InputGroup>
);

export default function Form({ player, clubs, positions }) {
    const isEdit = !!player;

    const { data, setData, post, processing, errors } = useForm({
        club_id: player?.club_id ?? (clubs.length > 0 ? clubs[0].id : ''),
        first_name: player?.first_name ?? '',
        last_name: player?.last_name ?? '',
        photo: null,
        position: player?.position ?? 'ZM',
        age: player?.age ?? 22,
        overall: player?.overall ?? 60,
        pace: player?.pace ?? 60,
        shooting: player?.shooting ?? 60,
        passing: player?.passing ?? 60,
        defending: player?.defending ?? 60,
        physical: player?.physical ?? 60,
        stamina: player?.stamina ?? 80,
        morale: player?.morale ?? 60,
        market_value: player?.market_value ?? 1000000,
        salary: player?.salary ?? 15000,
        _method: isEdit ? 'PUT' : 'POST',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            post(route('players.update', player.id));
        } else {
            post(route('players.store'));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={isEdit ? `${player.full_name} bearbeiten` : 'Neuen Spieler anlegen'} />

            <div className="max-w-6xl mx-auto pb-20">
                {/* Header Section */}
                <div className="flex items-center justify-between mb-8">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={isEdit ? route('players.show', player.id) : route('players.index')}
                            className="p-2 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl hover:text-white hover:border-slate-700 transition-all"
                        >
                            <CaretLeft size={24} weight="bold" />
                        </Link>
                        <div>
                            <p className="sim-section-title">Kader-Management</p>
                            <h1 className="text-3xl font-black text-white italic uppercase tracking-tight">
                                {isEdit ? 'Spieler-Akte bearbeiten' : 'Neuen Spieler verpflichten'}
                            </h1>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Identity & Basic Stats */}
                        <div className="lg:col-span-2 flex flex-col gap-6">
                            <Card title="Identität & Basis" icon={IdentificationCard}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <InputGroup label="Vorname" error={errors.first_name}>
                                        <input 
                                            type="text" 
                                            value={data.first_name} 
                                            onChange={e => setData('first_name', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                    <InputGroup label="Nachname" error={errors.last_name}>
                                        <input 
                                            type="text" 
                                            value={data.last_name} 
                                            onChange={e => setData('last_name', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                </div>

                                <div className="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <InputGroup label="Verein" error={errors.club_id}>
                                        <select 
                                            value={data.club_id} 
                                            onChange={e => setData('club_id', e.target.value)}
                                            className="sim-select-modern"
                                            required
                                        >
                                            {clubs.map(c => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                    </InputGroup>
                                    <InputGroup label="Haupt-Position" error={errors.position}>
                                        <select 
                                            value={data.position} 
                                            onChange={e => setData('position', e.target.value)}
                                            className="sim-select-modern"
                                            required
                                        >
                                            {Object.entries(positions).map(([key, label]) => (
                                                <option key={key} value={key}>{label} ({key})</option>
                                            ))}
                                        </select>
                                    </InputGroup>
                                    <InputGroup label="Alter" error={errors.age}>
                                        <input 
                                            type="number" 
                                            min="15" max="45"
                                            value={data.age} 
                                            onChange={e => setData('age', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                </div>
                            </Card>

                            <Card title="Physische & Mentale Verfassung" icon={Heartbeat}>
                                <div className="grid grid-cols-2 md:grid-cols-3 gap-6">
                                    <AttributeInput 
                                        label="Gesamt (OVR)" 
                                        value={data.overall} 
                                        onChange={val => setData('overall', val)}
                                        error={errors.overall}
                                        icon={TrendUp}
                                    />
                                    <AttributeInput 
                                        label="Ausdauer" 
                                        value={data.stamina} 
                                        onChange={val => setData('stamina', val)}
                                        error={errors.stamina}
                                        icon={Lightning}
                                        max={100}
                                    />
                                    <AttributeInput 
                                        label="Moral" 
                                        value={data.morale} 
                                        onChange={val => setData('morale', val)}
                                        error={errors.morale}
                                        icon={UserCircle}
                                        max={100}
                                    />
                                </div>
                            </Card>
                        </div>

                        {/* Photo & Profile */}
                        <div className="lg:col-span-1">
                            <Card title="Spielerprofil" icon={Camera}>
                                <div className="flex flex-col items-center gap-6 mb-6">
                                    <div className="relative group">
                                        <div className="h-32 w-32 rounded-3xl bg-slate-900 border-2 border-slate-800 overflow-hidden group-hover:border-amber-500/50 transition-all flex items-center justify-center p-1 shadow-2xl">
                                            {data.photo ? (
                                                <img 
                                                    src={URL.createObjectURL(data.photo)} 
                                                    className="h-full w-full object-cover rounded-2xl" 
                                                    alt="Preview" 
                                                />
                                            ) : isEdit && player.photo_url ? (
                                                <img 
                                                    src={player.photo_url} 
                                                    className="h-full w-full object-cover rounded-2xl" 
                                                    alt={player.full_name} 
                                                />
                                            ) : (
                                                <UserCircle size={64} className="text-slate-800" weight="fill" />
                                            )}
                                        </div>
                                        <input 
                                            type="file" 
                                            onChange={e => setData('photo', e.target.files[0])}
                                            className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            accept="image/*"
                                        />
                                        <div className="absolute -bottom-2 -right-2 bg-amber-600 p-2 rounded-xl shadow-lg border border-amber-500 group-hover:scale-110 transition-transform">
                                            <Camera size={16} className="text-black" weight="bold" />
                                        </div>
                                    </div>
                                    <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest text-center px-4">
                                        Lade ein Quadratisches Bild (PNG/JPG) für die beste Darstellung hoch.
                                    </p>
                                </div>

                                <div className="space-y-4 pt-4 border-t border-slate-800/50">
                                    <InputGroup label="Marktwert (€)" error={errors.market_value}>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Coins size={16} className="text-amber-500" weight="fill" />
                                            </div>
                                            <input 
                                                type="number" 
                                                min="0" step="0.01"
                                                value={data.market_value} 
                                                onChange={e => setData('market_value', e.target.value)}
                                                className="sim-input-modern pl-10"
                                                required
                                            />
                                        </div>
                                    </InputGroup>
                                    <InputGroup label="Monatsgehalt (€)" error={errors.salary}>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Lightning size={16} className="text-cyan-400" />
                                            </div>
                                            <input 
                                                type="number" 
                                                min="0" step="0.01"
                                                value={data.salary} 
                                                onChange={e => setData('salary', e.target.value)}
                                                className="sim-input-modern pl-10"
                                                required
                                            />
                                        </div>
                                    </InputGroup>
                                </div>
                            </Card>
                        </div>

                        {/* Attributes Matrix */}
                        <div className="lg:col-span-3">
                            <Card title="Leistungswerte (Attribute)" icon={Target}>
                                <div className="grid grid-cols-2 md:grid-cols-5 gap-6">
                                    <AttributeInput 
                                        label="Tempo" 
                                        value={data.pace} 
                                        onChange={val => setData('pace', val)}
                                        error={errors.pace}
                                        icon={Lightning}
                                    />
                                    <AttributeInput 
                                        label="Schuss" 
                                        value={data.shooting} 
                                        onChange={val => setData('shooting', val)}
                                        error={errors.shooting}
                                        icon={Sword}
                                    />
                                    <AttributeInput 
                                        label="Pass" 
                                        value={data.passing} 
                                        onChange={val => setData('passing', val)}
                                        error={errors.passing}
                                        icon={ArrowsClockwise}
                                    />
                                    <AttributeInput 
                                        label="Defensive" 
                                        value={data.defending} 
                                        onChange={val => setData('defending', val)}
                                        error={errors.defending}
                                        icon={Shield}
                                    />
                                    <AttributeInput 
                                        label="Physis" 
                                        value={data.physical} 
                                        onChange={val => setData('physical', val)}
                                        error={errors.physical}
                                        icon={UserCircle}
                                    />
                                </div>
                            </Card>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-between pt-8 border-t border-slate-800/50">
                        <Link 
                            href={isEdit ? route('players.show', player.id) : route('players.index')}
                            className="text-slate-500 hover:text-white font-bold uppercase text-xs tracking-[0.2em] px-4 transition-all"
                        >
                            Abbrechen
                        </Link>
                        <button 
                            type="submit" 
                            disabled={processing}
                            className="bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black font-black py-4 px-12 rounded-xl shadow-[0_0_40px_rgba(217,177,92,0.2)] hover:scale-[1.05] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-3 uppercase tracking-widest text-sm"
                        >
                            {processing && <ArrowsClockwise size={20} className="animate-spin" />}
                            {isEdit ? 'Profil aktualisieren' : 'Spieler verpflichten'}
                        </button>
                    </div>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-input-modern {
                    @apply w-full bg-slate-900/80 border-2 border-slate-800 rounded-xl px-4 py-3 text-white focus:border-amber-500/50 focus:bg-slate-900 transition-all outline-none font-medium text-sm placeholder:text-slate-700;
                }
                .sim-select-modern {
                    @apply w-full bg-slate-900/80 border-2 border-slate-800 rounded-xl px-4 py-3 text-white focus:border-amber-500/50 focus:bg-slate-900 transition-all outline-none font-bold text-sm cursor-pointer appearance-none shadow-sm;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23d9b15c' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: right 1rem center;
                    background-size: 1.2rem;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
