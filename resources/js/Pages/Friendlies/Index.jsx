import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    SoccerBall, PaperPlaneTilt, Check, X,
    Calendar, Clock, ChatCircle, Users,
    ArrowRight, CheckCircle, XCircle, Hourglass
} from '@phosphor-icons/react';

const StatusIcon = ({ status }) => {
    if (status === 'accepted') return <CheckCircle size={16} weight="fill" className="text-emerald-400" />;
    if (status === 'rejected') return <XCircle size={16} weight="fill" className="text-rose-400" />;
    return <Hourglass size={16} weight="fill" className="text-amber-400" />;
};

export default function Index({ clubs, activeClub, opponents, outgoingRequests, incomingRequests, friendlyMatches }) {
    const [tab, setTab] = useState('matches');

    const { data, setData, post, processing, errors, reset } = useForm({
        club_id: activeClub?.id || '',
        opponent_club_id: '',
        kickoff_at: '',
        message: '',
    });

    const handleRequest = (e) => {
        e.preventDefault();
        post(route('friendlies.store'), {
            onSuccess: () => reset('opponent_club_id', 'kickoff_at', 'message'),
        });
    };

    const accept = (id) => router.post(route('friendlies.accept', id));
    const reject = (id) => router.post(route('friendlies.reject', id));

    const tabs = [
        { key: 'matches', label: 'Testspiele', count: friendlyMatches.length },
        { key: 'incoming', label: 'Eingehend', count: incomingRequests.length },
        { key: 'outgoing', label: 'Ausgehend', count: outgoingRequests.length },
        { key: 'request', label: '+ Anfrage stellen', count: null },
    ];

    return (
        <AuthenticatedLayout>
            <Head title="Freundschaftsspiele" />

            <div className="max-w-[1100px] mx-auto space-y-8">
                {/* Header */}
                <div>
                    <p className="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-1">Wettbewerb</p>
                    <h1 className="text-4xl font-black text-white uppercase tracking-tighter italic">Freundschaftsspiele</h1>
                    {activeClub && (
                        <p className="text-sm font-bold text-slate-400 mt-1 italic">{activeClub.name}</p>
                    )}
                </div>

                {/* Tab Navigation */}
                <nav className="flex items-center gap-1 p-1 rounded-2xl bg-slate-900/60 border border-slate-800 w-fit flex-wrap">
                    {tabs.map(t => (
                        <button
                            key={t.key}
                            onClick={() => setTab(t.key)}
                            className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all ${
                                tab === t.key
                                    ? 'bg-cyan-500 text-black shadow-lg shadow-cyan-500/20'
                                    : 'text-slate-500 hover:text-slate-300'
                            }`}
                        >
                            {t.label}
                            {t.count !== null && t.count > 0 && (
                                <span className={`px-1.5 py-0.5 rounded-full text-[8px] font-black ${tab === t.key ? 'bg-black/20 text-black' : 'bg-slate-800 text-slate-400'}`}>
                                    {t.count}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>

                <AnimatePresence mode="wait">
                    {/* Scheduled/Played Matches */}
                    {tab === 'matches' && (
                        <motion.div key="matches" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} className="space-y-4">
                            {friendlyMatches.length === 0 ? (
                                <div className="sim-card p-20 text-center border-dashed border-slate-800">
                                    <SoccerBall size={48} weight="thin" className="text-slate-700 mx-auto mb-6" />
                                    <p className="text-slate-500 italic font-bold uppercase tracking-widest text-sm">Noch keine Testspiele geplant.</p>
                                </div>
                            ) : friendlyMatches.map(m => (
                                <div key={m.id} className="sim-card p-5 flex items-center gap-6 hover:border-cyan-500/20 transition-all">
                                    <div className="flex-1 flex items-center gap-6">
                                        <div className={`flex items-center gap-3 flex-1 justify-end ${m.is_home ? 'opacity-100' : 'opacity-50'}`}>
                                            <span className="text-sm font-black text-white uppercase truncate">{m.home_club?.short_name}</span>
                                            <img src={m.home_club?.logo_url} className="w-9 h-9 object-contain" />
                                        </div>
                                        <div className="flex flex-col items-center gap-1">
                                            {m.status === 'played'
                                                ? <span className="text-xl font-black text-white italic">{m.home_score} : {m.away_score}</span>
                                                : <span className="text-sm font-black text-slate-400 italic">{m.kickoff_formatted}</span>
                                            }
                                            <span className="text-[9px] font-black text-slate-600 uppercase tracking-widest">Testspiel</span>
                                        </div>
                                        <div className={`flex items-center gap-3 flex-1 ${!m.is_home ? 'opacity-100' : 'opacity-50'}`}>
                                            <img src={m.away_club?.logo_url} className="w-9 h-9 object-contain" />
                                            <span className="text-sm font-black text-white uppercase truncate">{m.away_club?.short_name}</span>
                                        </div>
                                    </div>
                                    <a href={route('matches.show', m.id)} className="ml-auto shrink-0">
                                        <ArrowRight size={18} className="text-slate-700 hover:text-cyan-400 transition-colors" />
                                    </a>
                                </div>
                            ))}
                        </motion.div>
                    )}

                    {/* Incoming Requests */}
                    {tab === 'incoming' && (
                        <motion.div key="incoming" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} className="space-y-4">
                            {incomingRequests.length === 0 ? (
                                <div className="sim-card p-20 text-center border-dashed border-slate-800">
                                    <p className="text-slate-500 italic font-bold uppercase tracking-widest text-sm">Keine eingehenden Anfragen.</p>
                                </div>
                            ) : incomingRequests.map(req => (
                                <div key={req.id} className="sim-card p-5 flex items-center gap-6">
                                    <StatusIcon status={req.status} />
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-black text-white italic">Von <span className="text-cyan-400">{req.challenger_club?.name}</span></p>
                                        {req.message && <p className="text-xs text-slate-500 italic mt-1 truncate">"{req.message}"</p>}
                                        <p className="text-[9px] font-black text-slate-600 uppercase tracking-widest mt-1">{req.accepted_match?.kickoff_at || '—'}</p>
                                    </div>
                                    {req.status === 'pending' && (
                                        <div className="flex items-center gap-2 shrink-0">
                                            <button onClick={() => accept(req.id)}
                                                className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-[9px] font-black uppercase tracking-widest hover:bg-emerald-500/30 transition-all">
                                                <Check size={12} weight="bold" /> Annehmen
                                            </button>
                                            <button onClick={() => reject(req.id)}
                                                className="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-[9px] font-black uppercase tracking-widest hover:bg-rose-500/20 transition-all">
                                                <X size={12} weight="bold" /> Ablehnen
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ))}
                        </motion.div>
                    )}

                    {/* Outgoing Requests */}
                    {tab === 'outgoing' && (
                        <motion.div key="outgoing" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} className="space-y-4">
                            {outgoingRequests.length === 0 ? (
                                <div className="sim-card p-20 text-center border-dashed border-slate-800">
                                    <p className="text-slate-500 italic font-bold uppercase tracking-widest text-sm">Keine ausgehenden Anfragen.</p>
                                </div>
                            ) : outgoingRequests.map(req => (
                                <div key={req.id} className="sim-card p-5 flex items-center gap-6">
                                    <StatusIcon status={req.status} />
                                    <div className="flex-1 min-w-0">
                                        <p className="text-sm font-black text-white italic">An <span className="text-indigo-400">{req.challenged_club?.name}</span></p>
                                        {req.message && <p className="text-xs text-slate-500 italic mt-1 truncate">"{req.message}"</p>}
                                    </div>
                                    <span className={`px-3 py-1 rounded-full border text-[9px] font-black uppercase tracking-widest shrink-0 ${
                                        req.status === 'accepted' ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' :
                                        req.status === 'rejected' ? 'bg-rose-500/10 border-rose-500/20 text-rose-400' :
                                        'bg-amber-500/10 border-amber-500/20 text-amber-400'
                                    }`}>
                                        {req.status === 'accepted' ? 'Angenommen' : req.status === 'rejected' ? 'Abgelehnt' : 'Ausstehend'}
                                    </span>
                                </div>
                            ))}
                        </motion.div>
                    )}

                    {/* New Request Form */}
                    {tab === 'request' && (
                        <motion.div key="request" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }}>
                            <div className="sim-card p-10 max-w-2xl bg-[#0c1222]/80 border-slate-800/50">
                                <div className="flex items-center gap-4 mb-8 border-b border-slate-800 pb-6">
                                    <PaperPlaneTilt size={28} weight="duotone" className="text-cyan-400" />
                                    <div>
                                        <h3 className="text-xl font-black text-white uppercase tracking-tighter italic">Freundschaftsspiel anfragen</h3>
                                        <p className="text-xs text-slate-500 uppercase tracking-widest font-bold mt-1">Fordere einen anderen Verein heraus</p>
                                    </div>
                                </div>

                                <form onSubmit={handleRequest} className="space-y-6">
                                    <div>
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Gegner</label>
                                        <select
                                            value={data.opponent_club_id}
                                            onChange={e => setData('opponent_club_id', e.target.value)}
                                            className="sim-select w-full"
                                        >
                                            <option value="">Verein wählen...</option>
                                            {opponents.map(club => (
                                                <option key={club.id} value={club.id}>{club.name}</option>
                                            ))}
                                        </select>
                                        {errors.opponent_club_id && <p className="text-rose-400 text-xs mt-2">{errors.opponent_club_id}</p>}
                                    </div>

                                    <div>
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Anstosszeit</label>
                                        <input
                                            type="datetime-local"
                                            value={data.kickoff_at}
                                            onChange={e => setData('kickoff_at', e.target.value)}
                                            className="sim-input w-full"
                                        />
                                        {errors.kickoff_at && <p className="text-rose-400 text-xs mt-2">{errors.kickoff_at}</p>}
                                    </div>

                                    <div>
                                        <label className="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-3">Nachricht (optional)</label>
                                        <textarea
                                            value={data.message}
                                            onChange={e => setData('message', e.target.value)}
                                            rows={3}
                                            placeholder="Optionale Nachricht an den Gegner..."
                                            className="sim-input w-full resize-none"
                                        />
                                    </div>

                                    <div className="pt-4 border-t border-slate-800">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            className="flex items-center gap-3 px-8 py-4 rounded-2xl bg-gradient-to-r from-cyan-500 to-indigo-600 text-black font-black uppercase tracking-widest text-xs shadow-lg shadow-cyan-500/20 hover:shadow-cyan-500/30 transition-all disabled:opacity-50"
                                        >
                                            <PaperPlaneTilt size={18} weight="bold" />
                                            Anfrage senden
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </AuthenticatedLayout>
    );
}
