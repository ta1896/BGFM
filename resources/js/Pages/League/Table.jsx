import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { 
    Trophy, TrendUp, TrendDown, Minus, 
    ArrowUp, ArrowDown, Crown, ShieldCheck
} from '@phosphor-icons/react';

const DiffBadge = ({ diff }) => {
    if (!diff || diff === 0) return <Minus size={14} className="text-slate-600" />;
    return diff > 0
        ? <ArrowUp size={14} className="text-emerald-400" weight="bold" />
        : <ArrowDown size={14} className="text-rose-400" weight="bold" />;
};

export default function Table({ competitionSeasons, activeCompetitionSeason, table, ownedClubIds }) {
    const changeCS = (e) => {
        router.get(route('league.table'), { competition_season: e.target.value }, { preserveState: false });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Ligatabelle" />

            <div className="max-w-[1100px] mx-auto space-y-8">
                {/* Header */}
                <div className="flex flex-wrap items-start justify-between gap-6">
                    <div>
                        <p className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-1">Wettbewerb</p>
                        <h1 className="text-4xl font-black text-white uppercase tracking-tighter italic">Tabelle</h1>
                        {activeCompetitionSeason && (
                            <p className="text-sm font-bold text-[var(--text-muted)] mt-1 italic">
                                {activeCompetitionSeason.competition?.name} — {activeCompetitionSeason.season?.name}
                            </p>
                        )}
                    </div>

                    <select
                        value={activeCompetitionSeason?.id || ''}
                        onChange={changeCS}
                        className="sim-select py-2.5 text-xs uppercase font-black min-w-[280px]"
                    >
                        <option value="">Wettbewerb wählen...</option>
                        {competitionSeasons.map(cs => (
                            <option key={cs.id} value={cs.id}>
                                {cs.competition?.name} — {cs.season?.name}
                            </option>
                        ))}
                    </select>
                </div>

                {table.length === 0 ? (
                    <div className="sim-card p-20 text-center border-dashed border-[var(--border-pillar)]">
                        <Trophy size={48} weight="thin" className="text-slate-700 mx-auto mb-6" />
                        <p className="text-[var(--text-muted)] font-bold uppercase tracking-widest text-sm italic">
                            {activeCompetitionSeason ? 'Noch keine Spiele gespielt.' : 'Bitte einen Wettbewerb auswählen.'}
                        </p>
                    </div>
                ) : (
                    <div className="sim-card overflow-hidden p-0">
                        {/* Table Header */}
                        <div className="grid grid-cols-[3rem_3rem_1fr_repeat(8,_3.5rem)] gap-2 px-6 py-4 bg-[var(--bg-pillar)]/60 border-b border-white/5 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                            <div className="text-center">#</div>
                            <div></div>
                            <div>Verein</div>
                            <div className="text-center">Sp</div>
                            <div className="text-center">S</div>
                            <div className="text-center">U</div>
                            <div className="text-center">N</div>
                            <div className="text-center">TD</div>
                            <div className="text-center">Pkt</div>
                            <div className="text-center">Form</div>
                            <div className="text-center hidden lg:block">Trend</div>
                        </div>

                        {/* Table Rows */}
                        {table.map((row, idx) => {
                            const isOwned = ownedClubIds.includes(row.club_id ?? row.club?.id);
                            const pos = idx + 1;
                            const posStyle = pos === 1
                                ? 'text-amber-400'
                                : pos <= 3 ? 'text-emerald-400'
                                : pos >= table.length - 2 ? 'text-rose-400'
                                : 'text-[var(--text-muted)]';

                            return (
                                <motion.div
                                    key={row.club_id || idx}
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    transition={{ delay: idx * 0.02 }}
                                    className={`grid grid-cols-[3rem_3rem_1fr_repeat(8,_3.5rem)] gap-2 px-6 py-4 border-b border-white/5 hover:bg-white/[0.03] transition-all items-center ${isOwned ? 'bg-amber-500/[0.03] border-l-2 border-l-amber-500/30' : ''}`}
                                >
                                    {/* Pos */}
                                    <div className={`text-center text-sm font-black italic ${posStyle}`}>{pos}</div>

                                    {/* Logo */}
                                    <div className="flex justify-center">
                                        <img loading="lazy"
                                            src={row.club?.logo_url || '/images/default-club.png'}
                                            className="w-8 h-8 object-contain"
                                        />
                                    </div>

                                    {/* Name */}
                                    <div className="flex items-center gap-3 min-w-0">
                                        <Link
                                            href={route('clubs.show', row.club_id ?? row.club?.id)}
                                            className={`text-sm font-black uppercase tracking-tight truncate hover:text-amber-500 transition-colors ${isOwned ? 'text-white' : 'text-slate-300'}`}
                                        >
                                            {row.club?.name || row.club_name}
                                        </Link>
                                        {pos === 1 && <Crown size={14} weight="fill" className="text-amber-400 shrink-0" />}
                                        {isOwned && <ShieldCheck size={14} weight="fill" className="text-amber-500 shrink-0" />}
                                    </div>

                                    {/* Stats */}
                                    <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.played}</div>
                                    <div className="text-center text-xs font-bold text-emerald-500">{row.won}</div>
                                    <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.drawn}</div>
                                    <div className="text-center text-xs font-bold text-rose-500">{row.lost}</div>
                                    <div className="text-center text-xs font-bold text-slate-300">
                                        {row.goals_for}:{row.goals_against}
                                        <span className={`text-[9px] ml-1 ${row.goal_difference > 0 ? 'text-emerald-500' : row.goal_difference < 0 ? 'text-rose-500' : 'text-slate-600'}`}>
                                            ({row.goal_difference > 0 ? '+' : ''}{row.goal_difference})
                                        </span>
                                    </div>
                                    <div className={`text-center text-base font-black italic ${isOwned ? 'text-amber-500' : 'text-white'}`}>{row.points}</div>

                                    {/* Form */}
                                    <div className="flex items-center justify-center gap-0.5">
                                        {(row.form || []).slice(-5).map((r, i) => (
                                            <div key={i} className={`w-2.5 h-2.5 rounded-full ${r === 'W' ? 'bg-emerald-500' : r === 'D' ? 'bg-amber-500' : 'bg-rose-500'}`} />
                                        ))}
                                    </div>

                                    {/* Trend */}
                                    <div className="text-center hidden lg:flex justify-center">
                                        <DiffBadge diff={row.position_change} />
                                    </div>
                                </motion.div>
                            );
                        })}
                    </div>
                )}

                {/* Legend */}
                <div className="flex flex-wrap items-center gap-6 text-[9px] font-black text-slate-600 uppercase tracking-widest">
                    <div className="flex items-center gap-2"><div className="w-2.5 h-2.5 rounded-full bg-emerald-500" /> Sieg</div>
                    <div className="flex items-center gap-2"><div className="w-2.5 h-2.5 rounded-full bg-amber-500" /> Unentschieden</div>
                    <div className="flex items-center gap-2"><div className="w-2.5 h-2.5 rounded-full bg-rose-500" /> Niederlage</div>
                    <div className="flex items-center gap-2"><ShieldCheck size={12} weight="fill" className="text-amber-500" /> Dein Verein</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
