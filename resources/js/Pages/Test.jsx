import React from 'react';
import { Head } from '@inertiajs/react';
import { RocketLaunch, CheckCircle, Sparkle } from '@phosphor-icons/react';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';

export default function Test() {
    return (
        <div className="min-h-screen overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 p-6">
            <Head title="Tech Stack Test" />

            <div className="flex min-h-screen items-center justify-center">
                <PageReveal className="relative w-full max-w-xl border-t-4 border-indigo-500 p-10 sim-card shadow-2xl shadow-indigo-500/10">
                    <div className="absolute -left-12 -top-12 h-48 w-48 rounded-full bg-indigo-500 opacity-10 blur-3xl" />

                    <div className="mb-8 flex items-center gap-4">
                        <div className="rounded-2xl border border-indigo-500/30 bg-indigo-500/20 p-3">
                            <RocketLaunch size={32} weight="duotone" className="text-indigo-400" />
                        </div>
                        <div>
                            <h1 className="text-3xl font-black tracking-tight text-white">
                                Tech Stack <span className="text-indigo-400">Deployed</span>
                            </h1>
                            <p className="text-sm font-medium text-[var(--text-muted)]">Laravel + React + UI Primitives + Phosphor Icons</p>
                        </div>
                    </div>

                    <StaggerGroup className="space-y-6">
                        <div className="flex items-start gap-3">
                            <CheckCircle size={24} className="mt-0.5 shrink-0 text-emerald-400" weight="fill" />
                            <div>
                                <p className="font-bold text-white">Inertia.js V2 Bridge</p>
                                <p className="text-xs text-[var(--text-muted)]">Nahtlose Verbindung zwischen Laravel PHP und React UI Komponenten.</p>
                            </div>
                        </div>

                        <div className="flex items-start gap-3">
                            <Sparkle size={24} className="mt-0.5 shrink-0 text-cyan-400" weight="fill" />
                            <div>
                                <p className="font-bold text-white">Transitions und Designsystem</p>
                                <p className="text-xs text-[var(--text-muted)]">Page Transitions, Reveal-Animationen und gemeinsame Komponenten statt schwerer Runtime-Animationen.</p>
                            </div>
                        </div>
                    </StaggerGroup>

                    <div className="mt-10">
                        <a href="/" className="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 py-4 font-bold text-white transition-colors hover:bg-indigo-500 shadow-lg shadow-indigo-600/30">
                            Back to Home
                        </a>
                    </div>
                </PageReveal>
            </div>
        </div>
    );
}
