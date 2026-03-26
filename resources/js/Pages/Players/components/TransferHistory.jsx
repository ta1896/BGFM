import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import { 
    ArrowsLeftRight, 
    Calendar, 
    Coins, 
    TrendUp, 
    ArrowSquareOut,
    ArrowsClockwise
} from '@phosphor-icons/react';

export default function TransferHistory({ player, isOwner }) {
    const { post, processing } = useForm();
    
    const handleSync = () => {
        post(route('players.sync-history', player.id), {
            preserveScroll: true
        });
    };

    const formatValue = (value) => {
        if (!value) return '-';
        if (value >= 1000000) return (value / 1000000).toFixed(1) + ' Mio. €';
        if (value >= 1000) return (value / 1000).toFixed(0) + ' Tsd. €';
        return value + ' €';
    };

    if (!player.transfer_history || player.transfer_history.length === 0) {
        return (
            <div className="sim-card border border-white/5 bg-gradient-to-b from-white/[0.03] to-transparent rounded-2xl p-8 text-center">
                <ArrowsLeftRight className="w-12 h-12 text-[var(--text-muted)] mx-auto mb-4 opacity-20" />
                <h3 className="text-sm font-bold text-[var(--text-muted)] uppercase tracking-wider mb-2">Keine Transferhistorie</h3>
                <p className="text-xs text-[var(--text-muted)] mb-6">Wir konnten bisher keine Transferdaten für diesen Spieler finden.</p>
                
                {isOwner && player.tm_profile_url && (
                    <button
                        onClick={handleSync}
                        disabled={processing}
                        className="flex items-center gap-2 px-4 py-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 rounded-lg text-xs font-bold transition-all mx-auto"
                    >
                        <ArrowsClockwise className={`w-3.5 h-3.5 ${processing ? 'animate-spin' : ''}`} />
                        JETZT SYNCHRONISIEREN
                    </button>
                )}
            </div>
        );
    }

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <div className="flex items-center gap-4">
                    <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500/20 to-amber-600/5 flex items-center justify-center text-amber-500 border border-amber-500/20 shadow-[0_0_20px_rgba(245,158,11,0.1)]">
                        <ArrowsLeftRight className="w-6 h-6" weight="duotone" />
                    </div>
                    <div>
                        <h2 className="text-xl font-black uppercase tracking-tight italic text-white">Transferhistorie</h2>
                        <p className="text-[10px] text-[var(--text-muted)] uppercase font-black tracking-[0.2em] opacity-80">Karrierestationen & Ablösesummen</p>
                    </div>
                </div>

                {isOwner && player.tm_profile_url && (
                    <button
                        onClick={handleSync}
                        disabled={processing}
                        className="flex items-center gap-2.5 px-4 py-2 bg-white/5 hover:bg-white/10 border border-white/10 text-white rounded-xl text-[10px] font-black transition-all uppercase tracking-widest active:scale-[0.97] disabled:opacity-50"
                    >
                        <ArrowsClockwise className={`w-3.5 h-3.5 ${processing ? 'animate-spin' : ''}`} weight="bold" />
                        Sync History
                    </button>
                )}
            </div>

            <div className="sim-card overflow-hidden border border-white/5 bg-gradient-to-b from-white/[0.03] to-transparent">
                <div className="overflow-x-auto text-nowrap">
                    <table className="w-full text-left border-collapse">
                        <thead>
                            <tr className="bg-white/[0.02] border-b border-white/5">
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Saison</th>
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Abgebender Verein</th>
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] text-center">
                                    <ArrowsLeftRight className="w-3.5 h-3.5 mx-auto opacity-30" />
                                </th>
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)]">Aufnehmender Verein</th>
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] text-right">Marktwert</th>
                                <th className="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-cyan-400 text-right">Ablöse</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-white/[0.03]">
                            {player.transfer_history.map((entry) => (
                                <tr key={entry.id} className="hover:bg-white/[0.03] transition-all group relative">
                                    <td className="px-6 py-5">
                                        <div className="flex flex-col">
                                            <span className="text-xs font-black text-white tracking-widest italic">{entry.season}</span>
                                            <span className="text-[9px] text-[var(--text-muted)] font-bold uppercase tracking-tighter mt-0.5 opacity-60">{entry.transfer_date}</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-3.5">
                                            <div className="w-9 h-9 rounded-xl bg-black/40 p-1.5 flex items-center justify-center border border-white/5 group-hover:border-white/20 transition-all shadow-inner">
                                                {entry.left_club_logo ? (
                                                    <img src={entry.left_club_logo} alt={entry.left_club_name} className="w-full h-full object-contain filter drop-shadow-md" />
                                                ) : (
                                                    <ArrowsLeftRight className="w-4 h-4 text-[var(--text-muted)] opacity-20" />
                                                )}
                                            </div>
                                            <ClubLink
                                                id={entry.left_club_id}
                                                name={entry.left_club_name}
                                                className="text-xs font-black text-amber-500/80 hover:text-amber-500 transition-colors uppercase italic tracking-tight"
                                            />
                                        </div>
                                    </td>
                                    <td className="px-6 py-5 text-center">
                                       <div className="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center mx-auto group-hover:bg-amber-500/10 transition-colors">
                                            <ArrowsLeftRight size={12} className="text-white/10 group-hover:text-amber-500 transition-colors" />
                                       </div>
                                    </td>
                                    <td className="px-6 py-5">
                                        <div className="flex items-center gap-3.5">
                                            <div className="w-9 h-9 rounded-xl bg-black/40 p-1.5 flex items-center justify-center border border-white/5 group-hover:border-white/20 transition-all shadow-inner">
                                                {entry.joined_club_logo ? (
                                                    <img src={entry.joined_club_logo} alt={entry.joined_club_name} className="w-full h-full object-contain filter drop-shadow-md" />
                                                ) : (
                                                    <ArrowsLeftRight className="w-4 h-4 text-[var(--text-muted)] opacity-20" />
                                                )}
                                            </div>
                                            <ClubLink
                                                id={entry.joined_club_id}
                                                name={entry.joined_club_name}
                                                className="text-xs font-black text-amber-500/80 hover:text-amber-500 transition-colors uppercase italic tracking-tight"
                                            />
                                        </div>
                                    </td>
                                    <td className="px-6 py-5 text-right">
                                        <span className="text-[10px] font-black text-[var(--text-muted)] tabular-nums tracking-tighter uppercase">{formatValue(entry.market_value)}</span>
                                    </td>
                                    <td className="px-6 py-5 text-right">
                                        <span className={`text-base font-black tracking-tighter tabular-nums ${entry.is_loan ? 'text-cyan-400' : 'text-white'} drop-shadow-sm`}>
                                            {entry.fee}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    );
}
