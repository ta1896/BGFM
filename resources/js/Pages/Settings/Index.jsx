import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { PageReveal } from '@/Components/PageReveal';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import {
    Gear,
    Lock,
    Suitcase,
    CheckCircle,
    Fingerprint,
    Trash,
    Plus,
} from '@phosphor-icons/react';

const TabButton = ({ active, onClick, icon: Icon, label }) => (
    <button
        onClick={onClick}
        className={`flex items-center gap-3 rounded-xl border-2 px-6 py-4 font-bold transition-all duration-300 ${
            active
                ? 'border-cyan-500/50 bg-cyan-500/10 text-cyan-400 shadow-[0_0_20px_rgba(34,211,238,0.1)]'
                : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 text-[var(--text-muted)] hover:border-[var(--border-pillar)] hover:text-[var(--text-main)]'
        }`}
    >
        <Icon size={20} weight={active ? 'fill' : 'bold'} />
        {label}
    </button>
);

export default function Settings({ userClubs, passkeys }) {
    const { auth } = usePage().props;
    const [activeTab, setActiveTab] = useState('general');

    const generalForm = useForm({
        default_club_id: auth.user.default_club_id || '',
    });

    const submitGeneral = (event) => {
        event.preventDefault();
        generalForm.patch(route('settings.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Einstellungen" />

            <div className="max-w-[1200px] mx-auto space-y-8">
                <PageHeader eyebrow="System" title="Einstellungen" />

                <div className="flex flex-wrap gap-4">
                    <TabButton active={activeTab === 'general'} onClick={() => setActiveTab('general')} icon={Gear} label="Allgemein" />
                    <TabButton active={activeTab === 'security'} onClick={() => setActiveTab('security')} icon={Lock} label="Sicherheit" />
                </div>

                {activeTab === 'general' && (
                    <PageReveal>
                        <SectionCard title="Vereins-Einstellungen" icon={Suitcase} bodyClassName="p-8">
                            <p className="mb-8 text-sm text-[var(--text-muted)]">
                                Verwalte deine Standard-Werte fuer ein schnelleres Spielerlebnis.
                            </p>

                            <form onSubmit={submitGeneral} className="space-y-6">
                                <div className="space-y-4">
                                    <label className="block text-sm font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                        Bevorzugter Verein
                                    </label>
                                    <p className="mb-4 text-xs italic text-[var(--text-muted)]">
                                        Dieser Verein wird nach dem Login automatisch als aktiver Verein geladen.
                                    </p>
                                    <select
                                        value={generalForm.data.default_club_id}
                                        onChange={(event) => generalForm.setData('default_club_id', event.target.value)}
                                        className="sim-select w-full cursor-pointer"
                                    >
                                        <option value="">Kein Standard-Verein gewaehlt</option>
                                        {userClubs.map((club) => (
                                            <option key={club.id} value={club.id}>
                                                {club.name}
                                            </option>
                                        ))}
                                    </select>
                                    {generalForm.errors.default_club_id && (
                                        <p className="text-xs font-medium text-rose-500">{generalForm.errors.default_club_id}</p>
                                    )}
                                </div>

                                <div className="mt-8 flex items-center justify-end border-t border-[var(--border-muted)] pt-6">
                                    <button disabled={generalForm.processing} className="sim-btn-primary px-8">
                                        {generalForm.processing ? 'Wird gespeichert...' : 'Aenderungen speichern'}
                                    </button>
                                </div>
                            </form>
                        </SectionCard>
                    </PageReveal>
                )}

                {activeTab === 'security' && (
                    <PageReveal className="space-y-8">
                        <SectionCard title="Passkeys (WebAuthn)" icon={Fingerprint} bodyClassName="p-8">
                            <p className="mb-8 text-sm text-[var(--text-muted)]">
                                Sicheres Einloggen ohne Passwort via Biometrie oder Sicherheitsschluessel.
                            </p>

                            <div className="space-y-4">
                                {passkeys && passkeys.length > 0 ? (
                                    <div className="grid gap-4">
                                        {passkeys.map((passkey) => (
                                            <div key={passkey.id} className="group flex items-center justify-between rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 p-4">
                                                <div className="flex items-center gap-4">
                                                    <div className="flex h-10 w-10 items-center justify-center rounded-full bg-purple-500/20 text-purple-400">
                                                        <Fingerprint size={20} weight="fill" />
                                                    </div>
                                                    <div>
                                                        <p className="leading-tight font-bold text-[var(--text-main)]">{passkey.alias || 'Unbenannter Schluessel'}</p>
                                                        <p className="text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                                            {passkey.created_at_formatted || 'Passkey'}
                                                        </p>
                                                    </div>
                                                </div>
                                                <button className="rounded-lg p-2 text-[var(--text-muted)] transition-all hover:bg-rose-400/10 hover:text-rose-400">
                                                    <Trash size={20} />
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="rounded-3xl border-2 border-dashed border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 py-12 text-center">
                                        <Fingerprint size={48} weight="thin" className="mx-auto mb-4 text-slate-700" />
                                        <p className="font-medium text-[var(--text-muted)]">Bisher wurden keine Passkeys registriert.</p>
                                    </div>
                                )}

                                <button className="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border-2 border-purple-500/30 bg-purple-600/10 py-4 font-bold text-purple-400 transition-all hover:bg-purple-600/20">
                                    <Plus size={20} weight="bold" />
                                    Neuen Passkey hinzufuegen
                                </button>
                            </div>
                        </SectionCard>

                        <SectionCard title="Sicherheits-Check" icon={Lock} bodyClassName="p-8">
                            <p className="mb-8 text-sm text-[var(--text-muted)]">Ueberpruefe deine Kontosicherheit regelmaessig.</p>

                            <div className="flex items-start gap-4 rounded-2xl border border-emerald-500/20 bg-emerald-500/5 p-6">
                                <CheckCircle size={24} weight="fill" className="mt-1 text-emerald-500" />
                                <div>
                                    <p className="mb-1 font-bold text-[var(--text-main)]">E-Mail verifiziert</p>
                                    <p className="text-sm text-[var(--text-muted)]">
                                        Deine E-Mail Adresse ist bestaetigt und schuetzt dein Konto zusaetzlich.
                                    </p>
                                </div>
                            </div>
                        </SectionCard>
                    </PageReveal>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
