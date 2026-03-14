import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Trophy, Globe, Trash, ArrowLeft, 
    FloppyDisk, CalendarPlus, ListChecks,
    Warning, Play, PencilSimple
} from '@phosphor-icons/react';

const Card = ({ title, children, icon: Icon }) => (
    <div className="sim-card p-6">
        <div className="flex items-center gap-3 mb-6">
            <div className="p-2 rounded-lg bg-slate-800/50 border border-slate-700/30">
                <Icon size={20} className="text-cyan-400" />
            </div>
            <h3 className="text-lg font-bold text-white tracking-tight leading-none uppercase italic">{title}</h3>
        </div>
        {children}
    </div>
);

export default function Form({ competition, countries, availableSeasons }) {
    const isEdit = !!competition;
    
    const { data, setData, post, patch, processing, errors } = useForm({
        country_id: competition?.country_id || '',
        name: competition?.name || '',
        short_name: competition?.short_name || '',
        type: competition?.type || 'league',
        scope: competition?.scope || '',
        tier: competition?.tier || '',
        logo: null,
        is_active: competition ? !!competition.is_active : true,
    });

    const seasonForm = useForm({
        season_id: '',
        format: '',
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            // Inertia's patch method doesn't support file uploads easily as a raw patch request 
            // in some versions of Laravel/Inertia without _method spoofing if using multipart.
            // But usually we can just post to the update route with _method: 'PUT' if needed,
            // or use Inertia's post with forceFormData.
            router.post(route('admin.competitions.update', competition.id), {
                _method: 'PUT',
                ...data,
            }, {
                forceFormData: true,
            });
        } else {
            post(route('admin.competitions.store'));
        }
    };

    const addSeason = (e) => {
        e.preventDefault();
        seasonForm.post(route('admin.competitions.add-season', competition.id), {
            onSuccess: () => seasonForm.reset(),
        });
    };

    const deleteCompetition = () => {
        if (confirm('Möchtest du diesen Wettbewerb wirklich löschen?')) {
            router.delete(route('admin.competitions.destroy', competition.id));
        }
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? `${competition.name} bearbeiten` : 'Wettbewerb erstellen'} />

            <div className="max-w-5xl mx-auto space-y-8 pb-20">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={route('admin.competitions.index')}
                            className="p-2 rounded-xl bg-slate-800/50 text-slate-400 hover:text-white transition"
                        >
                            <ArrowLeft size={20} weight="bold" />
                        </Link>
                        <div>
                            <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">
                                {isEdit ? 'Wettbewerb bearbeiten' : 'Neuer Wettbewerb'}
                            </h2>
                            <p className="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">
                                {isEdit ? competition.name : 'Konfiguration eines neuen Ligen/Pokalsystems'}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div className="lg:col-span-2 space-y-8">
                        <form onSubmit={submit}>
                            <Card title="Basis-Konfiguration" icon={Trophy}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Land (Optional)</label>
                                        <select 
                                            className="sim-select w-full"
                                            value={data.country_id}
                                            onChange={e => setData('country_id', e.target.value)}
                                        >
                                            <option value="">- Kein Land (International) -</option>
                                            {countries.map(c => (
                                                <option key={c.id} value={c.id}>{c.name}</option>
                                            ))}
                                        </select>
                                        {errors.country_id && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.country_id}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Vollständiger Name</label>
                                        <input 
                                            type="text"
                                            className="sim-input w-full"
                                            value={data.name}
                                            onChange={e => setData('name', e.target.value)}
                                            required
                                        />
                                        {errors.name && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Kurzform (z.B. BL1)</label>
                                        <input 
                                            type="text"
                                            className="sim-input w-full"
                                            value={data.short_name}
                                            onChange={e => setData('short_name', e.target.value)}
                                        />
                                        {errors.short_name && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.short_name}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Typ</label>
                                        <select 
                                            className="sim-select w-full"
                                            value={data.type}
                                            onChange={e => setData('type', e.target.value)}
                                            required
                                        >
                                            <option value="league">Liga</option>
                                            <option value="cup">Pokal</option>
                                        </select>
                                        {errors.type && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.type}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Wettbewerbsebene</label>
                                        <select 
                                            className="sim-select w-full"
                                            value={data.scope}
                                            onChange={e => setData('scope', e.target.value)}
                                        >
                                            <option value="">Automatisch (nach Land)</option>
                                            <option value="national">National</option>
                                            <option value="international">International</option>
                                        </select>
                                        {errors.scope && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.scope}</p>}
                                    </div>

                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Stufe (Tier)</label>
                                        <input 
                                            type="number"
                                            min="1"
                                            max="10"
                                            className="sim-input w-full"
                                            value={data.tier}
                                            onChange={e => setData('tier', e.target.value)}
                                        />
                                        {errors.tier && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.tier}</p>}
                                    </div>

                                    <div className="space-y-2 md:col-span-2">
                                        <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Logo Upload</label>
                                        <div className="flex items-center gap-4 p-4 rounded-xl bg-slate-950/50 border-2 border-dashed border-slate-800 hover:border-slate-700 transition group relative">
                                            <input 
                                                type="file"
                                                className="absolute inset-0 opacity-0 cursor-pointer"
                                                onChange={e => setData('logo', e.target.files[0])}
                                            />
                                            {isEdit && competition.logo_url && !data.logo && (
                                                <img src={competition.logo_url} className="h-12 w-12 object-contain" />
                                            )}
                                            <div className="text-sm text-slate-500">
                                                {data.logo ? data.logo.name : 'Datei hierher ziehen oder klicken'}
                                            </div>
                                        </div>
                                        {errors.logo && <p className="text-rose-500 text-[10px] font-bold mt-1">{errors.logo}</p>}
                                    </div>
                                </div>

                                <div className="mt-8 pt-6 border-t border-slate-800/50 flex items-center justify-between">
                                    <label className="flex items-center gap-3 cursor-pointer group">
                                        <div className={`w-10 h-6 rounded-full p-1 transition-colors ${data.is_active ? 'bg-cyan-500' : 'bg-slate-700'}`}>
                                            <div className={`w-4 h-4 bg-white rounded-full transition-transform ${data.is_active ? 'translate-x-4' : 'translate-x-0'}`} />
                                        </div>
                                        <input 
                                            type="checkbox" 
                                            className="hidden" 
                                            checked={data.is_active}
                                            onChange={e => setData('is_active', e.target.checked)}
                                        />
                                        <span className="text-xs font-black uppercase tracking-widest text-slate-400 group-hover:text-white transition-colors">Wettbewerb Aktiv</span>
                                    </label>

                                    <button 
                                        type="submit" 
                                        disabled={processing}
                                        className="sim-btn-primary px-8 py-3 flex items-center gap-2"
                                    >
                                        <FloppyDisk size={18} weight="bold" />
                                        {isEdit ? 'Änderungen speichern' : 'Wettbewerb anlegen'}
                                    </button>
                                </div>
                            </Card>
                        </form>

                        {isEdit && (
                            <section className="sim-card border-rose-500/10 bg-rose-500/[0.02] p-6">
                                <div className="flex items-center justify-between">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 rounded-lg bg-rose-500/10 border border-rose-500/20">
                                            <Warning size={20} className="text-rose-500" />
                                        </div>
                                        <div>
                                            <h3 className="text-lg font-bold text-white tracking-tight leading-none">Gefahrenzone</h3>
                                            <p className="text-rose-100/40 text-[10px] font-bold uppercase tracking-widest mt-1">Löschen ist endgültig</p>
                                        </div>
                                    </div>
                                    <button 
                                        onClick={deleteCompetition}
                                        className="bg-rose-600 hover:bg-rose-700 text-white font-black py-2 px-6 rounded-xl transition"
                                    >
                                        Löschen
                                    </button>
                                </div>
                            </section>
                        )}
                    </div>

                    <div className="space-y-8">
                        {isEdit && (
                            <Card title="Saisons" icon={CalendarPlus}>
                                <div className="space-y-3 mb-6">
                                    {competition.competition_seasons.map(cs => (
                                        <div key={cs.id} className="p-4 rounded-xl bg-slate-800/20 border border-slate-800/50 group">
                                            <div className="flex items-center justify-between mb-2">
                                                <span className="font-black text-cyan-400 uppercase tracking-tighter text-lg leading-none">{cs.season.name}</span>
                                                <div className="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Link 
                                                        href={route('admin.competition-seasons.edit', cs.id)}
                                                        className="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition"
                                                    >
                                                        <PencilSimple size={14} />
                                                    </Link>
                                                </div>
                                            </div>
                                            <div className="flex items-center justify-between">
                                                <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">{cs.format}</span>
                                                <button 
                                                    onClick={() => router.post(route('admin.competition-seasons.generate-fixtures', cs.id))}
                                                    className="text-[9px] font-black text-cyan-500 hover:text-cyan-400 uppercase tracking-widest border border-cyan-500/20 px-2 py-1 rounded bg-cyan-500/5 transition"
                                                >
                                                    Fixture Gen
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                    {competition.competition_seasons.length === 0 && (
                                        <p className="text-center py-8 text-slate-600 text-xs italic">Noch keine Saisons zugeordnet.</p>
                                    )}
                                </div>

                                <div className="p-4 rounded-xl bg-slate-950/50 border border-slate-800/50 border-dashed">
                                    <h4 className="text-[10px] font-black text-white uppercase tracking-[0.2em] mb-4">Saison zuordnen</h4>
                                    <form onSubmit={addSeason} className="space-y-4">
                                        <div className="space-y-2">
                                            <select 
                                                className="sim-select text-xs w-full"
                                                value={seasonForm.data.season_id}
                                                onChange={e => seasonForm.setData('season_id', e.target.value)}
                                                required
                                            >
                                                <option value="">Wähle Saison...</option>
                                                {availableSeasons.map(s => (
                                                    <option key={s.id} value={s.id}>{s.name}</option>
                                                ))}
                                            </select>
                                        </div>
                                        <div className="space-y-2">
                                            <input 
                                                type="text"
                                                placeholder="Format (z.B. league_18)"
                                                className="sim-input text-xs w-full"
                                                value={seasonForm.data.format}
                                                onChange={e => seasonForm.setData('format', e.target.value)}
                                                required
                                            />
                                        </div>
                                        <button 
                                            type="submit" 
                                            disabled={seasonForm.processing}
                                            className="w-full bg-slate-800 hover:bg-slate-700 text-cyan-400 font-black py-2 rounded-lg text-[10px] uppercase tracking-widest transition"
                                        >
                                            Zuordnen
                                        </button>
                                    </form>
                                </div>
                            </Card>
                        )}

                        <Card title="Infos & Hilfe" icon={ListChecks}>
                            <div className="space-y-4">
                                <p className="text-xs text-slate-400 leading-relaxed">
                                    Ligen werden automatisch der nationalen Ebene zugeordnet, wenn ein Land gewählt wurde. 
                                    Internationale Pokale hingegen benötigen kein Land.
                                </p>
                                <div className="p-3 rounded-lg bg-indigo-500/5 border border-indigo-500/10">
                                    <p className="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-1">Pro-Tipp</p>
                                    <p className="text-[11px] text-slate-400">Verwende einheitliche Kurznamen für eine bessere Übersicht im System.</p>
                                </div>
                            </div>
                        </Card>
                    </div>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(34,211,238,0.2)] disabled:opacity-50 disabled:scale-100;
                }
            `}} />
        </AdminLayout>
    );
}
