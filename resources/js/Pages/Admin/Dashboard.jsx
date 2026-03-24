import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    Users, UserGear, BuildingOffice, UserCircle, CalendarCheck, Suitcase, Warehouse,
    Lightning, Play, CaretRight, ArrowsClockwise, ListNumbers
} from '@phosphor-icons/react';
import MetricCard from '@/Components/MetricCard';
import PageHeader from '@/Components/PageHeader';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

const actionClass = 'rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-3 text-center text-sm font-black text-[var(--text-main)] transition-colors hover:bg-[var(--bg-content)]';
const actionPrimaryClass = 'rounded-xl border border-[var(--accent-primary)]/25 bg-[var(--accent-primary)]/10 px-4 py-3 text-center text-sm font-black text-[var(--accent-primary)] transition-colors hover:bg-[var(--accent-primary)]/15';
const actionHighlightClass = 'rounded-xl border border-[var(--accent-secondary)]/25 bg-[var(--accent-secondary)]/10 px-4 py-3 text-center text-sm font-black text-[var(--accent-secondary)] transition-colors hover:bg-[var(--accent-secondary)]/15';

export default function Dashboard({ stats, latestUsers, latestClubs, activeCompetitionSeasons }) {
    const { data, setData, post, processing } = useForm({
        competition_season_id: '',
    });

    const runSimulation = (event) => {
        event.preventDefault();
        post(route('admin.simulation.process-matchday'));
    };

    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />

            <div className="space-y-10 pb-20">
                <PageHeader eyebrow="Administration" title="Dashboard" />

                <StaggerGroup className="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                    <MetricCard label="Total User" value={stats.users} icon={Users} />
                    <MetricCard label="Administratoren" value={stats.admins} icon={UserGear} />
                    <MetricCard label="Vereine" value={stats.clubs} icon={BuildingOffice} />
                    <MetricCard label="CPU Teams" value={stats.cpu_clubs} icon={Lightning} />
                    <MetricCard label="Spieler" value={stats.players} icon={UserCircle} />
                    <MetricCard label="Aufstellungen" value={stats.lineups} icon={ListNumbers} />
                    <MetricCard label="Geplante Spiele" value={stats.scheduled_matches} icon={CalendarCheck} />
                    <MetricCard label="Sponsoren" value={stats.active_sponsors} icon={Suitcase} />
                    <MetricCard label="Stadionprojekte" value={stats.active_stadium_projects} icon={Warehouse} />
                </StaggerGroup>

                <div className="grid gap-8 xl:grid-cols-2">
                    <PageReveal>
                        <SectionCard title="Simulation und Kontrolle" icon={Play} bodyClassName="space-y-8 p-6">
                            <form onSubmit={runSimulation} className="grid grid-cols-1 gap-4 rounded-2xl border border-[var(--border-muted)] bg-[var(--sim-shell-bg)]/50 p-4 md:grid-cols-3">
                                <div className="md:col-span-2">
                                    <label className="mb-2 block px-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Wettbewerb waehlen</label>
                                    <select
                                        className="sim-select w-full"
                                        value={data.competition_season_id}
                                        onChange={(event) => setData('competition_season_id', event.target.value)}
                                    >
                                        <option value="">Alle aktiven Ligen</option>
                                        {activeCompetitionSeasons.map((competitionSeason) => (
                                            <option key={competitionSeason.id} value={competitionSeason.id}>{competitionSeason.label}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex items-end">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex h-[46px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] font-black text-white transition-opacity disabled:opacity-50"
                                    >
                                        {processing ? <ArrowsClockwise size={18} className="animate-spin" /> : <Play size={18} weight="fill" />}
                                        Spieltag starten
                                    </button>
                                </div>
                            </form>

                            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                <Link href={route('admin.competitions.create')} className={actionClass}>Liga erstellen</Link>
                                <Link href={route('admin.clubs.create')} className={actionPrimaryClass}>Verein erstellen</Link>
                                <Link href={route('admin.players.create')} className={actionClass}>Spieler erstellen</Link>
                                <Link href={route('admin.match-engine.index')} className={actionPrimaryClass}>Match Engine</Link>
                                <Link href={route('admin.monitoring.index')} className={actionHighlightClass}>System Monitor</Link>
                                <Link href={route('admin.simulation.settings.index')} className={actionClass}>Simulation Setup</Link>
                            </div>
                        </SectionCard>
                    </PageReveal>

                    <div className="grid gap-8 sm:grid-cols-2">
                        <PageReveal>
                            <SectionCard title="Letzte User" icon={Users} bodyClassName="space-y-2 p-6">
                                {latestUsers.map((user) => (
                                    <div key={user.id} className="flex items-center justify-between rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 p-3 transition-colors hover:bg-[var(--bg-content)]/40">
                                        <div className="min-w-0">
                                            <p className="mb-1 truncate text-sm font-bold text-[var(--text-main)]">{user.name}</p>
                                            <p className="truncate text-[10px] font-medium text-[var(--text-muted)]">{user.email}</p>
                                        </div>
                                        {user.is_admin && (
                                            <span className="rounded border border-[var(--accent-primary)]/20 bg-[var(--accent-primary)]/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-[var(--accent-primary)]">
                                                Admin
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </SectionCard>
                        </PageReveal>

                        <PageReveal>
                            <SectionCard title="Letzte Vereine" icon={BuildingOffice} bodyClassName="space-y-2 p-6">
                                {latestClubs.map((club) => (
                                    <div key={club.id} className="flex items-center justify-between rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 p-3 transition-colors hover:bg-[var(--bg-content)]/40">
                                        <div className="min-w-0">
                                            <p className="mb-1 truncate text-sm font-bold text-[var(--text-main)]">{club.name}</p>
                                            <p className="truncate text-[10px] font-medium italic text-[var(--text-muted)]">Owner: {club.user?.name || 'CPU'}</p>
                                        </div>
                                        <Link href={route('admin.clubs.edit', club.id)} className="rounded-lg p-1.5 text-[var(--text-muted)] transition-colors hover:bg-[var(--accent-primary)]/10 hover:text-[var(--accent-primary)]">
                                            <CaretRight size={16} weight="bold" />
                                        </Link>
                                    </div>
                                ))}
                            </SectionCard>
                        </PageReveal>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
