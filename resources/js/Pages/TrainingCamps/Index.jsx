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
        className={`sim-card border-slate-800/50 ${className}`}
    >
        {title && (
            <div className="px-6 py-4 border-b border-slate-800/50 bg-slate-900/40 flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <Icon size={20} weight="duotone" className="text-cyan-400" />
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
                    <p className="text-slate-400 max-w-md">Es konnte kein aktiver Verein gefunden werden. Bitte wähle einen Verein aus der Liste.</p>
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
                                <tr className="border-b border-slate-800/50 text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
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
                                            <div className="flex items-center gap-3 text-sm font-bold text-slate-400 font-mono italic">
                                                <Calendar size={16} className="text-slate-600" />
                                                {new Date(camp.starts_on).toLocaleDateString('de-DE')} - {new Date(camp.ends_on).toLocaleDateString('de-DE')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="h-8 w-8 rounded-lg bg-slate-800 flex items-center justify-center text-cyan-400 border border-slate-700/50">
                                                    <MapPin size={18} weight="duotone" />
                                                </div>
                                                <span className="text-sm font-black text-white uppercase tracking-tight">
                                                    {camp.name}
                                                </span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-[10px] font-black uppercase tracking-widest text-slate-300 px-2 py-1 rounded bg-slate-800 border border-slate-700">
                                                {camp.focus}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="text-sm font-bold text-slate-500">{camp.intensity}</span>
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
                                        <td colSpan="5" className="px-6 py-12 text-center text-slate-500 italic text-sm">
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
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black py-2 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(34,211,238,0.2)];
                }
            `}} />
        </AuthenticatedLayout>
    );
}
