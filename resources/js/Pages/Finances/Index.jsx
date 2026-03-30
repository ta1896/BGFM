import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, usePage } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import PageHeader from '@/Components/PageHeader';
import MetricCard from '@/Components/MetricCard';
import SectionCard from '@/Components/SectionCard';
import EmptyState from '@/Components/EmptyState';
import {
    ArrowsLeftRight,
    TrendUp,
    TrendDown,
    Calendar,
    Coin,
    Wallet,
    WarningCircle,
    ChartPie,
} from '@phosphor-icons/react';

const CONTEXT_LABELS = {
    match_income: 'Spieleinnahmen',
    transfer: 'Transfers',
    salary: 'Gehälter',
    sponsor: 'Sponsoren',
    stadium: 'Stadion',
    training: 'Training',
    admin_adjustment: 'Admin',
    other: 'Sonstige',
};

function SummaryRow({ item }) {
    const label = CONTEXT_LABELS[item.context_type] ?? item.context_type;
    const net = item.net;
    const maxBar = Math.max(item.income, item.expense);

    return (
        <div className="grid grid-cols-[8rem_1fr_1fr_6rem] gap-4 items-center border-b border-white/5 px-6 py-4 hover:bg-white/[0.02] transition-colors">
            <div className="text-[10px] font-black uppercase tracking-wider text-[var(--text-muted)]">{label}</div>
            <div className="flex items-center gap-2">
                <div className="h-1.5 rounded-full bg-emerald-500/20 flex-1 overflow-hidden">
                    <div className="h-full bg-emerald-500 rounded-full" style={{ width: maxBar > 0 ? `${(item.income / maxBar) * 100}%` : '0%' }} />
                </div>
                <span className="text-[10px] font-black text-emerald-400 w-20 text-right">
                    +{item.income.toLocaleString('de-DE', { maximumFractionDigits: 0 })}
                </span>
            </div>
            <div className="flex items-center gap-2">
                <div className="h-1.5 rounded-full bg-rose-500/20 flex-1 overflow-hidden">
                    <div className="h-full bg-rose-500 rounded-full" style={{ width: maxBar > 0 ? `${(item.expense / maxBar) * 100}%` : '0%' }} />
                </div>
                <span className="text-[10px] font-black text-rose-400 w-20 text-right">
                    -{item.expense.toLocaleString('de-DE', { maximumFractionDigits: 0 })}
                </span>
            </div>
            <div className={`text-right text-sm font-black italic ${net >= 0 ? 'text-emerald-400' : 'text-rose-400'}`}>
                {net >= 0 ? '+' : ''}{net.toLocaleString('de-DE', { maximumFractionDigits: 0 })}
            </div>
        </div>
    );
}

export default function Finances({ activeClub, transactions, summary = [] }) {
    usePage();

    if (!activeClub) {
        return (
            <AuthenticatedLayout>
                <EmptyState
                    icon={WarningCircle}
                    title="Kein Verein aktiv"
                    description="Es konnte kein aktiver Verein gefunden werden. Bitte waehle einen Verein aus der Liste oder erstelle einen neuen."
                    className="py-20"
                />
            </AuthenticatedLayout>
        );
    }

    return (
        <AuthenticatedLayout>
            <Head title="Finanzen" />

            <div className="max-w-[1400px] mx-auto space-y-8">
                <PageHeader eyebrow="Finanz-Management" title="Budget & Bilanz" />

                <StaggerGroup className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <MetricCard
                        title="Transferbudget"
                        value={parseFloat(activeClub.budget).toLocaleString('de-DE')}
                        unit="EUR"
                        icon={Wallet}
                        accentClass="text-emerald-400"
                        footer={<div className="mt-4 inline-flex items-center gap-2 rounded border border-white/10 bg-white/5 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-emerald-400">Live Balance</div>}
                    />
                    <MetricCard
                        title="Club Coins"
                        value={parseInt(activeClub.coins).toLocaleString('de-DE')}
                        unit="Coins"
                        icon={Coin}
                        accentClass="text-amber-400"
                        footer={<div className="mt-4 inline-flex items-center gap-2 rounded border border-white/10 bg-white/5 px-2 py-1 text-[10px] font-black uppercase tracking-widest text-amber-400">Live Balance</div>}
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

                {summary.length > 0 && (
                    <PageReveal delay={100}>
                        <SectionCard title="Einnahmen & Ausgaben nach Kategorie" icon={ChartPie} bodyClassName="overflow-hidden">
                            <div className="grid grid-cols-[8rem_1fr_1fr_6rem] gap-4 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                <div>Kategorie</div>
                                <div className="flex items-center gap-2 text-emerald-400">Einnahmen</div>
                                <div className="flex items-center gap-2 text-rose-400">Ausgaben</div>
                                <div className="text-right">Netto</div>
                            </div>
                            {summary.map((item) => <SummaryRow key={item.context_type} item={item} />)}
                            <div className="grid grid-cols-[8rem_1fr_1fr_6rem] gap-4 items-center border-t border-white/10 bg-[var(--bg-pillar)]/40 px-6 py-4">
                                <div className="text-[10px] font-black uppercase tracking-wider text-[var(--text-main)]">Gesamt</div>
                                <div className="text-right pr-0">
                                    <span className="text-[10px] font-black text-emerald-400">
                                        +{summary.reduce((s, i) => s + i.income, 0).toLocaleString('de-DE', { maximumFractionDigits: 0 })}
                                    </span>
                                </div>
                                <div className="text-right pr-0">
                                    <span className="text-[10px] font-black text-rose-400">
                                        -{summary.reduce((s, i) => s + i.expense, 0).toLocaleString('de-DE', { maximumFractionDigits: 0 })}
                                    </span>
                                </div>
                                <div className={`text-right text-sm font-black italic ${summary.reduce((s, i) => s + i.net, 0) >= 0 ? 'text-emerald-400' : 'text-rose-400'}`}>
                                    {summary.reduce((s, i) => s + i.net, 0) >= 0 ? '+' : ''}{summary.reduce((s, i) => s + i.net, 0).toLocaleString('de-DE', { maximumFractionDigits: 0 })}
                                </div>
                            </div>
                        </SectionCard>
                    </PageReveal>
                )}

                <PageReveal delay={140}>
                    <SectionCard title="Transaktionshistorie" icon={ArrowsLeftRight}>
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
                                {transactions.data.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-[var(--text-muted)] italic text-sm">
                                            Noch keine Finanztransaktionen fuer diesen Verein vorhanden.
                                        </td>
                                    </tr>
                                )}
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
                    </SectionCard>
                </PageReveal>
            </div>
        </AuthenticatedLayout>
    );
}
