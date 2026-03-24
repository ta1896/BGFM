import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Plus, ArrowLeft, Strategy, Cards, Note } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function Field({ label, icon: Icon, error, children }) {
    return (
        <div className="space-y-2">
            <label className="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                {Icon && <Icon size={16} weight="bold" className="text-[var(--accent-primary)]" />}
                {label}
            </label>
            {children}
            {error && <div className="text-[10px] font-bold uppercase italic tracking-widest text-rose-500">{error}</div>}
        </div>
    );
}

export default function Create({ club, formations, defaultFormation }) {
    const { data, setData, post, processing, errors } = useForm({
        club_id: club.id,
        name: '',
        formation: defaultFormation,
        notes: '',
        is_active: true,
    });

    const submit = (event) => {
        event.preventDefault();
        post(route('lineups.store'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Neue Aufstellung erstellen" />

            <div className="mx-auto max-w-4xl space-y-8">
                <PageHeader
                    eyebrow="Matchcenter"
                    title="Neue Aufstellung"
                    actions={
                        <Link
                            href={route('lineups.index')}
                            className="inline-flex items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-3 text-sm font-black text-[var(--text-muted)] transition-colors hover:text-[var(--accent-primary)]"
                        >
                            <ArrowLeft size={20} weight="bold" />
                            Zurueck
                        </Link>
                    }
                />

                <PageReveal>
                    <SectionCard title="Setup" icon={Strategy} bodyClassName="space-y-8 p-8">
                        <form onSubmit={submit} className="space-y-8">
                            <div className="grid gap-8 md:grid-cols-2">
                                <Field label="Formation" icon={Strategy} error={errors.formation}>
                                    <select
                                        className="sim-input"
                                        value={data.formation}
                                        onChange={(event) => setData('formation', event.target.value)}
                                        required
                                    >
                                        {formations.map((formation) => (
                                            <option key={formation} value={formation}>
                                                {formation}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Name der Aufstellung" icon={Cards} error={errors.name}>
                                    <input
                                        className="sim-input"
                                        value={data.name}
                                        onChange={(event) => setData('name', event.target.value)}
                                        placeholder="z.B. Standard Liga-Elf"
                                        required
                                    />
                                </Field>
                            </div>

                            <Field label="Taktische Notizen" icon={Note} error={errors.notes}>
                                <textarea
                                    className="sim-textarea h-32"
                                    value={data.notes}
                                    onChange={(event) => setData('notes', event.target.value)}
                                    placeholder="Anweisungen fuer das Team..."
                                />
                            </Field>

                            <div className="flex flex-wrap items-center justify-between gap-4 border-t border-white/5 pt-8">
                                <label className="group flex cursor-pointer items-center gap-3">
                                    <div className="relative">
                                        <input
                                            type="checkbox"
                                            className="peer sr-only"
                                            checked={data.is_active}
                                            onChange={(event) => setData('is_active', event.target.checked)}
                                        />
                                        <div className="h-6 w-12 rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] transition-colors peer-checked:border-[var(--accent-primary)]/50 peer-checked:bg-[var(--accent-primary)]/20" />
                                        <div className="absolute left-1 top-1 h-4 w-4 rounded-full bg-slate-600 shadow-lg transition-all peer-checked:left-7 peer-checked:bg-[var(--accent-primary)]" />
                                    </div>
                                    <span className="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] transition-colors group-hover:text-[var(--text-main)]">
                                        Als aktive Aufstellung setzen
                                    </span>
                                </label>

                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-12 py-4 text-xs font-black uppercase tracking-widest text-white transition-opacity disabled:opacity-50"
                                >
                                    <Plus size={20} weight="bold" />
                                    Erstellen und bearbeiten
                                </button>
                            </div>
                        </form>
                    </SectionCard>
                </PageReveal>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-input {
                    @apply w-full rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/80 px-4 py-3 text-sm font-medium text-white outline-none transition-all placeholder:text-slate-700 focus:border-[var(--accent-primary)]/50;
                }
                .sim-textarea {
                    @apply w-full resize-none rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/80 px-4 py-3 text-sm font-medium text-white outline-none transition-all placeholder:text-slate-700 focus:border-[var(--accent-primary)]/50;
                }
            ` }} />
        </AuthenticatedLayout>
    );
}
