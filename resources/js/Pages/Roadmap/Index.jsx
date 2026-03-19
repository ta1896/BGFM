import React, { useMemo, useState } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import RoadmapLayout from '@/Layouts/RoadmapLayout';
import {
    ArrowBendUpRight,
    CaretDown,
    CaretRight,
    ChatCircleDots,
    Checks,
    ClockClockwise,
    Lightning,
    NotePencil,
    Sparkle,
    Stack,
    Target,
    TrendUp,
    User,
} from '@phosphor-icons/react';

const statusTone = {
    planned: 'border-amber-300/20 bg-amber-400/10 text-amber-100',
    in_progress: 'border-cyan-300/20 bg-cyan-400/10 text-cyan-100',
    done: 'border-emerald-300/20 bg-emerald-400/10 text-emerald-100',
    cancelled: 'border-rose-300/20 bg-rose-400/10 text-rose-100',
};

const statusLabel = {
    planned: 'Offen',
    in_progress: 'In Arbeit',
    done: 'Fertiggestellt',
    cancelled: 'Abgelehnt',
};

const categoryTone = {
    quick: 'border-emerald-300/20 bg-emerald-400/10 text-emerald-100',
    mid: 'border-sky-300/20 bg-sky-400/10 text-sky-100',
    big: 'border-violet-300/20 bg-violet-400/10 text-violet-100',
};

const categoryLabel = {
    quick: 'Quick Win',
    mid: 'Mittelfristig',
    big: 'Grosses Feature',
};

const sizeLabel = {
    small: 'Klein',
    huge: 'Gross',
};

const orderedStatuses = ['in_progress', 'planned', 'done', 'cancelled'];

