import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Gear, Lock, Suitcase, WarningCircle, 
    CheckCircle, User, Fingerprint, Trash, 
    Plus, ArrowsClockwise 
} from '@phosphor-icons/react';

const TabButton = ({ active, onClick, icon: Icon, label }) => (
    <button 
        onClick={onClick}
        className={`flex items-center gap-3 px-6 py-4 rounded-xl border-2 transition-all duration-300 font-bold ${
            active 
                ? 'bg-cyan-500/10 border-cyan-500/50 text-cyan-400 shadow-[0_0_20px_rgba(34,211,238,0.1)]' 
                : 'bg-slate-900/40 border-slate-800 text-slate-500 hover:text-slate-300 hover:border-slate-700'
        }`}
    >
        <Icon size={20} weight={active ? 'fill' : 'bold'} />
        {label}
    </button>
);

const Card = ({ title, description, children, icon: Icon }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="sim-card p-8 border-slate-800/50 relative overflow-hidden"
    >
        <div className="absolute top-0 right-0 p-8 opacity-[0.02] pointer-events-none">
            {Icon && <Icon size={120} weight="fill" />}
        </div>
        <div className="mb-8">
            <h3 className="text-xl font-bold text-white mb-2">{title}</h3>
            {description && <p className="text-slate-400 text-sm">{description}</p>}
        </div>
        {children}
    </motion.div>
);

export default function Settings({ userClubs, passkeys }) {
    const { auth, flash } = usePage().props;
    const [activeTab, setActiveTab] = useState('general');

    const generalForm = useForm({
        default_club_id: auth.user.default_club_id || '',
    });

    const submitGeneral = (e) => {
        e.preventDefault();
        generalForm.patch(route('settings.update'), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Einstellungen" />

            <div className="max-w-[1200px] mx-auto space-y-8">
                {/* Tab Navigation */}
                <div className="flex flex-wrap gap-4">
                    <TabButton 
                        active={activeTab === 'general'} 
                        onClick={() => setActiveTab('general')}
                        icon={Gear}
                        label="Allgemein"
                    />
                    <TabButton 
                        active={activeTab === 'security'} 
                        onClick={() => setActiveTab('security')}
                        icon={Lock}
                        label="Sicherheit"
                    />
                </div>

                <AnimatePresence mode="wait">
                    {activeTab === 'general' && (
                        <motion.div
                            key="general"
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: 20 }}
                            className="space-y-8"
                        >
                            <Card 
                                title="Vereins-Einstellungen" 
                                description="Verwalte deine Standard-Werte für ein schnelleres Spielerlebnis."
                                icon={Suitcase}
                            >
                                <form onSubmit={submitGeneral} className="space-y-6">
                                    <div className="space-y-4">
                                        <label className="block text-sm font-bold text-slate-300 uppercase tracking-widest">
                                            Bevorzugter Verein
                                        </label>
                                        <p className="text-xs text-slate-500 italic mb-4">
                                            Dieser Verein wird nach dem Login automatisch als aktiver Verein geladen.
                                        </p>
                                        <select 
                                            value={generalForm.data.default_club_id}
                                            onChange={e => generalForm.setData('default_club_id', e.target.value)}
                                            className="w-full bg-slate-800 border-2 border-slate-700/50 rounded-xl px-4 py-3 text-white focus:border-cyan-500/50 focus:ring-0 transition-all cursor-pointer"
                                        >
                                            <option value="">Kein Standard-Verein gewählt</option>
                                            {userClubs.map(club => (
                                                <option key={club.id} value={club.id}>{club.name}</option>
                                            ))}
                                        </select>
                                        {generalForm.errors.default_club_id && (
                                            <p className="text-rose-500 text-xs font-medium">{generalForm.errors.default_club_id}</p>
                                        )}
                                    </div>

                                    <div className="flex items-center justify-end border-t border-slate-800 pt-6 mt-8">
                                        <button 
                                            disabled={generalForm.processing}
                                            className="sim-btn-primary px-8"
                                        >
                                            {generalForm.processing ? 'Wird gespeichert...' : 'Änderungen speichern'}
                                        </button>
                                    </div>
                                </form>
                            </Card>
                        </motion.div>
                    )}

                    {activeTab === 'security' && (
                        <motion.div
                            key="security"
                            initial={{ opacity: 0, x: -20 }}
                            animate={{ opacity: 1, x: 0 }}
                            exit={{ opacity: 0, x: 20 }}
                            className="space-y-8"
                        >
                            <Card 
                                title="Passkeys (WebAuthn)" 
                                description="Sicheres Einloggen ohne Passwort via Biometrie oder Sicherheitsschlüssel."
                                icon={Fingerprint}
                            >
                                <div className="space-y-4">
                                    {passkeys && passkeys.length > 0 ? (
                                        <div className="grid gap-4">
                                            {passkeys.map(passkey => (
                                                <div key={passkey.id} className="flex items-center justify-between p-4 bg-slate-800/50 rounded-2xl border border-slate-700/50 group">
                                                    <div className="flex items-center gap-4">
                                                        <div className="h-10 w-10 rounded-full bg-purple-500/20 text-purple-400 flex items-center justify-center">
                                                            <Fingerprint size={20} weight="fill" />
                                                        </div>
                                                        <div>
                                                            <p className="font-bold text-white leading-tight">{passkey.alias || 'Unbenannter Schlüssel'}</p>
                                                            <p className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">{passkey.created_at_formatted || 'Passkey'}</p>
                                                        </div>
                                                    </div>
                                                    <button className="p-2 text-slate-500 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition-all">
                                                        <Trash size={20} />
                                                    </button>
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="text-center py-12 bg-slate-900/50 rounded-3xl border-2 border-dashed border-slate-700/50">
                                            <Fingerprint size={48} weight="thin" className="mx-auto text-slate-700 mb-4" />
                                            <p className="text-slate-500 font-medium">Bisher wurden keine Passkeys registriert.</p>
                                        </div>
                                    )}

                                    <button className="w-full flex items-center justify-center gap-2 bg-purple-600/10 border-2 border-purple-500/30 text-purple-400 font-bold py-4 rounded-xl hover:bg-purple-600/20 transition-all mt-4">
                                        <Plus size={20} weight="bold" />
                                        Neuen Passkey hinzufügen
                                    </button>
                                </div>
                            </Card>

                            <Card 
                                title="Sicherheits-Check" 
                                description="Überprüfe deine Kontosicherheit regelmäßig."
                                icon={Lock}
                            >
                                <div className="bg-emerald-500/5 border border-emerald-500/20 rounded-2xl p-6 flex items-start gap-4">
                                    <CheckCircle size={24} weight="fill" className="text-emerald-500 mt-1" />
                                    <div>
                                        <p className="font-bold text-white mb-1">E-Mail verifiziert</p>
                                        <p className="text-sm text-slate-400">Deine E-Mail Adresse ist bestätigt und schützt dein Konto zusätzlich.</p>
                                    </div>
                                </div>
                            </Card>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-black py-3 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all disabled:opacity-50 disabled:scale-100;
                }
            `}} />
        </AuthenticatedLayout>
    );
}
