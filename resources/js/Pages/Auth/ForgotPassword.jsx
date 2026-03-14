import React from 'react';
import { useForm } from '@inertiajs/react';
import { ArrowRight, Envelope } from '@phosphor-icons/react';
import AuthField from '@/Components/AuthField';
import AuthLayout from '@/Layouts/AuthLayout';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <AuthLayout
            title="Passwort vergessen"
            heading="Reset Access."
            subtitle="Gib deine E-Mail ein und wir senden dir einen Reset-Link."
        >
            <form onSubmit={submit} className="space-y-6">
                <AuthField
                    label="Email"
                    icon={Envelope}
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="manager@openws.de"
                    error={errors.email}
                    required
                />

                <button
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 py-4 font-black text-white shadow-lg shadow-cyan-600/30 transition-all hover:from-cyan-400 hover:to-indigo-500 disabled:opacity-50"
                >
                    {processing ? 'Sending...' : 'Reset-Link senden'}
                    <ArrowRight size={20} weight="bold" className="transition group-hover:translate-x-1" />
                </button>
            </form>
        </AuthLayout>
    );
}
