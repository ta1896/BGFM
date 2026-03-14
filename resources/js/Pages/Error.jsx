import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Warning, Ghost, HandPalm, Wall, ArrowLeft, House } from '@phosphor-icons/react';
import { motion } from 'framer-motion';

export default function Error({ status }) {
    const title = {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Seite nicht gefunden',
        403: '403: Zugriff verweigert',
    }[status] || 'Ups! Da ist etwas schiefgelaufen.';

    const description = {
        503: 'Sorry, wir machen gerade Wartungsarbeiten. Bitte versuch es später nochmal.',
        500: 'Whoops, etwas ist auf unseren Servern kaputt gegangen.',
        404: 'Die Seite, die du suchst, existiert nicht oder wurde verschoben.',
        403: 'Du hast keine Berechtigung, auf diese Seite zuzugreifen.',
    }[status] || 'Ein unerwarteter Fehler ist aufgetreten.';

    const Icon = {
        503: HandPalm,
        500: Warning,
        404: Ghost,
        403: Wall,
    }[status] || Warning;

    return (
        <div className="min-h-screen bg-[#0a0b0d] flex flex-col items-center justify-center p-6 text-center select-none overflow-hidden relative">
            <Head title={title} />
            
            {/* Background Glow */}
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-[120px] pointer-events-none" />
            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] bg-amber-600/5 rounded-full blur-[100px] pointer-events-none" />

            <motion.div 
                initial={{ opacity: 0, scale: 0.9, y: 20 }}
                animate={{ opacity: 1, scale: 1, y: 0 }}
                className="relative z-10 max-w-lg w-full"
            >
                <div className="mb-8 inline-flex items-center justify-center p-6 rounded-3xl bg-[var(--bg-pillar)]/50 border border-[var(--border-pillar)] shadow-2xl relative group">
                    <div className="absolute inset-0 bg-gradient-to-br from-amber-500/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity rounded-3xl" />
                    <Icon size={80} weight="duotone" className="text-amber-500 relative z-10" />
                </div>

                <h1 className="text-4xl md:text-5xl font-black text-white italic uppercase tracking-tighter mb-4">
                    {title}
                </h1>
                
                <p className="text-[var(--text-muted)] text-lg mb-10 leading-relaxed font-medium">
                    {description}
                </p>

                <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        href="/"
                        className="sim-btn-primary px-8 py-3.5 w-full sm:w-auto flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest shadow-lg shadow-amber-900/40"
                    >
                        <House size={20} weight="bold" />
                        Zur Startseite
                    </Link>
                    
                    <button
                        onClick={() => window.history.back()}
                        className="sim-btn-muted px-8 py-3.5 w-full sm:w-auto flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 hover:bg-[var(--bg-content)]"
                    >
                        <ArrowLeft size={20} weight="bold" />
                        Zurück
                    </button>
                </div>

                <div className="mt-16 pt-8 border-t border-slate-900">
                    <p className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600 italic">
                        NewGen Simulation Engine • Error Log 0x{status}
                    </p>
                </div>
            </motion.div>
        </div>
    );
}
