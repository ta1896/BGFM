import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { 
    Scroll, 
    Monitor, 
    MagnifyingGlass, 
    Flask, 
    Timer, 
    Gear,
    CaretDown,
    CaretUp
} from '@phosphor-icons/react';
import { motion, AnimatePresence } from 'framer-motion';

export default function Logs({ logs }) {
    const navItems = [
        { name: 'Übersicht', icon: <Monitor size={20} />, href: route('admin.monitoring.index') },
        { name: 'Match-Analyse', icon: <MagnifyingGlass size={20} />, href: route('admin.monitoring.analysis') },
        { name: 'Match Lab', icon: <Flask size={20} />, href: route('admin.monitoring.lab') },
        { name: 'Scheduler', icon: <Timer size={20} />, href: route('admin.monitoring.scheduler') },
        { name: 'Internals', icon: <Gear size={20} />, href: route('admin.monitoring.internals') },
        { name: 'Logs', icon: <Scroll size={20} />, href: route('admin.monitoring.logs'), active: true },
    ];

    return (
        <AdminLayout
            header={
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-cyan-400">System Logs</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Vollständige Log-Übersicht</h1>
                        <p className="mt-2 text-sm text-slate-300">Anzeige der letzten 200 Einträge aus der laravel.log Datei.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zurück</Link>
                </div>
            }
        >
            <Head title="System Logs" />

            <div className="space-y-6">
                 {/* Sub Navigation */}
                 <div className="flex flex-wrap gap-4 mb-2">
                    {navItems.map((item) => (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={`flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm font-bold border ${
                                item.active 
                                ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 border-indigo-500' 
                                : 'bg-slate-800 text-slate-300 hover:bg-slate-700 border-slate-700'
                            }`}
                        >
                            {item.icon}
                            <span>{item.name}</span>
                        </Link>
                    ))}
                </div>

                <article className="sim-card overflow-hidden">
                    <div className="p-5 border-b border-white/5 bg-slate-800/20">
                        <div className="flex items-center gap-6">
                            <div className="flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-red-500 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></span>
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Error</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-orange-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></span>
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Warning</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <span className="w-2 h-2 rounded-full bg-slate-500 shadow-[0_0_8px_rgba(100,116,139,0.5)]"></span>
                                <span className="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Info</span>
                            </div>
                        </div>
                    </div>

                    <div className="h-[750px] overflow-y-auto bg-black/40 font-mono text-xs custom-scrollbar">
                        {logs.map((log, idx) => (
                            <LogEntry key={idx} log={log} index={idx} />
                        ))}
                        {logs.length === 0 && (
                            <div className="p-20 text-center text-slate-500 italic">
                                Keine Log-Einträge vorhanden.
                            </div>
                        )}
                    </div>
                </article>
            </div>
        </AdminLayout>
    );
}

function LogEntry({ log, index }) {
    const [isOpen, setIsOpen] = useState(false);

    const levelStyles = {
        ERROR: 'bg-red-500/20 text-red-400 border-red-500/20',
        CRITICAL: 'bg-red-500/20 text-red-400 border-red-500/20',
        ALERT: 'bg-red-500/20 text-red-400 border-red-500/20',
        EMERGENCY: 'bg-red-500/20 text-red-400 border-red-500/20',
        WARNING: 'bg-orange-500/20 text-orange-400 border-orange-500/20',
        INFO: 'bg-slate-700/50 text-slate-300 border-slate-700/50',
        DEBUG: 'bg-slate-800 text-slate-500 border-slate-800',
    };

    return (
        <div className="px-5 py-4 border-b border-white/5 hover:bg-white/5 transition-colors group">
            <div className="flex items-start gap-4">
                <span className="text-slate-500 shrink-0 select-none opacity-50">{log.timestamp}</span>
                <span className={`px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tighter shrink-0 border ${levelStyles[log.level] || levelStyles.INFO}`}>
                    {log.level}
                </span>
                <div className="flex-1 overflow-hidden">
                    <p className="text-slate-200 break-words leading-relaxed whitespace-pre-wrap">{log.message}</p>
                    
                    {log.context && (
                        <div className="mt-3">
                            <button 
                                onClick={() => setIsOpen(!isOpen)}
                                className="text-[10px] text-cyan-400 uppercase tracking-widest font-black flex items-center gap-1 hover:text-cyan-300 transition-colors"
                            >
                                {isOpen ? <CaretUp size={12} weight="bold" /> : <CaretDown size={12} weight="bold" />}
                                Context {isOpen ? 'ausblenden' : 'anzeigen'}
                            </button>
                            
                            <AnimatePresence>
                                {isOpen && (
                                    <motion.div 
                                        initial={{ height: 0, opacity: 0 }}
                                        animate={{ height: 'auto', opacity: 1 }}
                                        exit={{ height: 0, opacity: 0 }}
                                        className="overflow-hidden"
                                    >
                                        <div className="mt-2 p-4 bg-black/60 rounded-xl border border-white/5 text-[10px] text-slate-400 whitespace-pre-wrap leading-relaxed max-h-[500px] overflow-y-auto custom-scrollbar font-mono ring-1 ring-inset ring-white/5 shadow-inner">
                                            {log.context}
                                        </div>
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
