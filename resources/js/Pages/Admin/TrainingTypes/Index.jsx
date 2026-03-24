import React, { useEffect, useMemo, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { CheckCircle, FloppyDisk, Plus, Rows, Trash, Wrench } from '@phosphor-icons/react';

const EMPTY_EFFECT = { attribute: 'technical', delta: 1 };

function EffectRow({ effect, index, effectOptions, onChange, onRemove }) {
    return (
        <div className="grid gap-3 rounded-2xl border border-[var(--border-pillar)]/60 bg-[var(--bg-content)]/20 p-4 md:grid-cols-[1fr_120px_auto]">
            <select value={effect.attribute} onChange={(event) => onChange(index, 'attribute', event.target.value)} className="sim-select w-full">
                {Object.entries(effectOptions).map(([key, label]) => (
                    <option key={key} value={key}>{label}</option>
                ))}
            </select>
            <input type="number" min="-10" max="10" value={effect.delta} onChange={(event) => onChange(index, 'delta', Number(event.target.value))} className="sim-input w-full" />
            <button type="button" onClick={() => onRemove(index)} className="inline-flex items-center justify-center rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-rose-200">
                <Trash size={16} />
            </button>
        </div>
    );
}

export default function Index({ trainingTypes = [], options }) {
    const [selectedId, setSelectedId] = useState(trainingTypes[0]?.id ?? null);
    const [isEditorOpen, setIsEditorOpen] = useState(false);
    const selectedType = useMemo(() => trainingTypes.find((type) => type.id === selectedId) ?? null, [trainingTypes, selectedId]);
    const form = useForm({
        name: '',
        slug: '',
        description: '',
        category: Object.keys(options.categories ?? {})[0] ?? 'technical',
        team_focus: '',
        unit_focus: '',
        default_intensity: Object.keys(options.intensities ?? {})[0] ?? 'medium',
        tone: Object.keys(options.tones ?? {})[0] ?? 'cyan',
        icon: Object.keys(options.icons ?? {})[0] ?? 'GraduationCap',
        sort_order: 0,
        is_active: true,
        effects: [EMPTY_EFFECT],
    });

    useEffect(() => {
        if (!selectedType) {
            form.setData({
                name: '',
                slug: '',
                description: '',
                category: Object.keys(options.categories ?? {})[0] ?? 'technical',
                team_focus: '',
                unit_focus: '',
                default_intensity: Object.keys(options.intensities ?? {})[0] ?? 'medium',
                tone: Object.keys(options.tones ?? {})[0] ?? 'cyan',
                icon: Object.keys(options.icons ?? {})[0] ?? 'GraduationCap',
                sort_order: 0,
                is_active: true,
                effects: [EMPTY_EFFECT],
            });
            return;
        }

        form.setData({
            name: selectedType.name ?? '',
            slug: selectedType.slug ?? '',
            description: selectedType.description ?? '',
            category: selectedType.category ?? 'technical',
            team_focus: selectedType.team_focus ?? '',
            unit_focus: selectedType.unit_focus ?? '',
            default_intensity: selectedType.default_intensity ?? 'medium',
            tone: selectedType.tone ?? 'cyan',
            icon: selectedType.icon ?? 'GraduationCap',
            sort_order: selectedType.sort_order ?? 0,
            is_active: !!selectedType.is_active,
            effects: selectedType.effects?.length ? selectedType.effects : [EMPTY_EFFECT],
        });
    }, [selectedType]);

    const updateEffect = (index, field, value) => {
        form.setData('effects', form.data.effects.map((effect, effectIndex) => effectIndex === index ? { ...effect, [field]: value } : effect));
    };

    const createNew = () => {
        setSelectedId(null);
        setIsEditorOpen(true);
        form.setData({
            name: '',
            slug: '',
            description: '',
            category: Object.keys(options.categories ?? {})[0] ?? 'technical',
            team_focus: '',
            unit_focus: '',
            default_intensity: Object.keys(options.intensities ?? {})[0] ?? 'medium',
            tone: Object.keys(options.tones ?? {})[0] ?? 'cyan',
            icon: Object.keys(options.icons ?? {})[0] ?? 'GraduationCap',
            sort_order: (trainingTypes.at(-1)?.sort_order ?? 0) + 10,
            is_active: true,
            effects: [EMPTY_EFFECT],
        });
    };

    const submit = (event) => {
        event.preventDefault();
        if (selectedType) {
            form.put(route('admin.training-types.update', selectedType.id), {
                onSuccess: () => setIsEditorOpen(false),
            });
            return;
        }

        form.post(route('admin.training-types.store'), {
            onSuccess: () => setIsEditorOpen(false),
        });
    };

    const openEditorForType = (typeId) => {
        setSelectedId(typeId);
        setIsEditorOpen(true);
    };

    const renderEditor = (mobile = false) => (
        <form onSubmit={submit} className={`sim-card space-y-6 ${mobile ? 'h-full overflow-y-auto rounded-t-[2rem] border-x-0 border-b-0 p-5 pb-28' : 'p-6'}`}>
            <div className={`flex items-center justify-between gap-4 ${mobile ? 'sticky top-0 z-10 -mx-5 border-b border-[var(--border-pillar)] bg-[var(--bg-base)]/95 px-5 py-4 backdrop-blur-xl' : ''}`}>
                <div className="flex items-center gap-3">
                    <div className="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 p-3 text-cyan-200">
                        <Wrench size={18} />
                    </div>
                    <div>
                        <h3 className="text-lg font-black text-white">{selectedType ? 'Trainingstyp bearbeiten' : 'Trainingstyp erstellen'}</h3>
                        <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Beliebig viele Wirkungswerte pro Typ</p>
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    {mobile ? (
                        <button type="button" onClick={() => setIsEditorOpen(false)} className="rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/20 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                            Schliessen
                        </button>
                    ) : null}
                    {selectedType ? (
                        <button type="button" onClick={() => router.delete(route('admin.training-types.destroy', selectedType.id))} className="rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-rose-200">
                            Loeschen
                        </button>
                    ) : null}
                </div>
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className="sim-input w-full" placeholder="Name" />
                <input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} className="sim-input w-full" placeholder="Slug optional" />
                <input value={form.data.team_focus} onChange={(event) => form.setData('team_focus', event.target.value)} className="sim-input w-full" placeholder="team_focus z.B. build_up" />
                <input value={form.data.unit_focus} onChange={(event) => form.setData('unit_focus', event.target.value)} className="sim-input w-full" placeholder="unit_focus z.B. technical" />
                <select value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} className="sim-select w-full">
                    {Object.entries(options.categories ?? {}).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                </select>
                <select value={form.data.default_intensity} onChange={(event) => form.setData('default_intensity', event.target.value)} className="sim-select w-full">
                    {Object.entries(options.intensities ?? {}).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                </select>
                <select value={form.data.tone} onChange={(event) => form.setData('tone', event.target.value)} className="sim-select w-full">
                    {Object.entries(options.tones ?? {}).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                </select>
                <select value={form.data.icon} onChange={(event) => form.setData('icon', event.target.value)} className="sim-select w-full">
                    {Object.entries(options.icons ?? {}).map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                </select>
                <input type="number" min="0" max="9999" value={form.data.sort_order} onChange={(event) => form.setData('sort_order', Number(event.target.value))} className="sim-input w-full" placeholder="Sortierung" />
                <label className="flex items-center gap-3 rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-content)]/20 px-4 py-3 text-sm text-white">
                    <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} />
                    Aktiv
                </label>
            </div>

            <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} className="sim-input h-24 w-full resize-none" placeholder="Beschreibung" />

            <div className="space-y-4">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h4 className="text-sm font-black uppercase tracking-widest text-white">Wirkungen</h4>
                        <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Anzahl und Kombination frei definierbar</p>
                    </div>
                    <button type="button" onClick={() => form.setData('effects', [...form.data.effects, EMPTY_EFFECT])} className="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-cyan-200">
                        Effekt hinzufuegen
                    </button>
                </div>

                <div className="space-y-3">
                    {form.data.effects.map((effect, index) => (
                        <EffectRow
                            key={`${effect.attribute}-${index}`}
                            effect={effect}
                            index={index}
                            effectOptions={options.effects ?? {}}
                            onChange={updateEffect}
                            onRemove={(effectIndex) => form.setData('effects', form.data.effects.filter((_, indexToKeep) => indexToKeep !== effectIndex))}
                        />
                    ))}
                </div>
            </div>

            <div className={mobile ? 'fixed inset-x-0 bottom-0 z-10 border-t border-[var(--border-pillar)] bg-[var(--bg-base)]/95 px-5 py-4 backdrop-blur-xl' : ''}>
                <button type="submit" disabled={form.processing} className="sim-btn-primary inline-flex w-full items-center justify-center gap-2 px-6 py-3">
                    <FloppyDisk size={16} />
                    Speichern
                </button>
            </div>
        </form>
    );

    return (
        <AdminLayout>
            <Head title="Training Types" />

            <div className="space-y-8 pb-20">
                <div className="flex items-center justify-between">
                    <div>
                        <h2 className="text-2xl font-black uppercase italic tracking-tight text-white">Training Types</h2>
                        <p className="mt-1 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Methoden und Wirkungen fuer das Trainingszentrum</p>
                    </div>
                    <button type="button" onClick={createNew} className="sim-btn-primary inline-flex items-center gap-2 px-6 py-3">
                        <Plus size={16} />
                        Neuer Typ
                    </button>
                </div>

                <div className="grid gap-8 xl:grid-cols-12">
                    <div className="space-y-4 xl:col-span-4">
                        {trainingTypes.map((type) => (
                            <button key={type.id} type="button" onClick={() => openEditorForType(type.id)} className={`w-full rounded-3xl border p-5 text-left transition-all ${selectedId === type.id ? 'border-cyan-400/30 bg-cyan-500/10' : 'border-[var(--border-pillar)] bg-[var(--bg-content)]/20'}`}>
                                <div className="flex items-start justify-between gap-4">
                                    <div>
                                        <p className="text-sm font-black text-white">{type.name}</p>
                                        <p className="mt-1 text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{type.category} / {type.default_intensity}</p>
                                        <p className="mt-2 text-xs text-[var(--text-muted)]">{type.description || 'Keine Beschreibung'}</p>
                                    </div>
                                    {type.is_active ? <CheckCircle size={18} weight="fill" className="text-emerald-300" /> : <span className="rounded-full border border-white/10 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">Inaktiv</span>}
                                </div>
                            </button>
                        ))}
                    </div>

                    <div className="hidden xl:col-span-8 xl:block">
                        {renderEditor(false)}
                    </div>
                </div>
            </div>

            {isEditorOpen ? (
                <div className="fixed inset-0 z-50 xl:hidden">
                    <button type="button" aria-label="Overlay schliessen" className="absolute inset-0 bg-black/70 backdrop-blur-sm" onClick={() => setIsEditorOpen(false)} />
                    <div className="absolute inset-x-0 bottom-0 top-10 overflow-hidden">
                        {renderEditor(true)}
                    </div>
                </div>
            ) : null}
        </AdminLayout>
    );
}
