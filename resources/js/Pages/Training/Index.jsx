import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { PageReveal } from '@/Components/PageReveal';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import EmptyState from '@/Components/EmptyState';
import StatusMessage from '@/Components/StatusMessage';
import {
    GraduationCap,
    Lightning,
    Target,
    Heartbeat,
    Users,
    Calendar,
    WarningCircle,
    CheckCircle,
    Plus,
} from '@phosphor-icons/react';

const intensityColors = {
    low: 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20',
    medium: 'text-amber-400 bg-amber-500/10 border-amber-500/20',
    high: 'text-rose-400 bg-rose-500/10 border-rose-500/20',
};

const typeIcons = {
    fitness: Lightning,
    tactics: Target,
    technical: GraduationCap,
    recovery: Heartbeat,
    friendly: Users,
};

export default function Training({ sessions, club, prefillDate }) {
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, reset } = useForm({
        club_id: club?.id || '',
        type: 'technical',
        intensity: 'medium',
        focus_position: '',
        session_date: prefillDate,
        notes: '',
        player_ids: club?.players?.map((player) => player.id) || [],
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        post(route('training.store'), {
            onSuccess: () => {
                setShowForm(false);
                reset();
            },
        });
    };

    if (!club) {
        return (
            <AuthenticatedLayout>
                <EmptyState
                    icon={WarningCircle}
                    title="Kein Verein aktiv"
                    description="Es konnte kein aktiver Verein gefunden werden. Bitte waehle einen Verein aus der Liste."
                    className="py-20"
                />
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Training" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                <PageHeader
                    eyebrow="Leistungsentwicklung"
                    title="Trainingszentrum"
                    actions={(
                        <button onClick={() => setShowForm((open) => !open)} className="sim-btn-primary flex items-center gap-2 px-6 py-3">
                        <Plus size={20} weight="bold" />
                        Neue Einheit
                        </button>
                    )}
                />

                {showForm && (
                    <PageReveal className="overflow-hidden transition-all duration-300" delay={90}>
                        <SectionCard title="Neue Trainingseinheit Planen" icon={Plus}>
                            <form onSubmit={handleSubmit} className="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                <div className="md:col-span-2 lg:col-span-4">
                                    <StatusMessage variant="info">
                                        {club.players.length} Spieler aus {club.name} werden fuer neue Einheiten vorausgewaehlt.
                                    </StatusMessage>
                                </div>
                                <SelectField label="Typ" value={data.type} onChange={(event) => setData('type', event.target.value)}>
                                    <option value="technical">Technik</option>
                                    <option value="tactics">Taktik</option>
                                    <option value="fitness">Fitness</option>
                                    <option value="recovery">Erholung</option>
                                    <option value="friendly">Testspiel</option>
                                </SelectField>
                                <SelectField label="Intensitaet" value={data.intensity} onChange={(event) => setData('intensity', event.target.value)}>
                                    <option value="low">Niedrig</option>
                                    <option value="medium">Mittel</option>
                                    <option value="high">Hoch</option>
                                </SelectField>
                                <div className="space-y-2">
                                    <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Datum</label>
                                    <input
                                        type="date"
                                        value={data.session_date}
                                        onChange={(event) => setData('session_date', event.target.value)}
                                        className="w-full sim-input font-bold"
                                    />
                                </div>
                                <div className="space-y-2 lg:col-span-1">
                                    <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Aktion</label>
                                    <button type="submit" disabled={processing} className="w-full sim-btn-primary py-2.5">
                                        Planung speichern
                                    </button>
                                </div>
                            </form>
                        </SectionCard>
                    </PageReveal>
                )}

                {(club.medical_summary?.risk_count > 0 || club.medical_summary?.rehab_count > 0) && (
                    <PageReveal delay={95}>
                        <StatusMessage variant="warning">
                            {club.medical_summary.rehab_count > 0 ? `${club.medical_summary.rehab_count} Spieler in Reha. ` : ''}
                            {club.medical_summary.risk_count > 0 ? `${club.medical_summary.risk_count} Spieler mit hohem Verletzungsrisiko. ` : ''}
                            Belastung und Rueckkehrplaene im Medical Center pruefen.
                        </StatusMessage>
                    </PageReveal>
                )}

                <PageReveal delay={110}>
                    <SectionCard title="Belastungssteuerung" icon={Heartbeat}>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left">
                                <thead>
                                    <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                        <th className="px-6 py-4">Spieler</th>
                                        <th className="px-6 py-4">Pos</th>
                                        <th className="px-6 py-4">Fatigue</th>
                                        <th className="px-6 py-4">Sharpness</th>
                                        <th className="px-6 py-4">Zufriedenheit</th>
                                        <th className="px-6 py-4">Risiko</th>
                                        <th className="px-6 py-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-800/50">
                                    {club.load_rows?.map((row) => (
                                        <tr key={row.id}>
                                            <td className="px-6 py-4 text-sm font-black text-[var(--text-main)]">{row.name}</td>
                                            <td className="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{row.position}</td>
                                            <td className="px-6 py-4 text-sm font-black text-amber-400">{row.fatigue}</td>
                                            <td className="px-6 py-4 text-sm font-black text-emerald-400">{row.sharpness}</td>
                                            <td className="px-6 py-4 text-sm font-black text-cyan-400">{row.happiness}</td>
                                            <td className="px-6 py-4 text-sm font-black text-rose-400">{row.injury_risk}%</td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border ${
                                                    row.medical_status === 'risk'
                                                        ? 'text-rose-400 bg-rose-500/10 border-rose-500/20'
                                                        : row.medical_status === 'monitoring'
                                                            ? 'text-amber-400 bg-amber-500/10 border-amber-500/20'
                                                            : 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20'
                                                }`}>
                                                    {row.medical_status}
                                                </span>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="px-6 pb-6 pt-2">
                            <a href={route('medical.index')} className="text-[10px] font-black uppercase tracking-widest text-cyan-300 hover:text-white">
                                Zum Medical Center
                            </a>
                        </div>
                    </SectionCard>
                </PageReveal>

                <PageReveal delay={140}>
                    <SectionCard title="Trainingseinheiten" icon={Calendar}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                    <th className="px-6 py-4">Datum</th>
                                    <th className="px-6 py-4">Typ</th>
                                    <th className="px-6 py-4">Intensitaet</th>
                                    <th className="px-6 py-4">Spieler</th>
                                    <th className="px-6 py-4">Status</th>
                                    <th className="px-6 py-4 text-right">Aktionen</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {sessions.data.map((session) => {
                                    const Icon = typeIcons[session.type] || GraduationCap;

                                    return (
                                        <tr key={session.id} className="group hover:bg-white/[0.02] transition-colors">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span className="text-sm font-bold text-[var(--text-muted)] font-mono italic">
                                                    {new Date(session.session_date).toLocaleDateString('de-DE')}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex items-center gap-3">
                                                    <div className="h-8 w-8 rounded-lg bg-[var(--bg-content)] flex items-center justify-center text-[var(--accent-primary)] border border-[var(--border-muted)]">
                                                        <Icon size={18} weight="duotone" />
                                                    </div>
                                                    <span className="text-sm font-black text-[var(--text-main)] uppercase tracking-tight">
                                                        {session.type}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border ${intensityColors[session.intensity]}`}>
                                                    {session.intensity}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm font-bold text-[var(--text-muted)]">
                                                {session.player_count}
                                            </td>
                                            <td className="px-6 py-4">
                                                {session.applied_at ? (
                                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                                                        <CheckCircle size={14} weight="fill" />
                                                        Absolviert
                                                    </span>
                                                ) : (
                                                    <span className="flex items-center gap-1.5 text-[10px] font-black uppercase tracking-widest text-amber-500">
                                                        <HourglassMedium size={14} />
                                                        Geplant
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                {!session.applied_at && (
                                                    <button
                                                        onClick={() => post(route('training.apply', session.id))}
                                                        className="text-[10px] font-black uppercase tracking-widest text-[var(--accent-primary)] hover:text-[var(--text-main)] transition-colors border border-[var(--border-pillar)] px-3 py-1.5 rounded-lg bg-[var(--accent-glow)]"
                                                    >
                                                        Einheit durchfuehren
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                })}
                                {sessions.data.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-[var(--text-muted)] italic text-sm">
                                            Keine Trainingseinheiten fuer diesen Zeitraum geplant.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    </SectionCard>
                </PageReveal>
            </div>
        </AuthenticatedLayout>
    );
}

function SelectField({ label, value, onChange, children }) {
    return (
        <div className="space-y-2">
            <label className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</label>
            <select value={value} onChange={onChange} className="w-full sim-select font-bold">
                {children}
            </select>
        </div>
    );
}

const HourglassMedium = ({ size, className }) => (
    <div className={className} style={{ fontSize: size }}>
        ⌛
    </div>
);
