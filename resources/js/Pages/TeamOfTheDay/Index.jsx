import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import {
    TeamOfTheDayDetails,
    TeamOfTheDayHeader,
    TeamOfTheDayPitch,
} from '@/Pages/TeamOfTheDay/components/IndexSections';

export default function Index({ auth, teams, activeTeam, entries }) {
    const [selectedTeam, setSelectedTeam] = useState(activeTeam?.id || '');

    const handleTeamChange = (event) => {
        const id = event.target.value;
        setSelectedTeam(id);
        router.get(route('team-of-the-day.index'), { totd: id });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="text-xl font-semibold leading-tight text-white">Team der Woche</h2>}
        >
            <Head title="Team der Woche" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                    <TeamOfTheDayHeader teams={teams} selectedTeam={selectedTeam} onTeamChange={handleTeamChange} />

                    <div className="grid grid-cols-1 gap-8 xl:grid-cols-3">
                        <TeamOfTheDayPitch entries={entries} />
                        <TeamOfTheDayDetails entries={entries} />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
