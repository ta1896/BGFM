import React, { useEffect, useMemo, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import StatusMessage from '@/Components/StatusMessage';
import EmptyState from '@/Components/EmptyState';
import { Calendar, CheckCircle, GraduationCap, Heartbeat, Lightning, Plus, Rows, Target, Users, WarningCircle } from '@phosphor-icons/react';

const TONES = {
    amber: 'border-amber-400/30 bg-amber-500/10 text-amber-200',
    emerald: 'border-emerald-400/30 bg-emerald-500/10 text-emerald-200',
    cyan: 'border-cyan-400/30 bg-cyan-500/10 text-cyan-200',
    rose: 'border-rose-400/30 bg-rose-500/10 text-rose-200',
    violet: 'border-violet-400/30 bg-violet-500/10 text-violet-200',
    fuchsia: 'border-fuchsia-400/30 bg-fuchsia-500/10 text-fuchsia-200',
};

const ICONS = {
    Lightning,
    Target,
    GraduationCap,
    Heartbeat,
    Rows,
    Users,
};

function Metric({ label, value, tone = 'amber', note = null }) {
    return (
        <div className={`rounded-2xl border px-4 py-4 ${TONES[tone] ?? TONES.amber}`}>
            <p className="text-[10px] font-black uppercase tracking-widest opacity-80">{label}</p>
            <p className="mt-1 text-2xl font-black text-white">{value}</p>
            {note ? <p className="mt-1 text-[10px] font-black uppercase tracking-widest opacity-75">{note}</p> : null}
        </div>
    );
}

function SessionCard({ session }) {
    const impact = session.impact ?? null;
    const tone = session.training_type?.tone ?? 'cyan';

    return (
        <div className={`rounded-2xl border bg-[var(--bg-content)]/20 p-3 ${TONES[tone] ?? 'border-[var(--border-muted)]'}`}>
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{session.training_type?.name ?? session.team_focus}</p>
                    <p className="mt-1 text-sm font-black text-white">{(session.training_groups ?? []).map((g) => g.name).join(', ') || 'Ohne Gruppe'}</p>
                    <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-cyan-300">{session.unit_focus}</p>
                </div>
                <span className="rounded-full border border-white/10 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-slate-300">{session.intensity}</span>
            </div>
            <div className="mt-3 flex items-center justify-between gap-3">
                <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{session.player_count} Spieler</span>
                {session.applied_at ? (
                    <span className="inline-flex items-center gap-1 text-[10px] font-black uppercase tracking-widest text-emerald-300">
                        <CheckCircle size={12} weight="fill" />
                        Absolviert
                    </span>
                ) : session.can_apply_manually ? (
                    <button type="button" onClick={() => router.post(route('training.apply', session.id))} className="rounded-xl border border-amber-500/20 bg-amber-500/10 px-2.5 py-1.5 text-[10px] font-black uppercase tracking-widest text-amber-300">
                        Jetzt
                    </button>
                ) : (
                    <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">23:00 Auto</span>
                )}
            </div>
            {impact ? (
                <div className="mt-3 grid grid-cols-3 gap-2 border-t border-white/5 pt-3">
                    <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2 text-center">
                        <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">Ausdauer</p>
                        <p className="mt-1 text-sm font-black text-white">{impact.stamina_total > 0 ? '+' : ''}{impact.stamina_total}</p>
                    </div>
                    <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2 text-center">
                        <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">Moral</p>
                        <p className="mt-1 text-sm font-black text-white">{impact.morale_total > 0 ? '+' : ''}{impact.morale_total}</p>
                    </div>
                    <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2 text-center">
                        <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">Overall</p>
                        <p className="mt-1 text-sm font-black text-white">{impact.overall_total > 0 ? '+' : ''}{impact.overall_total}</p>
                    </div>
                </div>
            ) : null}
        </div>
    );
}

function MethodButton({ method, active, onDragStart, onPick }) {
    const Icon = ICONS[method.icon] ?? GraduationCap;
    return (
        <button
            type="button"
            draggable
            onDragStart={(event) => onDragStart(event, method.id)}
            onClick={() => onPick(method.id)}
            className={`rounded-3xl border p-4 text-left transition-all hover:-translate-y-0.5 ${active ? 'border-white/20 bg-white/10' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35'} ${TONES[method.tone] ?? ''}`}
        >
            <div className="flex items-start justify-between gap-3">
                <div>
                    <p className="text-[10px] font-black uppercase tracking-[0.18em] opacity-80">{method.default_intensity}</p>
                    <h3 className="mt-2 text-sm font-black uppercase tracking-tight text-white">{method.name}</h3>
                    <p className="mt-1 text-[10px] font-black uppercase tracking-widest opacity-80">{method.unit_focus}</p>
                    <p className="mt-2 text-xs opacity-80">{method.description}</p>
                </div>
                <div className="flex h-10 w-10 items-center justify-center rounded-2xl border border-white/10 bg-black/15">
                    <Icon size={18} weight="duotone" />
                </div>
            </div>
        </button>
    );
}

export default function Training({ sessions, weekDays = [], club, prefillDate, trainingGroups = [], trainingTypes = [] }) {
    const [tab, setTab] = useState('planner');
    const [selectedSlot, setSelectedSlot] = useState({ date: prefillDate, slot: 0 });
    const [selectedMethod, setSelectedMethod] = useState(trainingTypes[0]?.id ?? null);
    const [editingGroupId, setEditingGroupId] = useState(trainingGroups[0]?.id ?? null);
    const [expandedHistoryId, setExpandedHistoryId] = useState(null);

    const plannerForm = useForm({
        club_id: club?.id ?? '',
        training_type_id: trainingTypes[0]?.id ?? '',
        session_date: prefillDate,
        team_focus: trainingTypes[0]?.team_focus ?? '',
        unit_focus: trainingTypes[0]?.unit_focus ?? '',
        intensity: trainingTypes[0]?.default_intensity ?? 'medium',
        training_group_ids: [],
        player_ids: [],
        notes: '',
    });
    const groupForm = useForm({ club_id: club?.id ?? '', name: '', color: 'cyan', notes: '', player_ids: [] });

    const trainingOptions = useMemo(() => ({
        teamFocuses: [...new Map(trainingTypes.map((method) => [method.team_focus, { key: method.team_focus, label: method.team_focus }])).values()],
        playerFocuses: [...new Map(trainingTypes.map((method) => [method.unit_focus, { key: method.unit_focus, label: method.unit_focus }])).values()],
        intensities: [
            { key: 'low', label: 'Leicht' },
            { key: 'medium', label: 'Normal' },
            { key: 'high', label: 'Hoch' },
        ],
    }), [trainingTypes]);
    const selectedMethodConfig = useMemo(() => trainingTypes.find((m) => m.id === selectedMethod) ?? trainingTypes[0] ?? null, [selectedMethod, trainingTypes]);
    const selectedGroups = useMemo(() => trainingGroups.filter((group) => plannerForm.data.training_group_ids.includes(group.id)), [trainingGroups, plannerForm.data.training_group_ids]);
    const selectedPlayers = useMemo(() => {
        const ids = new Set(selectedGroups.flatMap((group) => group.player_ids ?? []));
        return (club?.players ?? []).filter((player) => ids.has(player.id));
    }, [club?.players, selectedGroups]);
    const editingGroup = useMemo(() => trainingGroups.find((group) => group.id === editingGroupId) ?? null, [trainingGroups, editingGroupId]);
    const weekSummary = useMemo(() => {
        const all = weekDays.flatMap((day) => day.sessions ?? []);
        return { total: all.length, open: all.filter((session) => !session.applied_at).length };
    }, [weekDays]);
    const todayOpenSessions = useMemo(() => {
        const today = weekDays.find((day) => day.is_today);
        return (today?.sessions ?? []).filter((session) => !session.applied_at);
    }, [weekDays]);

    useEffect(() => {
        if (!selectedMethodConfig) return;
        plannerForm.setData((current) => ({
            ...current,
            training_type_id: selectedMethodConfig.id,
            session_date: selectedSlot.date,
            team_focus: selectedMethodConfig.team_focus,
            unit_focus: selectedMethodConfig.unit_focus,
            intensity: selectedMethodConfig.default_intensity,
        }));
    }, [selectedMethodConfig, selectedSlot.date]);

    useEffect(() => {
        if (!editingGroup) return;
        groupForm.setData({
            club_id: club?.id ?? '',
            name: editingGroup.name,
            color: editingGroup.color ?? 'cyan',
            notes: editingGroup.notes ?? '',
            player_ids: editingGroup.player_ids ?? [],
        });
    }, [editingGroupId, editingGroup, club?.id]);

    if (!club) {
        return (
            <AuthenticatedLayout>
                <EmptyState icon={WarningCircle} title="Kein Verein aktiv" description="Es konnte kein aktiver Verein gefunden werden." className="py-20" />
            </AuthenticatedLayout>
        );
    }

    const assignMethod = (methodId, date = selectedSlot.date) => {
        setSelectedMethod(methodId);
        setSelectedSlot((current) => ({ ...current, date }));
        plannerForm.setData('session_date', date);
    };

    return (
        <AuthenticatedLayout>
            <Head title="Training" />
            <div className="mx-auto max-w-[1600px] space-y-8 pb-20">
                <PageHeader eyebrow="Leistungsentwicklung" title="Trainingszentrum" />

                {(club.medical_summary?.risk_count > 0 || club.medical_summary?.rehab_count > 0) && (
                    <StatusMessage variant="warning">
                        {club.medical_summary.rehab_count > 0 ? `${club.medical_summary.rehab_count} Spieler in Reha. ` : ''}
                        {club.medical_summary.risk_count > 0 ? `${club.medical_summary.risk_count} Spieler mit hohem Verletzungsrisiko.` : ''}
                    </StatusMessage>
                )}

                <div className="grid gap-4 md:grid-cols-4">
                    <Metric label="Wochenplan" value={weekSummary.total} tone="amber" note="geplante Einheiten" />
                    <Metric label="Offen" value={weekSummary.open} tone="rose" note="nicht absolviert" />
                    <Metric label="Gruppen" value={trainingGroups.length} tone="cyan" note="frei definierbar" />
                    <Metric label="Spieler" value={selectedPlayers.length} tone="emerald" note="in aktiven Gruppen" />
                </div>

                <div className="flex flex-wrap gap-3">
                    <button type="button" onClick={() => setTab('planner')} className={`rounded-2xl border px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] ${tab === 'planner' ? 'border-cyan-400/40 bg-cyan-500/10 text-cyan-200' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35 text-[var(--text-muted)]'}`}>Wochenplan</button>
                    <button type="button" onClick={() => setTab('groups')} className={`rounded-2xl border px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] ${tab === 'groups' ? 'border-amber-400/40 bg-amber-500/10 text-amber-200' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35 text-[var(--text-muted)]'}`}>Gruppen</button>
                    <button type="button" onClick={() => setTab('history')} className={`rounded-2xl border px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] ${tab === 'history' ? 'border-emerald-400/40 bg-emerald-500/10 text-emerald-200' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35 text-[var(--text-muted)]'}`}>History</button>
                    {todayOpenSessions.length > 0 ? (
                        <button
                            type="button"
                            onClick={() => router.post(route('training.apply-today'))}
                            className="ml-auto rounded-2xl border border-emerald-400/30 bg-emerald-500/10 px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-emerald-200"
                        >
                            Heutige Einheit ausloesen ({todayOpenSessions.length})
                        </button>
                    ) : null}
                </div>

                {tab === 'planner' ? (
                    <div className="grid gap-8 xl:grid-cols-12">
                        <div className="space-y-8 xl:col-span-8">
                            <SectionCard title="Wochenkalender" icon={Calendar} bodyClassName="p-6">
                                <div className="grid gap-4 lg:grid-cols-3">
                                    {weekDays.map((day) => (
                                        <div key={day.date} className="space-y-4 rounded-[28px] border border-[var(--border-muted)] bg-[var(--bg-pillar)]/20 p-4">
                                            {[0, 1, 2].map((slot) => {
                                                const session = day.sessions?.[slot] ?? null;
                                                const empty = !session;
                                                const active = selectedSlot.date === day.date && selectedSlot.slot === slot;

                                                return (
                                                    <div
                                                        key={`${day.date}-${slot}`}
                                                        onDragOver={empty ? (event) => event.preventDefault() : undefined}
                                                        onDrop={empty ? (event) => {
                                                            const methodKey = Number(event.dataTransfer.getData('trainingMethod'));
                                                            setSelectedSlot({ date: day.date, slot });
                                                            assignMethod(methodKey, day.date);
                                                        } : undefined}
                                                        onClick={() => {
                                                            if (!empty) return;
                                                            setSelectedSlot({ date: day.date, slot });
                                                            plannerForm.setData('session_date', day.date);
                                                        }}
                                                        className={`rounded-3xl border p-4 ${active ? 'border-cyan-400/40 bg-cyan-500/10' : 'border-[var(--border-muted)] bg-[var(--bg-content)]/20'} ${empty ? 'cursor-pointer' : ''}`}
                                                    >
                                                        <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{day.label}</p>
                                                        <p className="mt-1 text-xs font-black uppercase tracking-widest text-white">Slot {slot + 1}</p>
                                                        <div className="mt-3">
                                                            {session ? <SessionCard session={session} /> : <div className="rounded-2xl border border-dashed border-white/10 px-3 py-8 text-center text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Leer</div>}
                                                        </div>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    ))}
                                </div>
                            </SectionCard>

                            <SectionCard title="Trainingsmethoden" icon={Rows} bodyClassName="p-6">
                                <div className="mb-4 rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 px-4 py-3 text-sm text-[var(--text-muted)]">
                                    Desktop: Kachel in einen leeren Slot ziehen. Mobil: Slot waehlen und Kachel antippen.
                                </div>
                                <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                    {trainingTypes.map((method) => (
                                        <MethodButton
                                            key={method.id}
                                            method={method}
                                            active={selectedMethod === method.id}
                                            onDragStart={(event, methodKey) => event.dataTransfer.setData('trainingMethod', methodKey)}
                                            onPick={(methodKey) => assignMethod(methodKey)}
                                        />
                                    ))}
                                </div>
                            </SectionCard>

                        </div>

                        <div className="space-y-8 xl:col-span-4">
                            <SectionCard title="Einheit planen" icon={Plus} bodyClassName="p-6">
                                <form onSubmit={(event) => {
                                    event.preventDefault();
                                    plannerForm.transform((data) => ({ ...data, player_ids: selectedPlayers.map((player) => player.id) })).post(route('training.store'));
                                }} className="space-y-5">
                                    <div className="rounded-3xl border border-cyan-400/14 bg-cyan-500/8 p-4">
                                        <p className="text-[10px] font-black uppercase tracking-[0.18em] text-cyan-200/80">Ausgewaehlter Slot</p>
                                        <h3 className="mt-2 text-lg font-black text-white">{plannerForm.data.session_date}</h3>
                                        <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Slot {selectedSlot.slot + 1} von 3</p>
                                    </div>

                                    <select value={plannerForm.data.training_type_id} onChange={(event) => assignMethod(Number(event.target.value), plannerForm.data.session_date)} className="sim-select w-full">
                                        {trainingTypes.map((type) => <option key={type.id} value={type.id}>{type.name}</option>)}
                                    </select>

                                    <div className="grid gap-4">
                                        <select value={plannerForm.data.team_focus} onChange={(event) => plannerForm.setData('team_focus', event.target.value)} className="sim-select w-full">
                                            {(trainingOptions?.teamFocuses ?? []).map((item) => <option key={item.key} value={item.key}>{item.label}</option>)}
                                        </select>
                                        <select value={plannerForm.data.unit_focus} onChange={(event) => plannerForm.setData('unit_focus', event.target.value)} className="sim-select w-full">
                                            {(trainingOptions?.playerFocuses ?? []).map((item) => <option key={item.key} value={item.key}>{item.label}</option>)}
                                        </select>
                                        <select value={plannerForm.data.intensity} onChange={(event) => plannerForm.setData('intensity', event.target.value)} className="sim-select w-full">
                                            {(trainingOptions?.intensities ?? []).map((item) => <option key={item.key} value={item.key}>{item.label}</option>)}
                                        </select>
                                    </div>

                                    <div className="space-y-3">
                                        <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">Trainingsgruppen</p>
                                        <div className="grid gap-3 sm:grid-cols-2">
                                            {trainingGroups.map((group) => (
                                                <button
                                                    key={group.id}
                                                    type="button"
                                                    onClick={() => plannerForm.setData('training_group_ids', plannerForm.data.training_group_ids.includes(group.id) ? plannerForm.data.training_group_ids.filter((id) => id !== group.id) : [...plannerForm.data.training_group_ids, group.id])}
                                                    className={`rounded-2xl border px-4 py-3 text-left transition-all ${plannerForm.data.training_group_ids.includes(group.id) ? (TONES[group.color] ?? TONES.cyan) : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35 text-[var(--text-muted)]'}`}
                                                >
                                                    <span className="block text-[10px] font-black uppercase tracking-[0.18em]">{group.name}</span>
                                                    <span className="mt-1 block text-[11px] font-bold opacity-80">{group.players?.length ?? 0} Spieler</span>
                                                </button>
                                            ))}
                                        </div>
                                    </div>

                                    <textarea value={plannerForm.data.notes} onChange={(event) => plannerForm.setData('notes', event.target.value)} className="sim-input h-28 w-full resize-none" placeholder="Hinweise zur Einheit ..." />
                                    <button type="submit" disabled={plannerForm.processing || plannerForm.data.training_group_ids.length === 0} className="sim-btn-primary w-full py-3">Einheit speichern</button>
                                </form>
                            </SectionCard>
                        </div>
                    </div>
                ) : tab === 'groups' ? (
                    <div className="grid gap-8 xl:grid-cols-12">
                        <div className="space-y-8 xl:col-span-4">
                            <SectionCard title="Gruppe bearbeiten" icon={Users} bodyClassName="p-6">
                                <form onSubmit={(event) => {
                                    event.preventDefault();
                                    if (editingGroup) {
                                        groupForm.put(route('training.groups.update', editingGroup.id));
                                        return;
                                    }
                                    groupForm.post(route('training.groups.store'));
                                }} className="space-y-5">
                                    <div className="flex gap-3">
                                        <button type="button" onClick={() => { setEditingGroupId(null); groupForm.setData({ club_id: club.id, name: '', color: 'cyan', notes: '', player_ids: [] }); }} className="rounded-2xl border border-cyan-400/20 bg-cyan-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-cyan-200">Neu</button>
                                        {editingGroup ? <button type="button" onClick={() => router.delete(route('training.groups.destroy', editingGroup.id))} className="rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-rose-200">Loeschen</button> : null}
                                    </div>
                                    <input value={groupForm.data.name} onChange={(event) => groupForm.setData('name', event.target.value)} className="sim-input w-full" placeholder="Gruppenname" />
                                    <select value={groupForm.data.color} onChange={(event) => groupForm.setData('color', event.target.value)} className="sim-select w-full">
                                        {Object.keys(TONES).map((color) => <option key={color} value={color}>{color}</option>)}
                                    </select>
                                    <textarea value={groupForm.data.notes} onChange={(event) => groupForm.setData('notes', event.target.value)} className="sim-input h-24 w-full resize-none" placeholder="Notizen" />
                                    <button type="submit" className="sim-btn-primary w-full py-3">{editingGroup ? 'Gruppe aktualisieren' : 'Gruppe erstellen'}</button>
                                </form>
                            </SectionCard>

                            <SectionCard title="Vorhandene Gruppen" icon={Rows} bodyClassName="p-6">
                                <div className="space-y-3">
                                    {trainingGroups.map((group) => (
                                        <button key={group.id} type="button" onClick={() => setEditingGroupId(group.id)} className={`w-full rounded-3xl border p-4 text-left transition-all ${editingGroupId === group.id ? (TONES[group.color] ?? TONES.cyan) : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35'}`}>
                                            <p className="text-sm font-black text-white">{group.name}</p>
                                            <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{group.players?.length ?? 0} Spieler</p>
                                        </button>
                                    ))}
                                </div>
                            </SectionCard>
                        </div>

                        <div className="xl:col-span-8">
                            <SectionCard title="Spieler zuordnen" icon={Users} bodyClassName="p-6">
                                {editingGroup ? (
                                    <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                        {(club.players ?? []).map((player) => {
                                            const active = groupForm.data.player_ids.includes(player.id);
                                            return (
                                                <button key={player.id} type="button" onClick={() => groupForm.setData('player_ids', active ? groupForm.data.player_ids.filter((id) => id !== player.id) : [...groupForm.data.player_ids, player.id])} className={`rounded-3xl border p-4 text-left transition-all ${active ? 'border-amber-400/30 bg-amber-500/10' : 'border-[var(--border-muted)] bg-[var(--bg-pillar)]/35'}`}>
                                                    <div className="flex items-start justify-between gap-3">
                                                        <div>
                                                            <p className="text-sm font-black text-white">{player.name}</p>
                                                            <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{player.position}</p>
                                                        </div>
                                                        {active ? <CheckCircle size={18} weight="fill" className="text-amber-300" /> : <Plus size={18} className="text-[var(--text-muted)]" />}
                                                    </div>
                                                </button>
                                            );
                                        })}
                                    </div>
                                ) : (
                                    <div className="rounded-3xl border border-dashed border-[var(--border-pillar)] px-6 py-16 text-center text-sm text-[var(--text-muted)]">
                                        Links eine Gruppe auswaehlen oder neue Gruppe anlegen.
                                    </div>
                                )}
                            </SectionCard>
                        </div>
                    </div>
                ) : (
                    <SectionCard title="History & Wirkung" icon={CheckCircle} bodyClassName="p-6">
                        <div className="space-y-4">
                            {(sessions?.data ?? []).length > 0 ? sessions.data.map((session) => {
                                const expanded = expandedHistoryId === session.id;
                                return (
                                    <div key={session.id} className="rounded-3xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/25 p-4">
                                        <button
                                            type="button"
                                            onClick={() => setExpandedHistoryId(expanded ? null : session.id)}
                                            className="flex w-full items-start justify-between gap-4 text-left"
                                        >
                                            <div>
                                                <p className="text-[10px] font-black uppercase tracking-[0.18em] text-[var(--text-muted)]">{session.session_date}</p>
                                                <h3 className="mt-1 text-sm font-black text-white">{session.training_type?.name ?? session.team_focus} / {session.unit_focus}</h3>
                                                <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-cyan-300">{(session.training_groups ?? []).map((group) => group.name).join(', ') || 'Ohne Gruppe'}</p>
                                            </div>
                                            <div className="grid grid-cols-3 gap-2 text-center">
                                                <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2">
                                                    <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">A</p>
                                                    <p className="mt-1 text-sm font-black text-white">{session.impact?.stamina_total > 0 ? '+' : ''}{session.impact?.stamina_total ?? 0}</p>
                                                </div>
                                                <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2">
                                                    <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">M</p>
                                                    <p className="mt-1 text-sm font-black text-white">{session.impact?.morale_total > 0 ? '+' : ''}{session.impact?.morale_total ?? 0}</p>
                                                </div>
                                                <div className="rounded-xl border border-white/5 bg-black/15 px-2 py-2">
                                                    <p className="text-[8px] font-black uppercase tracking-widest text-[var(--text-muted)]">OVR</p>
                                                    <p className="mt-1 text-sm font-black text-white">{session.impact?.overall_total > 0 ? '+' : ''}{session.impact?.overall_total ?? 0}</p>
                                                </div>
                                            </div>
                                        </button>

                                        {expanded ? (
                                            <div className="mt-4 border-t border-white/5 pt-4">
                                                <div className="grid gap-3 md:grid-cols-3">
                                                    <Metric label="Avg Ausdauer" value={session.impact?.stamina_avg ?? 0} tone="amber" />
                                                    <Metric label="Avg Moral" value={session.impact?.morale_avg ?? 0} tone="emerald" />
                                                    <Metric label="Avg Overall" value={session.impact?.overall_avg ?? 0} tone="cyan" />
                                                </div>
                                                <div className="mt-4 space-y-2">
                                                    {(session.impact?.top_players ?? []).map((player) => (
                                                        <div key={player.id} className="flex items-center justify-between gap-3 rounded-2xl border border-white/5 bg-black/15 px-3 py-3">
                                                            <div>
                                                                <p className="text-sm font-black text-white">{player.name}</p>
                                                                <p className="mt-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                                                    A {player.stamina_delta > 0 ? '+' : ''}{player.stamina_delta} / M {player.morale_delta > 0 ? '+' : ''}{player.morale_delta} / OVR {player.overall_delta > 0 ? '+' : ''}{player.overall_delta}
                                                                </p>
                                                            </div>
                                                            <span className="rounded-full border border-cyan-400/20 bg-cyan-500/10 px-3 py-1 text-[10px] font-black uppercase tracking-widest text-cyan-200">
                                                                Total {player.total_delta > 0 ? '+' : ''}{player.total_delta}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        ) : null}
                                    </div>
                                );
                            }) : (
                                <div className="rounded-2xl border border-dashed border-white/10 px-4 py-10 text-center text-sm text-[var(--text-muted)]">
                                    Noch keine Trainingshistorie vorhanden.
                                </div>
                            )}
                        </div>
                    </SectionCard>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
