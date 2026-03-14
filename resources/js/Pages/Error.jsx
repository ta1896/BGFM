import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Warning, Ghost, HandPalm, Wall, ArrowLeft, House } from '@phosphor-icons/react';
import PageTransition from '@/Components/PageTransition';

export default function Error({ status }) {
    const title = {
        503: '503: Service Unavailable',
        500: '500: Server Error',
        404: '404: Seite nicht gefunden',
        403: '403: Zugriff verweigert',
    }[status] || 'Ups! Da ist etwas schiefgelaufen.';

    const description = {
        503: 'Sorry, wir machen gerade Wartungsarbeiten. Bitte versuch es spaeter nochmal.',
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
        <PageTransition>
            <div className="min-h-screen bg-[var(--sim-shell-bg)] text-[var(--text-main)] flex flex-col items-center justify-center p-6 text-center select-none overflow-hidden relative">
                <Head title={title} />

            <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[560px] h-[560px] rounded-full blur-[140px] pointer-events-none bg-[color:var(--accent-glow)]" />
            <div className="absolute inset-0 opacity-60 pointer-events-none" style={{ backgroundImage: 'radial-gradient(circle at 50% 20%, color-mix(in srgb, var(--accent-primary) 18%, transparent), transparent 34%)' }} />

                <div className="relative z-10 max-w-lg w-full opacity-100 translate-y-0 transition-all duration-300">
                <div className="mb-8 inline-flex items-center justify-center p-6 rounded-3xl bg-[var(--bg-pillar)]/60 border border-[var(--border-pillar)] shadow-2xl relative group">
                    <div className="absolute inset-0 rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity" style={{ background: 'linear-gradient(135deg, color-mix(in srgb, var(--accent-primary) 16%, transparent), transparent 65%)' }} />
                    <Icon size={80} weight="duotone" className="relative z-10 text-[var(--accent-primary)]" />
                </div>

                <h1 className="text-4xl md:text-5xl font-black italic uppercase tracking-tighter mb-4 text-[var(--text-main)]">
                    {title}
                </h1>

                <p className="text-lg mb-10 leading-relaxed font-medium text-[var(--text-muted)]">
                    {description}
                </p>

                <div className="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        href="/"
                        className="sim-btn-primary px-8 py-3.5 w-full sm:w-auto flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest"
                    >
                        <House size={20} weight="bold" />
                        Zur Startseite
                    </Link>

                    <button
                        onClick={() => window.history.back()}
                        className="sim-btn-muted px-8 py-3.5 w-full sm:w-auto flex items-center justify-center gap-3 text-sm font-black uppercase tracking-widest"
                    >
                        <ArrowLeft size={20} weight="bold" />
                        Zurueck
                    </button>
                </div>

                <div className="mt-16 pt-8 border-t border-[var(--border-muted)]">
                    <p className="text-[10px] font-black uppercase tracking-[0.3em] italic text-[var(--text-muted)]">
                        NewGen Simulation Engine · Error Log 0x{status}
                    </p>
                </div>
                </div>
            </div>
        </PageTransition>
    );
}
