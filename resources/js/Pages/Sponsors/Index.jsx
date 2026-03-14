import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    Handshake, 
    Crown, 
    Calendar, 
    TrendUp, 
    XCircle,
    CheckCircle,
    Info,
    CurrencyEur,
    Star,
    HourglassMedium
} from '@phosphor-icons/react';

const OfferCard = ({ offer, activeClub, onSign, disabled }) => {
    const [months, setMonths] = useState(12);
    
    return (
        <motion.div 
            layout
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            className="sim-card p-6 flex flex-col h-full group border-[var(--border-muted)] hover:border-amber-500/30 transition-all"
        >
            <div className="flex justify-between items-start mb-6">
                <div>
                    <h4 className="text-xl font-black text-white group-hover:text-amber-500 transition-colors uppercase tracking-tighter">
                        {offer.name}
                    </h4>
                    <span className="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-[var(--bg-content)] text-[var(--text-muted)] border border-[var(--border-pillar)] mt-2">
                        {offer.tier}
                    </span>
                </div>
                <div className="h-12 w-12 rounded-xl bg-[var(--bg-content)]/50 flex items-center justify-center text-[var(--text-muted)] group-hover:text-amber-500 transition-colors border border-[var(--border-muted)]">
                    <Handshake size={28} weight="duotone" />
                </div>
            </div>

            <div className="space-y-4 mb-8 flex-1">
                <div className="flex justify-between items-center text-sm">
                    <span className="text-[var(--text-muted)] font-bold uppercase tracking-widest text-[10px]">Basisbetrag</span>
                    <span className="font-black text-emerald-400 font-mono">
                        {offer.base_weekly_amount.toLocaleString('de-DE')} €
                    </span>
                </div>
                <div className="flex justify-between items-center text-sm">
                    <span className="text-[var(--text-muted)] font-bold uppercase tracking-widest text-[10px]">Reputation-Anforderung</span>
                    <div className="flex items-center gap-1">
                        <Star size={14} weight="fill" className="text-amber-400" />
                        <span className="font-black text-white font-mono">{offer.reputation_min}</span>
                    </div>
                </div>
            </div>

            <div className="mt-auto space-y-4">
                <div className="flex items-center gap-2">
                    <div className="relative flex-1">
                        <input 
                            type="number" 
                            min="1" 
                            max="60" 
                            value={months}
                            onChange={(e) => setMonths(e.target.value)}
                            className="w-full bg-[var(--bg-pillar)] border-2 border-[var(--border-pillar)] rounded-xl px-4 py-3 text-white font-bold text-center focus:border-amber-500/50 focus:ring-0 transition-all disabled:opacity-50"
                            disabled={disabled}
                        />
                        <span className="absolute right-3 top-3.5 text-[10px] font-black uppercase tracking-widest text-slate-600 pointer-events-none">Mte</span>
                    </div>
                </div>
                <button 
                    onClick={() => onSign(offer.id, months)}
                    disabled={disabled}
                    className="w-full sim-btn-primary py-4 disabled:opacity-20 disabled:grayscale transition-all"
                >
                    {disabled ? 'Vertrag aktiv' : 'Angebot annehmen'}
                </button>
            </div>
        </motion.div>
    );
};

