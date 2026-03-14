import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { NavigationArrow, HardHat, Users, Ticket, Tree, ShieldCheck, Wrench, Hammer, Sparkle } from '@phosphor-icons/react';
import MetricCard from '@/Components/MetricCard';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function LevelMetric({ label, level, icon: Icon }) {
    return (
        <div className="flex items-center justify-between rounded-xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/80 p-4 transition-colors hover:bg-[var(--bg-content)]/40">
            <div className="flex items-center gap-4">
                <div className="flex h-10 w-10 items-center justify-center rounded-lg border border-[var(--border-pillar)] bg-[var(--sim-shell-bg)] text-[var(--text-muted)]">
                    <Icon size={22} weight="duotone" />
                </div>
                <div>
                    <p className="text-sm font-black uppercase tracking-tight text-[var(--text-main)]">{label}</p>
                    <p className="text-[10px] font-bold uppercase text-[var(--text-muted)]">Stufe {level}</p>
                </div>
            </div>
            <div className="flex gap-1">
                {[...Array(5)].map((_, index) => (
                    <div
                        key={index}
                        className={`h-1.5 w-4 rounded-full ${index < level ? 'bg-[var(--accent-primary)] shadow-[0_0_8px_rgba(0,0,0,0.15)]' : 'bg-[var(--bg-content)]'}`}
                    />
                ))}
            </div>
        </div>
    );
}

