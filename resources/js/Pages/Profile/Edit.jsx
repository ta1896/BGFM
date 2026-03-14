import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage, Link } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    User, Lock, Warning, Trash, 
    CheckCircle, Envelope, ShieldCheck,
    ArrowsClockwise, Fingerprint
} from '@phosphor-icons/react';

const Card = ({ title, description, children, icon: Icon, variant = 'default' }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className={`sim-card p-8 border-[var(--border-muted)] relative overflow-hidden ${
            variant === 'danger' ? 'border-rose-500/20 shadow-[0_0_30px_rgba(244,63,94,0.05)]' : ''
        }`}
    >
        <div className="absolute top-0 right-0 p-8 opacity-[0.02] pointer-events-none">
            {Icon && <Icon size={120} weight="fill" className={variant === 'danger' ? 'text-rose-500' : 'text-cyan-400'} />}
        </div>
        <div className="mb-8 relative z-10">
            <h3 className={`text-xl font-bold mb-2 ${variant === 'danger' ? 'text-rose-400' : 'text-white'}`}>{title}</h3>
            {description && <p className="text-[var(--text-muted)] text-sm max-w-lg">{description}</p>}
        </div>
        <div className="relative z-10">
            {children}
        </div>
    </motion.div>
);

const SectionHeader = ({ title, subtitle }) => (
    <div className="mb-8 pl-4 border-l-4 border-cyan-500/50">
        <h2 className="text-3xl font-black text-white tracking-tight leading-none uppercase italic">{title}</h2>
        {subtitle && <p className="text-[var(--text-muted)] text-sm mt-2 font-bold uppercase tracking-widest">{subtitle}</p>}
    </div>
);

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

    const submitProfile = (e) => {
        e.preventDefault();
        profileForm.patch(route('profile.update'), {
            preserveScroll: true,
        });
    };

    const submitPassword = (e) => {
        e.preventDefault();
        passwordForm.put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    };

    const deleteUser = (e) => {
        e.preventDefault();
        deleteForm.delete(route('profile.destroy'), {
            preserveScroll: true,
            onSuccess: () => setConfirmingUserDeletion(false),
            onFinish: () => deleteForm.reset(),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Profil Einstellungen" />

            <div className="max-w-4xl mx-auto space-y-12 pb-20">
                <SectionHeader 
                    title="Profil-Zentrale" 
                    subtitle="Verwalte deine Identität und Sicherheit"
                />

                {/* Profile Information */}
                <Card 
                    title="Persönliche Daten" 
                    description="Aktualisiere deinen Namen und deine E-Mail-Adresse für die Kommunikation."
                    icon={User}
                >
                    <form onSubmit={submitProfile} className="space-y-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div className="space-y-2">
                                <label className="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Manager-Name</label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <User size={18} className="text-[var(--text-muted)] group-focus-within:text-cyan-400 transition-colors" />
                                    </div>
                                    <input 
                                        type="text"
                                        value={profileForm.data.name}
                                        onChange={e => profileForm.setData('name', e.target.value)}
                                        className="w-full bg-[var(--bg-pillar)]/50 border-2 border-[var(--border-pillar)] rounded-xl pl-11 pr-4 py-3 text-white focus:border-cyan-500/50 focus:ring-4 focus:ring-cyan-500/5 transition-all outline-none"
                                        required
                                        autoComplete="name"
                                    />
                                </div>
                                {profileForm.errors.name && <p className="text-rose-500 text-xs mt-1 font-bold">{profileForm.errors.name}</p>}
                            </div>

                            <div className="space-y-2">
                                <label className="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest px-1">E-Mail Adresse</label>
                                <div className="relative group">
                                    <div className="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <Envelope size={18} className="text-[var(--text-muted)] group-focus-within:text-cyan-400 transition-colors" />
                                    </div>
                                    <input 
                                        type="email"
                                        value={profileForm.data.email}
                                        onChange={e => profileForm.setData('email', e.target.value)}
                                        className="w-full bg-[var(--bg-pillar)]/50 border-2 border-[var(--border-pillar)] rounded-xl pl-11 pr-4 py-3 text-white focus:border-cyan-500/50 focus:ring-4 focus:ring-cyan-500/5 transition-all outline-none"
                                        required
                                        autoComplete="username"
                                    />
                                </div>
                                {profileForm.errors.email && <p className="text-rose-500 text-xs mt-1 font-bold">{profileForm.errors.email}</p>}
                            </div>
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div className="bg-amber-500/5 border border-amber-500/20 rounded-xl p-4 flex items-center justify-between">
                                <p className="text-sm text-amber-200 font-medium">Deine E-Mail ist noch nicht verifiziert.</p>
                                <Link 
                                    href={route('verification.send')} 
                                    method="post" 
                                    as="button"
                                    className="text-xs font-black bg-amber-500/10 text-amber-400 px-4 py-2 rounded-lg hover:bg-amber-500/20 transition-all uppercase tracking-widest"
                                >
                                    Verifizierung senden
                                </Link>
                            </div>
                        )}

                        <div className="flex items-center gap-4 pt-4 border-t border-[var(--border-muted)]">
                            <button 
                                type="submit" 
                                disabled={profileForm.processing}
                                className="sim-btn-primary px-8 flex items-center gap-2"
                            >
                                {profileForm.processing && <ArrowsClockwise size={18} className="animate-spin" />}
                                Daten aktualisieren
                            </button>
                            
                            <AnimatePresence>
                                {status === 'profile-updated' && (
                                    <motion.p 
                                        initial={{ opacity: 0, x: -10 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        exit={{ opacity: 0 }}
                                        className="text-emerald-400 text-sm font-bold flex items-center gap-2"
                                    >
                                        <CheckCircle size={18} weight="fill" />
                                        Gespeichert
                                    </motion.p>
                                )}
                            </AnimatePresence>
                        </div>
                    </form>
                </Card>

                {/* Update Password */}
                <Card 
                    title="Sicherheit" 
                    description="Schütze dein Konto mit einem starken, einzigartigen Passwort."
                    icon={ShieldCheck}
                >
                    <form onSubmit={submitPassword} className="space-y-6">
                        <div className="space-y-4 max-w-md">
                            <div className="space-y-2">
                                <label className="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Aktuelles Passwort</label>
                                <input 
                                    type="password"
                                    value={passwordForm.data.current_password}
                                    onChange={e => passwordForm.setData('current_password', e.target.value)}
                                    className="w-full bg-[var(--bg-pillar)]/50 border-2 border-[var(--border-pillar)] rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 transition-all outline-none"
                                    autoComplete="current-password"
                                />
                                {passwordForm.errors.current_password && <p className="text-rose-500 text-xs mt-1 font-bold">{passwordForm.errors.current_password}</p>}
                            </div>

                            <div className="space-y-2">
                                <label className="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Neues Passwort</label>
                                <input 
                                    type="password"
                                    value={passwordForm.data.password}
                                    onChange={e => passwordForm.setData('password', e.target.value)}
                                    className="w-full bg-[var(--bg-pillar)]/50 border-2 border-[var(--border-pillar)] rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 transition-all outline-none"
                                    autoComplete="new-password"
                                />
                                {passwordForm.errors.password && <p className="text-rose-500 text-xs mt-1 font-bold">{passwordForm.errors.password}</p>}
                            </div>

                            <div className="space-y-2">
                                <label className="text-xs font-black text-[var(--text-muted)] uppercase tracking-widest px-1">Passwort bestätigen</label>
                                <input 
                                    type="password"
                                    value={passwordForm.data.password_confirmation}
                                    onChange={e => passwordForm.setData('password_confirmation', e.target.value)}
                                    className="w-full bg-[var(--bg-pillar)]/50 border-2 border-[var(--border-pillar)] rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 transition-all outline-none"
                                    autoComplete="new-password"
                                />
                                {passwordForm.errors.password_confirmation && <p className="text-rose-500 text-xs mt-1 font-bold">{passwordForm.errors.password_confirmation}</p>}
                            </div>
                        </div>

                        <div className="flex items-center gap-4 pt-4 border-t border-[var(--border-muted)]">
                            <button 
                                type="submit" 
                                disabled={passwordForm.processing}
                                className="sim-btn-secondary px-8 flex items-center gap-2"
                            >
                                {passwordForm.processing && <ArrowsClockwise size={18} className="animate-spin" />}
                                Passwort ändern
                            </button>
                            
                            <AnimatePresence>
                                {status === 'password-updated' && (
                                    <motion.p 
                                        initial={{ opacity: 0, x: -10 }}
                                        animate={{ opacity: 1, x: 0 }}
                                        exit={{ opacity: 0 }}
                                        className="text-emerald-400 text-sm font-bold flex items-center gap-2"
                                    >
                                        <CheckCircle size={18} weight="fill" />
                                        Passwort aktualisiert
                                    </motion.p>
                                )}
                            </AnimatePresence>
                        </div>
                    </form>
                </Card>

                {/* Delete Account */}
                <Card 
                    title="Gefahrenzone" 
                    description="Wenn du deinen Account löschst, werden alle deine Daten dauerhaft entfernt. Dieser Vorgang kann nicht rückgängig gemacht werden."
                    icon={Warning}
                    variant="danger"
                >
                    <div className="flex flex-col items-start gap-4">
                        {!confirmingUserDeletion ? (
                            <button 
                                onClick={() => setConfirmingUserDeletion(true)}
                                className="bg-rose-600/10 border-2 border-rose-500/30 text-rose-400 font-black py-3 px-8 rounded-xl hover:bg-rose-600/20 transition-all flex items-center gap-2"
                            >
                                <Trash size={18} weight="bold" />
                                ACCOUNT LÖSCHEN
                            </button>
                        ) : (
                            <motion.div 
                                initial={{ opacity: 0, height: 0 }}
                                animate={{ opacity: 1, height: 'auto' }}
                                className="w-full bg-[var(--bg-pillar)]/50 border-2 border-rose-500/20 rounded-2xl p-6 space-y-6"
                            >
                                <div className="flex items-start gap-4">
                                    <div className="p-3 bg-rose-500/10 rounded-xl">
                                        <Warning size={24} className="text-rose-500" weight="fill" />
                                    </div>
                                    <div>
                                        <h4 className="text-white font-bold text-lg">Bist du dir absolut sicher?</h4>
                                        <p className="text-[var(--text-muted)] text-sm">Bitte gib dein Passwort ein, um die endgültige Löschung deines Kontos zu bestätigen.</p>
                                    </div>
                                </div>

                                <form onSubmit={deleteUser} className="space-y-4">
                                    <div className="max-w-md">
                                        <input 
                                            type="password"
                                            placeholder="Dein Passwort zur Bestätigung"
                                            value={deleteForm.data.password}
                                            onChange={e => deleteForm.setData('password', e.target.value)}
                                            className="w-full bg-[var(--sim-shell-bg)] border-2 border-[var(--border-pillar)] rounded-xl px-4 py-3 text-white focus:border-rose-500/50 transition-all outline-none"
                                            required
                                        />
                                        {deleteForm.errors.password && <p className="text-rose-500 text-xs mt-1 font-bold">{deleteForm.errors.password}</p>}
                                    </div>

                                    <div className="flex items-center gap-4 pt-2">
                                        <button 
                                            type="submit" 
                                            disabled={deleteForm.processing}
                                            className="bg-rose-600 hover:bg-rose-700 text-white font-black py-3 px-8 rounded-xl hover:scale-[1.02] transition-all disabled:opacity-50"
                                        >
                                            UNWIDERRUFLICH LÖSCHEN
                                        </button>
                                        <button 
                                            type="button" 
                                            onClick={() => setConfirmingUserDeletion(false)}
                                            className="text-[var(--text-muted)] font-bold hover:text-white transition-colors uppercase text-sm tracking-widest px-4"
                                        >
                                            Abbrechen
                                        </button>
                                    </div>
                                </form>
                            </motion.div>
                        )}
                    </div>
                </Card>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black py-3 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_4px_15px_rgba(34,211,238,0.2)] disabled:opacity-50 disabled:scale-100;
                }
                .sim-btn-secondary {
                    @apply bg-[var(--bg-content)] border-2 border-[var(--border-muted)] text-white font-black py-3 rounded-xl hover:bg-slate-700 hover:border-slate-600 transition-all active:scale-[0.98] disabled:opacity-50 disabled:scale-100;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
