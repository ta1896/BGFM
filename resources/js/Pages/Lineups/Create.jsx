import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    SoccerBall, 
    Plus, 
    ArrowLeft, 
    Strategy, 
    Cards, 
    Note,
    Checks,
    Warning
} from '@phosphor-icons/react';

export default function Create({ club }) {
    const { data, setData, post, processing, errors } = useForm({
        club_id: club.id,
        name: '',
        formation: '4-4-2',
        notes: '',
        is_active: true
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('lineups.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Neue Aufstellung erstellen" />

            <div className="max-w-4xl mx-auto space-y-12">
                {/* Header */}
                <div className="flex items-center gap-6">
                    <Link 
                        href={route('lineups.index')}
                        className="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-center text-slate-400 hover:text-cyan-400 hover:border-cyan-500/30 transition-all"
                    >
                        <ArrowLeft size={24} weight="bold" />
                    </Link>
                    <div>
                        <div className="flex items-center gap-2 mb-1">
                            <span className="h-px w-6 bg-cyan-500" />
                            <span className="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-500">Matchcenter // Strategie</span>
                        </div>
                        <h1 className="text-4xl font-black text-white tracking-tighter uppercase italic leading-none">
                            Neue <span className="text-slate-600">Aufstellung</span>
                        </h1>
                    </div>
                </div>

                <motion.form 
                    initial={{ opacity: 0, y: 20 }}
                    animate={{ opacity: 1, y: 0 }}
                    onSubmit={submit}
                    className="sim-card p-8 bg-[#0c1222]/80 backdrop-blur-xl border-slate-800/50 space-y-8"
                >
                    <div className="grid md:grid-cols-2 gap-8">
                        <div>
                            <label className="sim-label flex items-center gap-2 mb-2">
                                <Strategy size={16} weight="bold" className="text-cyan-400" />
                                Formation
                            </label>
                            <input 
                                className="sim-input"
                                value={data.formation}
                                onChange={e => setData('formation', e.target.value)}
                                placeholder="z.B. 4-3-3"
                                required
                            />
                            {errors.formation && <div className="text-rose-500 text-[10px] font-bold mt-1 uppercase italic tracking-widest">{errors.formation}</div>}
                        </div>

                        <div>
                            <label className="sim-label flex items-center gap-2 mb-2">
                                <Cards size={16} weight="bold" className="text-indigo-400" />
                                Name der Aufstellung
                            </label>
                            <input 
                                className="sim-input"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                placeholder="z.B. Standard Liga-Elf"
                                required
                            />
                            {errors.name && <div className="text-rose-500 text-[10px] font-bold mt-1 uppercase italic tracking-widest">{errors.name}</div>}
                        </div>
                    </div>

                    <div>
                        <label className="sim-label flex items-center gap-2 mb-2">
                            <Note size={16} weight="bold" className="text-slate-500" />
                            Taktische Notizen
                        </label>
                        <textarea 
                            className="sim-textarea h-32"
                            value={data.notes}
                            onChange={e => setData('notes', e.target.value)}
                            placeholder="Anweisungen für das Team..."
                        />
                        {errors.notes && <div className="text-rose-500 text-[10px] font-bold mt-1 uppercase italic tracking-widest">{errors.notes}</div>}
                    </div>

                    <div className="flex items-center justify-between pt-8 border-t border-white/5">
                        <label className="flex items-center gap-3 cursor-pointer group">
                            <div className="relative">
                                <input 
                                    type="checkbox" 
                                    className="peer sr-only"
                                    checked={data.is_active}
                                    onChange={e => setData('is_active', e.target.checked)}
                                />
                                <div className="w-12 h-6 bg-slate-800 rounded-full border border-slate-700 transition-colors peer-checked:bg-cyan-500/20 peer-checked:border-cyan-500/50" />
                                <div className="absolute left-1 top-1 w-4 h-4 bg-slate-600 rounded-full transition-all peer-checked:left-7 peer-checked:bg-cyan-400 shadow-lg" />
                            </div>
                            <span className="text-xs font-black text-slate-500 group-hover:text-white transition-colors uppercase tracking-widest">
                                Als aktive Aufstellung setzen
                            </span>
                        </label>

                        <button 
                            type="submit" 
                            disabled={processing}
                            className="sim-btn-primary px-12 py-4 flex items-center gap-3 group"
                        >
                            <Plus size={20} weight="bold" />
                            <span className="font-black uppercase tracking-widest text-xs">Erstellen & Bearbeiten</span>
                        </button>
                    </div>
                </motion.form>
            </div>
        </AuthenticatedLayout>
    );
}
