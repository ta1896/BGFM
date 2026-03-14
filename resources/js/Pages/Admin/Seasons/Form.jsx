import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { 
    Calendar, ArrowLeft, FloppyDisk, 
    CalendarCheck, Clock, CheckCircle
} from '@phosphor-icons/react';

const Card = ({ title, children, icon: Icon }) => (
    <div className="sim-card p-8">
        <div className="flex items-center gap-3 mb-8">
            <div className="p-2.5 rounded-xl bg-slate-800/50 border border-slate-700/30">
                <Icon size={24} className="text-cyan-400" />
            </div>
            <h3 className="text-xl font-bold text-white tracking-tight leading-none uppercase italic">{title}</h3>
        </div>
        {children}
    </div>
);

export default function Form({ season }) {
    const isEdit = !!season;
    
    const { data, setData, post, put, processing, errors } = useForm({
        name: season?.name || '',
        start_date: season?.start_date?.split(' ')[0] || '', // Handle ISO string
        end_date: season?.end_date?.split(' ')[0] || '',
        is_current: season ? !!season.is_current : false,
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.seasons.update', season.id));
        } else {
            post(route('admin.seasons.store'));
        }
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? `${season.name} bearbeiten` : 'Saison erstellen'} />

            <div className="max-w-3xl mx-auto space-y-8 pb-20">
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link 
                            href={route('admin.seasons.index')}
                            className="p-2 rounded-xl bg-slate-800/50 text-slate-400 hover:text-white transition"
                        >
                            <ArrowLeft size={20} weight="bold" />
                        </Link>
                        <div>
                            <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">
                                {isEdit ? 'Saison bearbeiten' : 'Neue Saison'}
                            </h2>
                            <p className="text-slate-500 text-sm font-bold uppercase tracking-widest mt-1">
                                {isEdit ? `Konfiguration für ${season.name}` : 'Einen neuen Spielzeitraum definieren'}
                            </p>
                        </div>
                    </div>
                </div>

                <form onSubmit={submit} className="space-y-8">
                    <Card title="Saison Details" icon={Calendar}>
                        <div className="space-y-6">
                            <div className="space-y-2">
                                <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1">Anzeigename (z.B. 2025/26)</label>
                                <input 
                                    type="text"
                                    className="sim-input w-full text-lg font-bold"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    placeholder="2025/26"
                                    required
                                />
                                {errors.name && <p className="text-rose-500 text-[10px] font-bold mt-1 uppercase tracking-widest">{errors.name}</p>}
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Clock size={12} />
                                        Startdatum
                                    </label>
                                    <input 
                                        type="date"
                                        className="sim-input w-full"
                                        value={data.start_date}
                                        onChange={e => setData('start_date', e.target.value)}
                                        required
                                    />
                                    {errors.start_date && <p className="text-rose-500 text-[10px] font-bold mt-1 uppercase tracking-widest">{errors.start_date}</p>}
                                </div>

                                <div className="space-y-2">
                                    <label className="text-[10px] font-black text-slate-500 uppercase tracking-widest px-1 flex items-center gap-1">
                                        <Clock size={12} />
                                        Enddatum
                                    </label>
                                    <input 
                                        type="date"
                                        className="sim-input w-full"
                                        value={data.end_date}
                                        onChange={e => setData('end_date', e.target.value)}
                                        required
                                    />
                                    {errors.end_date && <p className="text-rose-500 text-[10px] font-bold mt-1 uppercase tracking-widest">{errors.end_date}</p>}
                                </div>
                            </div>

                            <div className="pt-6">
                                <label className="flex items-center gap-4 cursor-pointer group p-4 rounded-2xl bg-slate-900/50 border border-slate-800/50 hover:bg-slate-900 transition-colors">
                                    <div className={`w-12 h-7 rounded-full p-1 transition-colors ${data.is_current ? 'bg-cyan-500' : 'bg-slate-700'}`}>
                                        <div className={`w-5 h-5 bg-white rounded-full transition-transform ${data.is_current ? 'translate-x-5' : 'translate-x-0'}`} />
                                    </div>
                                    <input 
                                        type="checkbox" 
                                        className="hidden" 
                                        checked={data.is_current}
                                        onChange={e => setData('is_current', e.target.checked)}
                                    />
                                    <div>
                                        <span className="text-sm font-black uppercase tracking-widest text-white group-hover:text-cyan-400 transition-colors">Aktuelle Hauptsaison</span>
                                        <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-0.5">Andere Saisons werden automatisch als inaktiv markiert</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div className="mt-10 pt-8 border-t border-slate-800/50 flex items-center justify-between">
                            <Link 
                                href={route('admin.seasons.index')}
                                className="text-xs font-black uppercase tracking-[0.2em] text-slate-500 hover:text-white transition-colors"
                            >
                                Abbrechen
                            </Link>

                            <button 
                                type="submit" 
                                disabled={processing}
                                className="sim-btn-primary px-10 py-3.5 flex items-center gap-3 text-sm"
                            >
                                <FloppyDisk size={20} weight="bold" />
                                {isEdit ? 'Saison aktualisieren' : 'Saison anlegen'}
                            </button>
                        </div>
                    </Card>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_20px_rgba(34,211,238,0.25)] disabled:opacity-50 disabled:scale-100;
                }
            `}} />
        </AdminLayout>
    );
}
