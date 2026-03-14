import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    Layout,
    Plus,
    Calendar,
    Checks,
    Warning,
} from '@phosphor-icons/react';

const MatchCard = ({ match, club }) => {
    const isHome = match.home_club_id === club.id;
    const userLineup = match.lineups && match.lineups.length > 0 ? match.lineups[0] : null;

    return (
        <div className="sim-card group relative overflow-hidden flex flex-col h-full bg-[#0c1222]/80 backdrop-blur-xl border-[var(--border-muted)] hover:-translate-y-1 transition-transform">
            <div className="absolute inset-0 bg-gradient-to-br from-[var(--accent-glow)] to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />

            <div className="p-6 relative z-10 flex flex-col h-full">
                <div className="flex items-center justify-between mb-6">
                    <div className="px-2 py-1 rounded bg-[var(--bg-pillar)] border border-[var(--border-pillar)] text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                        {match.match_type || 'Liga'}
                    </div>
                    <div className="flex items-center gap-1.5 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                        <Calendar size={12} weight="bold" />
                        {match.kickoff_at_formatted}
                    </div>
                </div>

                <div className="flex items-center justify-between gap-6 mb-8">
                    <ClubBadge logoUrl={match.home_club.logo_url} shortName={match.home_club.short_name} active={isHome} />
                    <div className="text-xl font-black text-slate-700 italic">VS</div>
                    <ClubBadge logoUrl={match.away_club.logo_url} shortName={match.away_club.short_name} active={!isHome} />
                </div>

                <div className="mt-auto pt-6 border-t border-[var(--border-muted)] flex items-center justify-between">
                    <div className="flex flex-col gap-1">
                        <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">Aufstellung</span>
                        {userLineup ? (
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-emerald-400 uppercase">
                                <Checks size={14} weight="bold" /> GESETZT
                            </div>
                        ) : (
                            <div className="flex items-center gap-1.5 text-[10px] font-black text-amber-500 uppercase">
                                <Warning size={14} weight="bold" /> AUSSTEHEND
                            </div>
                        )}
                    </div>

                    <Link
                        href={route('lineups.match', match.id)}
                        className={`px-4 py-2 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all ${
                            userLineup
                                ? 'bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700'
                                : 'sim-btn-primary'
                        }`}
                    >
                        {userLineup ? 'BEARBEITEN' : 'ERSTELLEN'}
                    </Link>
                </div>
            </div>
        </div>
    );
};

export default function Index({ club, matches, templates }) {
    usePage();

    return (
        <AuthenticatedLayout>
            <Head title="Aufstellungen & Taktik" />

            <div className="max-w-[1400px] mx-auto space-y-12">
                <div className="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                    <div>
                        <div className="flex items-center gap-2 mb-2">
                            <span className="h-px w-8 bg-[var(--accent-primary)]" />
                            <span className="text-[10px] font-black uppercase tracking-[0.4em] text-[var(--accent-primary)]">Matchcenter // Strategie</span>
                        </div>
                        <h1 className="text-5xl lg:text-7xl font-black text-[var(--text-main)] tracking-tighter uppercase italic leading-none">
                            Aufstellungen <span className="text-[var(--text-muted)]">&</span> Taktik
                        </h1>
                    </div>

                    <Link href={route('lineups.create')} className="sim-btn-primary flex items-center gap-3 px-8 py-4 group">
                        <Plus size={20} weight="bold" />
                        <span className="font-black uppercase tracking-widest text-xs">Neue Vorlage erstellen</span>
                    </Link>
                </div>

                <section className="space-y-6">
                    <div className="flex items-center gap-4">
                        <div className="p-2 rounded-xl bg-[var(--accent-glow)] text-[var(--accent-primary)] border border-[var(--border-pillar)]">
                            <Calendar size={24} weight="duotone" />
                        </div>
                        <h2 className="text-2xl font-black text-[var(--text-main)] uppercase tracking-tighter italic">Anstehende Termine</h2>
                    </div>

                    {matches.length === 0 ? (
                        <div className="sim-card p-20 text-center border-dashed border-2 border-[var(--border-pillar)] bg-[var(--bg-pillar)]/20">
                            <p className="text-[var(--text-muted)] font-bold uppercase tracking-widest text-sm">Keine geplanten Spiele erfasst</p>
                        </div>
                    ) : (
                        <div className="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                            {matches.map((match) => (
                                <MatchCard key={match.id} match={match} club={club} />
                            ))}
                        </div>
                    )}
                </section>

                <section className="space-y-6 pt-12 border-t border-[var(--border-muted)]">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div className="p-2 rounded-xl bg-[var(--bg-content)] text-[var(--text-muted)] border border-[var(--border-pillar)]">
                                <Layout size={24} weight="duotone" />
                            </div>
                            <h2 className="text-2xl font-black text-[var(--text-main)] uppercase tracking-tighter italic">Gespeicherte Vorlagen</h2>
                        </div>
                        <span className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest">{templates.length} Profile</span>
                    </div>

                    {templates.length === 0 ? (
                        <div className="text-slate-600 italic text-sm">Keine taktischen Vorlagen hinterlegt.</div>
                    ) : (
                        <div className="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            {templates.map((template) => (
                                <div key={template.id} className="sim-card p-6 bg-[var(--bg-pillar)]/50 border-[var(--border-pillar)] group hover:-translate-y-0.5 transition-transform">
                                    <div className="flex justify-between items-start mb-4">
                                        <h3 className="font-black text-[var(--text-main)] uppercase tracking-tight group-hover:text-[var(--accent-primary)] transition-colors line-clamp-1">
                                            {template.name}
                                        </h3>
                                        <div className="px-2 py-0.5 rounded bg-[var(--sim-shell-bg)] border border-[var(--border-pillar)] text-[10px] font-black text-[var(--accent-primary)] uppercase">
                                            {template.formation}
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2 text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-6">
                                        {template.players_count || template.players?.length} Spieler zugewiesen
                                    </div>

                                    <div className="flex items-center justify-between pt-4 border-t border-[var(--border-pillar)]">
                                        <Link href={route('lineups.edit', template.id)} className="text-[10px] font-black text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors">
                                            BEARBEITEN
                                        </Link>
                                        <form method="POST" action={route('lineups.destroy', template.id)} onSubmit={(event) => !confirm('Vorlage wirklich loeschen?') && event.preventDefault()}>
                                            <input type="hidden" name="_method" value="DELETE" />
                                            <button type="submit" className="text-[10px] font-black text-rose-500/70 hover:text-rose-400 transition-colors">
                                                LOESCHEN
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </AuthenticatedLayout>
    );
}

function ClubBadge({ logoUrl, shortName, active }) {
    return (
        <div className="flex flex-col items-center gap-3 flex-1">
            <div className="w-14 h-14 rounded-2xl bg-[var(--bg-pillar)] border border-[var(--border-pillar)] p-2.5 shadow-xl group-hover:border-[var(--accent-primary)]/30 transition-colors relative">
                <img loading="lazy" src={logoUrl} className="w-full h-full object-contain" alt={shortName} />
                {active && <div className="absolute -top-1 -right-1 w-3.5 h-3.5 bg-[var(--accent-primary)] rounded-full border-2 border-[#0c1222] shadow-[0_0_10px_rgba(217,177,92,0.5)]" />}
            </div>
            <span className="text-xs font-black text-[var(--text-main)] uppercase tracking-tighter text-center line-clamp-1">{shortName}</span>
        </div>
    );
}
