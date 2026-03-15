import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import { Heartbeat, WarningCircle, ShieldCheck, ArrowClockwise } from '@phosphor-icons/react';

export default function Index({ club, medicalBoard }) {
    const form = useForm({
        rehab_intensity: 'medium',
        return_phase: 'recovery',
        notes: '',
    });

    const submitPlan = (playerId, values) => {
        form.transform(() => values).post(route('medical.plan.update', playerId), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Medical Center" />
            <div className="mx-auto max-w-[1500px] space-y-8">
                <PageHeader
                    eyebrow="Squad Dynamics"
                    title="Medical Center"
                    description={club ? `${club.name} medizinische Steuerung fuer Reha, Risiko und Rueckkehr.` : 'Kein aktiver Verein'}
                />

                <div className="grid gap-4 md:grid-cols-3">
                    <SummaryCard icon={Heartbeat} label="Verletzt" value={medicalBoard.summary.injured_count} tone="rose" />
                    <SummaryCard icon={ArrowClockwise} label="Rueckkehrfenster" value={medicalBoard.summary.rehab_count} tone="amber" />
                    <SummaryCard icon={WarningCircle} label="Unter Beobachtung" value={medicalBoard.summary.risk_count} tone="cyan" />
                </div>

                <div className="grid gap-8 xl:grid-cols-[1.15fr_1fr]">
                    <SectionCard title="Reha-Board" icon={Heartbeat} bodyClassName="p-6 space-y-4">
                        {medicalBoard.injured.length ? medicalBoard.injured.map((player) => (
                            <MedicalRow
                                key={player.id}
                                player={player}
                                processing={form.processing}
                                onSubmit={submitPlan}
                            />
                        )) : (
                            <EmptyCopy text="Keine aktiven Verletzungen." />
                        )}
                    </SectionCard>

                    <div className="space-y-8">
                        <SectionCard title="Monitoring" icon={WarningCircle} bodyClassName="p-6 space-y-4">
                            {medicalBoard.monitoring.length ? medicalBoard.monitoring.map((player) => (
                                <CompactMedicalRow key={player.id} player={player} />
                            )) : <EmptyCopy text="Keine akuten Risikospieler." />}
                        </SectionCard>

                        <SectionCard title="Rueckkehrkandidaten" icon={ShieldCheck} bodyClassName="p-6 space-y-4">
                            {medicalBoard.return_candidates.length ? medicalBoard.return_candidates.map((player) => (
                                <CompactMedicalRow key={player.id} player={player} emphasize />
                            )) : <EmptyCopy text="Keine unmittelbaren Rueckkehrer." />}
                        </SectionCard>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

function SummaryCard({ icon: Icon, label, value, tone }) {
    const tones = {
        rose: 'border-rose-400/20 bg-rose-500/10 text-rose-300',
        amber: 'border-amber-400/20 bg-amber-500/10 text-amber-300',
        cyan: 'border-cyan-400/20 bg-cyan-500/10 text-cyan-300',
    };

    return (
        <div className="sim-card p-5">
            <div className={`mb-3 inline-flex rounded-2xl border p-3 ${tones[tone]}`}>
                <Icon size={18} weight="duotone" />
            </div>
            <div className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{label}</div>
            <div className="mt-2 text-3xl font-black text-white">{value}</div>
        </div>
    );
}

function MedicalRow({ player, processing, onSubmit }) {
    const defaults = {
        rehab_intensity: player.injury?.rehab_intensity || 'medium',
        return_phase: player.injury?.return_phase || 'recovery',
        notes: player.injury?.notes || '',
    };

    const [values, setValues] = React.useState(defaults);

    return (
        <div className="rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-5">
            <div className="flex flex-wrap items-start justify-between gap-4">
                <div className="flex items-center gap-4">
                    <img src={player.photo_url} alt={player.name} className="h-14 w-14 rounded-2xl border border-white/10 object-cover" />
                    <div>
                        <div className="text-sm font-black uppercase tracking-[0.05em] text-white">{player.name}</div>
                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                            {player.position} / {player.injury?.type} / Rueckkehr {player.injury?.expected_return || '-'}
                        </div>
                    </div>
                </div>
                <div className="flex flex-wrap gap-2">
                    <Badge>{player.injury?.severity}</Badge>
                    <Badge tone="amber">Setback {player.injury?.setback_risk}%</Badge>
                    <Badge tone="cyan">Risk {player.injury_risk}%</Badge>
                </div>
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-3">
                <Field label="Reha-Intensitaet">
                    <select value={values.rehab_intensity} onChange={(e) => setValues((current) => ({ ...current, rehab_intensity: e.target.value }))} className="sim-select w-full">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </Field>
                <Field label="Rueckkehrphase">
                    <select value={values.return_phase} onChange={(e) => setValues((current) => ({ ...current, return_phase: e.target.value }))} className="sim-select w-full">
                        <option value="recovery">Recovery</option>
                        <option value="individual">Individual</option>
                        <option value="partial">Partial Team</option>
                        <option value="full">Full Return</option>
                    </select>
                </Field>
                <Field label="Medical Note">
                    <input value={values.notes} onChange={(e) => setValues((current) => ({ ...current, notes: e.target.value }))} className="sim-input w-full" />
                </Field>
            </div>

            <div className="mt-4 flex justify-end">
                <button type="button" disabled={processing} onClick={() => onSubmit(player.id, values)} className="sim-btn-primary px-6 py-3 text-xs font-black uppercase tracking-widest">
                    {processing ? 'Speichert...' : 'Plan setzen'}
                </button>
            </div>
        </div>
    );
}

function CompactMedicalRow({ player, emphasize = false }) {
    return (
        <div className={`rounded-2xl border px-4 py-4 ${emphasize ? 'border-emerald-400/20 bg-emerald-500/5' : 'border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40'}`}>
            <div className="flex items-center gap-3">
                <img src={player.photo_url} alt={player.name} className="h-11 w-11 rounded-xl border border-white/10 object-cover" />
                <div className="min-w-0 flex-1">
                    <div className="truncate text-xs font-black uppercase tracking-[0.06em] text-white">{player.name}</div>
                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">
                        {player.position} / {player.medical_status}
                    </div>
                </div>
                <div className="text-right text-[10px] font-black uppercase tracking-[0.14em] text-[var(--text-muted)]">
                    <div>Fatigue {player.fatigue}%</div>
                    <div>Risk {player.injury_risk}%</div>
                </div>
            </div>
        </div>
    );
}

function Badge({ children, tone = 'rose' }) {
    const tones = {
        rose: 'border-rose-400/20 bg-rose-500/10 text-rose-300',
        amber: 'border-amber-400/20 bg-amber-500/10 text-amber-300',
        cyan: 'border-cyan-400/20 bg-cyan-500/10 text-cyan-300',
    };

    return <span className={`rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${tones[tone]}`}>{children}</span>;
}

function Field({ label, children }) {
    return (
        <div>
            <div className="mb-2 text-[10px] font-black uppercase tracking-[0.16em] text-[var(--text-muted)]">{label}</div>
            {children}
        </div>
    );
}

function EmptyCopy({ text }) {
    return <div className="rounded-2xl border border-dashed border-[var(--border-pillar)] px-4 py-8 text-sm text-[var(--text-muted)]">{text}</div>;
}
