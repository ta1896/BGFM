import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    Users, UserGear, Shield, Robot, UserCircle, ListNumbers,
    CalendarCheck, Suitcase, Warehouse, Play, ArrowsClockwise, CaretRight,
    Trophy, Gear, Wrench, FileText, Activity, List, Package, Gauge,
} from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function StatPill({ icon: Icon, label, value }) {
    return (
        <div className="flex items-center gap-3 rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 px-4 py-3">
            <Icon size={18} weight="duotone" className="shrink-0 text-[var(--text-muted)]" />
            <div className="min-w-0">
                <p className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
                <p className="text-xl font-black tracking-tighter text-[var(--text-main)]">{value}</p>
            </div>
        </div>
    );
}

function QuickCard({ href, icon: Icon, label, tone = 'default' }) {
    const tones = {
        default: 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] hover:bg-[var(--bg-content)] text-[var(--text-main)]',
        primary: 'border-[var(--accent-primary)]/25 bg-[var(--accent-primary)]/10 hover:bg-[var(--accent-primary)]/15 text-[var(--accent-primary)]',
        secondary: 'border-[var(--accent-secondary)]/25 bg-[var(--accent-secondary)]/10 hover:bg-[var(--accent-secondary)]/15 text-[var(--accent-secondary)]',
    };
    return (
        <Link
            href={href}
            className={`flex flex-col items-center gap-1.5 rounded-xl border px-3 py-3.5 text-center text-xs font-bold transition-colors ${tones[tone]}`}
        >
            <Icon size={20} weight="duotone" />
            <span>{label}</span>
        </Link>
    );
}

function StatGroup({ label, children }) {
    return (
        <div className="rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/30 p-4">
            <p className="mb-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</p>
            <div className="grid grid-cols-2 gap-2">{children}</div>
        </div>
    );
}

