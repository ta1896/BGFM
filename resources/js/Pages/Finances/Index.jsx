import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import {
    ArrowsLeftRight,
    TrendUp,
    TrendDown,
    Calendar,
    Coin,
    Wallet,
    WarningCircle,
} from '@phosphor-icons/react';

const StatCard = ({ title, value, unit, icon: Icon, colorClass }) => (
    <div className="sim-card p-6 relative overflow-hidden group border-[var(--border-muted)]">
        <div className={`absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity ${colorClass}`}>
            <Icon size={80} weight="fill" />
        </div>
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)] mb-2">{title}</p>
        <div className="flex items-baseline gap-2">
            <p className="text-3xl font-black text-[var(--text-main)] tracking-tighter">
                {typeof value === 'number' ? value.toLocaleString('de-DE') : value}
            </p>
            <span className="text-sm font-bold text-[var(--text-muted)] uppercase">{unit}</span>
        </div>
        <div className={`mt-4 inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded bg-white/5 border border-white/10 ${colorClass}`}>
            Live Balance
        </div>
    </div>
);

export default function Finances({ activeClub, transactions }) {
    usePage();

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <div className="flex flex-col items-center justify-center py-20 text-center">
                    <WarningCircle size={64} weight="thin" className="text-slate-700 mb-6" />
                    <h2 className="text-2xl font-bold text-[var(--text-main)] mb-2">Kein Verein aktiv</h2>
                    <p className="text-[var(--text-muted)] max-w-md">Es konnte kein aktiver Verein gefunden werden. Bitte waehle einen Verein aus der Liste oder erstelle einen neuen.</p>
                </div>
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Finanzen" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                <PageReveal className="flex items-end justify-between">
                    <div>
                        <p className="sim-section-title">Finanz-Management</p>
                        <h1 className="text-4xl font-black text-[var(--text-main)] tracking-tighter">Budget & Bilanz</h1>
                    </div>
                </PageReveal>

                <StaggerGroup className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <StatCard
                        title="Transferbudget"
                        value={parseFloat(activeClub.budget)}
                        unit="EUR"
                        icon={Wallet}
                        colorClass="text-emerald-400"
                    />
                    <StatCard
                        title="Club Coins"
                        value={parseInt(activeClub.coins)}
                        unit="Coins"
                        icon={Coin}
                        colorClass="text-amber-400"
                    />
                    <div className="sim-card p-6 bg-gradient-to-br from-[color:var(--accent-glow)] to-transparent border-[var(--border-pillar)] hidden lg:block">
                        <div className="flex flex-col h-full justify-between">
                            <div>
                                <h3 className="text-sm font-bold text-[var(--accent-primary)] uppercase tracking-widest mb-1">Status</h3>
                                <p className="text-[var(--text-main)] font-medium">Finanziell stabil</p>
                            </div>
                            <div className="mt-auto">
                                <p className="text-[10px] text-[var(--text-muted)] font-bold uppercase tracking-widest mb-2">Sponsor-Einnahmen lfd. Saison</p>
                                <div className="h-2 w-full bg-[var(--bg-content)] rounded-full overflow-hidden">
                                    <div className="h-full rounded-full w-[65%]" style={{ background: 'linear-gradient(90deg, var(--accent-primary), var(--accent-secondary))' }} />
                                </div>
                            </div>
                        </div>
                    </div>
                </StaggerGroup>

                <PageReveal delay={140}>
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
                                {transactions.data.map((tx) => {
                                    const isIncome = tx.direction === 'income';
                                    const isCoin = tx.asset_type === 'coins';

                                    return (
                                        <tr key={tx.id} className="group hover:bg-white/[0.02] transition-colors">
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
                                                    <span className="text-[10px] opacity-70 ml-1">{isCoin ? 'C' : 'EUR'}</span>
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 text-right font-bold text-slate-300 font-mono tracking-tighter">
                                                {tx.balance_after ? tx.balance_after.toLocaleString('de-DE', { minimumFractionDigits: isCoin ? 0 : 2 }) : '-'}
                                                <span className="text-[10px] text-[var(--text-muted)] ml-1">{isCoin ? 'C' : 'EUR'}</span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-[var(--text-muted)] italic max-w-xs truncate">
                                                {tx.note || '-'}
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>

                    {transactions.links.length > 3 && (
                        <div className="px-6 py-6 border-t border-[var(--border-muted)] flex justify-center gap-2">
                            {transactions.links.map((link, index) => (
                                <PaginationLink
                                    key={index}
                                    link={link}
                                    className={`px-3 py-1 rounded-lg text-xs font-bold transition-all ${
                                        link.active
                                            ? 'text-black shadow-[0_0_10px_rgba(0,0,0,0.1)]'
                                            : 'text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--bg-content)]'
                                    }`}
                                    disabledClassName="px-3 py-1 rounded-lg text-xs font-bold transition-all opacity-30 pointer-events-none"
                                    style={link.active ? { background: 'linear-gradient(135deg, var(--accent-primary), var(--accent-secondary))' } : undefined}
                                />
                            ))}
                        </div>
                    )}
                    </Card>
                </PageReveal>
            </div>
        </AuthenticatedLayout>
    );
}

const Card = ({ title, children, icon: Icon }) => (
    <div className="sim-card border-[var(--border-muted)]">
        <div className="px-6 py-4 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 flex items-center justify-between">
            <div className="flex items-center gap-3">
                <Icon size={20} weight="duotone" className="text-[var(--accent-primary)]" />
                <h2 className="text-lg font-black text-[var(--text-main)] tracking-tight uppercase">{title}</h2>
            </div>
        </div>
        {children}
    </div>
);
