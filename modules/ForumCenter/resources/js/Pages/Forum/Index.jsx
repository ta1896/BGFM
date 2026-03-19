import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ChatCircleDots, Chats, User, Clock } from '@phosphor-icons/react';
import UserAvatar from '@/Components/UserAvatar';

export default function Index({ categories }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between w-full">
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5 italic">Community Hub</p>
                        <h1 className="text-2xl font-black text-white italic uppercase tracking-tight leading-none">Forum Übersicht</h1>
                    </div>
                </div>
            }
        >
            <Head title="Forum" />

            <div className="max-w-7xl mx-auto space-y-8 pb-12 px-4 sm:px-6 lg:px-8">
                {categories.map((category) => (
                    <div key={category.id} className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-xl">
                        {/* Category Header */}
                        <div className="bg-gradient-to-r from-slate-800/80 to-slate-900/80 px-6 py-4 border-b border-[var(--border-pillar)] flex items-center gap-3">
                            <div className="h-2 w-2 rounded-full bg-amber-500 shadow-[0_0_10px_rgba(217,177,92,0.4)]" />
                            <h2 className="text-sm font-black uppercase tracking-widest text-white">{category.name}</h2>
                        </div>

                        {/* Forum List */}
                        <div className="divide-y divide-[var(--border-muted)]">
                            {category.forums.map((forum) => (
                                <div key={forum.id} className="group flex flex-col md:flex-row items-stretch md:items-center gap-4 px-6 py-5 hover:bg-white/[0.02] transition-all duration-300">
                                    {/* Icon & Name */}
                                    <div className="flex-1 flex items-start gap-4">
                                        <div className="mt-1 flex-shrink-0 w-12 h-12 rounded-xl bg-[var(--bg-content)] border border-[var(--border-pillar)] flex items-center justify-center text-amber-500 group-hover:scale-110 group-hover:bg-amber-500 group-hover:text-black transition-all">
                                            <ChatCircleDots size={28} weight="fill" />
                                        </div>
                                        <div className="min-w-0">
                                            <Link 
                                                href={route('forum.show', forum.slug)}
                                                className="text-lg font-bold text-white hover:text-amber-400 transition-colors block mb-1"
                                            >
                                                {forum.name}
                                            </Link>
                                            <p className="text-sm text-[var(--text-muted)] line-clamp-2 leading-relaxed">
                                                {forum.description}
                                            </p>
                                        </div>
                                    </div>

                                    {/* Stats (Desktop) */}
                                    <div className="hidden lg:flex flex-shrink-0 w-48 border-x border-[var(--border-muted)] px-6 flex-col justify-center gap-1 text-center">
                                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-widest">
                                            <span className="text-[var(--text-muted)]">Themen</span>
                                            <span className="text-white">{forum.threads_count}</span>
                                        </div>
                                        <div className="flex items-center justify-between text-xs font-bold uppercase tracking-widest">
                                            <span className="text-[var(--text-muted)]">Beiträge</span>
                                            <span className="text-white font-black">{forum.posts_count}</span>
                                        </div>
                                    </div>

                                    {/* Last Post Snippet */}
                                    <div className="flex-shrink-0 w-full md:w-64 flex items-center gap-3">
                                        {forum.last_thread ? (
                                            <>
                                                <div className="flex-shrink-0 relative">
                                                    <UserAvatar 
                                                        name={forum.last_thread.user_name} 
                                                        className="h-10 w-10 rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] overflow-hidden" 
                                                        textClassName="text-xs font-black"
                                                    />
                                                    <div className="absolute -bottom-1 -right-1 h-3 w-3 rounded-full bg-emerald-500 border-2 border-[var(--bg-pillar)]" />
                                                </div>
                                                <div className="min-w-0 flex-1">
                                                    <Link 
                                                        href={route('forum.thread.show', forum.last_thread.slug)}
                                                        className="text-xs font-bold text-white hover:text-amber-400 transition-colors block truncate mb-0.5"
                                                    >
                                                        {forum.last_thread.title}
                                                    </Link>
                                                    <div className="flex items-center gap-1.5 text-[10px] uppercase font-black tracking-tight text-[var(--text-muted)]">
                                                        <span className="text-amber-500/80">{forum.last_thread.user_name}</span>
                                                        <span className="opacity-30">•</span>
                                                        <span>{forum.last_thread.created_at}</span>
                                                    </div>
                                                </div>
                                            </>
                                        ) : (
                                            <p className="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] italic opacity-50">Keine Forenbeiträge</p>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
