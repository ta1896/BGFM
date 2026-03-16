import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import ManagerPresenceCard from '@/Components/live/ManagerPresenceCard';
import useLiveOverview from '@/hooks/useLiveOverview';
import { Broadcast } from '@phosphor-icons/react';

export default function Online({ onlineManagers = [], onlineWindowMinutes = 5 }) {
    const liveOverview = useLiveOverview({ initialOnlineManagers: onlineManagers, initialOnlineWindowMinutes: onlineWindowMinutes });

    return (
        <AuthenticatedLayout>
            <Head title="Manager Online" />

            <div className="space-y-8">
                <PageHeader eyebrow="Manager Live" title="Welche Manager sind online?" />
                <p className="max-w-3xl text-sm leading-relaxed text-[var(--text-muted)]">
                    {`Zeigt aktive Manager der letzten ${liveOverview.onlineWindowMinutes} Minuten und deren aktuelle Aktion.`}
                </p>

                <SectionCard>
                    <div className="mb-5 flex items-center justify-between gap-3">
                        <div>
                            <div className="text-xs font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Online Manager</div>
                            <div className="mt-1 text-2xl font-black uppercase tracking-tight text-white">{liveOverview.onlineManagersCount}</div>
                        </div>
                        <Link
                            href={route('live-ticker.index')}
                            className="inline-flex items-center gap-2 rounded-full border border-cyan-300/20 bg-cyan-300/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-cyan-200"
                        >
                            <Broadcast size={12} weight="fill" />
                            Live-Ticker
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-3 xl:grid-cols-2">
                        {liveOverview.onlineManagersCount > 0 ? liveOverview.onlineManagers.map((manager) => (
                            <ManagerPresenceCard key={manager.id} manager={manager} />
                        )) : (
                            <div className="rounded-2xl border border-white/10 bg-white/[0.03] px-4 py-8 text-center text-sm text-[var(--text-muted)] xl:col-span-2">
                                Aktuell ist kein Manager online.
                            </div>
                        )}
                    </div>
                </SectionCard>
            </div>
        </AuthenticatedLayout>
    );
}
