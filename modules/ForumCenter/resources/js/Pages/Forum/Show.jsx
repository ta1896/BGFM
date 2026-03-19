import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import UserAvatar from '@/Components/UserAvatar';
import { Chats, PushPin, Lock, Eyeglasses, Plus } from '@phosphor-icons/react';

export default function Show({ forum, threads }) {
    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between w-full">
                    <div className="flex items-center gap-4">
                        <Link href={route('forum.index')} className="p-2.5 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-amber-500 transition-all">
                            <Chats size={20} />
                        </Link>
                        <div>
                            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5 italic">{forum.description || 'Diskussionen'}</p>
                            <h1 className="text-2xl font-black text-white italic uppercase tracking-tight leading-none">{forum.name}</h1>
                        </div>
                    </div>

                    <Link 
                        href={route('forum.thread.create', forum.slug)}
                        className="flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-amber-500 text-black text-xs font-black uppercase tracking-widest hover:bg-amber-400 transition-all shadow-[0_0_20px_rgba(217,177,92,0.3)]"
                    >
                        <Plus size={16} weight="bold" />
                        Neues Thema
                    </Link>
                </div>
            }
        >
            <Head title={forum.name} />

            <div className="max-w-7xl mx-auto pb-12 px-4 sm:px-6 lg:px-8">
                <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-xl transition-all">
                    {/* List Header */}
                    <div className="grid grid-cols-12 gap-4 px-6 py-4 bg-slate-800/50 border-b border-[var(--border-pillar)] text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                        <div className="col-span-12 md:col-span-7">Thema</div>
                        <div className="hidden md:block col-span-2 text-center">Statistik</div>
                        <div className="hidden md:block col-span-3">Letzter Beitrag</div>
                    </div>

                    {/* Thread List */}
                    <div className="divide-y divide-[var(--border-muted)]">
                        {threads.data.length > 0 ? (threads.data.map((thread) => (
                            <div key={thread.id} className={`grid grid-cols-12 gap-4 px-6 py-5 hover:bg-white/[0.02] transition-colors ${thread.is_pinned ? 'bg-amber-500/[0.03]' : ''}`}>
                                {/* Thread Title & Info */}
                                <div className="col-span-12 md:col-span-7 flex items-start gap-4">
                                    <div className={`mt-1 flex-shrink-0 h-10 w-10 rounded-xl flex items-center justify-center border transition-all ${thread.is_pinned ? 'bg-amber-500/10 border-amber-500/20 text-amber-500 shadow-[0_0_15px_rgba(217,177,92,0.1)]' : 'bg-[var(--bg-content)] border-[var(--border-pillar)] text-[var(--text-muted)]'}`}>
                                        {thread.is_pinned ? <PushPin size={22} weight="fill" /> : <Chats size={22} />}
                                    </div>
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-2 mb-1">
                                            {thread.is_locked && <Lock size={14} className="text-rose-500" />}
                                            <Link 
                                                href={route('forum.thread.show', thread.slug)}
                                                className={`text-base font-bold text-white hover:text-amber-400 transition-colors line-clamp-1 ${thread.is_pinned ? 'text-amber-100' : ''}`}
                                            >
                                                {thread.title}
                                            </Link>
                                        </div>
                                        <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                            <span className="text-amber-500/80">{thread.user_name}</span>
                                            <span className="opacity-30">•</span>
                                            <span className="flex items-center gap-1"><Eyeglasses size={12} /> {thread.views_count} Aufrufe</span>
                                        </div>
                                    </div>
                                </div>

                                {/* Stats */}
                                <div className="hidden md:flex col-span-2 flex-col justify-center items-center gap-1">
                                    <span className="text-sm font-black text-white">{thread.posts_count}</span>
                                    <span className="text-[10px] font-bold uppercase tracking-tighter text-[var(--text-muted)]">Antworten</span>
                                </div>

                                {/* Last Post */}
                                <div className="hidden md:flex col-span-3 items-center gap-3">
                                    <UserAvatar 
                                        name={thread.last_post.user_name} 
                                        className="h-9 w-9 rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] overflow-hidden"
                                        textClassName="text-[10px] font-black"
                                    />
                                    <div className="min-w-0 flex-1">
                                        <div className="text-[11px] font-black text-white truncate mb-0.5">{thread.last_post.user_name}</div>
                                        <div className="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-tighter">{thread.last_post.created_at}</div>
                                    </div>
                                </div>
                            </div>
                        ))) : (
                            <div className="px-6 py-12 text-center">
                                <p className="text-sm font-black uppercase tracking-widest text-[var(--text-muted)] italic opacity-40">Keine Themen in diesem Bereich gefunden.</p>
                            </div>
                        )}
                    </div>
                </div>

                {/* Pagination (Simplified) */}
                {threads.links && threads.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {threads.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className={`px-4 py-2 rounded-xl border text-xs font-black transition-all ${link.active ? 'bg-amber-500 border-amber-600 text-black' : 'bg-slate-800/50 border-[var(--border-pillar)] text-white hover:border-amber-500/50'}`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
