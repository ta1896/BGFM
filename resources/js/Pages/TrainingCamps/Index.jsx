import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    Tent, 
    Calendar,
    WarningCircle,
    Info,
    CheckCircle,
    Plus,
    MapPin
} from '@phosphor-icons/react';

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

export default function TrainingCamps({ clubs, camps }) {
    const { activeClub } = usePage().props;

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
            <Head title="Trainingslager" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                {/* Header */}
                <div className="flex items-end justify-between">
                    <div>
                        <p className="sim-section-title">Vorbereitung & Fokus</p>
                        <h1 className="text-4xl font-black text-white tracking-tighter">Trainingslager</h1>
                    </div>
                    <button className="sim-btn-primary flex items-center gap-2 px-6 py-3">
                        <Plus size={20} weight="bold" />
                        Lager buchen
                    </button>
                </div>

                {/* Camps List */}
                <Card title="Aktive & Geplante Lager" icon={Tent}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                    <th className="px-6 py-4">Zeitraum</th>
                                    <th className="px-6 py-4">Name / Ort</th>
                                    <th className="px-6 py-4">Fokus</th>
                                    <th className="px-6 py-4">Intensität</th>
                                    <th className="px-6 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {camps.data.map((camp, idx) => (
                                    <motion.tr 
                                        key={camp.id}
                                        initial={{ opacity: 0 }}
                                        animate={{ opacity: 1 }}
                                        transition={{ delay: idx * 0.05 }}
                                        className="group hover:bg-white/[0.02] transition-colors"
                                    >
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <div className="flex items-center gap-3 text-sm font-bold text-[var(--text-muted)] font-mono italic">
                                                <Calendar size={16} className="text-slate-600" />
                                                {new Date(camp.starts_on).toLocaleDateString('de-DE')} - {new Date(camp.ends_on).toLocaleDateString('de-DE')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-8 w-8 rounded-lg bg-[var(--bg-content)] flex items-center justify-center text-amber-500 border border-[var(--border-muted)]">
                                                    <MapPin size={18} weight="duotone" />
                                                </div>
                                                <span className="text-sm font-black text-white uppercase tracking-tight">
                                                    {camp.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-[10px] font-black uppercase tracking-widest text-slate-300 px-2 py-1 rounded bg-[var(--bg-content)] border border-[var(--border-pillar)]">
                                                {camp.focus}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-sm font-bold text-[var(--text-muted)]">{camp.intensity}</span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <span className="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                                                <CheckCircle size={14} weight="fill" />
                                                Gebucht
                                            </span>
                                        </td>
                                    </motion.tr>
                                ))}
                                {camps.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-12 text-center text-[var(--text-muted)] italic text-sm">
                                            Keine Trainingslager in der Planung.
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
