import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    IdentificationCard, Target, Lightning, Sword, Shield, Heartbeat, TrendUp, Coins,
    ArrowsClockwise, CaretLeft, Camera, UserCircle, Envelope
} from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function CardField({ label, error, children }) {
    return (
        <div className="space-y-1">
            <label className="px-1 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">{label}</label>
            {children}
            {error && <p className="mt-1 px-1 text-[10px] font-bold uppercase tracking-widest text-rose-500">{error}</p>}
        </div>
    );
}

function AttributeInput({ label, value, onChange, error, icon: Icon, min = 1, max = 99 }) {
    return (
        <CardField label={label} error={error}>
            <div className="group relative">
                <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <Icon size={16} className="text-[var(--text-muted)] transition-colors group-focus-within:text-[var(--accent-primary)]" />
                </div>
                <input
                    type="number"
                    min={min}
                    max={max}
                    value={value}
                    onChange={(event) => onChange(event.target.value)}
                    className="sim-input-modern pl-10"
                    required
                />
                <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                    <span className="text-[10px] font-black text-slate-600">{value}</span>
                </div>
            </div>
        </CardField>
    );
}

export default function Form({ player, clubs, positions }) {
    const isEdit = Boolean(player);

    const { data, setData, post, processing, errors } = useForm({
        club_id: player?.club_id ?? (clubs.length > 0 ? clubs[0].id : ''),
        first_name: player?.first_name ?? '',
        last_name: player?.last_name ?? '',
        photo: null,
        position: player?.position ?? 'ZM',
        age: player?.age ?? 22,
        overall: player?.overall ?? 60,
        pace: player?.pace ?? 60,
        shooting: player?.shooting ?? 60,
        passing: player?.passing ?? 60,
        defending: player?.defending ?? 60,
        physical: player?.physical ?? 60,
        stamina: player?.stamina ?? 80,
        morale: player?.morale ?? 60,
        market_value: player?.market_value ?? 1000000,
        salary: player?.salary ?? 15000,
        _method: isEdit ? 'PUT' : 'POST',
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        if (isEdit) {
            post(route('players.update', player.id));
            return;
        }
        post(route('players.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title={isEdit ? `${player.full_name} bearbeiten` : 'Neuen Spieler anlegen'} />

            <div className="mx-auto max-w-6xl pb-20">
                <PageHeader
                    eyebrow="Kader-Management"
                    title={isEdit ? 'Spieler-Akte bearbeiten' : 'Neuen Spieler verpflichten'}
                    actions={
                        <Link
                            href={isEdit ? route('players.show', player.id) : route('players.index')}
                            className="inline-flex items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-3 text-sm font-black text-[var(--text-muted)] transition-colors hover:text-[var(--accent-primary)]"
                        >
                            <CaretLeft size={20} weight="bold" />
                            Zurueck
                        </Link>
                    }
                />

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <div className="flex flex-col gap-6 lg:col-span-2">
                            <PageReveal>
                                <SectionCard title="Identitaet und Basis" icon={IdentificationCard} bodyClassName="space-y-4 p-6">
                                    <div className="grid gap-4 md:grid-cols-2">
                                        <CardField label="Vorname" error={errors.first_name}>
                                            <input type="text" value={data.first_name} onChange={(event) => setData('first_name', event.target.value)} className="sim-input-modern" required />
                                        </CardField>
                                        <CardField label="Nachname" error={errors.last_name}>
                                            <input type="text" value={data.last_name} onChange={(event) => setData('last_name', event.target.value)} className="sim-input-modern" required />
                                        </CardField>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-3">
                                        <CardField label="Verein" error={errors.club_id}>
                                            <select value={data.club_id} onChange={(event) => setData('club_id', event.target.value)} className="sim-select-modern" required>
                                                {clubs.map((club) => <option key={club.id} value={club.id}>{club.name}</option>)}
                                            </select>
                                        </CardField>
                                        <CardField label="Haupt-Position" error={errors.position}>
                                            <select value={data.position} onChange={(event) => setData('position', event.target.value)} className="sim-select-modern" required>
                                                {Object.entries(positions).map(([key, label]) => <option key={key} value={key}>{label} ({key})</option>)}
                                            </select>
                                        </CardField>
                                        <CardField label="Alter" error={errors.age}>
                                            <input type="number" min="15" max="45" value={data.age} onChange={(event) => setData('age', event.target.value)} className="sim-input-modern" required />
                                        </CardField>
                                    </div>
                                </SectionCard>
                            </PageReveal>

                            <PageReveal delay={80}>
                                <SectionCard title="Physische und mentale Verfassung" icon={Heartbeat} bodyClassName="grid gap-6 p-6 md:grid-cols-3">
                                    <AttributeInput label="Gesamt (OVR)" value={data.overall} onChange={(value) => setData('overall', value)} error={errors.overall} icon={TrendUp} />
                                    <AttributeInput label="Ausdauer" value={data.stamina} onChange={(value) => setData('stamina', value)} error={errors.stamina} icon={Lightning} max={100} />
                                    <AttributeInput label="Moral" value={data.morale} onChange={(value) => setData('morale', value)} error={errors.morale} icon={UserCircle} max={100} />
                                </SectionCard>
                            </PageReveal>
                        </div>

                        <PageReveal delay={120}>
                            <SectionCard title="Spielerprofil" icon={Camera} bodyClassName="space-y-6 p-6">
                                <div className="mb-6 flex flex-col items-center gap-6">
                                    <div className="group relative">
                                        <div className="flex h-32 w-32 items-center justify-center overflow-hidden rounded-3xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)] p-1 shadow-2xl transition-all group-hover:border-[var(--accent-primary)]/50">
                                            {data.photo ? (
                                                <img src={URL.createObjectURL(data.photo)} className="h-full w-full rounded-2xl object-cover" alt="Preview" />
                                            ) : isEdit && player.photo_url ? (
                                                <img src={player.photo_url} className="h-full w-full rounded-2xl object-cover" alt={player.full_name} />
                                            ) : (
                                                <UserCircle size={64} className="text-slate-800" weight="fill" />
                                            )}
                                        </div>
                                        <input type="file" onChange={(event) => setData('photo', event.target.files[0])} className="absolute inset-0 z-10 cursor-pointer opacity-0" accept="image/*" />
                                        <div className="absolute -bottom-2 -right-2 rounded-xl border border-[var(--accent-primary)] bg-[var(--accent-primary)] p-2 shadow-lg transition-transform group-hover:scale-110">
                                            <Camera size={16} className="text-black" weight="bold" />
                                        </div>
                                    </div>
                                    <p className="px-4 text-center text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                        Lade ein quadratisches Bild (PNG/JPG) fuer die beste Darstellung hoch.
                                    </p>
                                </div>

                                <div className="space-y-4 border-t border-[var(--border-muted)] pt-4">
                                    <CardField label="Marktwert (EUR)" error={errors.market_value}>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <Coins size={16} className="text-[var(--accent-primary)]" weight="fill" />
                                            </div>
                                            <input type="number" min="0" step="0.01" value={data.market_value} onChange={(event) => setData('market_value', event.target.value)} className="sim-input-modern pl-10" required />
                                        </div>
                                    </CardField>
                                    <CardField label="Monatsgehalt (EUR)" error={errors.salary}>
                                        <div className="relative">
                                            <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <Envelope size={16} className="text-cyan-400" />
                                            </div>
                                            <input type="number" min="0" step="0.01" value={data.salary} onChange={(event) => setData('salary', event.target.value)} className="sim-input-modern pl-10" required />
                                        </div>
                                    </CardField>
                                </div>
                            </SectionCard>
                        </PageReveal>

                        <PageReveal delay={160} className="lg:col-span-3">
                            <SectionCard title="Leistungswerte" icon={Target} bodyClassName="grid gap-6 p-6 md:grid-cols-5">
                                <AttributeInput label="Tempo" value={data.pace} onChange={(value) => setData('pace', value)} error={errors.pace} icon={Lightning} />
                                <AttributeInput label="Schuss" value={data.shooting} onChange={(value) => setData('shooting', value)} error={errors.shooting} icon={Sword} />
                                <AttributeInput label="Pass" value={data.passing} onChange={(value) => setData('passing', value)} error={errors.passing} icon={ArrowsClockwise} />
                                <AttributeInput label="Defensive" value={data.defending} onChange={(value) => setData('defending', value)} error={errors.defending} icon={Shield} />
                                <AttributeInput label="Physis" value={data.physical} onChange={(value) => setData('physical', value)} error={errors.physical} icon={UserCircle} />
                            </SectionCard>
                        </PageReveal>
                    </div>

                    <div className="flex items-center justify-between border-t border-[var(--border-muted)] pt-8">
                        <Link href={isEdit ? route('players.show', player.id) : route('players.index')} className="px-4 text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] transition-colors hover:text-[var(--text-main)]">
                            Abbrechen
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-12 py-4 text-sm font-black uppercase tracking-widest text-white transition-opacity disabled:opacity-50"
                        >
                            {processing && <ArrowsClockwise size={20} className="animate-spin" />}
                            {isEdit ? 'Profil aktualisieren' : 'Spieler verpflichten'}
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
            ` }} />
        </AuthenticatedLayout>
    );
}