export default function Index({ items, groups, topItems, statusOptions, categoryOptions, sizeOptions }) {
    const { auth } = usePage().props;
    const createForm = useForm({
        title: '',
        summary: '',
        status: 'planned',
        category: 'mid',
        size_bucket: 'small',
        tags: [],
        priority: 3,
        effort: 3,
    });
    const [commentDrafts, setCommentDrafts] = useState({});
    const [tagDrafts, setTagDrafts] = useState({});
    const [openItems, setOpenItems] = useState(() => new Set(topItems.slice(0, 1).map((item) => item.id)));
    const [filters, setFilters] = useState({
        query: '',
        status: 'all',
        category: 'all',
        size: 'all',
        tag: 'all',
    });

    const availableTags = useMemo(() => {
        const seen = new Set();

        return items
            .flatMap((item) => item.tags || [])
            .filter((tag) => {
                const normalized = String(tag).toLowerCase();
                if (seen.has(normalized)) return false;
                seen.add(normalized);
                return true;
            })
            .sort((left, right) => left.localeCompare(right, 'de'));
    }, [items]);

    const stats = useMemo(() => ({
        total: items.length,
        active: items.filter((item) => ['planned', 'in_progress'].includes(item.status)).length,
        comments: items.reduce((sum, item) => sum + (item.comments_count || 0), 0),
        done: items.filter((item) => item.status === 'done').length,
    }), [items]);

    const filteredGroups = useMemo(() => {
        const query = filters.query.trim().toLowerCase();
        const matches = (item) => {
            if (filters.status !== 'all' && item.status !== filters.status) return false;
            if (filters.category !== 'all' && item.category !== filters.category) return false;
            if (filters.size !== 'all' && item.size_bucket !== filters.size) return false;
            if (filters.tag !== 'all' && !(item.tags || []).includes(filters.tag)) return false;

            if (!query) return true;

            return [
                item.title,
                item.summary,
                item.key,
                item.creator?.name,
                item.updater?.name,
                ...(item.tags || []),
            ].filter(Boolean).some((value) => String(value).toLowerCase().includes(query));
        };

        return orderedStatuses.reduce((acc, statusKey) => {
            acc[statusKey] = (groups[statusKey] || []).filter(matches);
            return acc;
        }, {});
    }, [filters, groups]);

    const submitCreate = (event) => {
        event.preventDefault();
        createForm.transform((data) => ({
            ...data,
            tags: parseTags(data.tags),
        })).post(route('roadmap-board.items.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset('title', 'summary', 'tags'),
        });
    };

    const updateItem = (itemId, field, value) => {
        router.patch(route('roadmap-board.items.update', itemId), { [field]: value }, { preserveScroll: true });
    };

    const submitComment = (itemId) => {
        const body = (commentDrafts[itemId] || '').trim();
        if (!body) return;

        router.post(route('roadmap-board.comments.store', itemId), { body }, {
            preserveScroll: true,
            onSuccess: () => setCommentDrafts((current) => ({ ...current, [itemId]: '' })),
        });
    };

    const addTagToItem = (item) => {
        const draft = tagDrafts[item.id] || '';
        const nextTags = uniqueTags([...(item.tags || []), ...parseTags(draft)]);
        if (nextTags.length === (item.tags || []).length) return;

        updateItem(item.id, 'tags', nextTags);
        setTagDrafts((current) => ({ ...current, [item.id]: '' }));
    };

    const removeTagFromItem = (item, tagToRemove) => {
        updateItem(item.id, 'tags', (item.tags || []).filter((tag) => tag !== tagToRemove));
    };

    const toggleItem = (itemId) => {
        setOpenItems((current) => {
            const next = new Set(current);
            if (next.has(itemId)) next.delete(itemId);
            else next.add(itemId);
            return next;
        });
    };

    return (
        <RoadmapLayout user={auth?.user}>
            <Head title="Roadmap Board" />

            <div className="space-y-6 pb-10">
                <section className="grid gap-4 xl:grid-cols-[1.15fr_0.85fr]">
                    <div className="rounded-[28px] border border-white/8 bg-[linear-gradient(160deg,rgba(12,16,22,0.94),rgba(15,20,27,0.9))] p-5 shadow-[0_20px_70px_rgba(0,0,0,0.34)] sm:p-6">
                        <div className="flex flex-wrap items-start justify-between gap-4">
                            <div className="max-w-2xl">
                                <div className="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200/75">Community Roadmap</div>
                                <h2 className="mt-2 text-2xl font-black tracking-[-0.04em] text-white sm:text-[2.2rem]">
                                    Oeffentliche Roadmap-Optik, aber mit voller Kontrolle.
                                </h2>
                                <p className="mt-3 text-sm leading-7 text-slate-300">
                                    Die Eintraege sind wie in einer oeffentlichen Produkt-Roadmap aufgebaut: Kategorie links, Status rechts, Diskussion und Steuerung im aufklappbaren Detailbereich.
                                </p>
                            </div>

                            <div className="rounded-[22px] border border-white/10 bg-white/[0.04] px-4 py-3">
                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/45">Angemeldet als</div>
                                <div className="mt-1 text-sm font-black text-white">{auth?.user?.name}</div>
                            </div>
                        </div>

                        <div className="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <SummaryCard icon={Stack} label="Eintraege" value={stats.total} />
                            <SummaryCard icon={Target} label="Aktiv" value={stats.active} />
                            <SummaryCard icon={ChatCircleDots} label="Kommentare" value={stats.comments} />
                            <SummaryCard icon={Checks} label="Fertig" value={stats.done} />
                        </div>
                    </div>

                    <div className="rounded-[28px] border border-white/8 bg-[linear-gradient(160deg,rgba(14,18,22,0.95),rgba(13,18,24,0.92))] p-5 shadow-[0_20px_70px_rgba(0,0,0,0.34)] sm:p-6">
                        <div className="flex items-center gap-3">
                            <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-amber-300/20 bg-amber-400/10">
                                <TrendUp size={18} weight="duotone" className="text-amber-100" />
                            </div>
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-amber-100/75">Prioritaetsliste</div>
                                <div className="mt-1 text-lg font-black text-white">Als naechstes bauen</div>
                            </div>
                        </div>

                        <div className="mt-5 space-y-3">
                            {topItems.map((item, index) => (
                                <button
                                    key={item.id}
                                    type="button"
                                    onClick={() => toggleItem(item.id)}
                                    className="w-full rounded-[22px] border border-white/8 bg-white/[0.03] px-4 py-3 text-left transition-all hover:border-white/14 hover:bg-white/[0.05]"
                                >
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <div className="flex items-center gap-2">
                                                <span className="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-amber-300/20 bg-amber-400/10 px-2 text-[10px] font-black text-amber-100">
                                                    {index + 1}
                                                </span>
                                                <span className={`inline-flex rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] ${categoryTone[item.category] || categoryTone.mid}`}>
                                                    {categoryLabel[item.category] || item.category}
                                                </span>
                                            </div>
                                            <div className="mt-2 text-sm font-black text-white">{item.title}</div>
                                        </div>
                                        <div className="flex items-center gap-3">
                                            <div className="text-sm font-black text-amber-100">{normalizeScore(item.weighted_score)}%</div>
                                            {openItems.has(item.id) ? (
                                                <CaretDown size={16} weight="bold" className="text-white/55" />
                                            ) : (
                                                <CaretRight size={16} weight="bold" className="text-white/55" />
                                            )}
                                        </div>
                                    </div>

                                    {openItems.has(item.id) ? (
                                        <div className="mt-4 border-t border-white/8 pt-4">
                                            <p className="text-sm leading-6 text-slate-300">{item.summary}</p>
                                            <div className="mt-3 flex flex-wrap gap-2">
                                                <MetaTag label={statusLabel[item.status] || item.status} />
                                                <MetaTag label={`Prioritaet ${item.priority}`} />
                                                <MetaTag label={`Aufwand ${item.effort}`} />
                                                {item.updated_at ? <MetaTag label={`Aktualisiert ${item.updated_at}`} /> : null}
                                            </div>
                                        </div>
                                    ) : null}
                                </button>
                            ))}
                        </div>
                    </div>
                </section>

                <section className="rounded-[28px] border border-white/8 bg-[linear-gradient(160deg,rgba(13,17,22,0.95),rgba(10,15,21,0.94))] p-5 shadow-[0_20px_70px_rgba(0,0,0,0.34)] sm:p-6">
                    <div className="flex items-center gap-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-2xl border border-cyan-300/20 bg-cyan-400/10">
                            <NotePencil size={18} weight="duotone" className="text-cyan-100" />
                        </div>
                        <div>
                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-cyan-100/75">Neuer Eintrag</div>
                            <div className="mt-1 text-lg font-black text-white">Roadmap-Punkt anlegen</div>
                        </div>
                    </div>

                    <form onSubmit={submitCreate} className="mt-5 grid gap-3 xl:grid-cols-[1.05fr_1.2fr_0.9fr_0.9fr_0.6fr_0.6fr_0.5fr_0.5fr_auto]">
                        <input value={createForm.data.title} onChange={(event) => createForm.setData('title', event.target.value)} className={fieldClassName} placeholder="Titel" />
                        <input value={createForm.data.summary} onChange={(event) => createForm.setData('summary', event.target.value)} className={fieldClassName} placeholder="Kurze Beschreibung" />
                        <select value={createForm.data.category} onChange={(event) => createForm.setData('category', event.target.value)} className={fieldClassName}>
                            {categoryOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>
                        <input value={Array.isArray(createForm.data.tags) ? createForm.data.tags.join(', ') : ''} onChange={(event) => createForm.setData('tags', parseTags(event.target.value))} className={fieldClassName} placeholder="Tags, mit Komma" />
                        <select value={createForm.data.status} onChange={(event) => createForm.setData('status', event.target.value)} className={fieldClassName}>
                            {statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>
                        <select value={createForm.data.size_bucket} onChange={(event) => createForm.setData('size_bucket', event.target.value)} className={fieldClassName}>
                            {sizeOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>
                        <select value={createForm.data.priority} onChange={(event) => createForm.setData('priority', Number(event.target.value))} className={fieldClassName}>
                            {[1, 2, 3, 4, 5].map((value) => <option key={value} value={value}>P{value}</option>)}
                        </select>
                        <select value={createForm.data.effort} onChange={(event) => createForm.setData('effort', Number(event.target.value))} className={fieldClassName}>
                            {[1, 2, 3, 4, 5].map((value) => <option key={value} value={value}>E{value}</option>)}
                        </select>
                        <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-[18px] border border-cyan-300/20 bg-cyan-400/10 px-4 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-cyan-100 transition-all hover:border-cyan-300/30 hover:bg-cyan-400/15 disabled:opacity-60" disabled={createForm.processing}>
                            <ArrowBendUpRight size={14} weight="bold" />
                            Anlegen
                        </button>
                    </form>
                </section>

                <section className="rounded-[28px] border border-white/8 bg-[linear-gradient(180deg,rgba(16,16,20,0.96),rgba(15,15,18,0.98))] shadow-[0_20px_70px_rgba(0,0,0,0.34)]">
                    <div className="border-b border-white/8 px-5 py-4 sm:px-6">
                        <div className="flex flex-wrap items-end justify-between gap-4">
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.2em] text-white/42">Alle Eintraege</div>
                                <div className="mt-1 text-lg font-black text-white">Roadmap-Liste</div>
                            </div>

                            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                                <input value={filters.query} onChange={(event) => setFilters((current) => ({ ...current, query: event.target.value }))} className={fieldClassName} placeholder="Suche..." />
                                <select value={filters.status} onChange={(event) => setFilters((current) => ({ ...current, status: event.target.value }))} className={fieldClassName}>
                                    <option value="all">Alle Status</option>
                                    {statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                                </select>
                                <select value={filters.category} onChange={(event) => setFilters((current) => ({ ...current, category: event.target.value }))} className={fieldClassName}>
                                    <option value="all">Alle Kategorien</option>
                                    {categoryOptions.map((option) => <option key={option.value} value={option.value}>{categoryLabel[option.value] || option.label}</option>)}
                                </select>
                                <select value={filters.size} onChange={(event) => setFilters((current) => ({ ...current, size: event.target.value }))} className={fieldClassName}>
                                    <option value="all">Alle Groessen</option>
                                    {sizeOptions.map((option) => <option key={option.value} value={option.value}>{sizeLabel[option.value] || option.label}</option>)}
                                </select>
                                <select value={filters.tag} onChange={(event) => setFilters((current) => ({ ...current, tag: event.target.value }))} className={fieldClassName}>
                                    <option value="all">Alle Tags</option>
                                    {availableTags.map((tag) => <option key={tag} value={tag}>{tag}</option>)}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="px-3 py-2 sm:px-4">
                        {orderedStatuses.map((statusKey) => (
                            <RoadmapStatusGroup
                                key={statusKey}
                                title={statusLabel[statusKey]}
                                items={filteredGroups[statusKey] || []}
                                openItems={openItems}
                                toggleItem={toggleItem}
                                onUpdate={updateItem}
                                onSubmitComment={submitComment}
                                commentDrafts={commentDrafts}
                                setCommentDrafts={setCommentDrafts}
                                tagDrafts={tagDrafts}
                                setTagDrafts={setTagDrafts}
                                addTagToItem={addTagToItem}
                                removeTagFromItem={removeTagFromItem}
                                statusOptions={statusOptions}
                            />
                        ))}
                    </div>
                </section>
            </div>
        </RoadmapLayout>
    );
}

function SummaryCard({ icon: Icon, label, value }) {
    return (
        <div className="rounded-[22px] border border-white/8 bg-white/[0.04] px-4 py-4">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">{label}</div>
                    <div className="mt-2 text-3xl font-black tracking-[-0.05em] text-white">{value}</div>
                </div>
                <div className="rounded-2xl border border-white/8 bg-black/20 p-2.5">
                    <Icon size={16} weight="duotone" className="text-white/88" />
                </div>
            </div>
        </div>
    );
}

function RoadmapStatusGroup({ title, items, openItems, toggleItem, onUpdate, onSubmitComment, commentDrafts, setCommentDrafts, tagDrafts, setTagDrafts, addTagToItem, removeTagFromItem, statusOptions }) {
    return (
        <div className="mb-4">
            <div className="px-2 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-white/38">
                {title} · {items.length}
            </div>
            <div className="overflow-hidden rounded-[24px] border border-white/8 bg-black/15">
                {items.length === 0 ? (
                    <div className="px-5 py-6 text-sm text-white/40">Keine Eintraege in diesem Bereich.</div>
                ) : items.map((item, index) => (
                    <RoadmapListRow
                        key={item.id}
                        item={item}
                        isOpen={openItems.has(item.id)}
                        toggleItem={toggleItem}
                        onUpdate={onUpdate}
                        onSubmitComment={onSubmitComment}
                        commentDrafts={commentDrafts}
                        setCommentDrafts={setCommentDrafts}
                        tagDrafts={tagDrafts}
                        setTagDrafts={setTagDrafts}
                        addTagToItem={addTagToItem}
                        removeTagFromItem={removeTagFromItem}
                        statusOptions={statusOptions}
                        bordered={index !== items.length - 1}
                    />
                ))}
            </div>
        </div>
    );
}

function RoadmapListRow({ item, isOpen, toggleItem, onUpdate, onSubmitComment, commentDrafts, setCommentDrafts, tagDrafts, setTagDrafts, addTagToItem, removeTagFromItem, statusOptions, bordered }) {
    return (
        <article className={bordered ? 'border-b border-white/8' : ''}>
            <div className="flex items-start gap-4 px-4 py-4 sm:px-5">
                <button type="button" onClick={() => toggleItem(item.id)} className="min-w-0 flex-1 text-left">
                    <div className="flex flex-wrap items-center gap-3">
                        <span className={`inline-flex rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] ${categoryTone[item.category] || categoryTone.mid}`}>
                            {categoryLabel[item.category] || item.category}
                        </span>
                        <h3 className="text-base font-black tracking-[-0.02em] text-white">{item.title}</h3>
                    </div>

                    <div className="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-[13px] text-slate-300">
                        <RowMeta icon={ClockClockwise} label={item.updated_at ? `aktualisiert ${item.updated_at}` : 'kuerzlich aktualisiert'} />
                        <RowScore value={normalizeScore(item.weighted_score)} />
                        <RowMeta icon={User} label={item.creator?.name || item.updater?.name || 'Team'} />
                        <RowMeta icon={ChatCircleDots} label={`${item.comments_count} Kommentare`} />
                        <RowMeta icon={Lightning} label={`Prioritaet ${item.priority} · Aufwand ${item.effort}`} />
                    </div>
                </button>

                <div className="flex items-center gap-3">
                    <div className={`hidden rounded-full border px-3 py-1.5 text-[11px] font-black uppercase tracking-[0.14em] sm:inline-flex ${statusTone[item.status] || statusTone.planned}`}>
                        {statusLabel[item.status] || item.status}
                    </div>
                    <button type="button" onClick={() => toggleItem(item.id)} className="inline-flex h-10 w-10 items-center justify-center rounded-[14px] border border-emerald-300/15 bg-emerald-400/10 text-emerald-100 transition-all hover:border-emerald-300/25 hover:bg-emerald-400/15">
                        {isOpen ? <CaretDown size={18} weight="bold" /> : <CaretRight size={18} weight="bold" />}
                    </button>
                </div>
            </div>

            {isOpen ? (
                <div className="border-t border-white/8 bg-[linear-gradient(180deg,rgba(255,255,255,0.03),rgba(255,255,255,0.015))] px-4 py-4 sm:px-5">
                    <div className="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
                        <div className="space-y-4">
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">Beschreibung</div>
                                <p className="mt-2 text-sm leading-7 text-slate-300">{item.summary}</p>
                            </div>

                            <div className="flex flex-wrap gap-2">
                                <MetaTag label={sizeLabel[item.size_bucket] || item.size_bucket} />
                                <MetaTag label={`Key ${item.key}`} />
                            </div>

                            <div className="rounded-[20px] border border-white/8 bg-white/[0.03] p-3">
                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">Tags</div>
                                <div className="mt-3 flex flex-wrap gap-2">
                                    {(item.tags || []).length === 0 ? (
                                        <span className="text-sm text-white/38">Keine Tags hinterlegt.</span>
                                    ) : (
                                        item.tags.map((tag) => (
                                            <button key={tag} type="button" onClick={() => removeTagFromItem(item, tag)} className="inline-flex items-center gap-2 rounded-full border border-cyan-300/18 bg-cyan-400/10 px-3 py-1.5 text-[11px] font-black text-cyan-100 transition-all hover:border-rose-300/25 hover:bg-rose-400/10 hover:text-rose-100">
                                                {tag}
                                                <span className="text-white/60">x</span>
                                            </button>
                                        ))
                                    )}
                                </div>

                                <div className="mt-3 flex flex-col gap-3 sm:flex-row">
                                    <input value={tagDrafts[item.id] || ''} onChange={(event) => setTagDrafts((current) => ({ ...current, [item.id]: event.target.value }))} className={fieldClassName} placeholder="Neue Tags mit Komma trennen" />
                                    <button type="button" onClick={() => addTagToItem(item)} className="inline-flex items-center justify-center gap-2 rounded-[16px] border border-cyan-300/18 bg-cyan-400/10 px-4 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-cyan-100 transition-all hover:border-cyan-300/30 hover:bg-cyan-400/15">
                                        <Sparkle size={14} weight="bold" />
                                        Tags hinzufuegen
                                    </button>
                                </div>
                            </div>

                            <div className="space-y-2">
                                {item.comments.map((comment) => (
                                    <div key={comment.id} className="rounded-[18px] border border-white/8 bg-black/20 px-3 py-3">
                                        <div className="flex items-center justify-between gap-3">
                                            <div className="text-[11px] font-black uppercase tracking-[0.14em] text-white">{comment.user?.name || 'User'}</div>
                                            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-white/35">{comment.created_at}</div>
                                        </div>
                                        <p className="mt-2 text-sm leading-6 text-slate-300">{comment.body}</p>
                                    </div>
                                ))}
                            </div>

                            <div className="rounded-[20px] border border-white/8 bg-white/[0.03] p-3">
                                <textarea value={commentDrafts[item.id] || ''} onChange={(event) => setCommentDrafts((current) => ({ ...current, [item.id]: event.target.value }))} className={`${fieldClassName} min-h-[92px]`} placeholder="Kommentar, Entscheidung, Hinweis oder Bedenken hinzufuegen..." />
                                <div className="mt-3 flex justify-end">
                                    <button type="button" onClick={() => onSubmitComment(item.id)} className="inline-flex items-center gap-2 rounded-[16px] border border-white/10 bg-white/[0.05] px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.14em] text-white/85 transition-all hover:border-white/20 hover:bg-white/[0.08]">
                                        <ChatCircleDots size={14} weight="bold" />
                                        Kommentar speichern
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div className="grid gap-3 self-start rounded-[22px] border border-white/8 bg-black/15 p-4">
                            <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">Steuerung</div>
                            <select value={item.status} onChange={(event) => onUpdate(item.id, 'status', event.target.value)} className={fieldClassName}>
                                {statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                            </select>
                            <select value={item.priority} onChange={(event) => onUpdate(item.id, 'priority', Number(event.target.value))} className={fieldClassName}>
                                {[1, 2, 3, 4, 5].map((value) => <option key={value} value={value}>Prioritaet {value}</option>)}
                            </select>
                            <select value={item.effort} onChange={(event) => onUpdate(item.id, 'effort', Number(event.target.value))} className={fieldClassName}>
                                {[1, 2, 3, 4, 5].map((value) => <option key={value} value={value}>Aufwand {value}</option>)}
                            </select>

                            <div className="grid gap-3 sm:grid-cols-2">
                                <InfoTile label="Score" value={`${normalizeScore(item.weighted_score)}%`} />
                                <InfoTile label="Status" value={statusLabel[item.status] || item.status} />
                                <InfoTile label="Kategorie" value={categoryLabel[item.category] || item.category} />
                                <InfoTile label="Groesse" value={sizeLabel[item.size_bucket] || item.size_bucket} />
                            </div>
                        </div>
                    </div>
                </div>
            ) : null}
        </article>
    );
}

function RowMeta({ icon: Icon, label }) {
    return (
        <div className="inline-flex items-center gap-2">
            <Icon size={15} weight="regular" className="text-white/55" />
            <span>{label}</span>
        </div>
    );
}

function RowScore({ value }) {
    const isHigh = value >= 70;
    const isMid = value >= 45;
    const tone = isHigh ? 'text-emerald-300' : isMid ? 'text-amber-300' : 'text-rose-300';

    return (
        <div className={`inline-flex items-center gap-2 font-black ${tone}`}>
            <TrendUp size={15} weight="bold" />
            {value}%
        </div>
    );
}

function MetaTag({ label }) {
    return (
        <span className="inline-flex rounded-full border border-white/10 bg-white/[0.04] px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.14em] text-white/68">
            {label}
        </span>
    );
}

function InfoTile({ label, value }) {
    return (
        <div className="rounded-[18px] border border-white/8 bg-white/[0.03] px-3 py-3">
            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-white/40">{label}</div>
            <div className="mt-1 text-sm font-black text-white">{value}</div>
        </div>
    );
}

function normalizeScore(score) {
    return Math.max(0, Math.min(100, Math.round(Number(score) || 0)));
}

function parseTags(input) {
    if (Array.isArray(input)) {
        return uniqueTags(input);
    }

    return uniqueTags(String(input || '').split(','));
}

function uniqueTags(tags) {
    const seen = new Set();

    return tags
        .map((tag) => String(tag).trim())
        .filter((tag) => tag.length > 0)
        .filter((tag) => {
            const normalized = tag.toLowerCase();
            if (seen.has(normalized)) return false;
            seen.add(normalized);
            return true;
        })
        .slice(0, 12);
}

const fieldClassName = 'w-full rounded-[16px] border border-white/10 bg-black/20 px-4 py-3 text-sm text-white outline-none transition-all placeholder:text-white/28 focus:border-cyan-300/30 focus:bg-black/25';
