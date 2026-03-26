import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    Scroll, 
    RocketLaunch, 
    PaintBrushBroad, 
    BugBeetle, 
    Cpu, 
    CursorClick,
    CalendarBlank,
    CheckCircle
} from '@phosphor-icons/react';

const typeIcons = {
    feature: { icon: RocketLaunch, color: 'text-emerald-400', bg: 'bg-emerald-400/10', border: 'border-emerald-400/20' },
    visual: { icon: PaintBrushBroad, color: 'text-purple-400', bg: 'bg-purple-400/10', border: 'border-purple-400/20' },
    ui: { icon: PaintBrushBroad, color: 'text-blue-400', bg: 'bg-blue-400/10', border: 'border-blue-400/20' },
    ux: { icon: CursorClick, color: 'text-amber-400', bg: 'bg-amber-400/10', border: 'border-amber-400/20' },
    fix: { icon: BugBeetle, color: 'text-rose-400', bg: 'bg-rose-400/10', border: 'border-rose-400/20' },
    performance: { icon: Cpu, color: 'text-cyan-400', bg: 'bg-cyan-400/10', border: 'border-cyan-400/20' },
};

export default function Index({ auth, patchlogs }) {
    return (
        <AuthenticatedLayout
            auth={auth}
            header={
                <div className="flex items-center justify-between w-full">
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5">Entwicklung & Updates</p>
                        <h1 className="text-xl font-black text-white italic uppercase tracking-tight leading-none">Änderungsprotokoll</h1>
                    </div>
                </div>
            }
        >
            <Head title="Patchlogs - Änderungsprotokoll" />

            <div className="max-w-5xl mx-auto space-y-12 pb-20">
                {/* Intro Section */}
                <section className="relative overflow-hidden rounded-[32px] border border-white/8 bg-[linear-gradient(160deg,rgba(12,16,22,0.94),rgba(15,20,27,0.9))] p-8 shadow-[0_20px_70px_rgba(0,0,0,0.34)]">
                    <div className="relative z-10 flex flex-col md:flex-row items-center gap-8">
                        <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-[24px] bg-gradient-to-br from-amber-400 to-amber-600 shadow-[0_10px_30px_rgba(217,177,92,0.3)]">
                            <Scroll size={40} weight="fill" className="text-black" />
                        </div>
                        <div>
                            <h2 className="text-3xl font-black tracking-tight text-white mb-3">Was ist neu bei NewGen?</h2>
                            <p className="text-lg text-slate-300 leading-relaxed max-w-2xl">
                                Wir arbeiten ständig daran, das Erlebnis für dich zu verbessern. Hier findest du eine Übersicht aller aktuellen Änderungen, neuen Features und Fehlerbehebungen.
                            </p>
                        </div>
                    </div>
                    {/* Decorative background element */}
                    <div className="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-amber-500/10 blur-[100px]" />
                </section>

                {/* Patchlogs Timeline */}
                <div className="space-y-16 relative before:absolute before:inset-y-0 before:left-8 md:before:left-1/2 before:w-px before:bg-white/5">
                    {patchlogs.map((log, logIdx) => (
                        <div key={log.version} className="relative group">
                            {/* Version Pulse Dot */}
                            <div className="absolute left-8 md:left-1/2 -translate-x-1/2 top-0 z-20 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 shadow-[0_0_15px_rgba(245,158,11,0.5)]">
                                <div className="h-2 w-2 rounded-full bg-black animate-pulse" />
                            </div>

                            <div className={`flex flex-col md:flex-row gap-8 items-start ${logIdx % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'}`}>
                                {/* Content Card */}
                                <div className="w-full md:w-[45%] pl-16 md:pl-0">
                                    <div className="rounded-[28px] border border-white/8 bg-white/[0.03] backdrop-blur-md p-6 transition-all hover:bg-white/[0.05] hover:border-white/12 shadow-xl">
                                        <div className="flex items-center justify-between mb-6">
                                            <div>
                                                <span className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/80 mb-1 block">Version</span>
                                                <h3 className="text-2xl font-black text-white italic">{log.version}</h3>
                                            </div>
                                            <div className="text-right">
                                                <div className="flex items-center gap-2 text-slate-400 mb-1 justify-end">
                                                    <CalendarBlank size={14} />
                                                    <span className="text-[10px] font-bold uppercase tracking-wider">{log.date}</span>
                                                </div>
                                                <p className="text-sm font-bold text-white/90">{log.title}</p>
                                            </div>
                                        </div>

                                        <div className="space-y-8">
                                            {log.categories.map((category) => (
                                                <div key={category.name}>
                                                    <h4 className="text-[11px] font-black uppercase tracking-[0.15em] text-white/30 mb-4 border-b border-white/5 pb-2">
                                                        {category.name}
                                                    </h4>
                                                    <div className="space-y-4">
                                                        {category.items.map((item, itemIdx) => {
                                                            const typeConfig = typeIcons[item.type] || typeIcons.feature;
                                                            const IconComp = typeConfig.icon;
                                                            
                                                            return (
                                                                <div key={itemIdx} className="flex gap-4 group/item">
                                                                    <div className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border ${typeConfig.border} ${typeConfig.bg} transition-transform group-hover/item:scale-110`}>
                                                                        <IconComp size={18} weight="bold" className={typeConfig.color} />
                                                                    </div>
                                                                    <div>
                                                                        <div className="flex items-center gap-2">
                                                                            <h5 className="font-bold text-white text-sm">{item.title}</h5>
                                                                            {item.type === 'feature' && (
                                                                                <div className="h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_5px_rgba(52,211,153,0.5)]" />
                                                                            )}
                                                                        </div>
                                                                        <p className="mt-1 text-xs text-slate-400 leading-relaxed font-medium">
                                                                            {item.description}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            );
                                                        })}
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>

                                {/* Spacer for Timeline Centering on Desktop */}
                                <div className="hidden md:block md:w-[10%]" />
                                <div className="hidden md:block md:w-[45%]" />
                            </div>
                        </div>
                    ))}
                </div>

                {/* Footer Note */}
                <div className="mt-10 rounded-2xl border border-white/5 bg-white/[0.02] p-6 text-center">
                    <div className="inline-flex h-12 w-12 items-center justify-center rounded-full bg-emerald-400/10 mb-4">
                        <CheckCircle size={24} weight="fill" className="text-emerald-400" />
                    </div>
                    <p className="text-sm text-slate-400 font-medium">
                        Vielen Dank für deine treue Unterstützung als Manager bei NewGen! <br/>
                        Hast du Feedback? Schreib uns gerne im <Link href={route('roadmap-board.index')} className="text-amber-500 hover:text-amber-400 transition-colors underline decoration-amber-500/30">Roadmap Board</Link>.
                    </p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