export default function Sponsors({ offers, activeContract, history, activeClub }) {
    const { auth } = usePage().props;
    const signForm = useForm({
        club_id: activeClub?.id,
        months: 12,
    });

    const handleSign = (sponsorId, months) => {
        signForm.setData({
            club_id: activeClub.id,
            months: months
        });
        signForm.post(route('sponsors.sign', sponsorId), {
            preserveScroll: true,
        });
    };

    const handleTerminate = () => {
        if (confirm('Möchtest du diesen Vertrag wirklich vorzeitig beenden?')) {
            useForm().post(route('sponsors.contracts.terminate', activeContract.id), {
                preserveScroll: true,
            });
        }
    };

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <h2 className="text-2xl font-bold text-white mb-2">Kein Verein aktiv</h2>
                    <p className="text-[var(--text-muted)] max-w-md">Es konnte kein aktiver Verein gefunden werden. Bitte wähle einen Verein aus der Liste oder erstelle einen neuen.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Sponsoring" />

            <div className="max-w-[1400px] mx-auto space-y-10">
                {/* Active Support Hero */}
                <div className="relative rounded-[2rem] overflow-hidden border border-[var(--border-muted)] bg-[var(--bg-pillar)]/40">
                    <div className="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/60 to-transparent z-10" />
                    <div className="absolute top-0 right-0 w-[600px] h-[600px] bg-amber-500/5 rounded-full blur-[100px] -translate-y-1/2 translate-x-1/2" />
                    
                    <div className="relative z-20 p-10 md:p-16 flex flex-col md:flex-row items-center justify-between gap-12">
                        <div className="flex-1">
                            <motion.div 
                                initial={{ opacity: 0, x: -20 }}
                                animate={{ opacity: 1, x: 0 }}
                                className="flex items-center gap-3 mb-6"
                            >
                                <div className="h-2 w-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(217,177,92,0.6)] animate-pulse" />
                                <span className="text-xs font-black uppercase tracking-[0.3em] text-amber-500">Status: Sponsoring Live</span>
                            </motion.div>
                            
                            {activeContract ? (
                                <motion.div
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: 0.1 }}
                                >
                                    <h2 className="text-5xl lg:text-7xl font-black text-white tracking-tighter mb-4 leading-none uppercase italic">
                                        {activeContract.sponsor.name}
                                    </h2>
                                    <p className="text-xl text-[var(--text-muted)] font-medium max-w-xl">
                                        Partner seit der laufenden Saison. Vertrag gültig bis <span className="text-white font-black">{activeContract.ends_on_formatted}</span>.
                                    </p>
                                </motion.div>
                            ) : (
                                <motion.div
                                    initial={{ opacity: 0, y: 20 }}
                                    animate={{ opacity: 1, y: 0 }}
                                    transition={{ delay: 0.1 }}
                                >
                                    <h2 className="text-5xl lg:text-7xl font-black text-slate-700 tracking-tighter mb-4 leading-none uppercase italic">
                                        Kein Partner
                                    </h2>
                                    <p className="text-xl text-[var(--text-muted)] font-medium max-w-xl">
                                        Registriere jetzt einen neuen Hauptsponsor, um deine wöchentlichen Einnahmen zu maximieren.
                                    </p>
                                </motion.div>
                            )}
                        </div>

                        {activeContract && (
                            <motion.div 
                                initial={{ opacity: 0, scale: 0.9 }}
                                animate={{ opacity: 1, scale: 1 }}
                                transition={{ delay: 0.2 }}
                                className="w-full md:w-auto sim-card-soft p-8 text-center md:text-right border-[var(--border-muted)] backdrop-blur-3xl"
                            >
                                <p className="text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] mb-2">Einnahmen / Woche</p>
                                <div className="flex items-baseline justify-center md:justify-end gap-2 mb-6">
                                    <span className="text-5xl font-black text-emerald-400 font-mono tracking-tighter">
                                        {activeContract.weekly_amount.toLocaleString('de-DE')}
                                    </span>
                                    <span className="text-xl font-black text-emerald-600/50">€</span>
                                </div>
                                <button 
                                    onClick={handleTerminate}
                                    className="text-[10px] font-black uppercase tracking-widest text-rose-500 hover:text-rose-400 transition-colors flex items-center justify-center md:justify-end gap-2 ml-auto"
                                >
                                    <XCircle size={16} weight="bold" />
                                    Vertrag kündigen
                                </button>
                            </motion.div>
                        )}
                    </div>
                </div>

                <div className="grid lg:grid-cols-3 gap-8">
                    {/* Available Offers */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="flex items-center justify-between">
                            <h3 className="text-xl font-black text-white uppercase tracking-widest flex items-center gap-3">
                                <Crown size={24} weight="duotone" className="text-amber-400" />
                                Sponsoring-Angebote
                            </h3>
                            <div className="px-3 py-1 rounded-full bg-[var(--bg-content)] text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                {offers.length} Verfügbar
                            </div>
                        </div>

                        <div className="grid md:grid-cols-2 gap-6">
                            {offers.map((offer) => (
                                <OfferCard 
                                    key={offer.id} 
                                    offer={offer} 
                                    onSign={handleSign}
                                    disabled={activeContract !== null}
                                />
                            ))}
                        </div>
                    </div>

                    {/* History Sidebar */}
                    <div className="space-y-6">
                        <h3 className="text-xl font-black text-white uppercase tracking-widest flex items-center gap-3">
                            <HourglassMedium size={24} weight="duotone" className="text-[var(--text-muted)]" />
                            Historie
                        </h3>
                        <div className="sim-card p-0 border-[var(--border-muted)] overflow-hidden">
                            <div className="divide-y divide-slate-800/50">
                                {history.length > 0 ? (
                                    history.map((contract) => (
                                        <div key={contract.id} className="p-4 hover:bg-white/[0.02] transition-colors flex justify-between items-center group">
                                            <div>
                                                <p className="font-bold text-white group-hover:text-amber-500 transition-colors">{contract.sponsor.name}</p>
                                                <p className="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest">
                                                    {contract.starts_on_formatted} - {contract.ends_on_formatted}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="font-black text-emerald-400 font-mono italic">+{contract.weekly_amount.toLocaleString('de-DE')} €</p>
                                                <p className={`text-[10px] font-black uppercase tracking-widest ${
                                                    contract.status === 'active' ? 'text-amber-500' : 'text-slate-600'
                                                }`}>
                                                    {contract.status}
                                                </p>
                                            </div>
                                        </div>
                                    ))
                                ) : (
                                    <div className="p-12 text-center text-slate-600 italic text-sm">
                                        Keine historischen Daten verfügbar.
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: `
                .sim-btn-primary {
                    @apply bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black font-black py-2 rounded-xl hover:scale-[1.02] active:scale-[0.98] transition-all shadow-[0_0_20px_rgba(217,177,92,0.15)];
                }
            `}} />
        </AuthenticatedLayout>
    );
}
