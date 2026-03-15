import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Trophy, Crown, Star } from '@phosphor-icons/react';

export default function Index({ competitionSeasons, activeCompetitionSeasonId, currentAwards, history }) {
    return (
        <AuthenticatedLayout>
            <Head title="Saison-Awards" />
            <div className="mx-auto max-w-[1500px] space-y-8">
                <PageHeader
                    eyebrow="Meta"
                    title="Saison-Awards"
                    actions={(
                        <select
                            value={activeCompetitionSeasonId || ''}
                            onChange={(event) => router.get(route('awards.index'), { competition_season_id: event.target.value }, { preserveState: true })}
                            className="sim-select min-w-[260px]"
                        >
                            {competitionSeasons.map((season) => (
                                <option key={season.id} value={season.id}>{season.label}</option>
                            ))}
                        </select>
                    )}
                />

                <SectionCard title="Aktuelle Gewinner" icon={Trophy} bodyClassName="p-6">
                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        {currentAwards.map((award) => (
                            <AwardCard key={award.id} award={award} />
                        ))}
                    </div>
                </SectionCard>

                <SectionCard title="Historie" icon={Star} bodyClassName="p-6 space-y-6">
                    {history.map((entry) => (
                        <div key={entry.competition_season_id} className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30 p-5">
                            <div className="mb-4 text-sm font-black uppercase tracking-[0.08em] text-white">{entry.season_label}</div>
                            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                {entry.awards.map((award) => (
                                    <HistoryRow key={award.id} award={award} />
                                ))}
                            </div>
                        </div>
                    ))}
                </SectionCard>
            </div>
        </AuthenticatedLayout>
    );
}

function AwardCard({ award }) {
    return (
        <div className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30 p-5">
            <div className="mb-4 flex items-start justify-between gap-3">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Award</div>
                    <div className="mt-1 text-lg font-black uppercase tracking-tight text-white">{award.label}</div>
                </div>
                <div className="rounded-2xl border border-amber-400/20 bg-amber-500/10 px-3 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-amber-200">
                    {award.value_label}
                </div>
            </div>

            <div className="flex items-center gap-4">
                {award.player ? (
                    <>
                        <img src={award.player.photo_url} alt={award.player.name} className="h-14 w-14 rounded-2xl border border-white/10 object-cover" />
                        <div className="min-w-0">
                            <Link href={route('players.show', award.player.id)} className="truncate text-sm font-black uppercase tracking-[0.06em] text-white hover:text-cyan-300">
                                {award.player.name}
                            </Link>
                            {award.club && (
                                <div className="mt-1 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                                    <img src={award.club.logo_url} alt={award.club.name} className="h-4 w-4 object-contain" />
                                    {award.club.name}
                                </div>
                            )}
                        </div>
                    </>
                ) : (
                    <div className="flex items-center gap-4">
                        {award.club && <img src={award.club.logo_url} alt={award.club.name} className="h-14 w-14 rounded-2xl border border-white/10 object-contain bg-[var(--bg-content)]/50 p-2" />}
                        <div>
                            <div className="text-sm font-black uppercase tracking-[0.06em] text-white">{award.user?.name || award.club?.name}</div>
                            {award.club && <div className="text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">{award.club.name}</div>}
                        </div>
                    </div>
                )}
            </div>

            <p className="mt-4 text-sm leading-relaxed text-[var(--text-muted)]">{award.summary}</p>
        </div>
    );
}

function HistoryRow({ award }) {
    return (
        <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/40 px-4 py-4">
            <div className="mb-2 flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                <Crown size={12} weight="fill" className="text-amber-300" />
                {award.label}
            </div>
            <div className="text-sm font-black text-white">
                {award.player?.name || award.user?.name || award.club?.name}
            </div>
            <div className="mt-1 text-[10px] font-black uppercase tracking-[0.14em] text-amber-200">{award.value_label}</div>
        </div>
    );
}
