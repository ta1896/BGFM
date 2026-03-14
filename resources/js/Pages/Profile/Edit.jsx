import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Warning, Trash, CheckCircle, Envelope, ShieldCheck, ArrowsClockwise, User } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function Field({ label, icon: Icon, type = 'text', value, onChange, autoComplete, error, placeholder }) {
    return (
        <div className="space-y-2">
            <label className="px-1 text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</label>
            <div className="relative group">
                {Icon && (
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <Icon size={18} className="text-[var(--text-muted)] transition-colors group-focus-within:text-[var(--accent-primary)]" />
                    </div>
                )}
                <input
                    type={type}
                    value={value}
                    onChange={onChange}
                    placeholder={placeholder}
                    autoComplete={autoComplete}
                    className={`w-full rounded-xl border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60 py-3 text-[var(--text-main)] outline-none transition-all focus:border-[var(--accent-primary)]/50 focus:ring-4 focus:ring-[var(--accent-primary)]/5 ${Icon ? 'pl-11 pr-4' : 'px-4'}`}
                />
            </div>
            {error && <p className="text-xs font-bold text-rose-500">{error}</p>}
        </div>
    );
}

export default function Edit({ mustVerifyEmail, status }) {
    const { auth } = usePage().props;
    const [confirmingUserDeletion, setConfirmingUserDeletion] = useState(false);

    const profileForm = useForm({
        name: auth.user.name,
        email: auth.user.email,
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const deleteForm = useForm({
        password: '',
    });

    const submitProfile = (event) => {
        event.preventDefault();
        profileForm.patch(route('profile.update'), { preserveScroll: true });
    };

    const submitPassword = (event) => {
        event.preventDefault();
        passwordForm.put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const deleteUser = (event) => {
        event.preventDefault();
        deleteForm.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => setConfirmingUserDeletion(false),
            onFinish: () => deleteForm.reset(),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Profil Einstellungen" />

            <div className="mx-auto max-w-5xl space-y-8 pb-20">
                <PageHeader
                    eyebrow="Account"
                    title="Profil und Sicherheit"
                    actions={
                        <div className="rounded-full border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-4 py-2 text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">
                            {auth.user.email}
                        </div>
                    }
                />

                <PageReveal className="grid gap-8">
                    <SectionCard title="Persoenliche Daten" icon={User} bodyClassName="space-y-6 p-6 md:p-8">
                        <p className="max-w-2xl text-sm text-[var(--text-muted)]">
                            Aktualisiere Name und E-Mail-Adresse fuer Benachrichtigungen und Login.
                        </p>

                        <form onSubmit={submitProfile} className="space-y-6">
                            <div className="grid gap-6 md:grid-cols-2">
                                <Field
                                    label="Manager-Name"
                                    icon={User}
                                    value={profileForm.data.name}
                                    onChange={(event) => profileForm.setData('name', event.target.value)}
                                    autoComplete="name"
                                    error={profileForm.errors.name}
                                />
                                <Field
                                    label="E-Mail-Adresse"
                                    icon={Envelope}
                                    type="email"
                                    value={profileForm.data.email}
                                    onChange={(event) => profileForm.setData('email', event.target.value)}
                                    autoComplete="username"
                                    error={profileForm.errors.email}
                                />
                            </div>

                            {mustVerifyEmail && auth.user.email_verified_at === null && (
                                <div className="flex flex-col gap-3 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4 md:flex-row md:items-center md:justify-between">
                                    <p className="text-sm font-medium text-amber-200">Deine E-Mail ist noch nicht verifiziert.</p>
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="rounded-xl bg-amber-500/10 px-4 py-2 text-xs font-black uppercase tracking-widest text-amber-400 transition-colors hover:bg-amber-500/20"
                                    >
                                        Verifizierung senden
                                    </Link>
                                </div>
                            )}

                            <div className="flex flex-wrap items-center gap-4 border-t border-[var(--border-muted)] pt-4">
                                <button type="submit" disabled={profileForm.processing} className="sim-btn-primary flex items-center gap-2 px-8">
                                    {profileForm.processing && <ArrowsClockwise size={18} className="animate-spin" />}
                                    Daten aktualisieren
                                </button>
                                {status === 'profile-updated' && (
                                    <span className="page-reveal inline-flex items-center gap-2 text-sm font-bold text-emerald-400">
                                        <CheckCircle size={18} weight="fill" />
                                        Gespeichert
                                    </span>
                                )}
                            </div>
                        </form>
                    </SectionCard>

                    <SectionCard title="Passwort" icon={ShieldCheck} bodyClassName="space-y-6 p-6 md:p-8">
                        <p className="max-w-2xl text-sm text-[var(--text-muted)]">
                            Nutze ein starkes Passwort und aendere es regelmaessig.
                        </p>
                        <form onSubmit={submitPassword} className="space-y-6">
                            <div className="grid max-w-2xl gap-4">
                                <Field
                                    label="Aktuelles Passwort"
                                    type="password"
                                    value={passwordForm.data.current_password}
                                    onChange={(event) => passwordForm.setData('current_password', event.target.value)}
                                    autoComplete="current-password"
                                    error={passwordForm.errors.current_password}
                                />
                                <Field
                                    label="Neues Passwort"
                                    type="password"
                                    value={passwordForm.data.password}
                                    onChange={(event) => passwordForm.setData('password', event.target.value)}
                                    autoComplete="new-password"
                                    error={passwordForm.errors.password}
                                />
                                <Field
                                    label="Passwort bestaetigen"
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={(event) => passwordForm.setData('password_confirmation', event.target.value)}
                                    autoComplete="new-password"
                                    error={passwordForm.errors.password_confirmation}
                                />
                            </div>

                            <div className="flex flex-wrap items-center gap-4 border-t border-[var(--border-muted)] pt-4">
                                <button type="submit" disabled={passwordForm.processing} className="sim-btn-secondary flex items-center gap-2 px-8">
                                    {passwordForm.processing && <ArrowsClockwise size={18} className="animate-spin" />}
                                    Passwort aendern
                                </button>
                                {status === 'password-updated' && (
                                    <span className="page-reveal inline-flex items-center gap-2 text-sm font-bold text-emerald-400">
                                        <CheckCircle size={18} weight="fill" />
                                        Passwort aktualisiert
                                    </span>
                                )}
                            </div>
                        </form>
                    </SectionCard>

                    <SectionCard title="Gefahrenzone" icon={Warning} bodyClassName="space-y-6 p-6 md:p-8">
                        <p className="max-w-2xl text-sm text-[var(--text-muted)]">
                            Wenn du deinen Account loeschst, werden alle Daten dauerhaft entfernt.
                        </p>

                        {!confirmingUserDeletion ? (
                            <button
                                type="button"
                                onClick={() => setConfirmingUserDeletion(true)}
                                className="inline-flex items-center gap-2 rounded-xl border-2 border-rose-500/30 bg-rose-600/10 px-8 py-3 font-black text-rose-400 transition-colors hover:bg-rose-600/20"
                            >
                                <Trash size={18} weight="bold" />
                                Account loeschen
                            </button>
                        ) : (
                            <PageReveal className="rounded-2xl border-2 border-rose-500/20 bg-[var(--bg-pillar)]/60 p-6">
                                <div className="mb-6 flex items-start gap-4">
                                    <div className="rounded-xl bg-rose-500/10 p-3">
                                        <Warning size={24} className="text-rose-500" weight="fill" />
                                    </div>
                                    <div>
                                        <h3 className="text-lg font-bold text-[var(--text-main)]">Bist du dir sicher?</h3>
                                        <p className="text-sm text-[var(--text-muted)]">
                                            Gib dein Passwort ein, um die endgueltige Loeschung zu bestaetigen.
                                        </p>
                                    </div>
                                </div>

                                <form onSubmit={deleteUser} className="space-y-4">
                                    <div className="max-w-md">
                                        <Field
                                            label="Passwort zur Bestaetigung"
                                            type="password"
                                            value={deleteForm.data.password}
                                            onChange={(event) => deleteForm.setData('password', event.target.value)}
                                            error={deleteForm.errors.password}
                                            placeholder="Passwort"
                                        />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-4 pt-2">
                                        <button
                                            type="submit"
                                            disabled={deleteForm.processing}
                                            className="rounded-xl bg-rose-600 px-8 py-3 font-black text-white transition-colors hover:bg-rose-700 disabled:opacity-50"
                                        >
                                            Unwiderruflich loeschen
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setConfirmingUserDeletion(false)}
                                            className="px-4 text-sm font-bold uppercase tracking-widest text-[var(--text-muted)] transition-colors hover:text-[var(--text-main)]"
                                        >
                                            Abbrechen
                                        </button>
                                    </div>
                                </form>
                            </PageReveal>
                        )}
                    </SectionCard>
                </PageReveal>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] py-3 font-black text-white transition-all disabled:opacity-50;
                }
                .sim-btn-secondary {
                    @apply rounded-xl border-2 border-[var(--border-muted)] bg-[var(--bg-content)] py-3 font-black text-[var(--text-main)] transition-all disabled:opacity-50;
                }
            ` }} />
        </AuthenticatedLayout>
    );
}
