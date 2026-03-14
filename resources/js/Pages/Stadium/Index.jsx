import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    NavigationArrow, 
    HardHat, 
    Users, 
    Ticket, 
    Tree, 
    ShieldCheck, 
    Wrench,
    ArrowCircleUp,
    CheckCircle,
    Hammer,
    Plant,
    Couch,
    Sparkle
} from '@phosphor-icons/react';

const MetricCard = ({ label, value, unit, icon: Icon, colorClass }) => (
    <div className="bg-slate-900/50 backdrop-blur-sm rounded-2xl p-5 border border-slate-800/50 group hover:border-slate-700/50 transition-all">
        <div className="flex justify-between items-start mb-3">
            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">{label}</p>
            <Icon size={20} className={colorClass} weight="duotone" />
        </div>
        <div className="flex items-baseline gap-1">
            <p className="text-2xl font-black text-white tracking-tighter">
                {typeof value === 'number' ? value.toLocaleString('de-DE') : value}
            </p>
            <span className="text-xs font-bold text-slate-600 uppercase italic">{unit}</span>
        </div>
    </div>
);

const LevelMetric = ({ label, level, icon: Icon, delay }) => (
    <motion.div 
        initial={{ opacity: 0, x: -20 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ delay }}
        className="flex items-center justify-between p-4 rounded-xl bg-slate-900/80 border border-slate-800/50 group hover:bg-slate-800/30 transition-all"
    >
        <div className="flex items-center gap-4">
            <div className="h-10 w-10 rounded-lg bg-slate-950 border border-slate-800 flex items-center justify-center text-slate-500 group-hover:text-amber-500 transition-colors">
                <Icon size={24} weight="duotone" />
            </div>
            <div>
                <p className="text-sm font-black text-white uppercase tracking-tighter">{label}</p>
                <p className="text-[10px] font-bold text-slate-500 uppercase">Stufe {level}</p>
            </div>
        </div>
        <div className="flex gap-1">
            {[...Array(5)].map((_, i) => (
                <div 
                    key={i} 
                    className={`h-1.5 w-4 rounded-full transition-all duration-500 ${
                        i < level ? 'bg-amber-500 shadow-[0_0_8px_rgba(217,177,92,0.4)]' : 'bg-slate-800'
                    }`}
                />
            ))}
        </div>
    </motion.div>
);

