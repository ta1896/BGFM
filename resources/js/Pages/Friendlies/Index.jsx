import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { PageReveal } from '@/Components/PageReveal';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import {
    SoccerBall,
    PaperPlaneTilt,
    Check,
    X,
    ArrowRight,
    CheckCircle,
    XCircle,
    Hourglass,
} from '@phosphor-icons/react';

const StatusIcon = ({ status }) => {
    if (status === 'accepted') return <CheckCircle size={16} weight="fill" className="text-emerald-400" />;
    if (status === 'rejected') return <XCircle size={16} weight="fill" className="text-rose-400" />;
    return <Hourglass size={16} weight="fill" className="text-amber-400" />;
};

export default function Index({ activeClub, opponents, outgoingRequests, incomingRequests, friendlyMatches }) {
    const [tab, setTab] = useState('matches');

    const { data, setData, post, processing, errors, reset } = useForm({
        club_id: activeClub?.id || '',
        opponent_club_id: '',
        kickoff_at: '',
        message: '',
    });

    const handleRequest = (event) => {
        event.preventDefault();
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
                <PageHeader
                    eyebrow="Wettbewerb"
                    title="Freundschaftsspiele"
                    actions={activeClub ? <p className="mt-1 text-sm font-bold italic text-[var(--text-muted)]">{activeClub.name}</p> : null}
                />

                <nav className="flex w-fit flex-wrap items-center gap-1 rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/60 p-1">
                    {tabs.map((tabEntry) => (
                        <button
                            key={tabEntry.key}
                            onClick={() => setTab(tabEntry.key)}
                            className={`flex items-center gap-2 rounded-xl px-5 py-2.5 text-[10px] font-black uppercase tracking-widest transition-all ${
                                tab === tabEntry.key ? 'bg-cyan-500 text-black shadow-lg shadow-cyan-500/20' : 'text-[var(--text-muted)] hover:text-slate-300'
                            }`}
                        >
                            {tabEntry.label}
                            {tabEntry.count !== null && tabEntry.count > 0 && (
                                <span className={`rounded-full px-1.5 py-0.5 text-[8px] font-black ${tab === tabEntry.key ? 'bg-black/20 text-black' : 'bg-[var(--bg-content)] text-[var(--text-muted)]'}`}>
                                    {tabEntry.count}
                                </span>
                            )}
                        </button>
                    ))}
                </nav>

                {tab === 'matches' && (
                    <PageReveal className="space-y-4">
                        {friendlyMatches.length === 0 ? (
                            <div className="sim-card border-[var(--border-pillar)] border-dashed">
                                <EmptyState
                                    icon={SoccerBall}
                                    title="Keine Testspiele"
                                    description="Noch keine Freundschaftsspiele fuer den aktiven Verein geplant."
                                    compact
                                />
                            </div>
                        ) : (
                            friendlyMatches.map((match) => (
                                <div key={match.id} className="sim-card flex items-center gap-6 p-5 transition-all hover:border-cyan-500/20">
                                    <div className="flex flex-1 items-center gap-6">
                                        <div className={`flex flex-1 items-center justify-end gap-3 ${match.is_home ? 'opacity-100' : 'opacity-50'}`}>
                                            <span className="truncate text-sm font-black uppercase text-white">{match.home_club?.short_name}</span>
                                            <img src={match.home_club?.logo_url} className="h-9 w-9 object-contain" />
                                        </div>
                                        <div className="flex flex-col items-center gap-1">
                                            {match.status === 'played' ? (
                                                <span className="text-xl font-black italic text-white">{match.home_score} : {match.away_score}</span>
                                            ) : (
                                                <span className="text-sm font-black italic text-[var(--text-muted)]">{match.kickoff_formatted}</span>
                                            )}
                                            <span className="text-[9px] font-black uppercase tracking-widest text-slate-600">Testspiel</span>
                                        </div>
                                        <div className={`flex flex-1 items-center gap-3 ${!match.is_home ? 'opacity-100' : 'opacity-50'}`}>
                                            <img src={match.away_club?.logo_url} className="h-9 w-9 object-contain" />
                                            <span className="truncate text-sm font-black uppercase text-white">{match.away_club?.short_name}</span>
                                        </div>
                                    </div>
                                    <a href={route('matches.show', match.id)} className="ml-auto shrink-0">
                                        <ArrowRight size={18} className="text-slate-700 transition-colors hover:text-cyan-400" />
                                    </a>
                                </div>
                            ))
                        )}
                    </PageReveal>
                )}

                {tab === 'incoming' && (
                    <PageReveal className="space-y-4">
                        {incomingRequests.length === 0 ? (
                            <div className="sim-card border-[var(--border-pillar)] border-dashed">
                                <EmptyState
                                    title="Keine eingehenden Anfragen"
                                    description="Aktuell wartet keine neue Herausforderung in deinem Postfach."
                                    compact
                                />
                            </div>
                        ) : (
                            incomingRequests.map((request) => (
                                <div key={request.id} className="sim-card flex items-center gap-6 p-5">
                                    <StatusIcon status={request.status} />
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-black italic text-white">
                                            Von <span className="text-cyan-400">{request.challenger_club?.name}</span>
                                        </p>
                                        {request.message && <p className="mt-1 truncate text-xs italic text-[var(--text-muted)]">"{request.message}"</p>}
                                        <p className="mt-1 text-[9px] font-black uppercase tracking-widest text-slate-600">{request.accepted_match?.kickoff_at || '-'}</p>
                                    </div>
                                    {request.status === 'pending' && (
                                        <div className="shrink-0 flex items-center gap-2">
                                            <button onClick={() => accept(request.id)} className="flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-500/20 px-4 py-2 text-[9px] font-black uppercase tracking-widest text-emerald-400 transition-all hover:bg-emerald-500/30">
                                                <Check size={12} weight="bold" /> Annehmen
                                            </button>
                                            <button onClick={() => reject(request.id)} className="flex items-center gap-1.5 rounded-xl border border-rose-500/20 bg-rose-500/10 px-4 py-2 text-[9px] font-black uppercase tracking-widest text-rose-400 transition-all hover:bg-rose-500/20">
                                                <X size={12} weight="bold" /> Ablehnen
                                            </button>
                                        </div>
                                    )}
                                </div>
                            ))
                        )}
                    </PageReveal>
                )}

                {tab === 'outgoing' && (
                    <PageReveal className="space-y-4">
                        {outgoingRequests.length === 0 ? (
                            <div className="sim-card border-[var(--border-pillar)] border-dashed">
                                <EmptyState
                                    title="Keine ausgehenden Anfragen"
                                    description="Du hast derzeit keine offenen Freundschaftsspiel-Anfragen verschickt."
                                    compact
                                />
                            </div>
                        ) : (
                            outgoingRequests.map((request) => (
                                <div key={request.id} className="sim-card flex items-center gap-6 p-5">
                                    <StatusIcon status={request.status} />
                                    <div className="min-w-0 flex-1">
                                        <p className="text-sm font-black italic text-white">
                                            An <span className="text-indigo-400">{request.challenged_club?.name}</span>
                                        </p>
                                        {request.message && <p className="mt-1 truncate text-xs italic text-[var(--text-muted)]">"{request.message}"</p>}
                                    </div>
                                    <span
                                        className={`shrink-0 rounded-full border px-3 py-1 text-[9px] font-black uppercase tracking-widest ${
                                            request.status === 'accepted'
                                                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                                                : request.status === 'rejected'
                                                  ? 'border-rose-500/20 bg-rose-500/10 text-rose-400'
                                                  : 'border-amber-500/20 bg-amber-500/10 text-amber-400'
                                        }`}
                                    >
                                        {request.status === 'accepted' ? 'Angenommen' : request.status === 'rejected' ? 'Abgelehnt' : 'Ausstehend'}
                                    </span>
                                </div>
                            ))
                        )}
                    </PageReveal>
                )}

                {tab === 'request' && (
                    <PageReveal>
                        <div className="sim-card max-w-2xl border-[var(--border-muted)] bg-[#0c1222]/80 p-10">
                            <div className="mb-8 flex items-center gap-4 border-b border-[var(--border-pillar)] pb-6">
                                <PaperPlaneTilt size={28} weight="duotone" className="text-cyan-400" />
                                <div>
                                    <h3 className="text-xl font-black uppercase italic tracking-tighter text-white">Freundschaftsspiel anfragen</h3>
                                    <p className="mt-1 text-xs font-bold uppercase tracking-widest text-[var(--text-muted)]">Fordere einen anderen Verein heraus</p>
                                </div>
                            </div>

                            <form onSubmit={handleRequest} className="space-y-6">
                                <div>
                                    <label className="mb-3 block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Gegner</label>
                                    <select value={data.opponent_club_id} onChange={(event) => setData('opponent_club_id', event.target.value)} className="sim-select w-full">
                                        <option value="">Verein waehlen...</option>
                                        {opponents.map((club) => (
                                            <option key={club.id} value={club.id}>
                                                {club.name}
                                            </option>
                                        ))}
                                    </select>
                                    {errors.opponent_club_id && <p className="mt-2 text-xs text-rose-400">{errors.opponent_club_id}</p>}
                                </div>

                                <div>
                                    <label className="mb-3 block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Anstosszeit</label>
                                    <input type="datetime-local" value={data.kickoff_at} onChange={(event) => setData('kickoff_at', event.target.value)} className="sim-input w-full" />
                                    {errors.kickoff_at && <p className="mt-2 text-xs text-rose-400">{errors.kickoff_at}</p>}
                                </div>

                                <div>
                                    <label className="mb-3 block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Nachricht (optional)</label>
                                    <textarea
                                        value={data.message}
                                        onChange={(event) => setData('message', event.target.value)}
                                        rows={3}
                                        placeholder="Optionale Nachricht an den Gegner..."
                                        className="sim-textarea w-full resize-none"
                                    />
                                </div>

                                <div className="border-t border-[var(--border-pillar)] pt-4">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="flex items-center gap-3 rounded-2xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-8 py-4 text-xs font-black uppercase tracking-widest text-black shadow-lg shadow-cyan-500/20 transition-all disabled:opacity-50"
                                    >
                                        <PaperPlaneTilt size={18} weight="bold" />
                                        Anfrage senden
                                    </button>
                                </div>
                            </form>
                        </div>
                    </PageReveal>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
