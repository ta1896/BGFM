import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { ArrowLeft, PaperPlaneTilt, Plus } from '@phosphor-icons/react';

export default function Create({ forum }) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        content: '',
        images: [],
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('forum.thread.store', forum.slug), {
            forceFormData: true,
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center gap-4">
                    <Link href={route('forum.show', forum.slug)} className="p-2.5 rounded-xl bg-white/5 border border-white/10 text-gray-400 hover:text-amber-500 transition-all">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5 italic">{forum.name}</p>
                        <h1 className="text-2xl font-black text-white italic uppercase tracking-tight leading-none">Neues Thema erstellen</h1>
                    </div>
                </div>
            }
        >
            <Head title="Neues Thema" />

            <div className="max-w-4xl mx-auto pb-24 px-4 sm:px-6 lg:px-8">
                <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-2xl">
                    <form onSubmit={submit} className="p-8 space-y-6">
                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-amber-500">Titel des Themas</label>
                            <input
                                type="text"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                className="w-full bg-[var(--bg-content)]/50 border border-[var(--border-pillar)] rounded-xl p-4 text-white focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all placeholder:text-slate-600 font-bold"
                                placeholder="Aussagekräftiger Titel..."
                                required
                            />
                            {errors.title && <div className="text-sm text-rose-500 font-bold">{errors.title}</div>}
                        </div>

                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-amber-500">Inhalt</label>
                            <textarea
                                value={data.content}
                                onChange={(e) => setData('content', e.target.value)}
                                className="w-full h-64 bg-[var(--bg-content)]/50 border border-[var(--border-pillar)] rounded-xl p-4 text-white focus:ring-2 focus:ring-amber-500/50 focus:border-amber-500 transition-all placeholder:text-slate-600 custom-scrollbar leading-relaxed"
                                placeholder="Schreibe deinen Beitrag..."
                                required
                            ></textarea>
                            {errors.content && <div className="text-sm text-rose-500 font-bold">{errors.content}</div>}
                        </div>
                        <div className="space-y-2">
                            <label className="text-[10px] font-black uppercase tracking-widest text-amber-500">Bilder (optional)</label>
                            <label className="flex items-center gap-4 px-6 py-4 rounded-xl bg-[var(--bg-content)]/50 border border-[var(--border-pillar)] text-gray-400 hover:text-amber-500 hover:border-amber-500/50 cursor-pointer transition-all group">
                                <div className="p-2 rounded-lg bg-white/5 group-hover:bg-amber-500/10 transition-colors">
                                    <Plus size={20} weight="bold" />
                                </div>
                                <span className="text-sm font-bold">
                                    {data.images.length > 0 
                                        ? `${data.images.length} Bilder ausgewählt` 
                                        : 'Bilder für diesen Beitrag hochladen...'}
                                </span>
                                <input 
                                    type="file" 
                                    className="hidden" 
                                    accept="image/*"
                                    multiple
                                    onChange={e => setData('images', Array.from(e.target.files))}
                                />
                            </label>
                            {errors.images && <div className="text-sm text-rose-500 font-bold">{errors.images}</div>}
                        </div>

                        <div className="flex justify-end pt-4">
                            <button
                                type="submit"
                                disabled={processing}
                                className="flex items-center gap-2 px-10 py-4 rounded-2xl bg-amber-500 text-black text-xs font-black uppercase tracking-widest hover:bg-amber-400 disabled:opacity-50 transition-all shadow-[0_5px_20px_rgba(217,177,92,0.3)] hover:-translate-y-1 active:translate-y-0"
                            >
                                <PaperPlaneTilt size={18} weight="bold" />
                                {processing ? 'Wird erstellt...' : 'Thema erstellen'}
                            </button>
                        </div>
                    </form>
                </div>

                <div className="mt-8 p-4 rounded-xl bg-amber-500/5 border border-amber-500/10 flex items-start gap-4">
                    <div className="p-2 rounded-lg bg-amber-500/20 text-amber-500 flex-shrink-0">
                        <PaperPlaneTilt size={20} />
                    </div>
                    <div>
                        <h4 className="text-xs font-black text-white uppercase tracking-wider mb-1">Forum-Regeln</h4>
                        <p className="text-[10px] text-[var(--text-muted)] leading-relaxed">
                            Bitte achte auf einen respektvollen Umgangston. Spamming oder beleidigende Inhalte führen zum Ausschluss. 
                            Wähle einen aussagekräftigen Titel für dein Thema.
                        </p>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
