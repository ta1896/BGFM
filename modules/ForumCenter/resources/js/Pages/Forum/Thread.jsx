import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import UserAvatar from '@/Components/UserAvatar';
import { ArrowLeft, ChatCircleDots, User, IdentificationCard, ShieldCheck, Quotes } from '@phosphor-icons/react';

export default function Thread({ thread, posts }) {
    const { data, setData, post, processing, reset, errors } = useForm({
        content: '',
    });

    const submitReply = (e) => {
        e.preventDefault();
        post(route('forum.post.store', thread.slug), {
            onSuccess: () => reset('content'),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between w-full">
                    <div className="flex items-center gap-4">
                        <Link href={route('forum.show', thread.forum.slug)} className="p-2.5 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-amber-500 transition-all">
                            <ArrowLeft size={20} />
                        </Link>
                        <div>
                            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5 italic">{thread.forum.name}</p>
                            <h1 className="text-2xl font-black text-white italic uppercase tracking-tight leading-none line-clamp-1">{thread.title}</h1>
                        </div>
                    </div>
                </div>
            }
        >
            <Head title={thread.title} />

            <div className="max-w-7xl mx-auto pb-24 px-4 sm:px-6 lg:px-8 space-y-6">
                {posts.data.map((post_item, index) => (
                    <div key={post_item.id} className="flex flex-col md:flex-row overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-2xl transition-all h-full">
                        {/* WoltLab Style Sidebar */}
                        <div className="w-full md:w-64 bg-slate-900/60 p-6 border-b md:border-b-0 md:border-r border-[var(--border-pillar)] flex flex-col items-center">
                            <div className="relative mb-4 group">
                                <div className="absolute -inset-1.5 bg-gradient-to-br from-amber-500 to-amber-900 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                                <UserAvatar 
                                    name={post_item.user.name} 
                                    className="relative h-28 w-28 rounded-full border-4 border-slate-800 bg-slate-700 shadow-2xl overflow-hidden" 
                                    textClassName="text-2xl font-black italic"
                                />
                                <div className="absolute bottom-1 right-2 h-5 w-5 rounded-full bg-emerald-500 border-4 border-slate-900" />
                            </div>
                            
                            <h3 className="text-lg font-black text-white mb-1 group-hover:text-amber-400 transition-colors text-center">{post_item.user.name}</h3>
                            <div className="mb-6 px-4 py-1.5 bg-amber-500/10 border border-amber-500/20 text-[10px] font-black uppercase tracking-widest text-amber-500 rounded-full shadow-[0_0_15px_rgba(217,177,92,0.1)]">
                                {post_item.user.club_name}
                            </div>

                            <div className="w-full space-y-2 border-t border-white/5 pt-4">
                                <div className="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                    <span className="flex items-center gap-1.5"><ChatCircleDots size={14} /> Beiträge</span>
                                    <span className="text-white">{post_item.user.posts_count}</span>
                                </div>
                                <div className="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                    <span className="flex items-center gap-1.5"><ShieldCheck size={14} /> Tokens</span>
                                    <span className="text-amber-400">{post_item.user.tokens}</span>
                                </div>
                                <div className="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">
                                    <span className="flex items-center gap-1.5"><IdentificationCard size={14} /> Status</span>
                                    <span className="text-emerald-400">Aktiv</span>
                                </div>
                            </div>
                        </div>

                        {/* WoltLab Style Content */}
                        <div className="flex-1 flex flex-col bg-[var(--bg-pillar)]/10">
                            <div className="px-6 py-4 bg-slate-800/30 border-b border-[var(--border-muted)] flex items-center justify-between">
                                <div className="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest">
                                    Veröffentlicht am {post_item.created_at}
                                </div>
                                <div className="text-[10px] font-black text-white/20 uppercase tracking-widest">
                                    #{index + 1 + (posts.current_page - 1) * posts.per_page}
                                </div>
                            </div>
                            
                            <div className="flex-1 p-8 text-slate-200 leading-loose text-lg whitespace-pre-wrap font-medium">
                                {post_item.content}
                            </div>

                            <div className="px-6 py-4 border-t border-[var(--border-muted)] flex justify-end gap-3">
                                <button className="p-2 text-gray-500 hover:text-amber-500 transition-all hover:bg-white/5 rounded-lg">
                                    <Quotes size={20} weight="fill" />
                                </button>
                                <button className="px-4 py-2 text-[10px] font-black uppercase tracking-widest text-gray-400 hover:text-white transition-all">
                                    Melden
                                </button>
                            </div>
                        </div>
                    </div>
                ))}

                {/* Reply Form */}
                {!thread.is_locked && (
                    <div className="mt-12 overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-2xl">
                        <div className="px-6 py-4 bg-slate-800/50 border-b border-[var(--border-pillar)]">
                            <h2 className="text-sm font-black uppercase tracking-widest text-white">Schnelle Antwort</h2>
                        </div>
                        <form onSubmit={submitReply} className="p-6">
                            <textarea
                                value={data.content}
                                onChange={(e) => setData('content', e.target.value)}
                                className="w-full h-40 bg-[var(--bg-content)]/50 border border-[var(--border-pillar)] rounded-xl p-4 text-white focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all placeholder:text-slate-600 custom-scrollbar"
                                placeholder="Schreibe deine Antwort hier..."
                                required
                            ></textarea>
                            {errors.content && <div className="mt-2 text-sm text-rose-500">{errors.content}</div>}
                            <div className="mt-4 flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-8 py-3 rounded-2xl bg-amber-500 text-black text-xs font-black uppercase tracking-widest hover:bg-amber-400 disabled:opacity-50 transition-all shadow-[0_5px_15px_rgba(217,177,92,0.2)]"
                                >
                                    {processing ? 'Sendet...' : 'Antwort absenden'}
                                </button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
