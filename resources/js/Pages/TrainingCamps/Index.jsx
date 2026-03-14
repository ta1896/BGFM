import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import { Tent, Calendar, WarningCircle, CheckCircle, Plus, MapPin } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

export default function TrainingCamps({ camps }) {
    const { activeClub } = usePage().props;

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <WarningCircle size={64} weight="thin" className="mb-6 text-slate-700" />
                    <h2 className="mb-2 text-2xl font-bold text-[var(--text-main)]">Kein Verein aktiv</h2>
                    <p className="max-w-md text-[var(--text-muted)]">Es konnte kein aktiver Verein gefunden werden. Bitte waehle einen Verein aus der Liste.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Trainingslager" />

            <div className="mx-auto max-w-[1400px] space-y-8">
                <PageHeader
                    eyebrow="Vorbereitung"
                    title="Trainingslager"
                    actions={
                        <button className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-6 py-3 font-black text-white transition-opacity disabled:opacity-50">
                            <Plus size={20} weight="bold" />
                            Lager buchen
                        </button>
                    }
                />

                <PageReveal>
                    <SectionCard title="Aktive und geplante Lager" icon={Tent} bodyClassName="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                    <th className="px-6 py-4">Zeitraum</th>
                                    <th className="px-6 py-4">Name und Ort</th>
                                    <th className="px-6 py-4">Fokus</th>
                                    <th className="px-6 py-4">Intensitaet</th>
                                    <th className="px-6 py-4 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {camps.data.map((camp) => (
                                    <tr key={camp.id} className="group transition-colors hover:bg-white/[0.02]">
                                        <td className="whitespace-nowrap px-6 py-4">
                                            <div className="flex items-center gap-3 font-mono text-sm font-bold italic text-[var(--text-muted)]">
                                                <Calendar size={16} className="text-slate-600" />
                                                {new Date(camp.starts_on).toLocaleDateString('de-DE')} - {new Date(camp.ends_on).toLocaleDateString('de-DE')}
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex h-8 w-8 items-center justify-center rounded-lg border border-[var(--border-muted)] bg-[var(--bg-content)] text-[var(--accent-primary)]">
                                                    <MapPin size={18} weight="duotone" />
                                                </div>
                                                <span className="text-sm font-black uppercase tracking-tight text-[var(--text-main)]">{camp.name}</span>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <span className="rounded border border-[var(--border-pillar)] bg-[var(--bg-content)] px-2 py-1 text-[10px] font-black uppercase tracking-widest text-slate-300">
                                                {camp.focus}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-sm font-bold text-[var(--text-muted)]">{camp.intensity}</td>
                                        <td className="px-6 py-4 text-right">
                                            <span className="inline-flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                                                <CheckCircle size={14} weight="fill" />
                                                Gebucht
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                                {camps.data.length === 0 && (
                                    <tr>
                                        <td colSpan="5" className="px-6 py-12 text-center text-sm italic text-[var(--text-muted)]">
                                            Keine Trainingslager in der Planung.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </SectionCard>
                </PageReveal>
            </div>
        </AuthenticatedLayout>
    );
}
