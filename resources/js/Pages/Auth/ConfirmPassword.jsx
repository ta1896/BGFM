import React from 'react';
import { useForm } from '@inertiajs/react';
import { ArrowRight, Lock } from '@phosphor-icons/react';
import AuthField from '@/Components/AuthField';
import AuthLayout from '@/Layouts/AuthLayout';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title="Passwort bestätigen"
            heading="Confirm Identity."
            subtitle="Bitte bestätige dein Passwort, um fortzufahren."
        >
            <form onSubmit={submit} className="space-y-6">
                <AuthField
                    label="Passwort"
                    icon={Lock}
                    type="password"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                    placeholder="••••••••"
                    error={errors.password}
                    required
                />

                <button
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 py-4 font-black text-white shadow-lg shadow-cyan-600/30 transition-all hover:from-cyan-400 hover:to-indigo-500 disabled:opacity-50"
                >
                    {processing ? 'Checking...' : 'Bestätigen'}
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>
            </form>
        </AuthLayout>
    );
}