export default function Stadium({ stadium, projects, projectTypes, activeClub }) {
    const { data, setData, post, processing } = useForm({
        club_id: activeClub?.id,
        project_type: 'capacity',
    });

    const submitProject = (event) => {
        event.preventDefault();
        post(route('stadium.projects.store'), { preserveScroll: true });
    };

    if (!activeClub || !stadium) {
        return <AuthenticatedLayout>Stadion-Management wird geladen...</AuthenticatedLayout>;
    }

    return (
        <AuthenticatedLayout>
            <Head title="Stadion und Infrastruktur" />

            <div className="mx-auto max-w-[1400px] space-y-8">
                <PageHeader eyebrow="Heimstaette" title={stadium.name} />

                <PageReveal className="rounded-[2rem] border border-[var(--border-muted)] bg-[linear-gradient(135deg,var(--bg-pillar),var(--sim-shell-bg))] p-8 md:p-10">
                    <div className="grid gap-8 lg:grid-cols-[1.4fr_1fr]">
                        <div className="space-y-4">
                            <div className="flex items-center gap-3">
                                <div className="h-10 w-10 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-2">
                                    <img loading="lazy" src={activeClub.logo_url} alt={activeClub.name} className="h-full w-full object-contain" />
                                </div>
                                <span className="text-[10px] font-black uppercase tracking-[0.4em] text-[var(--accent-primary)]">
                                    Infrastruktur
                                </span>
                            </div>
                            <p className="max-w-3xl text-lg leading-relaxed text-[var(--text-muted)]">
                                Modernisiere Stadion, Sicherheitskonzept und Umfeld von <span className="font-bold text-[var(--text-main)]">{activeClub.name}</span>,
                                um Einnahmen und Spieltagsqualitaet zu steigern.
                            </p>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <MetricCard label="Kapazitaet" value={stadium.capacity} unit="Plaetze" icon={Users} />
                            <MetricCard label="Ticketpreis" value={Number.parseFloat(stadium.ticket_price)} unit="EUR" icon={Ticket} />
                            <MetricCard label="Wartung" value={stadium.maintenance_cost} unit="EUR" icon={Wrench} />
                            <MetricCard label="Rasen" value={stadium.pitch_quality} unit="/10" icon={Sparkle} />
                        </div>
                    </div>
                </PageReveal>

                <div className="grid gap-8 lg:grid-cols-3">
                    <PageReveal className="space-y-8">
                        <SectionCard title="Bau-Zentrum" icon={HardHat} bodyClassName="space-y-6 p-6">
                            <form onSubmit={submitProject} className="space-y-6">
                                <div className="space-y-3">
                                    <label className="block text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Projekt-Kategorie</label>
                                    <div className="grid grid-cols-2 gap-2">
                                        {Object.entries(projectTypes).map(([type, label]) => (
                                            <button
                                                key={type}
                                                type="button"
                                                onClick={() => setData('project_type', type)}
                                                className={`rounded-xl border-2 px-3 py-4 text-left text-[10px] font-black uppercase tracking-widest transition-all ${
                                                    data.project_type === type
                                                        ? 'border-[var(--accent-primary)]/50 bg-[var(--accent-primary)]/10 text-[var(--text-main)]'
                                                        : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-[var(--text-muted)]'
                                                }`}
                                            >
                                                {label}
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                <button
                                    disabled={processing}
                                    className="w-full rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] py-4 text-sm font-black uppercase tracking-[0.1em] text-white transition-opacity disabled:opacity-50"
                                >
                                    {processing ? 'Planung laeuft...' : 'Projekt beauftragen'}
                                </button>
                            </form>
                        </SectionCard>

                        <SectionCard title="Aktuelle Infrastruktur" icon={NavigationArrow} bodyClassName="space-y-3 p-6">
                            <LevelMetric label="Trainingsanlagen" level={stadium.facility_level} icon={Sparkle} />
                            <LevelMetric label="Sicherheitskonzept" level={stadium.security_level} icon={ShieldCheck} />
                            <LevelMetric label="Parkplaetze und Umfeld" level={stadium.environment_level} icon={Tree} />
                        </SectionCard>
                    </PageReveal>

                    <PageReveal className="lg:col-span-2">
                        <SectionCard title="Baustellen-Protokoll" icon={Hammer} bodyClassName="overflow-hidden">
                            <div className="flex items-center justify-between border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/25 px-6 py-4">
                                <span className="text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">{projects.length} Eintraege</span>
                            </div>
                            <div className="overflow-x-auto">
                                <table className="w-full text-left">
                                    <thead className="border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                        <tr>
                                            <th className="px-6 py-5">Projekt</th>
                                            <th className="px-6 py-5">Level</th>
                                            <th className="px-6 py-5 text-right">Investment</th>
                                            <th className="px-6 py-5 text-right">Frist</th>
                                            <th className="px-6 py-5 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-white/5">
                                        {projects.length > 0 ? (
                                            projects.map((project) => (
                                                <tr key={project.id} className="transition-colors hover:bg-white/[0.02]">
                                                    <td className="px-6 py-4 font-black uppercase tracking-tight text-[var(--text-main)]">
                                                        {projectTypes[project.project_type] || project.project_type}
                                                    </td>
                                                    <td className="px-6 py-4 text-sm font-bold text-[var(--text-muted)]">
                                                        {project.level_from} - {project.level_to}
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-mono font-black text-[var(--text-main)]">
                                                        {project.cost.toLocaleString('de-DE')} EUR
                                                    </td>
                                                    <td className="px-6 py-4 text-right text-sm text-[var(--text-muted)]">
                                                        <p>{project.started_on_formatted}</p>
                                                        <p className="text-[10px] font-black uppercase tracking-widest text-slate-500">
                                                            bis {project.completes_on_formatted}
                                                        </p>
                                                    </td>
                                                    <td className="px-6 py-4 text-right">
                                                        <span className={`inline-flex rounded-lg border px-3 py-1 text-[10px] font-black uppercase tracking-widest ${
                                                            project.status === 'completed'
                                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                                : project.status === 'in_progress'
                                                                    ? 'border-amber-500/20 bg-amber-500/10 text-amber-400'
                                                                    : 'border-[var(--border-pillar)] bg-[var(--bg-content)] text-[var(--text-muted)]'
                                                        }`}>
                                                            {project.status}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="5" className="px-6 py-20 text-center text-sm italic text-[var(--text-muted)]">
                                                    Keine historischen Baudaten vorhanden.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </SectionCard>
                    </PageReveal>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
