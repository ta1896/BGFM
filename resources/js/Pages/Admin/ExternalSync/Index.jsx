import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { 
    Trash, Lightning,
    IdentificationBadge, Info,
    Users, Broadcast,
    TrendUp, X,
    CheckSquare,
} from '@phosphor-icons/react';
import SyncJournal from '@/Components/SyncJournal';

const StatCard = ({ title, value, icon: Icon, color = 'cyan', subtext, onClick }) => {
    const Component = onClick ? 'button' : 'div';
    return (
        <Component 
            onClick={onClick}
            className={`w-full text-left sim-card p-5 border-l-4 border-l-${color}-500/50 flex items-center justify-between group hover:border-l-${color}-500 ${onClick ? 'cursor-pointer hover:bg-white/5 active:scale-[0.98]' : ''} transition-all`}
        >
            <div>
                <p className="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest mb-1">{title}</p>
                <p className="text-2xl font-black text-white leading-none tracking-tighter tabular-nums">{value}</p>
                {subtext && <p className="text-[10px] text-[var(--text-muted)] mt-2 font-bold uppercase tracking-wider">{subtext}</p>}
                {onClick && <p className="text-[9px] text-[var(--text-muted)] mt-1 font-bold uppercase tracking-wider opacity-50 group-hover:opacity-100 group-hover:text-white transition-opacity">Klicken für Ansicht →</p>}
            </div>
            <div className={`p-3 rounded-2xl bg-${color}-500/5 border border-${color}-500/10 text-${color}-500/30 group-hover:text-${color}-500 group-hover:bg-${color}-500/10 transition-all`}>
                <Icon size={24} weight="duotone" />
            </div>
        </Component>
    );
};

const MODES = [
    {
        key: 'both',
        label: 'Beides',
        sublabel: 'Sofascore + Transfermarkt',
        icon: Lightning,
        color: 'cyan',
        glow: 'rgba(34,211,238,0.3)',
    },
    {
        key: 'sofascore',
        label: 'Nur Sofascore',
        sublabel: 'Attribute & Biografie',
        icon: Broadcast,
        color: 'indigo',
        glow: 'rgba(99,102,241,0.3)',
    },
    {
        key: 'transfermarkt',
        label: 'Nur Transfermarkt',
        sublabel: 'Transfer-Historie',
        icon: IdentificationBadge,
        color: 'amber',
        glow: 'rgba(245,158,11,0.3)',
    },
];

