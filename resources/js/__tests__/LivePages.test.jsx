import React from 'react';
import { render, screen } from '@testing-library/react';
import Online from '@/Pages/Managers/Online';
import Ticker from '@/Pages/Managers/Ticker';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...props }) => <a href={href} {...props}>{children}</a>,
    Head: () => null,
    usePage: () => ({
        props: {
            auth: { user: { id: 1, name: 'Tester' }, isAdmin: false, theme: 'catalyst' },
            activeClub: null,
            userClubs: [],
            flash: {},
            live: { matchesCount: 1 },
        },
    }),
    router: {
        reload: vi.fn(),
    },
}));

describe('Live pages smoke tests', () => {
    beforeEach(() => {
        global.route = vi.fn((name, value) => {
            if (!name) {
                return {
                    current: () => 'dashboard',
                };
            }

            return value ? `/${name}/${value}` : `/${name}`;
        });
    });

    it('renders online managers overview', () => {
        render(
            <Online
                onlineWindowMinutes={5}
                onlineManagers={[
                    {
                        id: 1,
                        manager: 'Test Manager',
                        club: { name: 'Test Club', logo_url: null },
                        activity_label: 'Im Matchcenter',
                        last_seen_label: 'vor 1 Minute',
                    },
                ]}
            />
        );

        expect(screen.getByText('Welche Manager sind online?')).toBeInTheDocument();
        expect(screen.getByText('Test Manager')).toBeInTheDocument();
        expect(screen.getByText('Im Matchcenter')).toBeInTheDocument();
    });

    it('renders live ticker overview', () => {
        render(
            <Ticker
                onlineManagersCount={2}
                liveMatches={[
                    {
                        id: 4,
                        live_minute: 39,
                        home_score: 1,
                        away_score: 0,
                        home_club: { name: 'Home FC', logo_url: null },
                        away_club: { name: 'Away FC', logo_url: null },
                    },
                ]}
            />
        );

        expect(screen.getByText('Welche Spiele laufen gerade?')).toBeInTheDocument();
        expect(screen.getByText('Home FC')).toBeInTheDocument();
        expect(screen.getByText('Away FC')).toBeInTheDocument();
    });
});
