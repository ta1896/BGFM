import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Trophy, Minus, ArrowUp, ArrowDown, Crown, ShieldCheck, HandPalm, SoccerBall } from '@phosphor-icons/react';
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

export default function Table({ competitionSeasons, activeCompetitionSeason, table, ownedClubIds, topScorers = [] }) {
    const [activeTab, setActiveTab] = useState('league');

    const changeCompetitionSeason = (event) => {
        router.get(route('league.table'), { competition_season: event.target.value }, { preserveState: false });
    };

    const fairplayTable = [...table].sort((a, b) => {
        const fairplayA = (a.yellow_cards ?? 0) + (a.red_cards ?? 0) * 3;
        const fairplayB = (b.yellow_cards ?? 0) + (b.red_cards ?? 0) * 3;
        return fairplayA - fairplayB;
    });

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

                {table.length > 0 && (
                    <div className="flex gap-2">
                        <button
                            onClick={() => setActiveTab('league')}
                            className={`flex items-center gap-2 rounded-xl border px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-colors ${
                                activeTab === 'league'
                                    ? 'border-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/10 text-[var(--accent-primary)]'
                                    : 'border-[var(--border-pillar)] bg-transparent text-[var(--text-muted)] hover:text-white'
                            }`}
                        >
                            <Trophy size={14} weight={activeTab === 'league' ? 'fill' : 'regular'} />
                            Ligatabelle
                        </button>
                        <button
                            onClick={() => setActiveTab('fairplay')}
                            className={`flex items-center gap-2 rounded-xl border px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-colors ${
                                activeTab === 'fairplay'
                                    ? 'border-emerald-400/40 bg-emerald-400/10 text-emerald-400'
                                    : 'border-[var(--border-pillar)] bg-transparent text-[var(--text-muted)] hover:text-white'
                            }`}
                        >
                            <HandPalm size={14} weight={activeTab === 'fairplay' ? 'fill' : 'regular'} />
                            Fairplay
                        </button>
                        <button
                            onClick={() => setActiveTab('scorers')}
                            className={`flex items-center gap-2 rounded-xl border px-4 py-2.5 text-[10px] font-black uppercase tracking-widest transition-colors ${
                                activeTab === 'scorers'
                                    ? 'border-amber-400/40 bg-amber-400/10 text-amber-400'
                                    : 'border-[var(--border-pillar)] bg-transparent text-[var(--text-muted)] hover:text-white'
                            }`}
                        >
                            <SoccerBall size={14} weight={activeTab === 'scorers' ? 'fill' : 'regular'} />
                            Torschuetzen
                        </button>
                    </div>
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
                ) : activeTab === 'league' ? (
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
                ) : activeTab === 'fairplay' ? (
                    <PageReveal>
                        <SectionCard title="Fairplay-Tabelle" icon={HandPalm} bodyClassName="overflow-hidden">
                            <div className="overflow-x-auto">
                                <div className="grid min-w-[700px] grid-cols-[3rem_3rem_1fr_repeat(4,_5rem)] gap-2 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                    <div className="text-center">#</div>
                                    <div />
                                    <div>Verein</div>
                                    <div className="text-center">Sp</div>
                                    <div className="text-center text-amber-400">Gelb</div>
                                    <div className="text-center text-rose-400">Rot</div>
                                    <div className="text-center">Fairplay-Pkt</div>
                                </div>
                                <StaggerGroup as="div">
                                    {fairplayTable.map((row, index) => {
                                        const isOwned = ownedClubIds.includes(row.club_id ?? row.club?.id);
                                        const yellow = row.yellow_cards ?? 0;
                                        const red = row.red_cards ?? 0;
                                        const fairplayScore = yellow + red * 3;
                                        const position = index + 1;
                                        return (
                                            <div
                                                key={row.club_id || index}
                                                className={`grid min-w-[700px] grid-cols-[3rem_3rem_1fr_repeat(4,_5rem)] gap-2 border-b border-white/5 px-6 py-4 transition-colors hover:bg-white/[0.03] ${isOwned ? 'border-l-2 border-l-[var(--accent-primary)]/40 bg-[var(--accent-primary)]/[0.03]' : ''}`}
                                            >
                                                <div className={`text-center text-sm font-black italic ${position === 1 ? 'text-emerald-400' : 'text-[var(--text-muted)]'}`}>
                                                    {position}
                                                </div>
                                                <div className="flex justify-center">
                                                    <img
                                                        loading="lazy"
                                                        src={row.club?.logo_url || '/images/default-club.png'}
                                                        alt={row.club?.name}
                                                        className="h-8 w-8 object-contain"
                                                    />
                                                </div>
                                                <div className="flex min-w-0 items-center gap-3">
                                                    <ClubLink
                                                        id={row.club_id ?? row.club?.id}
                                                        name={row.club?.name}
                                                        className={`truncate text-sm font-black uppercase tracking-tight transition-colors hover:text-[var(--accent-primary)] ${isOwned ? 'text-[var(--text-main)]' : 'text-slate-300'}`}
                                                    />
                                                    {isOwned && <ShieldCheck size={14} weight="fill" className="shrink-0 text-[var(--accent-primary)]" />}
                                                </div>
                                                <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.matches_played ?? 0}</div>
                                                <div className="text-center text-xs font-bold text-amber-400">{yellow}</div>
                                                <div className="text-center text-xs font-bold text-rose-400">{red}</div>
                                                <div className="text-center text-sm font-black text-[var(--text-main)]">{fairplayScore}</div>
                                            </div>
                                        );
                                    })}
                                </StaggerGroup>
                            </div>
                            <div className="border-t border-white/5 px-6 py-3 text-[9px] font-bold text-[var(--text-muted)]">
                                Fairplay-Punkte = Gelbe Karten × 1 + Rote Karten × 3 · Niedrigster Wert = beste Disziplin
                            </div>
                        </SectionCard>
                    </PageReveal>
                ) : (
                    <PageReveal>
                        <SectionCard title="Torschuetzenkoenig" icon={SoccerBall} bodyClassName="overflow-hidden">
                            {topScorers.length === 0 ? (
                                <div className="p-16 text-center">
                                    <SoccerBall size={40} weight="thin" className="mx-auto mb-4 text-slate-700" />
                                    <p className="text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Noch keine Tore gefallen.</p>
                                </div>
                            ) : (
                                <div className="overflow-x-auto">
                                    <div className="grid min-w-[640px] grid-cols-[3rem_3rem_1fr_6rem_5rem_5rem_5rem] gap-2 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                        <div className="text-center">#</div>
                                        <div />
                                        <div>Spieler</div>
                                        <div>Verein</div>
                                        <div className="text-center">Sp</div>
                                        <div className="text-center text-amber-400">Tore</div>
                                        <div className="text-center">Vorlagen</div>
                                    </div>
                                    <StaggerGroup as="div">
                                        {topScorers.map((row, index) => (
                                            <div
                                                key={row.player_id}
                                                className="grid min-w-[640px] grid-cols-[3rem_3rem_1fr_6rem_5rem_5rem_5rem] gap-2 items-center border-b border-white/5 px-6 py-3 transition-colors hover:bg-white/[0.03]"
                                            >
                                                <div className={`text-center text-sm font-black italic ${index === 0 ? 'text-amber-400' : index < 3 ? 'text-slate-300' : 'text-[var(--text-muted)]'}`}>
                                                    {index + 1}
                                                </div>
                                                <div className="flex justify-center">
                                                    {row.player_photo ? (
                                                        <img loading="lazy" src={row.player_photo} alt={row.player_name} className="h-8 w-8 rounded-lg object-cover" />
                                                    ) : (
                                                        <div className="h-8 w-8 rounded-lg bg-[var(--bg-content)] flex items-center justify-center text-[9px] font-black text-[var(--text-muted)]">
                                                            {row.position}
                                                        </div>
                                                    )}
                                                </div>
                                                <div className="min-w-0">
                                                    <Link href={route('players.show', row.player_id)} className="block truncate text-sm font-black uppercase tracking-tight text-[var(--text-main)] hover:text-[var(--accent-primary)] transition-colors">
                                                        {row.player_name}
                                                    </Link>
                                                    <span className="text-[9px] font-black text-[var(--accent-primary)] uppercase tracking-widest">{row.position}</span>
                                                </div>
                                                <div className="flex items-center gap-1.5 min-w-0">
                                                    {row.club_logo && <img loading="lazy" src={row.club_logo} alt={row.club_name} className="h-5 w-5 object-contain shrink-0" />}
                                                    <span className="truncate text-[10px] font-bold text-[var(--text-muted)]">{row.club_name}</span>
                                                </div>
                                                <div className="text-center text-xs font-bold text-[var(--text-muted)]">{row.matches}</div>
                                                <div className="text-center">
                                                    <span className={`text-base font-black italic ${index === 0 ? 'text-amber-400' : 'text-[var(--text-main)]'}`}>{row.goals}</span>
                                                    {index === 0 && <Crown size={10} weight="fill" className="inline ml-1 text-amber-400" />}
                                                </div>
                                                <div className="text-center text-sm font-bold text-slate-400">{row.assists}</div>
                                            </div>
                                        ))}
                                    </StaggerGroup>
                                </div>
                            )}
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