export default function Index({ stats, latestLogs, missingPlayers = {} }) {
    const { post, delete: destroy, processing } = useForm();
    const [selectedMode, setSelectedMode] = useState('both');
    const [showMissing, setShowMissing] = useState(null); // 'sofascore' | 'transfermarkt' | null

    const startSync = () => {
        const modeLabel = MODES.find(m => m.key === selectedMode)?.label ?? selectedMode;
        if (confirm(`Möchtest du jetzt den Sync starten?\nModus: ${modeLabel}`)) {
            post(route('admin.external-sync.start', { mode: selectedMode }));
        }
    };

    const clearLogs = () => {
        if (confirm('Möchtest du das gesamte Synchronisations-Journal leeren?')) {
            destroy(route('admin.external-sync.clear-logs'));
        }
    };

    const activeModeConfig = MODES.find(m => m.key === selectedMode);
    
    // Fallback falls empty
    const currentMissingList = showMissing ? (missingPlayers[showMissing] || []) : [];

    return (
        <AdminLayout>
            <Head title="Externe Daten-Synchronisation" />

            {/* Modal für fehlende Spieler */}
            {showMissing && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
                    <div className="absolute inset-0 bg-black/80 backdrop-blur-sm" onClick={() => setShowMissing(null)}></div>
                    <div className="relative w-full max-w-2xl max-h-[80vh] bg-[#0a0f16] border border-white/10 rounded-2xl shadow-2xl flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        <div className={`p-6 border-b border-white/10 flex items-center justify-between bg-gradient-to-r ${showMissing === 'sofascore' ? 'from-indigo-500/10' : 'from-cyan-500/10'} to-transparent`}>
                            <div>
                                <h3 className="text-xl font-black text-white uppercase italic">Fehlende {showMissing === 'sofascore' ? 'Sofascore' : 'Transfermarkt'} Spieler</h3>
                                <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                                    {currentMissingList.length} Spieler nicht verknüpft
                                </p>
                            </div>
                            <button onClick={() => setShowMissing(null)} className="p-2 rounded-xl hover:bg-white/10 text-white/50 hover:text-white transition-colors">
                                <X size={24} weight="bold" />
                            </button>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto p-2">
                            {currentMissingList.length === 0 ? (
                                <div className="p-12 text-center text-slate-500 font-bold uppercase text-sm tracking-widest">
                                    Alle Spieler sind erfolgreich verknüpft! 🎉
                                </div>
                            ) : (
                                <div className="divide-y divide-white/5">
                                    {currentMissingList.map(player => (
                                        <div key={player.id} className="p-4 flex items-center justify-between hover:bg-white/5 transition-colors">
                                            <div>
                                                <p className="font-bold text-white leading-tight">{player.full_name}</p>
                                                <p className="text-xs text-slate-500 uppercase font-black tracking-widest mt-1">ID: {player.id}</p>
                                            </div>
                                            <div className="px-3 py-1.5 rounded-lg bg-white/5 border border-white/5 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                                                {player.club ? player.club.name : 'Vereinslos'}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}

            <div className="space-y-8 pb-32">
                {/* Header */}
                <div className="flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Externe Daten-Synchronisation</h2>
                        <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">Zentrales Management für Sofascore & Transfermarkt Sync</p>
                    </div>

                    <button 
                        onClick={startSync}
                        disabled={processing}
                        style={{ boxShadow: `0 4px 20px ${activeModeConfig?.glow}` }}
                        className={`bg-${activeModeConfig?.color}-500 text-black px-6 py-3 rounded-2xl font-black uppercase text-xs tracking-widest flex items-center gap-2 hover:scale-[1.03] active:scale-[0.97] transition-all disabled:opacity-50`}
                    >
                        <Lightning size={18} weight="fill" />
                        {processing ? 'Wird gestartet...' : `${activeModeConfig?.label} Sync starten`}
                    </button>
                </div>

                {/* Stats */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatCard 
                        title="Spieler Gesamt" 
                        value={stats.total} 
                        icon={Users} 
                        color="slate"
                        subtext="In der Datenbank registriert"
                    />
                    <StatCard 
                        title="Sofascore Abdeckung" 
                        value={stats.with_sofascore} 
                        icon={Broadcast} 
                        color="indigo" 
                        subtext={`${Math.round((stats.with_sofascore / stats.total) * 100) || 0}% der Spieler verknüpft`}
                        onClick={() => setShowMissing('sofascore')}
                    />
                    <StatCard 
                        title="Transfermarkt Abdeckung" 
                        value={stats.with_transfermarkt} 
                        icon={IdentificationBadge} 
                        color="cyan" 
                        subtext={`${Math.round((stats.with_transfermarkt / stats.total) * 100) || 0}% der Spieler verknüpft`}
                        onClick={() => setShowMissing('transfermarkt')}
                    />
                </div>

                {/* Main Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div className="lg:col-span-8 space-y-8">

                        {/* Mode Selector */}
                        <div className="sim-card p-6">
                            <p className="text-[10px] font-black uppercase text-[var(--text-muted)] tracking-widest mb-5">Sync-Modus auswählen</p>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                {MODES.map(mode => {
                                    const Icon = mode.icon;
                                    const isActive = selectedMode === mode.key;
                                    return (
                                        <button
                                            key={mode.key}
                                            onClick={() => setSelectedMode(mode.key)}
                                            className={`relative flex flex-col items-start gap-3 p-5 rounded-2xl border-2 transition-all text-left ${
                                                isActive
                                                    ? `border-${mode.color}-500 bg-${mode.color}-500/10`
                                                    : 'border-[var(--border-muted)] bg-transparent hover:border-white/20 hover:bg-white/5'
                                            }`}
                                        >
                                            <div className={`p-2 rounded-xl ${isActive ? `bg-${mode.color}-500/20` : 'bg-white/5'}`}>
                                                <Icon size={20} className={isActive ? `text-${mode.color}-400` : 'text-slate-500'} weight={isActive ? 'fill' : 'bold'} />
                                            </div>
                                            <div>
                                                <p className={`text-sm font-black uppercase tracking-tight ${isActive ? 'text-white' : 'text-slate-400'}`}>{mode.label}</p>
                                                <p className={`text-[10px] font-bold uppercase tracking-wider mt-0.5 ${isActive ? `text-${mode.color}-400/70` : 'text-slate-600'}`}>{mode.sublabel}</p>
                                            </div>
                                            {isActive && (
                                                <div className={`absolute top-3 right-3 text-${mode.color}-400`}>
                                                    <CheckSquare size={16} weight="fill" />
                                                </div>
                                            )}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Info card – dynamically shows relevant sections */}
                        <div className="sim-card p-8 bg-gradient-to-br from-indigo-500/5 to-transparent relative overflow-hidden group">
                           <div className="absolute -top-10 -right-10 text-[120px] font-black text-white/[0.02] -rotate-12 pointer-events-none">
                               SYNC
                           </div>
                           <div className="relative z-10 flex flex-col md:flex-row gap-8 items-center">
                               <div className="flex-1 space-y-4">
                                   <div className="flex items-center gap-3">
                                       <div className="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20">
                                           <TrendUp size={20} className="text-indigo-400" />
                                       </div>
                                       <h3 className="text-lg font-black text-white uppercase italic">Gewählter Modus: {activeModeConfig?.label}</h3>
                                   </div>
                                   <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                                       {(selectedMode === 'both' || selectedMode === 'sofascore') && (
                                           <div className="sim-card p-4 border-t-2 border-t-indigo-500/30">
                                               <div className="flex items-center gap-2 mb-2">
                                                   <Broadcast size={14} className="text-indigo-400" />
                                                   <p className="text-[10px] font-black text-white uppercase">Sofascore</p>
                                               </div>
                                               <ul className="text-[9px] text-slate-500 space-y-1 font-bold uppercase">
                                                   <li>• Attribute (ATT / TEC / TAC / DEF / CRE)</li>
                                                   <li>• Biografie (Alter, Größe, Fuß)</li>
                                                   <li>• Torhüter-Mapping aktiv</li>
                                               </ul>
                                           </div>
                                       )}
                                       {(selectedMode === 'both' || selectedMode === 'transfermarkt') && (
                                           <div className="sim-card p-4 border-t-2 border-t-cyan-500/30">
                                               <div className="flex items-center gap-2 mb-2">
                                                   <IdentificationBadge size={14} className="text-cyan-400" />
                                                   <p className="text-[10px] font-black text-white uppercase">Transfermarkt</p>
                                               </div>
                                               <ul className="text-[9px] text-slate-500 space-y-1 font-bold uppercase">
                                                   <li>• Komplette Karriere-Historie</li>
                                                   <li>• Club-Matching via TM-ID</li>
                                                   <li>• Historische Marktwert-Trends</li>
                                               </ul>
                                           </div>
                                       )}
                                   </div>
                               </div>
                               
                               <div className="shrink-0 w-full md:w-auto">
                                    <div className="p-6 rounded-3xl bg-black/20 border border-[var(--border-muted)] border-dashed text-center space-y-4">
                                        <div className="mx-auto w-12 h-12 rounded-full bg-amber-500/10 flex items-center justify-center text-amber-500">
                                            <Info size={24} weight="bold" />
                                        </div>
                                        <p className="text-[10px] text-amber-500/70 font-black uppercase tracking-widest max-w-[180px]">
                                            Um Rate-Limits zu vermeiden, wird zwischen den Spielern eine Pause von 1 Sekunde eingelegt.
                                        </p>
                                    </div>
                               </div>
                           </div>
                        </div>
                    </div>

                    {/* Journal + Clear */}
                    <div className="lg:col-span-4 space-y-4 h-full">
                        <SyncJournal logs={latestLogs} />
                        <button
                            onClick={clearLogs}
                            disabled={processing}
                            className="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-red-500/20 text-red-500/60 hover:text-red-400 hover:border-red-500/40 hover:bg-red-500/5 text-[10px] font-black uppercase tracking-widest transition-all"
                        >
                            <Trash size={14} />
                            Journal leeren
                        </button>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
