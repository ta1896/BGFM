import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    Suitcase, Globe, ChartBar, 
    Coins, Wallet, IdentificationBadge,
    FileText, Camera, ArrowsClockwise,
    CaretLeft, UserCircle
} from '@phosphor-icons/react';

const Card = ({ title, children, icon: Icon }) => (
    <div className="sim-card p-6 border-slate-800/50 relative overflow-hidden h-full">
        <div className="absolute top-0 right-0 p-6 opacity-[0.03] pointer-events-none">
            {Icon && <Icon size={80} weight="fill" className="text-cyan-400" />}
        </div>
        <div className="flex items-center gap-3 mb-6 relative z-10">
            <div className="p-2 bg-slate-800 rounded-lg">
                {Icon && <Icon size={20} className="text-cyan-400" />}
            </div>
            <h3 className="text-lg font-bold text-white uppercase tracking-wider">{title}</h3>
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

export default function Form({ club, rolePlayers = [] }) {
    const isEdit = !!club;

    const { data, setData, post, processing, errors } = useForm({
        name: club?.name ?? '',
        short_name: club?.short_name ?? '',
        logo: null,
        country: club?.country ?? 'Deutschland',
        league: club?.league ?? 'Amateurliga',
        founded_year: club?.founded_year ?? '',
        reputation: club?.reputation ?? 50,
        fan_mood: club?.fan_mood ?? 50,
        season_objective: club?.season_objective ?? 'mid_table',
        budget: club?.budget ?? 500000,
        coins: club?.coins ?? 0,
        wage_budget: club?.wage_budget ?? 250000,
        captain_player_id: club?.captain_player_id ?? '',
        vice_captain_player_id: club?.vice_captain_player_id ?? '',
        notes: club?.notes ?? '',
        _method: isEdit ? 'PUT' : 'POST', // Method spoofing for file uploads
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            // For file uploads in Laravel via PUT/PATCH, we often need to POST with _method spoofing
            post(route('clubs.update', club.id));
        } else {
            post(route('clubs.store'));
        }
    };

    return (
        <AuthenticatedLayout>
            <Head title={isEdit ? `${club.name} bearbeiten` : 'Neuen Verein anlegen'} />

            <div className="max-w-5xl mx-auto pb-20">
                {/* Header Section */}
                <div className="flex items-center justify-between mb-8">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={isEdit ? route('clubs.show', club.id) : route('clubs.index')}
                            className="p-2 bg-slate-900 border border-slate-800 text-slate-400 rounded-xl hover:text-white hover:border-slate-700 transition-all"
                        >
                            <CaretLeft size={24} weight="bold" />
                        </Link>
                        <div>
                            <p className="sim-section-title">Vereins-Management</p>
                            <h1 className="text-3xl font-black text-white italic uppercase">{isEdit ? 'Verein bearbeiten' : 'Gründung & Registrierung'}</h1>
                        </div>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {/* Basic Info */}
                        <div className="lg:col-span-2">
                            <Card title="Basis-Informationen" icon={IdentificationBadge}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <InputGroup label="Vereinsname" error={errors.name}>
                                        <input 
                                            type="text" 
                                            value={data.name} 
                                            onChange={e => setData('name', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                    <InputGroup label="Kurzname (Code)" error={errors.short_name}>
                                        <input 
                                            type="text" 
                                            value={data.short_name} 
                                            onChange={e => setData('short_name', e.target.value)}
                                            className="sim-input-modern"
                                            placeholder="z.B. FCB, BVB"
                                        />
                                    </InputGroup>
                                </div>
                                
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <InputGroup label="Land" error={errors.country}>
                                        <input 
                                            type="text" 
                                            value={data.country} 
                                            onChange={e => setData('country', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                    <InputGroup label="Liga" error={errors.league}>
                                        <input 
                                            type="text" 
                                            value={data.league} 
                                            onChange={e => setData('league', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                    <InputGroup label="Gründungsjahr" error={errors.founded_year}>
                                        <input 
                                            type="number" 
                                            value={data.founded_year} 
                                            onChange={e => setData('founded_year', e.target.value)}
                                            className="sim-input-modern"
                                        />
                                    </InputGroup>
                                </div>
                            </Card>
                        </div>

                        {/* Logo & Reputation */}
                        <div className="lg:col-span-1">
                            <Card title="Brand & Status" icon={Camera}>
                                <InputGroup label="Vereinslogo" error={errors.logo}>
                                    <div className="relative group">
                                        <input 
                                            type="file" 
                                            onChange={e => setData('logo', e.target.files[0])}
                                            className="absolute inset-0 opacity-0 cursor-pointer z-10"
                                            accept="image/*"
                                        />
                                        <div className="bg-slate-900 border-2 border-dashed border-slate-800 rounded-xl p-4 transition-all group-hover:border-cyan-500/50 flex items-center gap-4">
                                            {isEdit && club.logo_path && !data.logo && (
                                                <img src={club.logo_url} className="h-10 w-10 object-contain rounded-lg bg-slate-950 p-1" alt="Logo" />
                                            )}
                                            {data.logo && (
                                                <div className="h-10 w-10 bg-emerald-500/10 flex items-center justify-center rounded-lg">
                                                    <FileText size={20} className="text-emerald-400" />
                                                </div>
                                            )}
                                            <div className="flex-1 overflow-hidden">
                                                <p className="text-[10px] text-slate-300 font-bold uppercase tracking-widest truncate">
                                                    {data.logo ? data.logo.name : (isEdit ? 'Logo ändern' : 'Datei wählen')}
                                                </p>
                                                <p className="text-[9px] text-slate-500 font-medium">PNG, JPG bis 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                </InputGroup>

                                <div className="grid grid-cols-2 gap-4">
                                    <InputGroup label="Reputation" error={errors.reputation}>
                                        <input 
                                            type="number" 
                                            min="1" max="99"
                                            value={data.reputation} 
                                            onChange={e => setData('reputation', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                    <InputGroup label="Fan-Mood" error={errors.fan_mood}>
                                        <input 
                                            type="number" 
                                            min="1" max="100"
                                            value={data.fan_mood} 
                                            onChange={e => setData('fan_mood', e.target.value)}
                                            className="sim-input-modern"
                                            required
                                        />
                                    </InputGroup>
                                </div>
                            </Card>
                        </div>

                        {/* Finances & Objectives */}
                        <div className="lg:col-span-2">
                            <Card title="Finanzplan & Ziele" icon={Wallet}>
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <InputGroup label="Transferbudget (€)" error={errors.budget}>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Wallet size={16} className="text-slate-500" />
                                            </div>
                                            <input 
                                                type="number" 
                                                min="0" step="0.01"
                                                value={data.budget} 
                                                onChange={e => setData('budget', e.target.value)}
                                                className="sim-input-modern pl-10"
                                                required
                                            />
                                        </div>
                                    </InputGroup>
                                    <InputGroup label="Gehaltsbudget (€)" error={errors.wage_budget}>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <IdentificationBadge size={16} className="text-slate-500" />
                                            </div>
                                            <input 
                                                type="number" 
                                                min="0" step="0.01"
                                                value={data.wage_budget} 
                                                onChange={e => setData('wage_budget', e.target.value)}
                                                className="sim-input-modern pl-10"
                                                required
                                            />
                                        </div>
                                    </InputGroup>
                                    <InputGroup label="Start-Coins" error={errors.coins}>
                                        <div className="relative">
                                            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Coins size={16} className="text-amber-500" weight="fill" />
                                            </div>
                                            <input 
                                                type="number" 
                                                min="0" step="1"
                                                value={data.coins} 
                                                onChange={e => setData('coins', e.target.value)}
                                                className="sim-input-modern pl-10"
                                            />
                                        </div>
                                    </InputGroup>
                                </div>

                                <InputGroup label="Saisonziel" error={errors.season_objective}>
                                    <select 
                                        value={data.season_objective} 
                                        onChange={e => setData('season_objective', e.target.value)}
                                        className="sim-select-modern"
                                    >
                                        <option value="avoid_relegation">Abstiegskampf / Klassenerhalt</option>
                                        <option value="mid_table">Gesichertes Mittelfeld</option>
                                        <option value="promotion">Obere Tabellenhälfte / Aufstieg</option>
                                        <option value="title">Meisterschaftskampf</option>
                                        <option value="cup_run">Fokus auf Pokalerfolg</option>
                                    </select>
                                </InputGroup>
                            </Card>
                        </div>

                        {/* Staff & Roles (Only if rolePlayers exists) */}
                        <div className="lg:col-span-1">
                            <Card title="Spielführer & Rollen" icon={UserCircle}>
                                {rolePlayers.length > 0 ? (
                                    <div className="space-y-4">
                                        <InputGroup label="Kapitän" error={errors.captain_player_id}>
                                            <select 
                                                value={data.captain_player_id} 
                                                onChange={e => setData('captain_player_id', e.target.value)}
                                                className="sim-select-modern"
                                            >
                                                <option value="">Keiner gewählt</option>
                                                {rolePlayers.map(p => (
                                                    <option key={p.id} value={p.id}>{p.full_name} ({p.position} | OVR {p.overall})</option>
                                                ))}
                                            </select>
                                        </InputGroup>
                                        <InputGroup label="Vize-Kapitän" error={errors.vice_captain_player_id}>
                                            <select 
                                                value={data.vice_captain_player_id} 
                                                onChange={e => setData('vice_captain_player_id', e.target.value)}
                                                className="sim-select-modern"
                                            >
                                                <option value="">Keiner gewählt</option>
                                                {rolePlayers.map(p => (
                                                    <option key={p.id} value={p.id}>{p.full_name} ({p.position} | OVR {p.overall})</option>
                                                ))}
                                            </select>
                                        </InputGroup>
                                    </div>
                                ) : (
                                    <div className="text-center py-6 bg-slate-900 shadow-inner rounded-2xl border border-slate-800">
                                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest leading-relaxed px-4">
                                            Rollen können erst festgelegt werden, wenn Spieler im Verein vorhanden sind.
                                        </p>
                                    </div>
                                )}
                            </Card>
                        </div>

                        {/* Notes */}
                        <div className="col-span-full">
                            <Card title="Zusätzliche Notizen" icon={FileText}>
                                <InputGroup label="Internes Protokoll / Beschreibung" error={errors.notes}>
                                    <textarea 
                                        value={data.notes} 
                                        onChange={e => setData('notes', e.target.value)}
                                        className="sim-textarea-modern h-32"
                                        placeholder="Hier können Vereinsphilosophie, Taktikvorgaben oder andere Notizen festgehalten werden..."
                                    />
                                </InputGroup>
                            </Card>
                        </div>
                    </div>

                    {/* Actions */}
                    <div className="flex items-center justify-between pt-8 border-t border-slate-800/50">
                        <Link 
                            href={isEdit ? route('clubs.show', club.id) : route('clubs.index')}
                            className="text-slate-500 hover:text-white font-bold uppercase text-xs tracking-[0.2em] px-4 transition-all"
                        >
                            Abbrechen
                        </Link>
                        <button 
                            type="submit" 
                            disabled={processing}
                            className="bg-gradient-to-r from-cyan-600 to-indigo-600 text-white font-black py-4 px-12 rounded-xl shadow-[0_0_40px_rgba(8,145,178,0.2)] hover:scale-[1.05] active:scale-[0.98] transition-all disabled:opacity-50 flex items-center gap-3 uppercase tracking-widest text-sm"
                        >
                            {processing && <ArrowsClockwise size={20} className="animate-spin" />}
                            {isEdit ? 'Daten aktualisieren' : 'Verein gründen'}
                        </button>
                    </div>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-input-modern {
                    @apply w-full bg-slate-900/80 border-2 border-slate-800 rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 focus:bg-slate-900 transition-all outline-none font-medium text-sm placeholder:text-slate-700;
                }
                .sim-select-modern {
                    @apply w-full bg-slate-900/80 border-2 border-slate-800 rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 focus:bg-slate-900 transition-all outline-none font-bold text-sm cursor-pointer appearance-none shadow-sm;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23475569' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: right 1rem center;
                    background-size: 1.2rem;
                }
                .sim-textarea-modern {
                    @apply w-full bg-slate-900/80 border-2 border-slate-800 rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 focus:bg-slate-900 transition-all outline-none font-medium text-sm placeholder:text-slate-700 resize-none;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
