import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import SidebarMenuGroup from '@/Components/SidebarMenuGroup';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...props }) => <a href={href} {...props}>{children}</a>,
}));

describe('SidebarMenuGroup', () => {
    beforeEach(() => {
        global.route = vi.fn((name) => `/${name}`);
    });

    it('opens only when the current route matches an item', () => {
        render(
            <SidebarMenuGroup
                group={{ label: 'Buero', items: [{ route: 'finances.index', label: 'Finanzen', active: 'finances.*' }] }}
                currentRoute="finances.index"
                autoOpenActive
                activeTextClassName="active"
                inactiveTextClassName="inactive"
                labelClassName="label"
            />,
        );

        expect(screen.getByRole('link', { name: 'Finanzen' })).toBeInTheDocument();
    });

    it('keeps inactive groups collapsed until toggled manually', () => {
        render(
            <SidebarMenuGroup
                group={{ label: 'Team', items: [{ route: 'players.index', label: 'Kader', active: 'players.*' }] }}
                currentRoute="dashboard"
                autoOpenActive
                activeTextClassName="active"
                inactiveTextClassName="inactive"
                labelClassName="label"
            />,
        );

        expect(screen.queryByRole('link', { name: 'Kader' })).not.toBeInTheDocument();
        fireEvent.click(screen.getByRole('button', { name: /Team/i }));
        expect(screen.getByRole('link', { name: 'Kader' })).toBeInTheDocument();
    });
});
