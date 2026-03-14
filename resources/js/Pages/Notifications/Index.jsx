import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { PageReveal, StaggerGroup } from '@/Components/PageReveal';
import PageHeader from '@/Components/PageHeader';
import EmptyState from '@/Components/EmptyState';
import {
    Envelope,
    EnvelopeOpen,
    CheckCircle,
    ArrowRight,
    Clock,
    Tray,
    Checks,
} from '@phosphor-icons/react';

export default function Notifications({ notifications }) {
    const { post } = useForm();

    const markAllSeen = () => {
        post(route('notifications.seen-all'), { preserveScroll: true });
    };

    const markSeen = (id) => {
        post(route('notifications.seen', id), { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Posteingang" />

            <div className="max-w-4xl mx-auto space-y-8">
                <PageHeader
                    eyebrow="Kommunikation"
                    title="Posteingang"
                    actions={
                        notifications.data.some((notification) => !notification.seen_at) ? (
                            <button
                                onClick={markAllSeen}
                                className="flex items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] px-5 py-2.5 text-[10px] font-black uppercase tracking-widest text-slate-300 shadow-lg shadow-black/20 transition-all hover:bg-slate-700 hover:text-white"
                            >
                                <Checks size={18} weight="bold" className="text-cyan-400" />
                                Alle als gelesen markieren
                            </button>
                        ) : null
                    }
                />

                <div className="sim-card min-h-[500px] overflow-hidden border-[var(--border-muted)] bg-[#0c1222]/80 p-0 shadow-2xl backdrop-blur-xl">
                    {notifications.data.length === 0 ? (
                        <PageReveal>
                            <EmptyState
                                icon={Tray}
                                title="Postfach leer"
                                description="Keine neuen Nachrichten vorhanden. Du bist auf dem neuesten Stand."
                            />
                        </PageReveal>
                    ) : (
                        <StaggerGroup className="divide-y divide-slate-800/50">
                            {notifications.data.map((notification) => (
                                <article
                                    key={notification.id}
                                    className={`relative flex gap-6 p-6 transition-all ${
                                        notification.seen_at ? 'bg-transparent opacity-50 grayscale-[0.5]' : 'border-l-4 border-l-cyan-500 bg-white/[0.02]'
                                    }`}
                                >
                                    <div className="shrink-0 pt-1">
                                        {!notification.seen_at ? (
                                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-cyan-500/20 bg-cyan-500/10 text-cyan-400 shadow-[0_0_15px_rgba(34,211,238,0.1)]">
                                                <Envelope size={20} weight="fill" />
                                            </div>
                                        ) : (
                                            <div className="flex h-10 w-10 items-center justify-center rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-slate-600">
                                                <EnvelopeOpen size={20} weight="bold" />
                                            </div>
                                        )}
                                    </div>

                                    <div className="min-w-0 flex-1">
                                        <div className="mb-2 flex items-start justify-between gap-4">
                                            <h3 className={`text-lg font-black uppercase italic tracking-tight ${!notification.seen_at ? 'text-white' : 'text-[var(--text-muted)]'}`}>
                                                {notification.title}
                                            </h3>
                                            <div className="flex items-center gap-2 whitespace-nowrap text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                                <Clock size={12} weight="bold" />
                                                {notification.created_at_formatted}
                                            </div>
                                        </div>

                                        <p className={`mb-6 text-base leading-relaxed font-medium ${!notification.seen_at ? 'text-slate-300' : 'text-[var(--text-muted)]'}`}>
                                            {notification.message}
                                        </p>

                                        <div className="flex flex-wrap items-center gap-4">
                                            {notification.club && (
                                                <div className="flex items-center gap-2 rounded-lg border border-[var(--border-pillar)] bg-[var(--bg-pillar)] px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.1em] text-[var(--text-muted)]">
                                                    <img className="h-5 w-5 object-contain" src={notification.club.logo_url} alt={notification.club.name} />
                                                    {notification.club.name}
                                                </div>
                                            )}

                                            <div className="flex-1" />

                                            <div className="flex items-center gap-4">
                                                {notification.action_url && (
                                                    <Link href={notification.action_url} className="inline-flex items-center gap-2 text-xs font-black text-cyan-400 transition-all hover:translate-x-1 hover:text-white">
                                                        Details oeffnen
                                                        <ArrowRight size={14} weight="bold" />
                                                    </Link>
                                                )}

                                                {!notification.seen_at && (
                                                    <button
                                                        onClick={() => markSeen(notification.id)}
                                                        className="inline-flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] transition-colors hover:text-cyan-400"
                                                    >
                                                        <CheckCircle size={14} weight="bold" />
                                                        Als gelesen markieren
                                                    </button>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            ))}
                        </StaggerGroup>
                    )}

                    {notifications.links.length > 3 && (
                        <div className="flex justify-center gap-2 border-t border-[var(--border-muted)] bg-[#0c1222] p-6">
                            {notifications.links.map((link, index) => (
                                <PaginationLink
                                    key={index}
                                    link={link}
                                    className={`rounded-lg px-3 py-1.5 text-[10px] font-black tracking-widest transition-all ${
                                        link.active ? 'bg-cyan-500 text-white shadow-[0_0_15px_rgba(34,211,238,0.3)]' : 'text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                                    }`}
                                    disabledClassName="rounded-lg px-3 py-1.5 text-[10px] font-black tracking-widest opacity-30 pointer-events-none"
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
