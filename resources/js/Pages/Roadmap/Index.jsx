import React, { useMemo, useState, useEffect } from 'react';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import RoadmapLayout from '@/Layouts/RoadmapLayout';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    verticalListSortingStrategy,
    rectSortingStrategy,
    useSortable,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
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
    X,
    DotsSixVertical,
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

export default function Index({ items: initialItems, groups: initialGroups, topItems, statusOptions, categoryOptions, sizeOptions }) {
    const { auth } = usePage().props;
    const [items, setItems] = useState(initialItems);
    const [selectedItem, setSelectedItem] = useState(null);
    const [isReordering, setIsReordering] = useState(false);

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
    const [filters, setFilters] = useState({
        query: '',
        status: 'all',
        category: 'all',
        size: 'all',
        tag: 'all',
    });

    useEffect(() => {
        setItems(initialItems);
    }, [initialItems]);

    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

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

    const filteredItems = useMemo(() => {
        const query = filters.query.trim().toLowerCase();
        return items.filter((item) => {
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
        });
    }, [filters, items]);

    const handleDragEnd = (event) => {
        const { active, over } = event;

        if (active && over && active.id !== over.id) {
            setItems((current) => {
                const oldIndex = current.findIndex((i) => i.id === active.id);
                const newIndex = current.findIndex((i) => i.id === over.id);
                const nextItems = arrayMove(current, oldIndex, newIndex);

                // Send to backend
                const reorderData = nextItems.map((item, index) => ({
                    id: item.id,
                    sort_order: index + 1,
                }));

                router.post(route('roadmap-board.reorder'), { items: reorderData }, {
                    preserveScroll: true,
                    onStart: () => setIsReordering(true),
                    onFinish: () => setIsReordering(false),
                });

                return nextItems;
            });
        }
    };

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
        router.patch(route('roadmap-board.items.update', itemId), { [field]: value }, {
            preserveScroll: true,
            onSuccess: (page) => {
                // If the updated item is currently open in modal, update it
                const updatedItem = page.props.items.find(i => i.id === itemId);
                if (selectedItem?.id === itemId) {
                    setSelectedItem(updatedItem);
                }
            }
        });
    };

    const submitComment = (itemId) => {
        const body = (commentDrafts[itemId] || '').trim();
        if (!body) return;

        router.post(route('roadmap-board.comments.store', itemId), { body }, {
            preserveScroll: true,
            onSuccess: (page) => {
                setCommentDrafts((current) => ({ ...current, [itemId]: '' }));
                const updatedItem = page.props.items.find(i => i.id === itemId);
                if (selectedItem?.id === itemId) {
                    setSelectedItem(updatedItem);
                }
            },
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

    return (
        <RoadmapLayout user={auth?.user}>
            <Head title="Roadmap Board" />

            <div className="space-y-6 pb-10">
                {/* Header Section */}
                <section className="rounded-[28px] border border-white/8 bg-[linear-gradient(160deg,rgba(12,16,22,0.94),rgba(15,20,27,0.9))] p-5 shadow-[0_20px_70px_rgba(0,0,0,0.34)] sm:p-6">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div className="max-w-2xl">
                            <div className="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200/75">Community Roadmap</div>
                            <h2 className="mt-2 text-2xl font-black tracking-[-0.04em] text-white sm:text-[2.2rem]">
                                Roadmap Board Redesign
                            </h2>
                            <p className="mt-3 text-sm leading-7 text-slate-300">
                                Erkunde unsere Vision. Sortiere die Kacheln per Drag & Drop, um Prioritaeten festzulegen. Klicke auf eine Kachel fuer Details.
                            </p>
                        </div>
                        <div className="flex items-center gap-4">
                            {isReordering && (
                                <div className="flex items-center gap-2 rounded-full bg-cyan-400/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.1em] text-cyan-100 border border-cyan-300/20">
                                    <div className="h-2 w-2 animate-pulse rounded-full bg-cyan-400"></div>
                                    Speichert...
                                </div>
                            )}
                            <div className="rounded-[22px] border border-white/10 bg-white/[0.04] px-4 py-3">
                                <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/45">Angemeldet als</div>
                                <div className="mt-1 text-sm font-black text-white">{auth?.user?.name}</div>
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <SummaryCard icon={Stack} label="Eintraege" value={stats.total} />
                        <SummaryCard icon={Target} label="Aktiv" value={stats.active} />
                        <SummaryCard icon={ChatCircleDots} label="Kommentare" value={stats.comments} />
                        <SummaryCard icon={Checks} label="Fertig" value={stats.done} />
                    </div>
                </section>

                {/* Filters Section */}
                <section className="rounded-[24px] border border-white/8 bg-black/20 p-4">
                    <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
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
                </section>

                {/* Create Item Form (Collapsed/Simple) */}
                <section className="rounded-[28px] border border-white/8 bg-[linear-gradient(160deg,rgba(13,17,22,0.95),rgba(10,15,21,0.94))] p-5 shadow-[0_20px_70px_rgba(0,0,0,0.34)] sm:p-6">
                    <form onSubmit={submitCreate} className="grid gap-3 lg:grid-cols-[1fr_2fr_1fr_1fr_auto]">
                        <input value={createForm.data.title} onChange={(event) => createForm.setData('title', event.target.value)} className={fieldClassName} placeholder="Titel des neuen Konzepts" />
                        <input value={createForm.data.summary} onChange={(event) => createForm.setData('summary', event.target.value)} className={fieldClassName} placeholder="Kurze Beschreibung..." />
                        <select value={createForm.data.category} onChange={(event) => createForm.setData('category', event.target.value)} className={fieldClassName}>
                            {categoryOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>
                        <select value={createForm.data.status} onChange={(event) => createForm.setData('status', event.target.value)} className={fieldClassName}>
                            {statusOptions.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
                        </select>
                        <button type="submit" className="inline-flex items-center justify-center gap-2 rounded-[18px] border border-cyan-300/20 bg-cyan-400/10 px-6 py-3 text-[11px] font-black uppercase tracking-[0.14em] text-cyan-100 transition-all hover:border-cyan-300/30 hover:bg-cyan-400/15 disabled:opacity-60" disabled={createForm.processing}>
                            <ArrowBendUpRight size={14} weight="bold" />
                            Anlegen
                        </button>
                    </form>
                </section>

                {/* Tiles Grid with DnD */}
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragEnd={handleDragEnd}
                >
                    <SortableContext
                        items={filteredItems.map(i => i.id)}
                        strategy={rectSortingStrategy}
                    >
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            {filteredItems.map((item) => (
                                <SortableItem
                                    key={item.id}
                                    item={item}
                                    onClick={() => setSelectedItem(item)}
                                />
                            ))}
                        </div>
                    </SortableContext>
                </DndContext>
            </div>

            {/* Detail Overlay / Modal */}
            {selectedItem && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-md">
                    <div className="relative w-full max-w-4xl max-h-[90vh] overflow-hidden rounded-[32px] border border-white/12 bg-[#0c1016] shadow-[0_50px_100px_rgba(0,0,0,0.6)]">
                        {/* Modal Header */}
                        <div className="flex items-center justify-between border-b border-white/8 px-6 py-4">
                            <div className="flex items-center gap-3">
                                <span className={`inline-flex rounded-full border px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] ${categoryTone[selectedItem.category] || categoryTone.mid}`}>
                                    {categoryLabel[selectedItem.category] || selectedItem.category}
                                </span>
                                <h3 className="text-xl font-black text-white">{selectedItem.title}</h3>
                            </div>
                            <button
                                onClick={() => setSelectedItem(null)}
                                className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/8 bg-white/5 text-white/60 transition-all hover:bg-white/10 hover:text-white"
                            >
                                <X size={20} weight="bold" />
                            </button>
                        </div>

                        {/* Modal Content */}
                        <div className="overflow-y-auto px-6 py-6" style={{ maxHeight: 'calc(90vh - 80px)' }}>
                            <div className="grid gap-8 lg:grid-cols-[1.5fr_1fr]">
                                <div className="space-y-6">
                                    <section>
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42 mb-3">Beschreibung</div>
                                        <p className="text-base leading-8 text-slate-300 whitespace-pre-wrap">{selectedItem.summary}</p>
                                    </section>

                                    <section className="rounded-[24px] border border-white/8 bg-white/[0.03] p-4">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42 mb-4">Tags & Metadaten</div>
                                        <div className="flex flex-wrap gap-2 mb-4">
                                            {selectedItem.tags?.map(tag => (
                                                <span key={tag} className="inline-flex items-center gap-2 rounded-full border border-cyan-300/18 bg-cyan-400/10 px-3 py-1.5 text-[11px] font-black text-cyan-100">
                                                    {tag}
                                                    <button onClick={() => removeTagFromItem(selectedItem, tag)} className="hover:text-rose-400 transition-colors">
                                                        <X size={12} weight="bold" />
                                                    </button>
                                                </span>
                                            ))}
                                        </div>
                                        <div className="flex gap-2">
                                            <input
                                                value={tagDrafts[selectedItem.id] || ''}
                                                onChange={(e) => setTagDrafts(c => ({ ...c, [selectedItem.id]: e.target.value }))}
                                                className={fieldClassName}
                                                placeholder="Tags hinzufuegen..."
                                                onKeyDown={(e) => e.key === 'Enter' && addTagToItem(selectedItem)}
                                            />
                                            <button onClick={() => addTagToItem(selectedItem)} className="rounded-2xl bg-white/5 px-4 text-white/80 hover:bg-white/10">
                                                <Sparkle size={18} />
                                            </button>
                                        </div>
                                    </section>

                                    <section className="space-y-4">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">Diskussion ({selectedItem.comments?.length || 0})</div>
                                        <div className="space-y-3">
                                            {selectedItem.comments?.map(comment => (
                                                <div key={comment.id} className="rounded-[20px] border border-white/5 bg-white/[0.02] p-4 text-sm">
                                                    <div className="flex justify-between text-[11px] font-black uppercase tracking-[0.1em] text-white/50 mb-2">
                                                        <span>{comment.user?.name}</span>
                                                        <span>{comment.created_at}</span>
                                                    </div>
                                                    <p className="text-slate-300 leading-6">{comment.body}</p>
                                                </div>
                                            ))}
                                        </div>
                                        <div className="pt-2">
                                            <textarea
                                                value={commentDrafts[selectedItem.id] || ''}
                                                onChange={(e) => setCommentDrafts(c => ({ ...c, [selectedItem.id]: e.target.value }))}
                                                className={`${fieldClassName} min-h-[100px] resize-none`}
                                                placeholder="Deine Gedanken hier..."
                                            />
                                            <div className="mt-3 flex justify-end">
                                                <button
                                                    onClick={() => submitComment(selectedItem.id)}
                                                    className="inline-flex items-center gap-2 rounded-2xl bg-cyan-400/10 border border-cyan-300/20 px-6 py-3 text-[11px] font-black uppercase tracking-[0.1em] text-cyan-100 hover:bg-cyan-400/15"
                                                >
                                                    <ChatCircleDots size={16} />
                                                    Kommentieren
                                                </button>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div className="space-y-6">
                                    <section className="rounded-[28px] border border-white/8 bg-black/40 p-5 space-y-4">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-white/42">Eigenschaften anpassen</div>
                                        <div className="space-y-4">
                                            <div>
                                                <label className="text-[10px] font-black text-white/30 uppercase block mb-2">Status</label>
                                                <select
                                                    value={selectedItem.status}
                                                    onChange={(e) => updateItem(selectedItem.id, 'status', e.target.value)}
                                                    className={fieldClassName}
                                                >
                                                    {statusOptions.map(opt => <option key={opt.value} value={opt.value}>{opt.label}</option>)}
                                                </select>
                                            </div>
                                            <div className="grid grid-cols-2 gap-3">
                                                <div>
                                                    <label className="text-[10px] font-black text-white/30 uppercase block mb-2">Prioritaet</label>
                                                    <select
                                                        value={selectedItem.priority}
                                                        onChange={(e) => updateItem(selectedItem.id, 'priority', Number(e.target.value))}
                                                        className={fieldClassName}
                                                    >
                                                        {[1, 2, 3, 4, 5].map(v => <option key={v} value={v}>P{v}</option>)}
                                                    </select>
                                                </div>
                                                <div>
                                                    <label className="text-[10px] font-black text-white/30 uppercase block mb-2">Aufwand</label>
                                                    <select
                                                        value={selectedItem.effort}
                                                        onChange={(e) => updateItem(selectedItem.id, 'effort', Number(e.target.value))}
                                                        className={fieldClassName}
                                                    >
                                                        {[1, 2, 3, 4, 5].map(v => <option key={v} value={v}>E{v}</option>)}
                                                    </select>
                                                </div>
                                            </div>
                                            <div className="pt-4 border-t border-white/5 grid grid-cols-2 gap-3">
                                                <InfoTile label="Score" value={`${normalizeScore(selectedItem.weighted_score)}%`} />
                                                <InfoTile label="Groesse" value={sizeLabel[selectedItem.size_bucket] || selectedItem.size_bucket} />
                                                <InfoTile label="Erstellt von" value={selectedItem.creator?.name || 'Unbekannt'} />
                                                <InfoTile label="Update" value={selectedItem.updated_at || 'Just now'} />
                                            </div>
                                        </div>
                                    </section>

                                    <div className="rounded-[24px] border border-emerald-300/10 bg-emerald-400/5 p-5 text-center">
                                        <div className="text-[10px] font-black uppercase tracking-[0.16em] text-emerald-200/50 mb-2">Item Key</div>
                                        <div className="text-sm font-mono text-emerald-100/40 break-all">{selectedItem.key}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </RoadmapLayout>
    );
}

function SortableItem({ item, onClick }) {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: item.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        zIndex: isDragging ? 50 : 'auto',
        opacity: isDragging ? 0.3 : 1,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className="group relative h-full"
        >
            <div
                className="flex h-full flex-col rounded-[24px] border border-white/8 bg-white/[0.04] p-5 transition-all hover:border-white/16 hover:bg-white/[0.06] hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] cursor-pointer"
                onClick={onClick}
            >
                <div className="flex items-start justify-between gap-3">
                    <span className={`inline-flex rounded-full border px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.14em] ${categoryTone[item.category] || categoryTone.mid}`}>
                        {categoryLabel[item.category] || item.category}
                    </span>
                    <button
                        {...attributes}
                        {...listeners}
                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-white/20 hover:bg-white/5 hover:text-white/60 cursor-grab active:cursor-grabbing"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <DotsSixVertical size={18} weight="bold" />
                    </button>
                </div>

                <h3 className="mt-4 text-[15px] font-black leading-tight text-white group-hover:text-emerald-300 transition-colors">
                    {item.title}
                </h3>

                <p className="mt-3 line-clamp-2 text-[13px] leading-6 text-slate-400">
                    {item.summary}
                </p>

                <div className="mt-auto pt-5">
                    <div className="flex items-center justify-between border-t border-white/6 pt-4">
                        <div className={`rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.12em] ${statusTone[item.status] || statusTone.planned}`}>
                            {statusLabel[item.status] || item.status}
                        </div>
                        <div className="flex items-center gap-1.5 text-amber-200/80">
                            <TrendUp size={14} weight="bold" />
                            <span className="text-[11px] font-black">{normalizeScore(item.weighted_score)}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

function InfoTile({ label, value }) {
    return (
        <div className="rounded-[18px] border border-white/8 bg-white/[0.03] px-3 py-3 text-left">
            <div className="text-[10px] font-black uppercase tracking-[0.14em] text-white/40">{label}</div>
            <div className="mt-1 text-sm font-black text-white truncate">{value}</div>
        </div>
    );
}

function normalizeScore(score) {
    return Math.max(0, Math.min(100, Math.round(Number(score) || 0)));
}

function parseTags(input) {
    if (Array.isArray(input)) return uniqueTags(input);
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
