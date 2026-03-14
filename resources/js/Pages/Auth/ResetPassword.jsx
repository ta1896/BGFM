import React from 'react';
import { useForm } from '@inertiajs/react';
import { ArrowRight, Envelope, Lock } from '@phosphor-icons/react';
import AuthField from '@/Components/AuthField';
import AuthLayout from '@/Layouts/AuthLayout';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        email: email ?? '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout
            title="Passwort zurücksetzen"
            heading="Choose New Access."
            subtitle="Lege ein neues Passwort für dein Managerkonto fest."
        >
            <form onSubmit={submit} className="space-y-6">
                <AuthField
                    label="Email"
                    icon={Envelope}
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    error={errors.email}
                    required
                />

                <AuthField
                    label="Neues Passwort"
                    icon={Lock}
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    placeholder="••••••••"
                    error={errors.password}
                    required
                />

                <AuthField
                    label="Passwort bestätigen"
                    icon={Lock}
                    type="password"
                    value={data.password_confirmation}
                    onChange={(e) => setData('password_confirmation', e.target.value)}
                    placeholder="••••••••"
                    error={errors.password_confirmation}
                    required
                />

                <button
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 py-4 font-black text-white shadow-lg shadow-indigo-600/30 transition-all hover:from-indigo-500 hover:to-cyan-500 disabled:opacity-50"
                >
                    {processing ? 'Updating...' : 'Passwort zurücksetzen'}
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>
            </form>
        </AuthLayout>
    );
}
