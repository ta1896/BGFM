import React from 'react';
import { render, screen } from '@testing-library/react';
import PaginationLink from '@/Components/PaginationLink';

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, dangerouslySetInnerHTML, ...props }) => (
        <a href={href} dangerouslySetInnerHTML={dangerouslySetInnerHTML} {...props}>
            {children}
        </a>
    ),
}));

describe('PaginationLink', () => {
    it('renders disabled pagination items without an anchor', () => {
        render(
            <PaginationLink
                link={{ url: null, label: '&laquo; Zurueck' }}
                className="enabled"
                disabledClassName="disabled"
            />,
        );

        expect(screen.getByText('« Zurueck')).toHaveAttribute('aria-disabled', 'true');
        expect(screen.queryByRole('link')).not.toBeInTheDocument();
    });

    it('renders enabled pagination items as links', () => {
        render(
            <PaginationLink
                link={{ url: '/finances?page=2', label: '2' }}
                className="enabled"
                disabledClassName="disabled"
            />,
        );

        expect(screen.getByRole('link', { name: '2' })).toHaveAttribute('href', '/finances?page=2');
    });
});
