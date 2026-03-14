import React from 'react';
import { render, screen } from '@testing-library/react';
import Login from '@/Pages/Auth/Login';

const mockUseForm = vi.fn(() => ({
    data: { email: '', password: '', remember: false },
    setData: vi.fn(),
    post: vi.fn(),
    processing: false,
    errors: {},
    reset: vi.fn(),
}));

vi.mock('@inertiajs/react', () => ({
    Link: ({ href, children, ...props }) => <a href={href} {...props}>{children}</a>,
    Head: () => null,
    usePage: () => ({ props: { flash: {} } }),
    useForm: () => mockUseForm(),
}));

describe('Auth pages', () => {
    beforeEach(() => {
        global.route = vi.fn((name) => `/${name}`);
    });

    it('renders the login screen and reset password link', () => {
        render(<Login status="Bereit" canResetPassword />);

        expect(screen.getByRole('heading', { name: 'Welcome Back.' })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /Sign In/i })).toBeInTheDocument();
        expect(screen.getByRole('link', { name: 'Forgot?' })).toHaveAttribute('href', '/password.request');
    });
});
