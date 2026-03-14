import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Envelope, 
    EnvelopeOpen, 
    CheckCircle, 
    ArrowRight, 
    Trash, 
    Clock,
    Notification,
    Tray,
    Checks
} from '@phosphor-icons/react';

export default function Notifications({ notifications }) {
    const { auth } = usePage().props;
    const { post } = useForm();

    const markAllSeen = () => {
        post(route('notifications.seen-all'), { preserveScroll: true });
    };

    const markSeen = (id) => {
        post(route('notifications.seen', id), { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Posteingang" />

            <div className="max-w-4xl mx-auto space-y-8">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                    <div>
                        <p className="sim-section-title">Kommunikation</p>
                        <h1 className="text-4xl font-black text-white tracking-tighter uppercase italic">Posteingang</h1>
                    </div>
                    
                    {notifications.data.some(n => !n.seen_at) && (
                        <motion.button 
                            whileHover={{ scale: 1.02 }}
                            whileTap={{ scale: 0.98 }}
                            onClick={markAllSeen}
                            className="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-black text-[10px] uppercase tracking-widest border border-slate-700 hover:bg-slate-700 hover:text-white transition-all shadow-lg shadow-black/20"
                        >
                            <Checks size={18} weight="bold" className="text-cyan-400" />
                            Alle als gelesen markieren
                        </motion.button>
                    )}
                </div>

                <div className="sim-card p-0 border-slate-800/50 shadow-2xl overflow-hidden min-h-[500px] flex flex-col bg-[#0c1222]/80 backdrop-blur-xl">
                    <AnimatePresence mode="popLayout">
                        {notifications.data.length === 0 ? (
                            <motion.div 
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                className="flex-1 flex flex-col items-center justify-center p-20 text-center"
                            >
                                <div className="w-24 h-24 rounded-3xl bg-slate-900 border border-slate-800 flex items-center justify-center mb-8 text-slate-700 shadow-inner">
                                    <Tray size={48} weight="thin" />
                                </div>
                                <h3 className="text-2xl font-black text-white mb-2 uppercase tracking-tighter">Postfach leer</h3>
                                <p className="text-slate-500 font-medium max-w-xs">Keine neuen Nachrichten vorhanden. Du bist auf dem neuesten Stand.</p>
                            </motion.div>
                        ) : (
                            <div className="divide-y divide-slate-800/50">
                                {notifications.data.map((notification, idx) => (
                                    <motion.article 
                                        key={notification.id}
                                        initial={{ opacity: 0, x: -10 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        transition={{ delay: idx * 0.03 }}
                                        className={`p-6 flex gap-6 transition-all relative ${
                                            notification.seen_at 
                                                ? 'bg-transparent opacity-50 grayscale-[0.5]' 
                                                : 'bg-white/[0.02] border-l-4 border-l-cyan-500'
                                        }`}
                                    >
                                        {/* Status Icon */}
                                        <div className="shrink-0 pt-1">
                                            {!notification.seen_at ? (
                                                <div className="w-10 h-10 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 border border-cyan-500/20 shadow-[0_0_15px_rgba(34,211,238,0.1)]">
                                                    <Envelope size={20} weight="fill" />
                                                </div>
                                            ) : (
                                                <div className="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center text-slate-600 border border-slate-800">
                                                    <EnvelopeOpen size={20} weight="bold" />
                                                </div>
                                            )}
                                        </div>

                                        <div className="flex-1 min-w-0">
                                            <div className="flex items-start justify-between gap-4 mb-2">
                                                <h3 className={`text-lg font-black tracking-tight uppercase italic ${
                                                    !notification.seen_at ? 'text-white' : 'text-slate-400'
                                                }`}>
                                                    {notification.title}
                                                </h3>
                                                <div className="flex items-center gap-2 text-[10px] font-black text-slate-500 uppercase tracking-widest whitespace-nowrap">
                                                    <Clock size={12} weight="bold" />
                                                    {notification.created_at_formatted}
                                                </div>
                                            </div>
                                            
                                            <p className={`text-base leading-relaxed mb-6 font-medium ${
                                                !notification.seen_at ? 'text-slate-300' : 'text-slate-500'
                                            }`}>
                                                {notification.message}
                                            </p>

                                            <div className="flex flex-wrap items-center gap-4">
                                                {notification.club && (
                                                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-[10px] font-black text-slate-400 uppercase tracking-[0.1em]">
                                                         <img className="w-5 h-5 object-contain" src={notification.club.logo_url} alt={notification.club.name} />
                                                         {notification.club.name}
                                                    </div>
                                                )}

                                                <div className="flex-1" />

                                                <div className="flex items-center gap-4">
                                                    {notification.action_url && (
                                                        <Link 
                                                            href={notification.action_url} 
                                                            className="text-xs font-black text-cyan-400 hover:text-white flex items-center gap-2 transition-all hover:translate-x-1"
                                                        >
                                                            DETAILS ÖFFNEN
                                                            <ArrowRight size={14} weight="bold" />
                                                        </Link>
                                                    )}
                                                    
                                                    {!notification.seen_at && (
                                                        <button 
                                                            onClick={() => markSeen(notification.id)}
                                                            className="text-[10px] font-black text-slate-500 hover:text-cyan-400 uppercase tracking-widest transition-colors flex items-center gap-2"
                                                        >
                                                            <CheckCircle size={14} weight="bold" />
                                                            ALS GELESEN MARKIEREN
                                                        </button>
                                                    )}
                                                </div>
                                            </div>
                                        </div>
                                    </motion.article>
                                ))}
                            </div>
                        )}
                    </AnimatePresence>

                    {/* Pagination */}
                    {notifications.links.length > 3 && (
                        <div className="p-6 border-t border-slate-800/50 bg-[#0c1222] flex justify-center gap-2">
                            {notifications.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    className={`px-3 py-1.5 rounded-lg text-[10px] font-black tracking-widest transition-all ${
                                        link.active 
                                            ? 'bg-cyan-500 text-white shadow-[0_0_15px_rgba(34,211,238,0.3)]' 
                                            : 'text-slate-500 hover:text-white hover:bg-slate-800'
                                    } ${!link.url && 'opacity-30 pointer-events-none'}`}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
