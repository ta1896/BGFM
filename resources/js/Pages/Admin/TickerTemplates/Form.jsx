import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Scroll, ArrowLeft, FloppyDisk, Tag, ChatText, Placeholder } from '@phosphor-icons/react';

export default function Form({ tickerTemplate, eventTypes, moods, styles }) {
    const isEdit = !!tickerTemplate;

    const { data, setData, post, put, processing, errors } = useForm({
        event_type:        tickerTemplate?.event_type        ?? '',
        text:              tickerTemplate?.text              ?? '',
        priority:          tickerTemplate?.priority          ?? 'normal',
        mood:              tickerTemplate?.mood              ?? 'neutral',
        commentator_style: tickerTemplate?.commentator_style ?? 'sachlich',
        locale:            tickerTemplate?.locale            ?? 'de',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.ticker-templates.update', tickerTemplate.id));
        } else {
            post(route('admin.ticker-templates.store'));
        }
    };

    const PLACEHOLDERS = ['{player}', '{opponent}', '{club}', '{score}'];

    const insertPlaceholder = (p) => {
        setData('text', data.text + p);
    };

    return (
        <AdminLayout
            header={
                <div className="flex items-center gap-4">
                    <Link href={route('admin.ticker-templates.index')} className="p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition">
                        <ArrowLeft size={20} />
                    </Link>
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-widest text-cyan-400">Ticker Vorlagen</p>
                        <h1 className="text-xl font-bold text-white">{isEdit ? 'Vorlage bearbeiten' : 'Neue Vorlage erstellen'}</h1>
                    </div>
                </div>
            }
        >
            <Head title={isEdit ? 'Vorlage bearbeiten' : 'Neue Vorlage'} />

            <form onSubmit={handleSubmit} className="max-w-3xl space-y-6">
                
                {/* Event Type */}
                <div className="sim-card p-6">
                    <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 mb-4 flex items-center gap-2">
                        <Tag size={14} /> Event-Typ & Eigenschaften
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Event-Typ *</label>
                            <select
                                className={`sim-select w-full ${errors.event_type ? 'border-red-500' : ''}`}
                                value={data.event_type}
                                onChange={e => setData('event_type', e.target.value)}
                            >
                                <option value="">— Wählen —</option>
                                {Object.entries(eventTypes).map(([k, v]) => (
                                    <option key={k} value={k}>{v}</option>
                                ))}
                            </select>
                            {errors.event_type && <p className="text-red-400 text-xs mt-1">{errors.event_type}</p>}
                        </div>

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Priorität *</label>
                            <div className="flex gap-2">
                                {['low', 'normal', 'high'].map(p => (
                                    <button
                                        key={p} type="button"
                                        onClick={() => setData('priority', p)}
                                        className={`flex-1 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border transition-all ${
                                            data.priority === p
                                                ? p === 'high' ? 'bg-red-500/20 text-red-400 border-red-500/40' :
                                                  p === 'low'  ? 'bg-slate-700 text-slate-300 border-slate-600' :
                                                                 'bg-indigo-500/20 text-indigo-400 border-indigo-500/40'
                                                : 'bg-slate-800 text-slate-600 border-slate-700 hover:text-white'
                                        }`}
                                    >{p}</button>
                                ))}
                            </div>
                        </div>

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Stimmung *</label>
                            <select
                                className="sim-select w-full"
                                value={data.mood}
                                onChange={e => setData('mood', e.target.value)}
                            >
                                {Object.entries(moods).map(([k, v]) => (
                                    <option key={k} value={k}>{v}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Kommentatorstil *</label>
                            <select
                                className="sim-select w-full"
                                value={data.commentator_style}
                                onChange={e => setData('commentator_style', e.target.value)}
                            >
                                {Object.entries(styles).map(([k, v]) => (
                                    <option key={k} value={k}>{v}</option>
                                ))}
                            </select>
                        </div>

                        <div>
                            <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Sprache *</label>
                            <select
                                className="sim-select w-full"
                                value={data.locale}
                                onChange={e => setData('locale', e.target.value)}
                            >
                                <option value="de">Deutsch (de)</option>
                                <option value="en">Englisch (en)</option>
                            </select>
                        </div>
                    </div>
                </div>

                {/* Text */}
                <div className="sim-card p-6">
                    <h3 className="text-xs font-black uppercase tracking-widest text-cyan-400 mb-4 flex items-center gap-2">
                        <ChatText size={14} /> Vorlage Text
                    </h3>

                    <div className="mb-3">
                        <label className="block text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">
                            Platzhalter einfügen
                        </label>
                        <div className="flex gap-2 flex-wrap">
                            {PLACEHOLDERS.map(p => (
                                <button
                                    key={p} type="button"
                                    onClick={() => insertPlaceholder(p)}
                                    className="px-3 py-1 rounded-lg text-[10px] font-mono font-black bg-slate-800 border border-slate-700 text-cyan-400 hover:bg-cyan-500/10 hover:border-cyan-500/30 transition"
                                >
                                    {p}
                                </button>
                            ))}
                        </div>
                    </div>

                    <textarea
                        rows={5}
                        className={`sim-input w-full font-mono text-sm resize-y ${errors.text ? 'border-red-500' : ''}`}
                        placeholder="z.B. {player} erzielt das Tor! Jetzt steht es {score}."
                        value={data.text}
                        onChange={e => setData('text', e.target.value)}
                    />
                    {errors.text && <p className="text-red-400 text-xs mt-1">{errors.text}</p>}
                    <p className="text-[10px] text-slate-600 mt-2">
                        Erlaubte Platzhalter: <span className="font-mono text-slate-500">{'{player}'}, {'{opponent}'}, {'{club}'}, {'{score}'}</span>
                    </p>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="sim-btn-primary px-8 py-3 flex items-center gap-2"
                    >
                        <FloppyDisk size={18} weight="bold" />
                        {isEdit ? 'Änderungen speichern' : 'Vorlage erstellen'}
                    </button>
                    <Link href={route('admin.ticker-templates.index')} className="sim-btn-muted px-6 py-3">
                        Abbrechen
                    </Link>
                </div>
            </form>
        </AdminLayout>
    );
}
