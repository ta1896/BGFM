import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import { Handshake, Crown, XCircle, Star, HourglassMedium } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';
import MetricCard from '@/Components/MetricCard';

function OfferCard({ offer, onSign, disabled }) {
    const [months, setMonths] = useState(12);

    return (
        <div className="sim-card flex h-full flex-col border-[var(--border-muted)] p-6 transition-colors hover:border-[var(--accent-primary)]/30">
            <div className="mb-6 flex items-start justify-between">
                <div>
                    <h4 className="text-xl font-black uppercase tracking-tighter text-[var(--text-main)]">{offer.name}</h4>
                    <span className="mt-2 inline-flex rounded border border-[var(--border-pillar)] bg-[var(--bg-content)] px-2 py-0.5 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                        {offer.tier}
                    </span>
                </div>
                <div className="flex h-12 w-12 items-center justify-center rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/50 text-[var(--text-muted)]">
                    <Handshake size={28} weight="duotone" />
                </div>
            </div>

            <div className="mb-8 flex-1 space-y-4">
                <div className="flex items-center justify-between text-sm">
                    <span className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Basisbetrag</span>
                    <span className="font-mono font-black text-emerald-400">{offer.base_weekly_amount.toLocaleString('de-DE')} EUR</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                    <span className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">Reputation</span>
                    <div className="flex items-center gap-1">
                        <Star size={14} weight="fill" className="text-[var(--accent-primary)]" />
                        <span className="font-mono font-black text-[var(--text-main)]">{offer.reputation_min}</span>
                    </div>
                </div>
            </div>

            <div className="mt-auto space-y-4">
                <div className="relative">
                    <input
                        type="number"
                        min="1"
                        max="60"
                        value={months}
                        onChange={(event) => setMonths(event.target.value)}
                        className="w-full rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-3 text-center font-bold text-[var(--text-main)] outline-none transition-all focus:border-[var(--accent-primary)]/50 disabled:opacity-50"
                        disabled={disabled}
                    />
                    <span className="pointer-events-none absolute right-3 top-3.5 text-[10px] font-black uppercase tracking-widest text-slate-600">Mte</span>
                </div>
                <button
                    type="button"
                    onClick={() => onSign(offer.id, months)}
                    disabled={disabled}
                    className="w-full rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] py-4 font-black text-white transition-opacity disabled:opacity-20"
                >
                    {disabled ? 'Vertrag aktiv' : 'Angebot annehmen'}
                </button>
            </div>
        </div>
    );
}

