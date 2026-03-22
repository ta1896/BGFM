import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    Wallet, Coins, IdentificationBadge, FileText, Camera, ArrowsClockwise, CaretLeft, UserCircle
} from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';
import { countries } from '@/constants/countries';

function CardField({ label, error, children }) {
    return (
        <div className="space-y-1">
            <label className="px-1 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">{label}</label>
            {children}
            {error && <p className="mt-1 px-1 text-[10px] font-bold uppercase tracking-widest text-rose-500">{error}</p>}
        </div>
    );
}

export default function Form({ club, rolePlayers = [] }) {
    const isEdit = Boolean(club);

    const { data, setData, post, processing, errors } = useForm({
        name: club?.name ?? '',
        short_name: club?.short_name ?? '',
        logo: null,
        country: club?.country ?? 'Deutschland',
        league: club?.league ?? 'Amateurliga',
        founded_year: club?.founded_year ?? '',
        reputation: club?.reputation ?? 50,
        fan_mood: club?.fan_mood ?? 50,
        season_objective: club?.season_objective ?? 'mid_table',
        budget: club?.budget ?? 500000,
        coins: club?.coins ?? 0,
        wage_budget: club?.wage_budget ?? 250000,
        captain_player_id: club?.captain_player_id ?? '',
        vice_captain_player_id: club?.vice_captain_player_id ?? '',
        notes: club?.notes ?? '',
        _method: isEdit ? 'PUT' : 'POST',
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        if (isEdit) {
            post(route('clubs.update', club.id));
            return;
        }
        post(route('clubs.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title={isEdit ? `${club.name} bearbeiten` : 'Neuen Verein anlegen'} />

            <div className="mx-auto max-w-5xl pb-20">
                <PageHeader
                    eyebrow="Vereins-Management"
                    title={isEdit ? 'Verein bearbeiten' : 'Gruendung und Registrierung'}
                    actions={
                        <Link
                            href={isEdit ? route('clubs.show', club.id) : route('clubs.index')}
                            className="inline-flex items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-3 text-sm font-black text-[var(--text-muted)] transition-colors hover:text-[var(--accent-primary)]"
                        >
                            <CaretLeft size={20} weight="bold" />
                            Zurueck
                        </Link>
                    }
                />

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <PageReveal className="lg:col-span-2">
                            <SectionCard title="Basis-Informationen" icon={IdentificationBadge} bodyClassName="space-y-4 p-6">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <CardField label="Vereinsname" error={errors.name}>
                                        <input type="text" value={data.name} onChange={(event) => setData('name', event.target.value)} className="sim-input-modern" required />
                                    </CardField>
                                    <CardField label="Kurzname (Code)" error={errors.short_name}>
                                        <input type="text" value={data.short_name} onChange={(event) => setData('short_name', event.target.value)} className="sim-input-modern" placeholder="z.B. FCB, BVB" />
                                    </CardField>
                                </div>
                                <div className="grid gap-4 md:grid-cols-3">
                                    <CardField label="Land" error={errors.country}>
                                        <select 
                                            value={data.country} 
                                            onChange={(event) => setData('country', event.target.value)} 
                                            className="sim-select-modern" 
                                            required
                                        >
                                            {countries.map(c => (
                                                <option key={c} value={c}>{c}</option>
                                            ))}
                                        </select>
                                    </CardField>
                                    <CardField label="Liga" error={errors.league}>
                                        <input type="text" value={data.league} onChange={(event) => setData('league', event.target.value)} className="sim-input-modern" required />
                                    </CardField>
                                    <CardField label="Gruendungsjahr" error={errors.founded_year}>
                                        <input type="number" value={data.founded_year} onChange={(event) => setData('founded_year', event.target.value)} className="sim-input-modern" />
                                    </CardField>
                                </div>
                            </SectionCard>
                        </PageReveal>

                        <PageReveal delay={80}>
                            <SectionCard title="Brand und Status" icon={Camera} bodyClassName="space-y-4 p-6">
                                <CardField label="Vereinslogo" error={errors.logo}>
                                    <div className="group relative">
                                        <input type="file" onChange={(event) => setData('logo', event.target.files[0])} className="absolute inset-0 z-10 cursor-pointer opacity-0" accept="image/*" />
                                        <div className="flex items-center gap-4 rounded-xl border-2 border-dashed border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-4 transition-all group-hover:border-[var(--accent-primary)]/50">
                                            {isEdit && club.logo_url && !data.logo && (
                                                <img src={club.logo_url} className="h-10 w-10 rounded-lg bg-[var(--sim-shell-bg)] p-1 object-contain" alt="Logo" />
                                            )}
                                            {data.logo && (
                                                <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500/10">
                                                    <FileText size={20} className="text-emerald-400" />
                                                </div>
                                            )}
                                            <div className="flex-1 overflow-hidden">
                                                <p className="truncate text-[10px] font-bold uppercase tracking-widest text-slate-300">
                                                    {data.logo ? data.logo.name : (isEdit ? 'Logo aendern' : 'Datei waehlen')}
                                                </p>
                                                <p className="text-[9px] font-medium text-[var(--text-muted)]">PNG, JPG bis 2MB</p>
                                            </div>
                                        </div>
                                    </div>
                                </CardField>

                                <div className="grid grid-cols-2 gap-4">
                                    <CardField label="Reputation" error={errors.reputation}>
                                        <input type="number" min="1" max="99" value={data.reputation} onChange={(event) => setData('reputation', event.target.value)} className="sim-input-modern" required />
                                    </CardField>
                                    <CardField label="Fan-Mood" error={errors.fan_mood}>
                                        <input type="number" min="1" max="100" value={data.fan_mood} onChange={(event) => setData('fan_mood', event.target.value)} className="sim-input-modern" required />
                                    </CardField>
                                </div>
                            </SectionCard>
                        </PageReveal>

                        <PageReveal delay={120} className="lg:col-span-2">
                            <SectionCard title="Finanzplan und Ziele" icon={Wallet} bodyClassName="space-y-4 p-6">
                                <div className="grid gap-4 md:grid-cols-3">
                                    <CardField label="Transferbudget (EUR)" error={errors.budget}>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <Wallet size={16} className="text-[var(--text-muted)]" />
                                            </div>
                                            <input type="number" min="0" step="0.01" value={data.budget} onChange={(event) => setData('budget', event.target.value)} className="sim-input-modern pl-10" required />
                                        </div>
                                    </CardField>
                                    <CardField label="Gehaltsbudget (EUR)" error={errors.wage_budget}>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <IdentificationBadge size={16} className="text-[var(--text-muted)]" />
                                            </div>
                                            <input type="number" min="0" step="0.01" value={data.wage_budget} onChange={(event) => setData('wage_budget', event.target.value)} className="sim-input-modern pl-10" required />
                                        </div>
                                    </CardField>
                                    <CardField label="Start-Coins" error={errors.coins}>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <Coins size={16} className="text-[var(--accent-primary)]" weight="fill" />
                                            </div>
                                            <input type="number" min="0" step="1" value={data.coins} onChange={(event) => setData('coins', event.target.value)} className="sim-input-modern pl-10" />
                                        </div>
                                    </CardField>
                                </div>

                                <CardField label="Saisonziel" error={errors.season_objective}>
                                    <select value={data.season_objective} onChange={(event) => setData('season_objective', event.target.value)} className="sim-select-modern">
                                        <option value="avoid_relegation">Abstiegskampf / Klassenerhalt</option>
                                        <option value="mid_table">Gesichertes Mittelfeld</option>
                                        <option value="promotion">Obere Tabellenhaelfte / Aufstieg</option>
                                        <option value="title">Meisterschaftskampf</option>
                                        <option value="cup_run">Fokus auf Pokalerfolg</option>
                                    </select>
                                </CardField>
                            </SectionCard>
                        </PageReveal>

                        <PageReveal delay={160}>
                            <SectionCard title="Spielfuehrer und Rollen" icon={UserCircle} bodyClassName="p-6">
                                {rolePlayers.length > 0 ? (
                                    <div className="space-y-4">
                                        <CardField label="Kapitaen" error={errors.captain_player_id}>
                                            <select value={data.captain_player_id} onChange={(event) => setData('captain_player_id', event.target.value)} className="sim-select-modern">
                                                <option value="">Keiner gewaehlt</option>
                                                {rolePlayers.map((player) => <option key={player.id} value={player.id}>{player.full_name} ({player.position} | OVR {player.overall})</option>)}
                                            </select>
                                        </CardField>
                                        <CardField label="Vize-Kapitaen" error={errors.vice_captain_player_id}>
                                            <select value={data.vice_captain_player_id} onChange={(event) => setData('vice_captain_player_id', event.target.value)} className="sim-select-modern">
                                                <option value="">Keiner gewaehlt</option>
                                                {rolePlayers.map((player) => <option key={player.id} value={player.id}>{player.full_name} ({player.position} | OVR {player.overall})</option>)}
                                            </select>
                                        </CardField>
                                    </div>
                                ) : (
                                    <div className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] py-6 text-center shadow-inner">
                                        <p className="px-4 text-[10px] font-bold uppercase tracking-widest leading-relaxed text-[var(--text-muted)]">
                                            Rollen koennen erst festgelegt werden, wenn Spieler im Verein vorhanden sind.
                                        </p>
                                    </div>
                                )}
                            </SectionCard>
                        </PageReveal>

                        <PageReveal delay={200} className="col-span-full">
                            <SectionCard title="Zusaetzliche Notizen" icon={FileText} bodyClassName="p-6">
                                <CardField label="Internes Protokoll / Beschreibung" error={errors.notes}>
                                    <textarea
                                        value={data.notes}
                                        onChange={(event) => setData('notes', event.target.value)}
                                        className="sim-textarea-modern h-32"
                                        placeholder="Hier koennen Vereinsphilosophie, Taktikvorgaben oder andere Notizen festgehalten werden..."
                                    />
                                </CardField>
                            </SectionCard>
                        </PageReveal>
                    </div>

                    <div className="flex items-center justify-between border-t border-[var(--border-muted)] pt-8">
                        <Link href={isEdit ? route('clubs.show', club.id) : route('clubs.index')} className="px-4 text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] transition-colors hover:text-[var(--text-main)]">
                            Abbrechen
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-12 py-4 text-sm font-black uppercase tracking-widest text-white transition-opacity disabled:opacity-50"
                        >
                            {processing && <ArrowsClockwise size={20} className="animate-spin" />}
                            {isEdit ? 'Daten aktualisieren' : 'Verein gruenden'}
                        </button>
                    </div>
                </form>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-input-modern {
                    @apply w-full rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/80 px-4 py-3 text-sm font-medium text-white outline-none transition-all placeholder:text-slate-700 focus:border-[var(--accent-primary)]/50;
                }
                .sim-select-modern {
                    @apply w-full appearance-none rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/80 px-4 py-3 text-sm font-bold text-white outline-none transition-all focus:border-[var(--accent-primary)]/50;
                    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23d9b15c' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E");
                    background-repeat: no-repeat;
                    background-position: right 1rem center;
                    background-size: 1.2rem;
                }
                .sim-textarea-modern {
                    @apply w-full resize-none rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/80 px-4 py-3 text-sm font-medium text-white outline-none transition-all placeholder:text-slate-700 focus:border-[var(--accent-primary)]/50;
                }
            ` }} />
        </AuthenticatedLayout>
    );
}
