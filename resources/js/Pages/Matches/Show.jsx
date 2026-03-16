import React, { useCallback, useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Lightning, Play } from '@phosphor-icons/react';
import {
    isKeyEvent,
    KeyEventsStrip,
    LineupPitch,
    MatchPulse,
    ModulePanels,
    MatchTabs,
    PlayersTab,
    PreviewTab,
    ScoreHero,
    StatsTab,
    TickerTab,
} from '@/Pages/Matches/components/MatchCenterSections';

export default function Show({
    id,
    status,
    live_minute,
    home_score,
    away_score,
    home_club,
    away_club,
    competition,
    matchday,
    kickoff_formatted,
    weather,
    type,
    actions,
    final_stats,
    team_states,
    player_states,
    lineups,
    planned_substitutions,
    comparison,
    can_simulate,
    module_panels,
}) {
    const [tab, setTab] = useState(status === 'scheduled' ? 'preview' : status === 'live' ? 'ticker' : 'stats');
    const [liveState, setLiveState] = useState({
        status,
        live_minute,
        home_score,
        away_score,
        actions,
        team_states,
        player_states,
        planned_substitutions,
    });
    const fetchState = useCallback(async () => {
        try {
            const res = await fetch(route('matches.live.state', id), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) {
                return;
            }

            const data = await res.json();
            setLiveState((prev) => ({
                ...prev,
                status: data.status,
                live_minute: data.live_minute,
                home_score: data.home_score,
                away_score: data.away_score,
                actions: data.actions || prev.actions,
                team_states: data.team_states || prev.team_states,
                player_states: data.player_states || prev.player_states,
                planned_substitutions: data.planned_substitutions || prev.planned_substitutions,
            }));
        } catch {}
    }, [id]);

    useEffect(() => {
        if (!window.Echo) {
            return undefined;
        }

        const channelName = `match.${id}`;
        const channel = window.Echo.channel(channelName);

        channel.listen('.match.state.updated', () => {
            fetchState();
        });

        return () => {
            window.Echo.leaveChannel(channelName);
        };
    }, [fetchState, id]);

    const simulate = () => router.post(route('matches.simulate', id));
    const startLive = () => router.post(route('matches.live-start', id));

    const homeState = liveState.team_states?.[String(home_club?.id)];
    const awayState = liveState.team_states?.[String(away_club?.id)];
    const homeLineup = lineups?.[String(home_club?.id)];
    const awayLineup = lineups?.[String(away_club?.id)];
    const keyActions = (liveState.actions || []).filter((action) => isKeyEvent(action.action_type));
    const allActions = liveState.actions || [];

    const tabs = [
        { key: 'preview', label: 'Vorschau' },
        { key: 'ticker', label: 'Ticker', count: allActions.length },
        { key: 'lineup', label: 'Aufstellung' },
        { key: 'stats', label: 'Statistiken' },
        ...(status !== 'scheduled' ? [{ key: 'players', label: 'Spieler' }] : []),
    ];

    return (
        <AuthenticatedLayout>
            <Head title={`${home_club?.short_name} vs ${away_club?.short_name}`} />

            <div className="mx-auto max-w-[1300px] space-y-8">
                <Link href={route('league.matches')} className="flex w-fit items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] transition-colors hover:text-amber-500">
                    <ArrowLeft size={14} weight="bold" />
                    Spielplan
                </Link>

                <ScoreHero
                    home_club={home_club}
                    away_club={away_club}
                    home_score={liveState.home_score}
                    away_score={liveState.away_score}
                    status={liveState.status}
                    live_minute={liveState.live_minute}
                    kickoff_formatted={kickoff_formatted}
                    competition={competition}
                    matchday={matchday}
                    weather={weather}
                    type={type}
                />

                {can_simulate && liveState.status !== 'played' && (
                    <div className="flex items-center gap-3">
                        <button onClick={simulate} className="flex items-center gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/20 px-6 py-3 font-mono text-[10px] font-black uppercase tracking-widest text-amber-500 transition-all hover:bg-amber-500/30">
                            <Lightning size={16} weight="fill" /> Simulieren
                        </button>
                        {liveState.status === 'scheduled' && (
                            <button onClick={startLive} className="flex items-center gap-2 rounded-2xl border border-rose-500/30 bg-rose-500/20 px-6 py-3 text-[10px] font-black uppercase tracking-widest text-rose-300 transition-all hover:bg-rose-500/30">
                                <Play size={16} weight="fill" /> Live-Ticker starten
                            </button>
                        )}
                    </div>
                )}

                <KeyEventsStrip actions={keyActions} />

                {(liveState.status === 'live' || liveState.status === 'played') && (
                    <MatchPulse
                        homeClub={home_club}
                        awayClub={away_club}
                        homeState={homeState}
                        awayState={awayState}
                        livePlayerStates={liveState.player_states}
                    />
                )}

                {module_panels?.length > 0 && (
                    <ModulePanels panels={module_panels} />
                )}

                <MatchTabs entries={tabs} activeTab={tab} onChange={setTab} />

                <div>
                    {tab === 'preview' && <PreviewTab comparison={comparison} />}

                    {tab === 'ticker' && <TickerTab actions={allActions} homeClubId={home_club?.id} />}

                    {tab === 'lineup' && (
                        <div className="sim-card p-6">
                            <LineupPitch
                                homeClub={home_club}
                                awayClub={away_club}
                                homeLineup={homeLineup}
                                awayLineup={awayLineup}
                                livePlayerStates={liveState.player_states}
                            />
                        </div>
                    )}

                    {tab === 'stats' && <StatsTab homeState={homeState} awayState={awayState} />}

                    {tab === 'players' && <PlayersTab clubs={[home_club, away_club]} finalStats={final_stats} />}
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: '.custom-scrollbar::-webkit-scrollbar{width:4px}.custom-scrollbar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:4px}' }} />
        </AuthenticatedLayout>
    );
}
