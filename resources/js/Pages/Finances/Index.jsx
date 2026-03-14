import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage, Link } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { motion } from 'framer-motion';
import { 
    Bank, 
    Coin, 
    ArrowsLeftRight, 
    TrendUp, 
    TrendDown,
    Calendar,
    Tag,
    Note,
    Wallet,
    WarningCircle
} from '@phosphor-icons/react';

const StatCard = ({ title, value, unit, icon: Icon, colorClass, delay }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ delay }}
        className="sim-card p-6 relative overflow-hidden group border-[var(--border-muted)]"
    >
        <div className={`absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity ${colorClass}`}>
            <Icon size={80} weight="fill" />
        </div>
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] mb-2">{title}</p>
        <div className="flex items-baseline gap-2">
            <p className="text-3xl font-black text-white tracking-tighter">
                {typeof value === 'number' ? value.toLocaleString('de-DE') : value}
            </p>
            <span className="text-sm font-bold text-[var(--text-muted)] uppercase">{unit}</span>
        </div>
        <div className={`mt-4 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded bg-white/5 border border-white/10 ${colorClass}`}>
            Live Balance
        </div>
    </motion.div>
);

export default function Finances({ clubs, activeClub, transactions }) {
    const { auth } = usePage().props;

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <WarningCircle size={64} weight="thin" className="text-slate-700 mb-6" />
                    <h2 className="text-2xl font-bold text-white mb-2">Kein Verein aktiv</h2>
                    <p className="text-[var(--text-muted)] max-w-md">Es konnte kein aktiver Verein gefunden werden. Bitte wähle einen Verein aus der Liste oder erstelle einen neuen.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Finanzen" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                {/* Header */}
                <div className="flex items-end justify-between">
                    <div>
                        <p className="sim-section-title">Finanz-Management</p>
                        <h1 className="text-4xl font-black text-white tracking-tighter">Budget & Bilanz</h1>
                    </div>
                </div>

                {/* Main Stats */}
                <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <StatCard 
                        title="Transferbudget" 
                        value={parseFloat(activeClub.budget)} 
                        unit="€" 
                        icon={Wallet} 
                        colorClass="text-emerald-400"
                        delay={0.1}
                    />
                    <StatCard 
                        title="Club Coins" 
                        value={parseInt(activeClub.coins)} 
                        unit="Coins" 
                        icon={Coin} 
                        colorClass="text-amber-400"
                        delay={0.2}
                    />
                    <motion.div 
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 0.3 }}
                        className="sim-card p-6 bg-gradient-to-br from-[#d9b15c]/10 to-[#8d6e32]/10 border-amber-500/30 hidden lg:block"
                    >
                        <div className="flex flex-col h-full justify-between">
                            <div>
                                <h3 className="text-sm font-bold text-amber-500 uppercase tracking-widest mb-1">Status</h3>
                                <p className="text-white font-medium">Finanziell stabil</p>
                            </div>
                            <div className="mt-auto">
                                <p className="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest mb-2">Sponsor-Einnahmen lfd. Saison</p>
                                <div className="h-2 w-full bg-[var(--bg-content)] rounded-full overflow-hidden">
                                    <div className="h-full bg-amber-500 w-[65%] rounded-full shadow-[0_0_10px_rgba(217,177,92,0.5)]"></div>
                                </div>
                            </div>
                        </div>
                    </motion.div>
                </div>

                {/* Transactions Table */}
                <Card title="Transaktionshistorie" icon={ArrowsLeftRight}>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-muted)] text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">
                                    <th className="px-6 py-4">Datum</th>
                                    <th className="px-6 py-4">Kontext</th>
                                    <th className="px-6 py-4">Kategorie</th>
                                    <th className="px-6 py-4 text-right">Betrag</th>
                                    <th className="px-6 py-4 text-right">Saldo</th>
                                    <th className="px-6 py-4">Notiz</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {transactions.data.map((tx, idx) => {
                                    const isIncome = tx.direction === 'income';
                                    const isCoin = tx.asset_type === 'coins';
                                    return (
                                        <motion.tr 
                                            key={tx.id}
                                            initial={{ opacity: 0 }}
                                            animate={{ opacity: 1 }}
                                            transition={{ delay: 0.1 + idx * 0.03 }}
                                            className="group hover:bg-white/[0.02] transition-colors"
                                        >
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex items-center gap-3">
                                                    <Calendar size={16} className="text-slate-600" />
                                                    <span className="text-sm font-bold text-[var(--text-muted)] font-mono italic">
                                                        {tx.booked_at_formatted}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span className={`inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border ${
                                                    isIncome 
                                                        ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' 
                                                        : 'bg-rose-500/10 text-rose-400 border-rose-500/20'
                                                }`}>
                                                    {tx.context_type}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm font-bold text-[var(--text-muted)] uppercase tracking-tighter">
                                                {tx.asset_type}
                                            </td>
                                            <td className={`px-6 py-4 text-right font-black font-mono tracking-tighter ${isIncome ? 'text-emerald-400' : 'text-rose-400'}`}>
                                                <div className="flex items-center justify-end gap-1">
                                                    {isIncome ? <TrendUp weight="bold" /> : <TrendDown weight="bold" />}
                                                    {isIncome ? '+' : '-'} {tx.amount.toLocaleString('de-DE', { minimumFractionDigits: isCoin ? 0 : 2 })}
                                                    <span className="text-[10px] opacity-70 ml-1">{isCoin ? 'C' : '€'}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right font-bold text-slate-300 font-mono tracking-tighter">
                                                {tx.balance_after ? tx.balance_after.toLocaleString('de-DE', { minimumFractionDigits: isCoin ? 0 : 2 }) : '-'}
                                                <span className="text-[10px] text-[var(--text-muted)] ml-1">{isCoin ? 'C' : '€'}</span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-[var(--text-muted)] italic max-w-xs truncate">
                                                {tx.note || '-'}
                                            </td>
                                        </motion.tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {transactions.links.length > 3 && (
                        <div className="px-6 py-6 border-t border-[var(--border-muted)] flex justify-center gap-2">
                            {transactions.links.map((link, i) => (
                                <PaginationLink
                                    key={i}
                                    link={link}
                                    className={`px-3 py-1 rounded-lg text-xs font-bold transition-all ${
                                        link.active 
                                            ? 'bg-amber-600 text-black shadow-[0_0_10px_rgba(217,177,92,0.3)]' 
                                            : 'text-[var(--text-muted)] hover:text-white hover:bg-[var(--bg-content)]'
                                    }`}
                                    disabledClassName="px-3 py-1 rounded-lg text-xs font-bold transition-all opacity-30 pointer-events-none"
                                />
                            ))}
                        </div>
                    )}
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}

const Card = ({ title, children, icon: Icon }) => (
    <motion.div 
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="sim-card border-[var(--border-muted)]"
    >
        <div className="px-6 py-4 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 flex items-center justify-between">
            <div className="flex items-center gap-3">
                <Icon size={20} weight="duotone" className="text-amber-500" />
                <h2 className="text-lg font-black text-white tracking-tight uppercase">{title}</h2>
            </div>
        </div>
        {children}
    </motion.div>
);
