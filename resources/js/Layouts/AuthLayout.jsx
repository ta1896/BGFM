import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import PageTransition from '@/Components/PageTransition';

export default function AuthLayout({ title, heading, subtitle, children, footer }) {
    const status = usePage().props.flash?.status;

    return (
        <div className="min-h-screen bg-[#0f172a] text-slate-100 flex items-center justify-center p-6 relative overflow-hidden font-sans">
            <Head title={title} />

            <div className="absolute top-0 -left-1/4 h-[1000px] w-[1000px] rounded-full bg-cyan-500/5 blur-[120px]" />
            <div className="absolute bottom-0 -right-1/4 h-[800px] w-[800px] rounded-full bg-indigo-500/5 blur-[120px]" />

            <div className="relative z-10 w-full max-w-lg">
                <div className="mb-10 text-center">
                    <Link href="/" className="mb-6 inline-flex items-center gap-3">
                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-indigo-600 text-white font-black shadow-xl shadow-cyan-500/20">
                            OW
                        </div>
                    </Link>
                    <h1 className="text-4xl font-black text-white tracking-tighter uppercase italic">{heading}</h1>
                    {subtitle && (
                        <p className="mt-2 font-medium text-[var(--text-muted)]">{subtitle}</p>
                    )}
                </div>

                <PageTransition className="rounded-[inherit]">
                    <div className="sim-card border-white/5 bg-[var(--bg-pillar)]/40 p-10 backdrop-blur-2xl shadow-2xl shadow-indigo-500/5">
                        {status && (
                            <div className="mb-6 rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-bold text-emerald-400">
                                {status}
                            </div>
                        )}

                        {children}
                    </div>
                </PageTransition>

                {footer && (
                    <div className="mt-8 text-center text-sm font-bold text-[var(--text-muted)]">
                        {footer}
                    </div>
                )}
            </div>
        </div>
    );
}
