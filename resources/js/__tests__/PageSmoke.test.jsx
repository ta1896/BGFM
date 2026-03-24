import React from 'react';
import { render, screen } from '@testing-library/react';
import Finances from '@/Pages/Finances/Index';
import Training from '@/Pages/Training/Index';
import Notifications from '@/Pages/Notifications/Index';
import Friendlies from '@/Pages/Friendlies/Index';

const mockPost = vi.fn();
let mockPageProps = {
    auth: { user: { id: 1, name: 'Tester' }, isAdmin: false, theme: 'catalyst' },
    activeClub: null,
    userClubs: [],
    flash: {},
};

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...props }) => <a href={href} {...props}>{children}</a>,
    Head: () => null,
    usePage: () => ({ props: mockPageProps }),
    useForm: (initial = {}) => ({
        data: initial,
        setData: vi.fn(),
        post: mockPost,
        processing: false,
        errors: {},
        reset: vi.fn(),
    }),
    router: {
        post: vi.fn(),
    },
}));

describe('Page smoke tests', () => {
    beforeEach(() => {
        mockPageProps = {
            auth: { user: { id: 1, name: 'Tester' }, isAdmin: false, theme: 'catalyst' },
            activeClub: null,
            userClubs: [],
            flash: {},
        };
        mockPost.mockReset();
        global.route = vi.fn((name, value) => {
            if (!name) {
                return {
                    current: () => 'dashboard',
                };
            }

            return value ? `/${name}/${value}` : `/${name}`;
        });
    });

    it('renders finance empty state without crashing', () => {
        render(<Finances activeClub={null} transactions={{ data: [], links: [] }} />);

        expect(screen.getByText('Kein Verein aktiv')).toBeInTheDocument();
    });

    it('renders training page with club payload and session table', () => {
        render(
            <Training
                club={{ id: 7, name: 'FC Test', players: [{ id: 1, name: 'Spieler 1' }] }}
                sessions={{ data: [{ id: 1, session_date: '2026-03-15', type: 'technical', intensity: 'medium', applied_at: null, player_count: 1 }], links: [] }}
                prefillDate="2026-03-15"
            />
        );

        expect(screen.getByText('Trainingszentrum')).toBeInTheDocument();
        expect(screen.getAllByText('technical').length).toBeGreaterThan(0);
    });

    it('renders notifications empty state', () => {
        render(<Notifications notifications={{ data: [], links: [] }} />);

        expect(screen.getByText('Postfach leer')).toBeInTheDocument();
    });

    it('renders friendlies tabs and default empty state', () => {
        render(
            <Friendlies
                activeClub={{ id: 5, name: 'FC Test' }}
                opponents={[]}
                outgoingRequests={[]}
                incomingRequests={[]}
                friendlyMatches={[]}
            />
        );

        expect(screen.getByText('Freundschaftsspiele')).toBeInTheDocument();
        expect(screen.getByText('Keine Testspiele')).toBeInTheDocument();
    });
});
