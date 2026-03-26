import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Trophy, Minus, ArrowUp, ArrowDown, Crown, ShieldCheck } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';
import ClubLink from '@/Components/ClubLink';

function DiffBadge({ diff }) {
    if (!diff || diff === 0) {
        return <Minus size={14} className="text-slate-600" />;
    }

    return diff > 0
        ? <ArrowUp size={14} className="text-emerald-400" weight="bold" />
        : <ArrowDown size={14} className="text-rose-400" weight="bold" />;
}

export default function Table({ competitionSeasons, activeCompetitionSeason, table, ownedClubIds }) {
    const changeCompetitionSeason = (event) => {
        router.get(route('league.table'), { competition_season: event.target.value }, { preserveState: false });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Ligatabelle" />

            <div className="mx-auto max-w-[1100px] space-y-8">
                <PageHeader
                    eyebrow="Wettbewerb"
                    title="Tabelle"
                    actions={
                        <select
                            value={activeCompetitionSeason?.id || ''}
                            onChange={changeCompetitionSeason}
                            className="sim-select min-w-[280px] py-2.5 text-xs font-black uppercase"
                        >
                            <option value="">Wettbewerb waehlen...</option>
                            {competitionSeasons.map((competitionSeason) => (
                                <option key={competitionSeason.id} value={competitionSeason.id}>
                                    {competitionSeason.competition?.name} - {competitionSeason.season?.name}
                                </option>
                            ))}
                        </select>
                    }
                />

                {activeCompetitionSeason && (
                    <PageReveal className="text-sm font-bold italic text-[var(--text-muted)]">
                        {activeCompetitionSeason.competition?.name} - {activeCompetitionSeason.season?.name}
                    </PageReveal>
                )}

                {table.length === 0 ? (
                    <PageReveal>
                        <SectionCard bodyClassName="p-20 text-center">
                            <Trophy size={48} weight="thin" className="mx-auto mb-6 text-slate-700" />
                            <p className="text-sm font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                {activeCompetitionSeason ? 'Noch keine Spiele gespielt.' : 'Bitte einen Wettbewerb auswaehlen.'}
                            </p>
                        </SectionCard>
                    </PageReveal>
                ) : (
                    <PageReveal>
                        <SectionCard title="Ligatabelle" icon={Trophy} bodyClassName="overflow-hidden">
                            <div className="overflow-x-auto">
                                <div className="grid min-w-[980px] grid-cols-[3rem_3rem_1fr_repeat(8,_3.5rem)] gap-2 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    <div className="text-center">#</div>
                                    <div />
                                    <div>Verein</div>
                                    <div className="text-center">Sp</div>
                                    <div className="text-center">S</div>
                                    <div className="text-center">U</div>
                                    <div className="text-center">N</div>
                                    <div className="text-center">TD</div>
                                    <div className="text-center">Pkt</div>
                                    <div className="text-center">Form</div>
                                    <div className="text-center">Trend</div>
                                </div>

                                <StaggerGroup as="div">
                                    {table.map((row, index) => {
                                        const isOwned = ownedClubIds.includes(row.club_id ?? row.club?.id);
                                        const position = index + 1;
                                        const positionClass = position === 1
                                            ? 'text-amber-400'
                                            : position <= 3
                                                ? 'text-emerald-400'
                                                : position >= table.length - 2
                                                    ? 'text-rose-400'
                                                    : 'text-[var(--text-muted)]';

                                        return (
                                            <div
                                                key={row.club_id || index}
                                                className={`grid min-w-[980px] grid-cols-[3rem_3rem_1fr_repeat(8,_3.5rem)] gap-2 border-b border-white/5 px-6 py-4 transition-colors hover:bg-white/[0.03] ${isOwned ? 'border-l-2 border-l-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/[0.03]' : ''}`}
                                            >
                                                <div className={`text-center text-sm font-black italic ${positionClass}`}>{position}</div>
                                                <div className="flex justify-center">
                                                    <img
                                                        loading="lazy"
                                                        src={row.club?.logo_url || '/images/default-club.png'}
                                                        alt={row.club?.name || row.club_name}
                                                        className="h-8 w-8 object-contain"
                                                    />
                                                </div>
                                                <div className="flex min-w-0 items-center gap-3">
                                                    <ClubLink
                                                        id={row.club_id ?? row.club?.id}
                                                        name={row.club?.name || row.club_name}
                                                        className={`truncate text-sm font-black uppercase tracking-tight transition-colors hover:text-[var(--accent-primary)] ${isOwned ? 'text-[var(--text-main)]' : 'text-slate-300'}`}
                                                    />
                                                    {position === 1 && <Crown size={14} weight="fill" className="shrink-0 text-amber-400" />}
                                                    {isOwned && <ShieldCheck size={14} weight="fill" className="shrink-0 text-[var(--accent-primary)]" />}
                                                </div>
                                                <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.played}</div>
                                                <div className="text-center text-xs font-bold text-emerald-500">{row.won}</div>
                                                <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.drawn}</div>
                                                <div className="text-center text-xs font-bold text-rose-500">{row.lost}</div>
                                                <div className="text-center text-xs font-bold text-slate-300">
                                                    {row.goals_for}:{row.goals_against}
                                                    <span className={`ml-1 text-[9px] ${row.goal_difference > 0 ? 'text-emerald-500' : row.goal_difference < 0 ? 'text-rose-500' : 'text-slate-600'}`}>
                                                        ({row.goal_difference > 0 ? '+' : ''}{row.goal_difference})
                                                    </span>
                                                </div>
                                                <div className={`text-center text-base font-black italic ${isOwned ? 'text-[var(--accent-primary)]' : 'text-[var(--text-main)]'}`}>{row.points}</div>
                                                <div className="flex items-center justify-center gap-0.5">
                                                    {(row.form || []).slice(-5).map((result, formIndex) => (
                                                        <div
                                                            key={formIndex}
                                                            className={`h-2.5 w-2.5 rounded-full ${result === 'W' ? 'bg-emerald-500' : result === 'D' ? 'bg-amber-500' : 'bg-rose-500'}`}
                                                        />
                                                    ))}
                                                </div>
                                                <div className="flex justify-center">
                                                    <DiffBadge diff={row.position_change} />
                                                </div>
                                            </div>
                                        );
                                    })}
                                </StaggerGroup>
                            </div>
                        </SectionCard>
                    </PageReveal>
                )}

                <PageReveal className="flex flex-wrap items-center gap-6 text-[9px] font-black uppercase tracking-widest text-slate-600">
                    <div className="flex items-center gap-2"><div className="h-2.5 w-2.5 rounded-full bg-emerald-500" /> Sieg</div>
                    <div className="flex items-center gap-2"><div className="h-2.5 w-2.5 rounded-full bg-amber-500" /> Unentschieden</div>
                    <div className="flex items-center gap-2"><div className="h-2.5 w-2.5 rounded-full bg-rose-500" /> Niederlage</div>
                    <div className="flex items-center gap-2"><ShieldCheck size={12} weight="fill" className="text-[var(--accent-primary)]" /> Dein Verein</div>
                </PageReveal>
            </div>
        </AuthenticatedLayout>
    );
}
