import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    GraduationCap, 
    Lightning, 
    Target, 
    Heartbeat, 
    Users, 
    TrendUp, 
    Calendar,
    WarningCircle,
    Info,
    CheckCircle,
    CaretDown,
    Plus
} from '@phosphor-icons/react';

const intensityColors = {
    low: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
    medium: 'text-amber-400 bg-amber-500/10 border-amber-500/20',
    high: 'text-rose-400 bg-rose-500/10 border-rose-500/20'
};

const typeIcons = {
    fitness: Lightning,
    tactics: Target,
    technical: GraduationCap,
    recovery: Heartbeat,
    friendly: Users
};

const Card = ({ title, children, icon: Icon, className = "" }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className={`sim-card border-[var(--border-muted)] ${className}`}
    >
        {title && (
            <div className="px-6 py-4 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Icon size={20} weight="duotone" className="text-amber-500" />
                    <h2 className="text-lg font-black text-white tracking-tight uppercase">{title}</h2>
                </div>
            </div>
        )}
        {children}
    </motion.div>
);

export default function Training({ clubs, sessions, filters, prefillClubId, prefillDate }) {
    const { activeClub } = usePage().props;
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        club_id: activeClub?.id || prefillClubId,
        type: 'technical',
        intensity: 'medium',
        focus_position: '',
        session_date: prefillDate,
        notes: '',
        player_ids: activeClub?.players?.map(p => p.id) || []
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('training.store'), {
            onSuccess: () => {
                setShowForm(false);
                reset();
            }
        });
    };

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <WarningCircle size={64} weight="thin" className="text-slate-700 mb-6" />
                    <h2 className="text-2xl font-bold text-white mb-2">Kein Verein aktiv</h2>
                    <p className="text-[var(--text-muted)] max-w-md">Es konnte kein aktiver Verein gefunden werden. Bitte wähle einen Verein aus der Liste.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Training" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                {/* Header */}
                <div className="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <p className="sim-section-title">Leistungsentwicklung</p>
                        <h1 className="text-4xl font-black text-white tracking-tighter">Trainingszentrum</h1>
                    </div>
                    <button 
                        onClick={() => setShowForm(!showForm)}
                        className="sim-btn-primary flex items-center gap-2 px-6 py-3"
                    >
                        <Plus size={20} weight="bold" />
                        Neue Einheit
                    </button>
                </div>

                <AnimatePresence>
                    {showForm && (
                        <motion.div
                            initial={{ opacity: 0, height: 0 }}
                            animate={{ opacity: 1, height: 'auto' }}
                            exit={{ opacity: 0, height: 0 }}
                            className="overflow-hidden"
                        >
                            <Card title="Neue Trainingseinheit Planen" icon={Plus}>
                                <form onSubmit={handleSubmit} className="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Typ</label>
                                        <select 
                                            value={data.type}
                                            onChange={e => setData('type', e.target.value)}
                                            className="w-full bg-[var(--bg-pillar)] border-[var(--border-pillar)] rounded-xl text-white font-bold"
                                        >
                                            <option value="technical">Technik</option>
                                            <option value="tactics">Taktik</option>
                                            <option value="fitness">Fitness</option>
                                            <option value="recovery">Erholung</option>
                                            <option value="friendly">Testspiel</option>
                                        </select>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Intensität</label>
                                        <select 
                                            value={data.intensity}
                                            onChange={e => setData('intensity', e.target.value)}
                                            className="w-full bg-[var(--bg-pillar)] border-[var(--border-pillar)] rounded-xl text-white font-bold"
                                        >
                                            <option value="low">Niedrig</option>
                                            <option value="medium">Mittel</option>
                                            <option value="high">Hoch</option>
                                        </select>
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Datum</label>
                                        <input 
                                            type="date"
                                            value={data.session_date}
                                            onChange={e => setData('session_date', e.target.value)}
                                            className="w-full bg-[var(--bg-pillar)] border-[var(--border-pillar)] rounded-xl text-white font-bold"
                                        />
                                    </div>
                                    <div className="space-y-2 lg:col-span-1">
                                        <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Aktion</label>
                                        <button 
                                            type="submit" 
                                            disabled={processing}
                                            className="w-full sim-btn-primary py-2.5"
                                        >
                                            Planung speichern
                                        </button>
                                    </div>
                                </form>
                            </Card>
                        </motion.div>
                    )}
                </AnimatePresence>

                {/* Sessions List */}
                <Card title="Trainingseinheiten" icon={Calendar}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                    <th className="px-6 py-4">Datum</th>
                                    <th className="px-6 py-4">Typ</th>
                                    <th className="px-6 py-4">Intensität</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {sessions.data.map((session, idx) => {
                                    const Icon = typeIcons[session.type] || GraduationCap;
                                    return (
                                        <motion.tr 
                                            key={session.id}
                                            initial={{ opacity: 0 }}
                                            animate={{ opacity: 1 }}
                                            transition={{ delay: idx * 0.05 }}
                                            className="group hover:bg-white/[0.02] transition-colors"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="text-sm font-bold text-[var(--text-muted)] font-mono italic">
                                                    {new Date(session.session_date).toLocaleDateString('de-DE')}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="h-8 w-8 rounded-lg bg-[var(--bg-content)] flex items-center justify-center text-amber-500 border border-[var(--border-muted)]">
                                                        <Icon size={18} weight="duotone" />
                                                    </div>
                                                    <span className="text-sm font-black text-white uppercase tracking-tight">
                                                        {session.type}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border ${intensityColors[session.intensity]}`}>
                                                    {session.intensity}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                {session.applied_at ? (
                                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                                                        <CheckCircle size={14} weight="fill" />
                                                        Absolviert
                                                    </span>
                                                ) : (
                                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-amber-500">
                                                        <HourglassMedium size={14} weight="fill" />
                                                        Geplant
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                {!session.applied_at && (
                                                    <button 
                                                        onClick={() => post(route('training.apply', session.id))}
                                                        className="text-[10px] font-black uppercase tracking-widest text-amber-500 hover:text-white transition-colors border border-amber-500/20 px-3 py-1.5 rounded-lg bg-amber-500/5"
                                                    >
                                                        Einheit durchführen
                                                    </button>
                                                )}
                                            </td>
                                        </motion.tr>
                                    );
                                })}
                                {sessions.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-12 text-center text-[var(--text-muted)] italic text-sm">
                                            Keine Trainingseinheiten für diesen Zeitraum geplant.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black font-black py-2 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(217,177,92,0.15)];
                }
            `}} />
        </AuthenticatedLayout>
    );
}

const HourglassMedium = ({ size, weight, className }) => (
    <div className={className}>⌛</div>
);
