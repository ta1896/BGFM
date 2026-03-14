import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { Scroll, Plus, MagnifyingGlass, Funnel, PencilSimple, Trash } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

const EVENT_COLORS = {
    goal: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    chance: 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    foul: 'bg-orange-500/10 text-orange-400 border-orange-500/20',
    yellow_card: 'bg-yellow-400/10 text-yellow-400 border-yellow-400/20',
    red_card: 'bg-red-500/10 text-red-400 border-red-500/20',
    injury: 'bg-pink-500/10 text-pink-400 border-pink-500/20',
    substitution: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    phase: 'bg-slate-500/10 text-[var(--text-muted)] border-slate-500/20',
};

export default function Index({ templates, eventTypes, moods, styles, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedEventTypes, setSelectedEventTypes] = useState(filters.event_types || []);
    const [mood, setMood] = useState(filters.mood || '');
    const [style, setStyle] = useState(filters.commentator_style || '');

    const applyFilters = () => {
        router.get(route('admin.ticker-templates.index'), {
            search,
            event_types: selectedEventTypes,
            mood,
            commentator_style: style,
        }, { preserveState: true, replace: true });
    };

    const resetFilters = () => {
        setSearch('');
        setSelectedEventTypes([]);
        setMood('');
        setStyle('');
        router.get(route('admin.ticker-templates.index'));
    };

    const handleDelete = (id) => {
        if (confirm('Vorlage wirklich loeschen?')) {
            router.delete(route('admin.ticker-templates.destroy', id));
        }
    };

    const toggleEventType = (type) => {
        setSelectedEventTypes((previous) => (
            previous.includes(type) ? previous.filter((item) => item !== type) : [...previous, type]
        ));
    };

    return (
        <AdminLayout>
            <Head title="Ticker Vorlagen" />

            <div className="space-y-6">
                <PageHeader
                    eyebrow="Kommentierung"
                    title="Ticker Vorlagen"
                    actions={
                        <Link href={route('admin.ticker-templates.create')} className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-6 py-2.5 font-black text-white">
                            <Plus size={18} weight="bold" />
                            Neue Vorlage
                        </Link>
                    }
                />

                <PageReveal>
                    <SectionCard title="Filter" icon={Funnel} bodyClassName="space-y-4 p-5">
                        <div className="grid gap-4 md:grid-cols-3">
                            <div className="relative">
                                <MagnifyingGlass size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                                <input
                                    type="text"
                                    placeholder="Texte durchsuchen..."
                                    value={search}
                                    onChange={(event) => setSearch(event.target.value)}
                                    onKeyDown={(event) => event.key === 'Enter' && applyFilters()}
                                    className="sim-input w-full pl-9"
                                />
                            </div>
                            <select className="sim-select w-full" value={mood} onChange={(event) => setMood(event.target.value)}>
                                <option value="">Alle Stimmungen</option>
                                {Object.entries(moods).map(([key, value]) => <option key={key} value={key}>{value}</option>)}
                            </select>
                            <select className="sim-select w-full" value={style} onChange={(event) => setStyle(event.target.value)}>
                                <option value="">Alle Stile</option>
                                {Object.entries(styles).map(([key, value]) => <option key={key} value={key}>{value}</option>)}
                            </select>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {Object.entries(eventTypes).map(([key, value]) => (
                                <button
                                    key={key}
                                    type="button"
                                    onClick={() => toggleEventType(key)}
                                    className={`rounded-lg border px-3 py-1 text-[10px] font-black uppercase tracking-widest transition-all ${
                                        selectedEventTypes.includes(key)
                                            ? (EVENT_COLORS[key] || 'border-cyan-500/30 bg-cyan-500/20 text-cyan-400')
                                            : 'border-[var(--border-pillar)] bg-[var(--bg-content)] text-[var(--text-muted)] hover:text-[var(--text-main)]'
                                    }`}
                                >
                                    {value}
                                </button>
                            ))}
                        </div>
                        <div className="flex gap-3 pt-2">
                            <button type="button" onClick={applyFilters} className="rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-5 py-2 text-xs font-black text-white">Anwenden</button>
                            <button type="button" onClick={resetFilters} className="rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] px-5 py-2 text-xs font-black text-[var(--text-muted)]">Zuruecksetzen</button>
                        </div>
                    </SectionCard>
                </PageReveal>

                <PageReveal delay={80}>
                    <SectionCard title="Vorlagen" icon={Scroll} bodyClassName="overflow-hidden">
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                    <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Event</th>
                                    <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Text</th>
                                    <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Stimmung</th>
                                    <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Stil</th>
                                    <th className="px-5 py-3 text-center text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Prio</th>
                                    <th className="px-5 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-800/50">
                                {templates.data.map((template) => (
                                    <tr key={template.id} className="group transition-colors hover:bg-[var(--bg-content)]/20">
                                        <td className="px-5 py-3">
                                            <span className={`rounded border px-2 py-0.5 text-[10px] font-black uppercase tracking-widest ${EVENT_COLORS[template.event_type] || 'border-slate-500/20 bg-slate-500/10 text-[var(--text-muted)]'}`}>
                                                {eventTypes[template.event_type] ?? template.event_type}
                                            </span>
                                        </td>
                                        <td className="max-w-md px-5 py-3">
                                            <p className="line-clamp-2 font-mono text-sm text-slate-300">{template.text}</p>
                                        </td>
                                        <td className="px-5 py-3 text-xs text-[var(--text-muted)]">{moods[template.mood] ?? template.mood}</td>
                                        <td className="px-5 py-3 text-xs text-[var(--text-muted)]">{styles[template.commentator_style] ?? template.commentator_style}</td>
                                        <td className="px-5 py-3 text-center">
                                            <span className={`rounded border px-2 py-0.5 text-[10px] font-black uppercase ${
                                                template.priority === 'high' ? 'border-red-500/20 bg-red-500/10 text-red-400' :
                                                template.priority === 'low' ? 'border-slate-600 bg-slate-700 text-[var(--text-muted)]' :
                                                'border-indigo-500/20 bg-indigo-500/10 text-indigo-400'
                                            }`}>
                                                {template.priority}
                                            </span>
                                        </td>
                                        <td className="px-5 py-3 text-right">
                                            <div className="flex items-center justify-end gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                                <Link href={route('admin.ticker-templates.edit', template.id)} className="rounded-lg p-1.5 text-[var(--text-muted)] transition hover:bg-cyan-500/10 hover:text-cyan-400">
                                                    <PencilSimple size={16} weight="bold" />
                                                </Link>
                                                <button type="button" onClick={() => handleDelete(template.id)} className="rounded-lg p-1.5 text-[var(--text-muted)] transition hover:bg-red-500/10 hover:text-red-400">
                                                    <Trash size={16} weight="bold" />
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {templates.data.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-5 py-12 text-center text-sm italic text-[var(--text-muted)]">Keine Vorlagen gefunden.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>

                        {templates.links && (
                            <div className="flex justify-center gap-2 border-t border-[var(--border-muted)] p-4">
                                {templates.links.map((link, index) => (
                                    <PaginationLink
                                        key={index}
                                        link={link}
                                        className={`rounded-xl border px-4 py-2 text-sm font-bold transition-all ${
                                            link.active
                                                ? 'border-cyan-400 bg-cyan-500 text-white'
                                                : 'border-[var(--border-muted)] bg-[var(--bg-content)]/50 text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                                        }`}
                                        disabledClassName="cursor-default rounded-xl border border-[var(--border-pillar)]/30 bg-[var(--bg-pillar)]/50 px-4 py-2 text-sm font-bold text-slate-600 opacity-50"
                                    />
                                ))}
                            </div>
                        )}
                    </SectionCard>
                </PageReveal>
            </div>
        </AdminLayout>
    );
}
