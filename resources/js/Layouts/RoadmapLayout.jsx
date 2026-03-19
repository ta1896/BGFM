import React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, SignOut } from '@phosphor-icons/react';

export default function RoadmapLayout({ user, children }) {
    return (
        <div className="min-h-screen overflow-hidden bg-[#071019] text-white">
            <div className="pointer-events-none fixed inset-0">
                <div className="absolute inset-x-0 top-0 h-[420px] bg-[radial-gradient(circle_at_top_left,rgba(16,185,129,0.18),transparent_42%),radial-gradient(circle_at_top_right,rgba(56,189,248,0.16),transparent_34%),linear-gradient(180deg,rgba(7,16,25,0.96),rgba(7,16,25,0))]" />
                <div className="absolute left-[-120px] top-[200px] h-[360px] w-[360px] rounded-full bg-cyan-400/10 blur-3xl" />
                <div className="absolute bottom-[-120px] right-[-80px] h-[360px] w-[360px] rounded-full bg-emerald-400/10 blur-3xl" />
            </div>

            <div className="relative mx-auto max-w-[1580px] px-5 py-5 sm:px-6 lg:px-8">
                <div className="mb-8 rounded-[32px] border border-white/8 bg-[linear-gradient(140deg,rgba(8,18,29,0.94),rgba(10,24,38,0.90)_45%,rgba(16,28,35,0.88))] shadow-[0_30px_100px_rgba(0,0,0,0.45)] backdrop-blur-xl">
                    <div className="flex flex-wrap items-center justify-between gap-4 border-b border-white/8 px-5 py-4 sm:px-6">
                        <div className="flex items-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-cyan-300/20 bg-cyan-400/10 text-cyan-200 shadow-[0_10px_30px_rgba(6,182,212,0.18)]">
                                <div className="h-3.5 w-3.5 rounded-full bg-cyan-300 shadow-[0_0_22px_rgba(103,232,249,0.9)]" />
                            </div>
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-200/80">Roadmap Workspace</div>
                                <div className="mt-1 text-xl font-black tracking-[-0.04em] text-white sm:text-2xl">Standalone Product Board</div>
                            </div>
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <Link
                                href={route('dashboard')}
                                className="inline-flex items-center gap-2 rounded-2xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-white/78 transition-all hover:border-white/20 hover:bg-white/[0.07] hover:text-white"
                            >
                                <ArrowLeft size={14} weight="bold" />
                                Dashboard
                            </Link>
                            <div className="rounded-2xl border border-white/10 bg-black/20 px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-white/70">
                                {user?.name}
                            </div>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="inline-flex items-center gap-2 rounded-2xl border border-rose-300/15 bg-rose-500/10 px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-rose-100 transition-all hover:border-rose-300/30 hover:bg-rose-500/15"
                            >
                                <SignOut size={14} weight="bold" />
                                Logout
                            </Link>
                        </div>
                    </div>

                    <div className="grid gap-6 px-5 py-6 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                        <div className="max-w-3xl">
                            <div className="inline-flex rounded-full border border-cyan-300/15 bg-cyan-400/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-100/90">
                                Public-style planning surface
                            </div>
                            <h1 className="mt-4 max-w-2xl text-3xl font-black tracking-[-0.05em] text-white sm:text-4xl lg:text-[3.35rem] lg:leading-[0.95]">
                                Ship roadmap ideas with clearer status, priority and discussion.
                            </h1>
                            <p className="mt-4 max-w-2xl text-sm leading-7 text-slate-300 sm:text-[15px]">
                                This board is separate from the main game UI, but keeps the existing login. Use it as the product workspace for planning, discussion and execution.
                            </p>
                        </div>

                        <div className="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                            <HeroInfoCard label="Purpose" value="Plan outside the app" tone="cyan" />
                            <HeroInfoCard label="Access" value="Shared team workspace" tone="emerald" />
                            <HeroInfoCard label="Flow" value="Discuss, rank, ship" tone="amber" />
                        </div>
                    </div>
                </div>

                {children}
            </div>
        </div>
    );
}

function HeroInfoCard({ label, value, tone }) {
    const tones = {
        cyan: 'border-cyan-300/15 bg-cyan-400/10 text-cyan-100',
        emerald: 'border-emerald-300/15 bg-emerald-400/10 text-emerald-100',
        amber: 'border-amber-300/15 bg-amber-400/10 text-amber-100',
    };

    return (
        <div className={`rounded-[24px] border px-4 py-4 ${tones[tone] || tones.cyan}`}>
            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/55">{label}</div>
            <div className="mt-2 text-sm font-black tracking-[-0.02em] text-white">{value}</div>
        </div>
    );
}
