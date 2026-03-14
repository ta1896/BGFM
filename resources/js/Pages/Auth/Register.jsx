import React from 'react';
import { Link, useForm } from '@inertiajs/react';
import { ArrowRight, Envelope, Lock, User } from '@phosphor-icons/react';
import AuthField from '@/Components/AuthField';
import AuthLayout from '@/Layouts/AuthLayout';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout
            title="Register"
            heading="Start Your Legacy."
            subtitle="Erstelle dein Managerkonto in weniger als 30 Sekunden."
            footer={(
                <>
                    Bereits Manager?
                    <Link href={route('login')} className="ml-1 text-indigo-400 hover:text-indigo-300">
                        Hier einloggen
                    </Link>
                </>
            )}
        >
            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-6 md:grid-cols-2">
                    <AuthField
                        label="Manager Name"
                        icon={User}
                        type="text"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Alex Mueller"
                        error={errors.name}
                        required
                    />

                    <AuthField
                        label="Email"
                        icon={Envelope}
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="alex@manager.de"
                        error={errors.email}
                        required
                    />
                </div>

                <AuthField
                    label="Passwort wählen"
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
                    className="group flex w-full items-center justify-center gap-3 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 py-4 font-black text-white shadow-lg shadow-indigo-600/30 transition-all hover:from-indigo-500 hover:to-cyan-500 disabled:opacity-50"
                >
                    {processing ? 'Creating Account...' : 'Create Manager Account'}
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>
            </form>
        </AuthLayout>
    );
}