export default function Sponsors({ offers, activeContract, history, activeClub }) {
    const signForm = useForm({
        club_id: activeClub?.id,
        months: 12,
    });
    const terminateForm = useForm({});

    const handleSign = (sponsorId, months) => {
        signForm.setData({
            club_id: activeClub.id,
            months,
        });
        signForm.post(route('sponsors.sign', sponsorId), { preserveScroll: true });
    };

    const handleTerminate = () => {
        if (confirm('Moechtest du diesen Vertrag wirklich vorzeitig beenden?')) {
            terminateForm.post(route('sponsors.contracts.terminate', activeContract.id), { preserveScroll: true });
        }
    };

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <h2 className="mb-2 text-2xl font-bold text-[var(--text-main)]">Kein Verein aktiv</h2>
                    <p className="max-w-md text-[var(--text-muted)]">Es konnte kein aktiver Verein gefunden werden. Bitte waehle einen Verein aus der Liste oder erstelle einen neuen.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Sponsoring" />

            <div className="mx-auto max-w-[1400px] space-y-8">
                <PageHeader eyebrow="Business" title="Sponsoring" />

                <PageReveal className="rounded-[2rem] border border-[var(--border-muted)] bg-[linear-gradient(135deg,var(--bg-pillar),var(--sim-shell-bg))] p-10 md:p-14">
                    <div className="grid gap-8 md:grid-cols-[1.5fr_1fr] md:items-center">
                        <div>
                            <div className="mb-6 flex items-center gap-3">
                                <div className="h-2 w-2 rounded-full bg-[var(--accent-primary)] shadow-[0_0_8px_rgba(217,177,92,0.6)]" />
                                <span className="text-xs font-black uppercase tracking-[0.3em] text-[var(--accent-primary)]">Status: Sponsoring Live</span>
                            </div>
                            {activeContract ? (
                                <>
                                    <h2 className="mb-4 text-5xl font-black uppercase italic leading-none tracking-tighter text-[var(--text-main)] lg:text-7xl">{activeContract.sponsor.name}</h2>
                                    <p className="max-w-xl text-xl font-medium text-[var(--text-muted)]">
                                        Partner seit der laufenden Saison. Vertrag gueltig bis <span className="font-black text-[var(--text-main)]">{activeContract.ends_on_formatted}</span>.
                                    </p>
                                </>
                            ) : (
                                <>
                                    <h2 className="mb-4 text-5xl font-black uppercase italic leading-none tracking-tighter text-slate-700 lg:text-7xl">Kein Partner</h2>
                                    <p className="max-w-xl text-xl font-medium text-[var(--text-muted)]">
                                        Registriere jetzt einen neuen Hauptsponsor, um deine woechentlichen Einnahmen zu maximieren.
                                    </p>
                                </>
                            )}
                        </div>

                        {activeContract && (
                            <div className="sim-card-soft border-[var(--border-muted)] p-8 text-center backdrop-blur-3xl md:text-right">
                                <p className="mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Einnahmen / Woche</p>
                                <div className="mb-6 flex items-baseline justify-center gap-2 md:justify-end">
                                    <span className="font-mono text-5xl font-black tracking-tighter text-emerald-400">{activeContract.weekly_amount.toLocaleString('de-DE')}</span>
                                    <span className="text-xl font-black text-emerald-600/50">EUR</span>
                                </div>
                                <button type="button" onClick={handleTerminate} className="ml-auto inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-rose-500 transition-colors hover:text-rose-400">
                                    <XCircle size={16} weight="bold" />
                                    Vertrag kuendigen
                                </button>
                            </div>
                        )}
                    </div>
                </PageReveal>

                {activeContract && (
                    <PageReveal delay={80} className="grid gap-4 md:grid-cols-3">
                        <MetricCard label="Aktiver Partner" value={activeContract.sponsor.name} icon={Handshake} />
                        <MetricCard label="Wochenrate" value={activeContract.weekly_amount} unit="EUR" icon={Crown} />
                        <MetricCard label="Laufzeit bis" value={activeContract.ends_on_formatted} icon={HourglassMedium} />
                    </PageReveal>
                )}

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <PageReveal>
                            <div className="flex items-center justify-between">
                                <h3 className="flex items-center gap-3 text-xl font-black uppercase tracking-widest text-[var(--text-main)]">
                                    <Crown size={24} weight="duotone" className="text-[var(--accent-primary)]" />
                                    Sponsoring-Angebote
                                </h3>
                                <div className="rounded-full bg-[var(--bg-content)] px-3 py-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{offers.length} Verfuegbar</div>
                            </div>
                        </PageReveal>

                        <StaggerGroup className="grid gap-6 md:grid-cols-2">
                            {offers.map((offer) => (
                                <OfferCard key={offer.id} offer={offer} onSign={handleSign} disabled={activeContract !== null} />
                            ))}
                        </StaggerGroup>
                    </div>

                    <PageReveal delay={120}>
                        <SectionCard title="Historie" icon={HourglassMedium} bodyClassName="overflow-hidden p-0">
                            <div className="divide-y divide-slate-800/50">
                                {history.length > 0 ? history.map((contract) => (
                                    <div key={contract.id} className="flex items-center justify-between p-4 transition-colors hover:bg-white/[0.02]">
                                        <div>
                                            <p className="font-bold text-[var(--text-main)]">{contract.sponsor.name}</p>
                                            <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                                {contract.starts_on_formatted} - {contract.ends_on_formatted}
                                            </p>
                                        </div>
                                        <div className="text-right">
                                            <p className="font-mono font-black italic text-emerald-400">+{contract.weekly_amount.toLocaleString('de-DE')} EUR</p>
                                            <p className={`text-[10px] font-black uppercase tracking-widest ${contract.status === 'active' ? 'text-[var(--accent-primary)]' : 'text-slate-600'}`}>
                                                {contract.status}
                                            </p>
                                        </div>
                                    </div>
                                )) : (
                                    <div className="p-12 text-center text-sm italic text-slate-600">Keine historischen Daten verfuegbar.</div>
                                )}
                            </div>
                        </SectionCard>
                    </PageReveal>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
