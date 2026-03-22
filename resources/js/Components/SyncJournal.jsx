import React from 'react';
import { router } from '@inertiajs/react';
import { 
    ClockCounterClockwise, Trash, CheckCircle, 
    XCircle, DotsThreeOutline, Play,
    UserCircle
} from '@phosphor-icons/react';

function StatusBadge({ status }) {
    const configs = {
        pending: { color: 'bg-slate-500/10 text-slate-400 border-slate-500/20', label: 'Wartend', icon: DotsThreeOutline },
        running: { color: 'bg-amber-500/10 text-amber-500 border-amber-500/20 animate-pulse', label: 'Läuft', icon: Play },
        completed: { color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20', label: 'Fertig', icon: CheckCircle },
        failed: { color: 'bg-red-500/10 text-red-500 border-red-500/20', label: 'Fehler', icon: XCircle },
    };

    const config = configs[status] || configs.pending;
    const Icon = config.icon;

    return (
        <span className={`inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[8px] font-black uppercase tracking-widest ${config.color}`}>
            <Icon size={10} weight="fill" />
            {config.label}
        </span>
    );
}

export default function SyncJournal({ logs = [], clearRoute = 'admin.players.bulk-sync.clear' }) {
    const safeLogs = Array.isArray(logs) ? logs : [];

    const clearJournal = () => {
        if (confirm('Möchtest du das gesamte Sync-Journal wirklich leeren?')) {
            router.delete(route(clearRoute));
        }
    };

    return (
        <div className="sim-card overflow-hidden h-full flex flex-col">
            <div className="flex items-center justify-between p-4 border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/30">
                <div className="flex items-center gap-2">
                    <ClockCounterClockwise size={18} className="text-cyan-400" weight="bold" />
                    <h3 className="text-xs font-black text-white uppercase tracking-widest italic">Sync-Journal</h3>
                </div>
                {safeLogs.length > 0 && (
                    <button 
                        onClick={clearJournal}
                        className="text-[9px] font-black uppercase tracking-widest text-red-400 hover:text-red-300 transition-colors flex items-center gap-1"
                    >
                        <Trash size={12} weight="bold" />
                        Leeren
                    </button>
                )}
            </div>

            <div className="flex-1 overflow-y-auto max-h-[500px]">
                {safeLogs.length > 0 ? (
                    <div className="divide-y divide-slate-800/50">
                        {safeLogs.map((log) => (
                            <div key={log.id} className="p-4 hover:bg-slate-800/20 transition-colors group">
                                <div className="flex items-center justify-between mb-2">
                                    <div className="flex items-center gap-2">
                                        <span className="text-[10px] font-black text-white uppercase">Sofascore Sync</span>
                                        <span className="text-[9px] text-[var(--text-muted)] font-mono">{log.season}</span>
                                    </div>
                                    <StatusBadge status={log.status} />
                                </div>

                                {log.status === 'running' ? (
                                    <div className="space-y-2">
                                        <div className="flex items-center justify-between text-[9px] font-bold text-amber-500/80 uppercase tracking-tighter">
                                            <span className="flex items-center gap-1">
                                                <UserCircle size={10} weight="bold" />
                                                {log.details?.current_player || 'Wird vorbereitet...'}
                                            </span>
                                            <span>{log.details?.processed || 0} / {log.details?.total_players || '?'}</span>
                                        </div>
                                        <div className="h-1 w-full bg-slate-800 rounded-full overflow-hidden">
                                            <div 
                                                className="h-full bg-amber-500 transition-all duration-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"
                                                style={{ width: `${(log.details?.processed / log.details?.total_players) * 100}%` }}
                                            ></div>
                                        </div>
                                    </div>
                                ) : (
                                    <p className="text-[10px] text-[var(--text-muted)] italic leading-relaxed line-clamp-2 mb-1">
                                        {log.message || 'Kein Bericht verfügbar.'}
                                    </p>
                                )}

                                <div className="mt-2 flex items-center justify-between text-[8px] font-black uppercase tracking-[0.1em] text-slate-600">
                                    <div className="flex gap-2">
                                        <span className="text-emerald-500/70">Bio/Attr: {log.details?.success || 0}</span>
                                        <span className="text-blue-500/70">TM-Hist: {log.details?.success_transfers || 0}</span>
                                        <span className="text-red-500/70">ERR: {(log.details?.failed || 0) + (log.details?.failed_transfers || 0)}</span>
                                    </div>
                                    <span>
                                        {new Date(log.created_at).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' })} Uhr
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="p-10 text-center space-y-3">
                        <ClockCounterClockwise size={32} className="mx-auto text-slate-800 opacity-20" />
                        <p className="text-[10px] font-black uppercase tracking-widest text-slate-600">Keine Einträge vorhanden</p>
                    </div>
                )}
            </div>
        </div>
    );
}
