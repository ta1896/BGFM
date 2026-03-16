import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import LiveMatchCard from '@/Components/live/LiveMatchCard';
import useLiveOverview from '@/hooks/useLiveOverview';
import { UsersThree } from '@phosphor-icons/react';

export default function Ticker({ liveMatches = [], onlineManagersCount = 0 }) {
    const liveOverview = useLiveOverview({ initialLiveMatches: liveMatches });

    return (
        <AuthenticatedLayout>
            <Head title="Live-Ticker" />

            <div className="space-y-8">
                <PageHeader eyebrow="Live-Ticker" title="Welche Spiele laufen gerade?" />
                <p className="max-w-3xl text-sm leading-relaxed text-[var(--text-muted)]">
                    Live-Matches als schnelle Uebersicht. Von hier kommst du direkt ins Matchcenter oder zur Manager-Online-Ansicht.
                </p>

                <SectionCard>
                    <div className="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <div className="text-xs font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Live Matches</div>
                            <div className="mt-1 text-2xl font-black uppercase tracking-tight text-white">{liveOverview.liveMatchesCount}</div>
                        </div>
                        <Link
                            href={route('manager-live.index')}
                            className="inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-emerald-200"
                        >
                            <UsersThree size={12} weight="fill" />
                            {liveOverview.onlineManagersCount || onlineManagersCount} Manager online
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-3">
                        {liveOverview.liveMatchesCount > 0 ? liveOverview.liveMatches.map((match) => (
                            <LiveMatchCard key={match.id} match={match} href={route('matches.show', match.id)} />
                        )) : (
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-8 text-center text-sm text-[var(--text-muted)]">
                                Derzeit laeuft kein Match live.
                            </div>
                        )}
                    </div>
                </SectionCard>
            </div>
        </AuthenticatedLayout>
    );
}
