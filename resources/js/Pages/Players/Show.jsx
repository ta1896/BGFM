import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, usePage } from '@inertiajs/react';
import {
    PlayerCareerTab,
    PlayerCustomizeTab,
    PlayerHistoryTab,
    PlayerMatchesTab,
    PlayerOverviewTab,
    PlayerShowHeader,
} from '@/Pages/Players/components/ShowSections';

export default function Show({ player, careerStats, recentMatches, isOwner, positions, squadDynamics }) {
    const { features } = usePage().props;
    const playerConversationsEnabled = !!features?.player_conversations_enabled;
    const [activeTab, setActiveTab] = useState('overview');
    const { data, setData, patch, processing } = useForm({
        market_value: player.market_value,
        position: player.position,
        position_second: player.position_second || '',
        position_third: player.position_third || '',
        photo_url: '',
    });
    const promiseForm = useForm({
        promise_type: 'regular_rotation',
        expected_minutes_share: player.expected_playtime || 50,
        deadline_at: '',
        notes: '',
    });
    const conversationForm = useForm({
        topic: 'morale',
        approach: 'supportive',
        manager_message: '',
    });

    const handleUpdate = (event) => {
        event.preventDefault();
        patch(route('players.update', player.id), {
            preserveScroll: true,
        });
    };

    const handlePromiseSubmit = (event) => {
        event.preventDefault();
        promiseForm.post(route('players.playtime-promise.store', player.id), {
            preserveScroll: true,
        });
    };

    const handleConversationSubmit = (event) => {
        event.preventDefault();
        conversationForm.post(route('players.conversations.store', player.id), {
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title={player.full_name} />

            <div className="mx-auto max-w-[1400px] space-y-8">
                <PlayerShowHeader player={player} isOwner={isOwner} activeTab={activeTab} onTabChange={setActiveTab} />

                <div className="min-h-[500px]">
                    {activeTab === 'overview' && <PlayerOverviewTab player={player} squadDynamics={squadDynamics} />}
                    {activeTab === 'career' && <PlayerCareerTab careerStats={careerStats} />}
                    {activeTab === 'matches' && <PlayerMatchesTab player={player} recentMatches={recentMatches} />}
                    {activeTab === 'history' && <PlayerHistoryTab squadDynamics={squadDynamics} playerConversationsEnabled={playerConversationsEnabled} />}
                    {activeTab === 'customize' && (
                        <PlayerCustomizeTab
                            isOwner={isOwner}
                            data={data}
                            setData={setData}
                            positions={positions}
                            processing={processing}
                            onSubmit={handleUpdate}
                            promiseForm={promiseForm}
                            onPromiseSubmit={handlePromiseSubmit}
                            conversationForm={conversationForm}
                            onConversationSubmit={handleConversationSubmit}
                            player={player}
                            squadDynamics={squadDynamics}
                            playerConversationsEnabled={playerConversationsEnabled}
                        />
                    )}
                </div>
            </div>

            <style
                dangerouslySetInnerHTML={{
                    __html: `
                        .no-scrollbar::-webkit-scrollbar { display: none; }
                        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
                        .sim-btn-primary {
                            @apply bg-gradient-to-br from-[#d9b15c] via-[#b69145] to-[#8d6e32] text-black shadow-[0_10px_40px_rgba(217,177,92,0.15)] hover:brightness-110 transition-all rounded-2xl border-none;
                        }
                        .sim-input-indigo {
                            @apply bg-amber-500/5 border border-amber-500/20 rounded-2xl px-5 py-3.5 text-white placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all;
                        }
                    `,
                }}
            />
        </AuthenticatedLayout>
    );
}
