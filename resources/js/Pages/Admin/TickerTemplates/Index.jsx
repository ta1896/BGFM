import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import PaginationLink from '@/Components/PaginationLink';
import { 
    Scroll, Plus, MagnifyingGlass, Funnel, 
    PencilSimple, Trash, Tag, ChatText
} from '@phosphor-icons/react';
import { motion } from 'framer-motion';

const EVENT_COLORS = {
    goal:         'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
    chance:       'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
    foul:         'bg-orange-500/10 text-orange-400 border-orange-500/20',
    yellow_card:  'bg-yellow-400/10 text-yellow-400 border-yellow-400/20',
    red_card:     'bg-red-500/10 text-red-400 border-red-500/20',
    injury:       'bg-pink-500/10 text-pink-400 border-pink-500/20',
    substitution: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
    phase:        'bg-slate-500/10 text-[var(--text-muted)] border-slate-500/20',
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
        setSearch(''); setSelectedEventTypes([]); setMood(''); setStyle('');
        router.get(route('admin.ticker-templates.index'));
    };

    const handleDelete = (id) => {
        if (confirm('Vorlage wirklich löschen?')) {
            router.delete(route('admin.ticker-templates.destroy', id));
        }
    };

    const toggleEventType = (type) => {
        setSelectedEventTypes(prev =>
            prev.includes(type) ? prev.filter(t => t !== type) : [...prev, type]
        );
    };

    return (
        <AdminLayout>
            <Head title="Ticker Vorlagen" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black text-white tracking-tight uppercase italic">Ticker Vorlagen</h2>
                        <p className="text-[var(--text-muted)] text-[10px] font-black uppercase tracking-[0.2em] mt-1">
                            {templates.total} Vorlagen im System
                        </p>
                    </div>
                    <Link
                        href={route('admin.ticker-templates.create')}
                        className="sim-btn-primary px-6 py-2.5 flex items-center gap-2"
                    >
                        <Plus size={18} weight="bold" />
                        Neue Vorlage
                    </Link>
                </div>

                {/* Filters */}
                <div className="sim-card p-5 space-y-4">
                    <div className="flex items-center gap-2 text-cyan-400 mb-3">
                        <Funnel size={16} weight="bold" />
                        <span className="text-[10px] font-black uppercase tracking-widest">Filter</span>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div className="relative">
                            <MagnifyingGlass size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]" />
                            <input
                                type="text"
                                placeholder="Texte durchsuchen..."
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                                onKeyDown={e => e.key === 'Enter' && applyFilters()}
                                className="sim-input pl-9 w-full"
                            />
                        </div>
                        <select className="sim-select w-full" value={mood} onChange={e => setMood(e.target.value)}>
                            <option value="">Alle Stimmungen</option>
                            {Object.entries(moods).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
                        </select>
                        <select className="sim-select w-full" value={style} onChange={e => setStyle(e.target.value)}>
                            <option value="">Alle Stile</option>
                            {Object.entries(styles).map(([k, v]) => <option key={k} value={k}>{v}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-wrap gap-2 mt-2">
                        {Object.entries(eventTypes).map(([k, v]) => (
                            <button
                                key={k}
                                onClick={() => toggleEventType(k)}
                                className={`px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest border transition-all ${
                                    selectedEventTypes.includes(k)
                                        ? (EVENT_COLORS[k] || 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30')
                                        : 'bg-[var(--bg-content)] border-[var(--border-pillar)] text-[var(--text-muted)] hover:text-white'
                                }`}
                            >
                                {v}
                            </button>
                        ))}
                    </div>
                    <div className="flex gap-3 pt-2">
                        <button onClick={applyFilters} className="sim-btn-primary px-5 py-2 text-xs">Anwenden</button>
                        <button onClick={resetFilters} className="sim-btn-muted px-5 py-2 text-xs">Zurücksetzen</button>
                    </div>
                </div>

                {/* Templates Table */}
                <div className="sim-card overflow-hidden">
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b border-[var(--border-pillar)] bg-[var(--bg-pillar)]/50">
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Event</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Text</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Stimmung</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Stil</th>
                                <th className="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] text-center">Prio</th>
                                <th className="px-5 py-3" />
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-800/50">
                            {templates.data.map((tmpl, idx) => (
                                <motion.tr
                                    key={tmpl.id}
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    transition={{ delay: idx * 0.02 }}
                                    className="hover:bg-[var(--bg-content)]/20 transition-colors group"
                                >
                                    <td className="px-5 py-3">
                                        <span className={`px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border ${EVENT_COLORS[tmpl.event_type] || 'bg-slate-500/10 text-[var(--text-muted)] border-slate-500/20'}`}>
                                            {eventTypes[tmpl.event_type] ?? tmpl.event_type}
                                        </span>
                                    </td>
                                    <td className="px-5 py-3 max-w-md">
                                        <p className="text-sm text-slate-300 line-clamp-2 font-mono">{tmpl.text}</p>
                                    </td>
                                    <td className="px-5 py-3 text-xs text-[var(--text-muted)]">{moods[tmpl.mood] ?? tmpl.mood}</td>
                                    <td className="px-5 py-3 text-xs text-[var(--text-muted)]">{styles[tmpl.commentator_style] ?? tmpl.commentator_style}</td>
                                    <td className="px-5 py-3 text-center">
                                        <span className={`text-[10px] font-black uppercase px-2 py-0.5 rounded border ${
                                            tmpl.priority === 'high' ? 'bg-red-500/10 text-red-400 border-red-500/20' :
                                            tmpl.priority === 'low' ? 'bg-slate-700 text-[var(--text-muted)] border-slate-600' :
                                            'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'
                                        }`}>{tmpl.priority}</span>
                                    </td>
                                    <td className="px-5 py-3 text-right">
                                        <div className="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <Link
                                                href={route('admin.ticker-templates.edit', tmpl.id)}
                                                className="p-1.5 text-[var(--text-muted)] hover:text-cyan-400 hover:bg-cyan-500/10 rounded-lg transition"
                                            >
                                                <PencilSimple size={16} weight="bold" />
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(tmpl.id)}
                                                className="p-1.5 text-[var(--text-muted)] hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                                            >
                                                <Trash size={16} weight="bold" />
                                            </button>
                                        </div>
                                    </td>
                                </motion.tr>
                            ))}
                            {templates.data.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="px-5 py-12 text-center text-[var(--text-muted)] italic text-sm">
                                        Keine Vorlagen gefunden.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>

                    {/* Pagination */}
                    {templates.links && (
                        <div className="flex justify-center gap-2 p-4 border-t border-[var(--border-muted)]">
                            {templates.links.map((link, idx) => (
                                <PaginationLink
                                    key={idx}
                                    link={link}
                                    className={`px-4 py-2 rounded-xl text-sm font-bold transition-all border ${
                                        link.active
                                        ? 'bg-cyan-500 border-cyan-400 text-white'
                                        : 'bg-[var(--bg-content)]/50 border-[var(--border-muted)] text-[var(--text-muted)] hover:bg-[var(--bg-content)] hover:text-white'
                                    }`}
                                    disabledClassName="px-4 py-2 rounded-xl text-sm font-bold border bg-[var(--bg-pillar)]/50 border-[var(--border-pillar)]/30 text-slate-600 cursor-default opacity-50"
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
