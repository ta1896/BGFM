import React, { useCallback, useEffect, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Lightning, Play } from '@phosphor-icons/react';
import {
    HighlightsTab,
    OverviewTab,
    LineupPitch,
    Live2DTab,
    LiveTableTab,
    MatchTabs,
    PlayersTab,
    ScoreHero,
    StatsTab,
    TickerTab,
} from '@/Pages/Matches/components/MatchCenterSections';
import LiveLineupEditorTab from '@/Pages/Matches/components/LiveLineupEditorTab';

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
    pre_match_report,
    can_simulate,
    manageable_club_ids,
    module_panels,
    live_table,
    live_lineup_editor,
    live_pitch,
}) {
    const { activeClub } = usePage().props;
    const [tab, setTab] = useState(status === 'scheduled' ? 'overview' : 'ticker');
    const [commandBusy, setCommandBusy] = useState(false);
    const [liveCommandFeedback, setLiveCommandFeedback] = useState(null);
    const [liveState, setLiveState] = useState({
        status,
        live_minute,
        display_minute: String(live_minute ?? 0),
        home_score,
        away_score,
        actions,
        team_states,
        player_states,
        planned_substitutions,
        live_table,
        module_panels,
        lineups,
        live_lineup_editor,
        live_pitch,
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
                display_minute: data.display_minute ?? prev.display_minute,
                home_score: data.home_score,
                away_score: data.away_score,
                actions: data.actions || prev.actions,
                team_states: data.team_states || prev.team_states,
                player_states: data.player_states || prev.player_states,
                planned_substitutions: data.planned_substitutions || prev.planned_substitutions,
                live_table: data.live_table || prev.live_table,
                module_panels: data.module_panels || prev.module_panels,
                lineups: data.lineups || prev.lineups,
                live_lineup_editor: data.live_lineup_editor || prev.live_lineup_editor,
                live_pitch: data.live_pitch || prev.live_pitch,
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
    const postMatchCommand = useCallback(async (routeName, payload) => {
        setCommandBusy(true);
        try {
            const response = await fetch(route(routeName, id), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify(payload),
            });

            if (!response.ok) {
                let message = 'Aktion konnte nicht angewendet werden.';
                try {
                    const errorData = await response.json();
                    const firstError = Object.values(errorData?.errors || {}).flat()[0];
                    message = firstError || errorData?.message || message;
                } catch {}
                setLiveCommandFeedback({ type: 'error', message });
                return;
            }

            const data = await response.json();
            setLiveCommandFeedback({ type: 'success', message: data?.status || 'Aenderung uebernommen.' });
            setLiveState((prev) => ({
                ...prev,
                ...data,
                actions: data.actions || prev.actions,
                display_minute: data.display_minute ?? prev.display_minute,
                team_states: data.team_states || prev.team_states,
                player_states: data.player_states || prev.player_states,
                planned_substitutions: data.planned_substitutions || prev.planned_substitutions,
                live_table: data.live_table || prev.live_table,
                module_panels: data.module_panels || prev.module_panels,
                lineups: data.lineups || prev.lineups,
                live_lineup_editor: data.live_lineup_editor || prev.live_lineup_editor,
                live_pitch: data.live_pitch || prev.live_pitch,
            }));
        } catch {
            setLiveCommandFeedback({ type: 'error', message: 'Netzwerkfehler beim Anwenden der Live-Aktion.' });
        } finally {
            setCommandBusy(false);
        }
    }, [id]);

    const homeState = liveState.team_states?.[String(home_club?.id)];
    const awayState = liveState.team_states?.[String(away_club?.id)];
    const homeLineup = liveState.lineups?.[String(home_club?.id)];
    const awayLineup = liveState.lineups?.[String(away_club?.id)];
    const allActions = liveState.actions || [];
    const highlightCount = allActions.filter((action) => ['goal', 'own_goal', 'yellow_card', 'red_card', 'yellow_red_card', 'substitution'].includes(action.action_type)).length;
    const activeClubId = Number(activeClub?.id || 0);
    const canManageLiveLineup = activeClubId > 0
        && manageable_club_ids?.includes(activeClubId)
        && [home_club?.id, away_club?.id].includes(activeClubId)
        && !['scheduled', 'played'].includes(liveState.status);

    const tabs = [
        ...(liveState.status === 'scheduled' ? [
            { key: 'overview', label: 'Vorbericht' },
        ] : [
            { key: 'ticker', label: 'Ticker', count: allActions.length },
            { key: 'highlights', label: 'Highlight', count: highlightCount },
            { key: '2d', label: '2D' },
            { key: 'overview', label: 'Uebersicht' },
            { key: 'lineup', label: 'Aufstellung' },
            ...(canManageLiveLineup ? [{ key: 'live-lineup', label: 'Live-Taktik' }] : []),
            { key: 'stats', label: 'Statistiken' },
            ...(liveState.live_table?.rows?.length ? [{ key: 'live-table', label: 'Livetabelle' }] : []),
            { key: 'players', label: 'Spieler' },
        ]),
    ];

    return (
        <AuthenticatedLayout>
            <Head title={`${home_club?.short_name || home_club?.name || 'Heim'} vs ${away_club?.short_name || away_club?.name || 'Gast'}`} />

            <div className="mx-auto max-w-[1300px] space-y-6 sm:space-y-8">
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
                    display_minute={liveState.display_minute}
                    kickoff_formatted={kickoff_formatted}
                    competition={competition}
                    matchday={matchday}
                    weather={weather}
                    type={type}
                    actions={allActions}
                />

                {can_simulate && liveState.status !== 'played' && (
                    <div className="grid grid-cols-1 gap-3 sm:flex sm:flex-wrap sm:items-center">
                        <button onClick={simulate} className="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-500/30 bg-amber-500/20 px-5 py-3 font-mono text-[10px] font-black uppercase tracking-widest text-amber-500 transition-all hover:bg-amber-500/30 sm:px-6">
                            <Lightning size={16} weight="fill" /> Simulieren
                        </button>
                        {liveState.status === 'scheduled' && (
                            <button onClick={startLive} className="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-500/30 bg-rose-500/20 px-5 py-3 text-[10px] font-black uppercase tracking-widest text-rose-300 transition-all hover:bg-rose-500/30 sm:px-6">
                                <Play size={16} weight="fill" /> Live-Ticker starten
                            </button>
                        )}
                    </div>
                )}

                {tabs.length > 1 ? (
                    <MatchTabs entries={tabs} activeTab={tab} onChange={setTab} />
                ) : null}

                <div>
                    {tab === 'ticker' && liveState.status !== 'scheduled' && <TickerTab actions={allActions} homeClubId={home_club?.id} status={liveState.status} />}

                    {tab === 'highlights' && liveState.status !== 'scheduled' && <HighlightsTab actions={allActions} homeClubId={home_club?.id} />}

                    {tab === 'overview' && (
                        <OverviewTab
                            status={liveState.status}
                            homeClub={home_club}
                            awayClub={away_club}
                            homeState={homeState}
                            awayState={awayState}
                            livePlayerStates={liveState.player_states}
                            manageableClubIds={manageable_club_ids}
                            teamStates={liveState.team_states}
                            onStyleChange={(clubId, tacticalStyle) => postMatchCommand('matches.live.style', { club_id: clubId, tactical_style: tacticalStyle })}
                            onShout={(clubId, shout) => postMatchCommand('matches.live.shout', { club_id: clubId, shout })}
                            modulePanels={liveState.module_panels}
                            comparison={comparison}
                            preMatchReport={pre_match_report}
                        />
                    )}

                    {tab === '2d' && liveState.status !== 'scheduled' && (
                        <Live2DTab
                            homeClub={home_club}
                            awayClub={away_club}
                            livePitch={liveState.live_pitch}
                            liveMinute={liveState.live_minute}
                            displayMinute={liveState.display_minute}
                        />
                    )}

                    {tab === 'lineup' && liveState.status !== 'scheduled' && (
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

                    {tab === 'live-lineup' && canManageLiveLineup && (
                        <LiveLineupEditorTab
                            clubs={[home_club, away_club]}
                            lineups={liveState.lineups}
                            manageableClubIds={manageable_club_ids}
                            liveLineupEditor={liveState.live_lineup_editor}
                            teamStates={liveState.team_states}
                            busy={commandBusy}
                            feedback={liveCommandFeedback}
                            onSync={(clubId, payload) => postMatchCommand('matches.live.lineup.sync', { club_id: clubId, ...payload })}
                            onSubstitute={(clubId, payload) => postMatchCommand('matches.live.substitute', { club_id: clubId, ...payload })}
                        />
                    )}

                    {tab === 'stats' && liveState.status !== 'scheduled' && <StatsTab homeState={homeState} awayState={awayState} />}

                    {tab === 'live-table' && liveState.status !== 'scheduled' && <LiveTableTab liveTable={liveState.live_table} />}

                    {tab === 'players' && liveState.status !== 'scheduled' && <PlayersTab clubs={[home_club, away_club]} finalStats={final_stats} />}
                </div>
            </div>

            <style dangerouslySetInnerHTML={{ __html: '.custom-scrollbar::-webkit-scrollbar{width:4px}.custom-scrollbar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:4px}' }} />
        </AuthenticatedLayout>
    );
}