export default function Stadium({ stadium, projects, projectTypes, activeClub }) {
    const { auth } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        club_id: activeClub?.id,
        project_type: 'capacity',
    });

    const submitProject = (e) => {
        e.preventDefault();
        post(route('stadium.projects.store'), {
            preserveScroll: true,
        });
    };

    if (!activeClub || !stadium) return <AuthenticatedLayout>Stadion-Management wird geladen...</AuthenticatedLayout>;

    return (
        <AuthenticatedLayout>
            <Head title="Stadion & Infrastruktur" />

            <div className="max-w-[1400px] mx-auto space-y-10">
                {/* Hero section */}
                <div className="relative rounded-[2.5rem] overflow-hidden border border-slate-800/50 bg-[#0c1222]">
                    <div className="absolute inset-0 bg-gradient-to-br from-indigo-950/20 via-transparent to-slate-950/40 z-10" />
                    <div className="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_50%_0%,_#1e293b_0%,#000_100%)]" />
                    
                    <div className="relative z-20 p-8 md:p-14">
                        <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-10">
                            <div className="flex-1">
                                <motion.div 
                                    initial={{ opacity: 0, y: 10 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    className="flex items-center gap-3 mb-6"
                                >
                                    <div className="w-10 h-10 rounded-xl bg-slate-900 border border-slate-800 p-2 shadow-xl shadow-black/50">
                                        <img src={activeClub.logo_url} className="w-full h-full object-contain" />
                                    </div>
                                    <span className="text-[10px] font-black uppercase tracking-[0.4em] text-amber-500">Heimstätte // Infrastruktur</span>
                                </motion.div>
                                
                                <motion.h1 
                                    initial={{ opacity: 0, scale: 0.95 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    className="text-5xl lg:text-7xl font-black text-white tracking-tighter mb-4 leading-none uppercase italic"
                                >
                                    {stadium.name}
                                </motion.h1>
                                <p className="text-xl text-slate-400 font-medium max-w-2xl leading-relaxed">
                                    Modernste Spielstätte und Trainingszentrum der <span className="text-white font-bold">{activeClub.name}</span>. Investiere in den Ausbau für höhere Einnahmen und bessere Performance.
                                </p>
                            </div>

                            <motion.div 
                                initial={{ opacity: 0, x: 20 }}
                                animate={{ opacity: 1, x: 0 }}
                                className="grid sm:grid-cols-2 gap-4 min-w-[320px]"
                            >
                                <MetricCard label="Kapazität" value={stadium.capacity} unit="Plätze" icon={Users} colorClass="text-amber-500" />
                                <MetricCard label="Ticketpreis" value={parseFloat(stadium.ticket_price)} unit="€" icon={Ticket} colorClass="text-amber-400" />
                                <div className="bg-slate-950/50 backdrop-blur-md rounded-2xl p-4 border border-slate-800/30 flex items-center justify-between">
                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Wartung</span>
                                    <span className="font-mono font-black text-rose-400">{stadium.maintenance_cost.toLocaleString('de-DE')} €</span>
                                </div>
                                <div className="bg-slate-950/50 backdrop-blur-md rounded-2xl p-4 border border-slate-800/30 flex items-center justify-between">
                                    <span className="text-[10px] font-black text-slate-500 uppercase tracking-widest">Rasen</span>
                                    <span className="font-mono font-black text-emerald-400">{stadium.pitch_quality}/10</span>
                                </div>
                            </motion.div>
                        </div>
                    </div>
                </div>

                <div className="grid lg:grid-cols-3 gap-8">
                    {/* Left: Project Creation */}
                    <div className="space-y-8">
                        <div className="sim-card p-8 border-slate-800 shadow-2xl">
                            <h3 className="text-xl font-black text-white uppercase tracking-widest flex items-center gap-3 mb-6">
                                <HardHat size={28} weight="duotone" className="text-amber-500" />
                                Bau-Zentrum
                            </h3>
                            
                            <form onSubmit={submitProject} className="space-y-6">
                                <div>
                                    <label className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500 mb-2 block">Projekt-Kategorie</label>
                                    <div className="grid grid-cols-2 gap-2">
                                        {Object.entries(projectTypes).map(([type, label]) => (
                                            <button
                                                key={type}
                                                type="button"
                                                onClick={() => setData('project_type', type)}
                                                className={`px-3 py-4 rounded-xl text-left border-2 transition-all ${
                                                    data.project_type === type 
                                                        ? 'bg-amber-500/10 border-amber-500/50 text-white' 
                                                        : 'bg-slate-900 border-slate-800 text-slate-500 hover:border-slate-700'
                                                }`}
                                            >
                                                <p className="text-[10px] font-black uppercase tracking-widest">{label}</p>
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <motion.button 
                                    whileHover={{ scale: 1.02 }}
                                    whileTap={{ scale: 0.98 }}
                                    disabled={processing}
                                    className="w-full sim-btn-primary py-5 uppercase font-black tracking-[0.1em]"
                                >
                                    {processing ? 'Planung läuft...' : 'Projekt beauftragen'}
                                </motion.button>
                            </form>
                        </div>

                        <div className="sim-card p-6 bg-slate-950/50">
                            <h4 className="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 border-b border-slate-800 pb-4 flex items-center gap-2">
                                <NavigationArrow size={16} /> Aktuelle Infrastruktur
                            </h4>
                            <div className="space-y-3">
                                <LevelMetric label="Trainingsanlagen" level={stadium.facility_level} icon={Sparkle} delay={0.1} />
                                <LevelMetric label="Sicherheitskonzept" level={stadium.security_level} icon={ShieldCheck} delay={0.2} />
                                <LevelMetric label="Parkplätze & Umfeld" level={stadium.environment_level} icon={Tree} delay={0.3} />
                            </div>
                        </div>
                    </div>

                    {/* Right: Project History */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-black text-white uppercase tracking-widest flex items-center gap-3">
                                <Hammer size={24} weight="duotone" className="text-slate-500" />
                                Baustellen-Protokoll
                            </h3>
                            <div className="px-3 py-1 rounded-full bg-slate-900 text-[10px] font-black text-slate-500">
                                {projects.length} Einträge
                            </div>
                        </div>

                        <div className="sim-card p-0 border-slate-800 overflow-hidden">
                            <table className="w-full text-left">
                                <thead className="bg-[#0c1222] border-b border-slate-800">
                                    <tr className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">
                                        <th className="px-6 py-5">Projekt</th>
                                        <th className="px-6 py-5">Level</th>
                                        <th className="px-6 py-5 text-right">Investment</th>
                                        <th className="px-6 py-5 text-right">Frist</th>
                                        <th className="px-6 py-5 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/50">
                                    {projects.length > 0 ? projects.map((project, idx) => (
                                        <motion.tr 
                                            key={project.id}
                                            initial={{ opacity: 0 }}
                                            animate={{ opacity: 1 }}
                                            transition={{ delay: 0.1 + (idx * 0.05) }}
                                            className="group hover:bg-white/[0.02] transition-colors"
                                        >
                                            <td className="px-6 py-4">
                                                <span className="font-black text-white uppercase tracking-tighter group-hover:text-amber-500 transition-colors">
                                                    {projectTypes[project.project_type] || project.project_type}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-xs text-slate-500 font-bold">{project.level_from}</span>
                                                    <div className="h-px w-3 bg-slate-800" />
                                                    <span className="text-xs text-amber-500 font-black">{project.level_to}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right font-mono font-black text-slate-300 italic ring-offset-2">
                                                {project.cost.toLocaleString('de-DE')} €
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <p className="text-xs text-slate-400 font-bold">{project.started_on_formatted}</p>
                                                <p className="text-[10px] text-slate-600 font-black uppercase tracking-tighter">bis {project.completes_on_formatted}</p>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <span className={`inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border transition-all ${
                                                    project.status === 'completed' 
                                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                                                        : project.status === 'in_progress'
                                                            ? 'bg-amber-500/10 text-amber-400 border-amber-500/20 shadow-[0_0_15px_rgba(245,158,11,0.1)]'
                                                            : 'bg-slate-800 text-slate-500 border-slate-700'
                                                }`}>
                                                    {project.status === 'in_progress' && <Wrench size={10} className="mr-1.5 animate-bounce" weight="bold" />}
                                                    {project.status}
                                                </span>
                                            </td>
                                        </motion.tr>
                                    )) : (
                                        <tr>
                                            <td colSpan="5" className="px-6 py-20 text-center text-slate-600 italic">
                                                Keine historischen Baudaten in diesem Zeitraum erfasst.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black font-black py-2 rounded-xl border-none shadow-[0_0_30px_rgba(217,177,92,0.15)] hover:brightness-110 transition-all;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