export default function Dashboard({ stats, latestUsers, latestClubs, activeCompetitionSeasons }) {
    const { data, setData, post, processing } = useForm({ competition_season_id: '' });

    const runSimulation = (e) => {
        e.preventDefault();
        post(route('admin.simulation.process-matchday'));
    };

    return (
        <AdminLayout>
            <Head title="Admin Dashboard" />

            <div className="space-y-8 pb-20">
                <PageHeader eyebrow="Administration" title="Dashboard" />

                {/* Stats: 3 semantic groups */}
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <PageReveal>
                        <StatGroup label="Plattform">
                            <StatPill icon={Users} label="User" value={stats.users} />
                            <StatPill icon={UserGear} label="Admins" value={stats.admins} />
                        </StatGroup>
                    </PageReveal>

                    <PageReveal>
                        <StatGroup label="Spielwelt">
                            <StatPill icon={Shield} label="Vereine" value={stats.clubs} />
                            <StatPill icon={Robot} label="CPU Teams" value={stats.cpu_clubs} />
                            <StatPill icon={UserCircle} label="Spieler" value={stats.players} />
                            <StatPill icon={ListNumbers} label="Aufstellungen" value={stats.lineups} />
                        </StatGroup>
                    </PageReveal>

                    <PageReveal>
                        <StatGroup label="Aktivität">
                            <StatPill icon={CalendarCheck} label="Geplante Spiele" value={stats.scheduled_matches} />
                            <StatPill icon={Suitcase} label="Sponsoren" value={stats.active_sponsors} />
                            <StatPill icon={Warehouse} label="Stadionprojekte" value={stats.active_stadium_projects} />
                        </StatGroup>
                    </PageReveal>
                </div>

                {/* Main grid */}
                <div className="grid gap-6 xl:grid-cols-3">
                    {/* Left column */}
                    <div className="xl:col-span-2 space-y-6">
                        {/* Simulation control */}
                        <PageReveal>
                            <SectionCard title="Spieltag starten" icon={Play} bodyClassName="p-6">
                                <form onSubmit={runSimulation} className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div className="md:col-span-2">
                                        <label className="mb-2 block px-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                            Wettbewerb
                                        </label>
                                        <select
                                            className="sim-select w-full"
                                            value={data.competition_season_id}
                                            onChange={(e) => setData('competition_season_id', e.target.value)}
                                        >
                                            <option value="">Alle aktiven Ligen</option>
                                            {activeCompetitionSeasons.map((cs) => (
                                                <option key={cs.id} value={cs.id}>{cs.label}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="flex items-end">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="flex h-[46px] w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] font-black text-white transition-opacity disabled:opacity-50"
                                        >
                                            {processing
                                                ? <ArrowsClockwise size={18} className="animate-spin" />
                                                : <Play size={18} weight="fill" />
                                            }
                                            Starten
                                        </button>
                                    </div>
                                </form>
                            </SectionCard>
                        </PageReveal>

                        {/* Quick access */}
                        <PageReveal>
                            <SectionCard title="Schnellzugriff" icon={Gauge} bodyClassName="p-6 space-y-6">
                                <div>
                                    <p className="mb-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Erstellen</p>
                                    <div className="grid grid-cols-3 gap-3">
                                        <QuickCard href={route('admin.competitions.create')} icon={Trophy} label="Liga" />
                                        <QuickCard href={route('admin.clubs.create')} icon={Shield} label="Verein" tone="primary" />
                                        <QuickCard href={route('admin.players.create')} icon={UserCircle} label="Spieler" />
                                    </div>
                                </div>

                                <div>
                                    <p className="mb-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Engine & Simulation</p>
                                    <div className="grid grid-cols-3 gap-3">
                                        <QuickCard href={route('admin.match-engine.index')} icon={Gear} label="Match Engine" tone="primary" />
                                        <QuickCard href={route('admin.simulation.settings.index')} icon={Wrench} label="Sim Settings" />
                                        <QuickCard href={route('admin.ticker-templates.index')} icon={FileText} label="Ticker" />
                                    </div>
                                </div>

                                <div>
                                    <p className="mb-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">System</p>
                                    <div className="grid grid-cols-3 gap-3">
                                        <QuickCard href={route('admin.monitoring.index')} icon={Activity} label="Monitoring" tone="secondary" />
                                        <QuickCard href={route('admin.navigation.index')} icon={List} label="Navigation" />
                                        <QuickCard href={route('admin.modules.index')} icon={Package} label="Module" />
                                    </div>
                                </div>
                            </SectionCard>
                        </PageReveal>
                    </div>

                    {/* Right column: recent activity */}
                    <div className="space-y-6">
                        <PageReveal>
                            <SectionCard title="Letzte User" icon={Users} bodyClassName="space-y-2 p-4">
                                {latestUsers.map((user) => (
                                    <div key={user.id} className="flex items-center justify-between rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 p-3 transition-colors hover:bg-[var(--bg-content)]/40">
                                        <div className="min-w-0">
                                            <p className="mb-0.5 truncate text-sm font-bold text-[var(--text-main)]">{user.name}</p>
                                            <p className="truncate text-[10px] font-medium text-[var(--text-muted)]">{user.email}</p>
                                        </div>
                                        {user.is_admin && (
                                            <span className="ml-2 shrink-0 rounded border border-[var(--accent-primary)]/20 bg-[var(--accent-primary)]/10 px-2 py-0.5 text-[9px] font-black uppercase tracking-widest text-[var(--accent-primary)]">
                                                Admin
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </SectionCard>
                        </PageReveal>

                        <PageReveal>
                            <SectionCard title="Letzte Vereine" icon={Shield} bodyClassName="space-y-2 p-4">
                                {latestClubs.map((club) => (
                                    <div key={club.id} className="flex items-center justify-between rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 p-3 transition-colors hover:bg-[var(--bg-content)]/40">
                                        <div className="min-w-0">
                                            <p className="mb-0.5 truncate text-sm font-bold text-[var(--text-main)]">{club.name}</p>
                                            <p className="truncate text-[10px] italic font-medium text-[var(--text-muted)]">{club.user?.name ?? 'CPU'}</p>
                                        </div>
                                        <Link
                                            href={route('admin.clubs.edit', club.id)}
                                            className="ml-2 shrink-0 rounded-lg p-1.5 text-[var(--text-muted)] transition-colors hover:bg-[var(--accent-primary)]/10 hover:text-[var(--accent-primary)]"
                                        >
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
