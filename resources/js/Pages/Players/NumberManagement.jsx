import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { TShirt, CheckCircle, Warning, Crown } from '@phosphor-icons/react';

const POSITION_ORDER = ['TW', 'LV', 'IV', 'RV', 'DM', 'LM', 'ZM', 'RM', 'OM', 'LF', 'HS', 'MS', 'RF'];

function NumberInput({ player, takenNumbers, onSave }) {
    const [value, setValue] = useState(player.shirt_number ?? '');
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);

    const isDuplicate =
        value !== '' &&
        Number(value) !== player.shirt_number &&
        takenNumbers.includes(Number(value));

    function handleSave() {
        if (isDuplicate) return;
        setSaving(true);
        setError(null);
        router.patch(
            route('players.update', player.id),
            { shirt_number: value === '' ? null : Number(value) },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSaving(false);
                    onSave(player.id, value === '' ? null : Number(value));
                },
                onError: (errors) => {
                    setSaving(false);
                    setError(errors.shirt_number ?? 'Fehler');
                },
            },
        );
    }

    function handleKey(e) {
        if (e.key === 'Enter') handleSave();
        if (e.key === 'Escape') {
            setValue(player.shirt_number ?? '');
            setError(null);
        }
    }

    const changed = Number(value) !== player.shirt_number && !(value === '' && player.shirt_number === null);

    return (
        <div className="flex flex-col gap-1">
            <div className="flex items-center gap-2">
                <input
                    type="number"
                    min="1"
                    max="99"
                    value={value}
                    onChange={(e) => { setValue(e.target.value); setError(null); }}
                    onKeyDown={handleKey}
                    onBlur={() => { if (changed && !isDuplicate) handleSave(); }}
                    disabled={saving}
                    placeholder="—"
                    className={`sim-input w-20 text-center font-black text-lg py-2 ${
                        isDuplicate ? 'border-rose-500/60 text-rose-400' : changed ? 'border-amber-400/40' : ''
                    }`}
                />
                {saving && (
                    <span className="text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest animate-pulse">
                        Speichern...
                    </span>
                )}
                {isDuplicate && (
                    <Warning size={16} className="text-rose-400" weight="fill" />
                )}
            </div>
            {error && (
                <span className="text-[9px] font-black text-rose-400 uppercase tracking-widest">{error}</span>
            )}
            {isDuplicate && (
                <span className="text-[9px] font-black text-rose-400 uppercase tracking-widest">Nummer vergeben</span>
            )}
        </div>
    );
}

export default function NumberManagement({ players, takenNumbers, clubs, activeClubId }) {
    const [localNumbers, setLocalNumbers] = useState(
        Object.fromEntries(players.map((p) => [p.id, p.shirt_number])),
    );

    const currentTaken = Object.values(localNumbers).filter(Boolean);

    function handleSave(playerId, newNumber) {
        setLocalNumbers((prev) => ({ ...prev, [playerId]: newNumber }));
    }

    const unassigned = players.filter((p) => !localNumbers[p.id]);
    const assigned = players.filter((p) => localNumbers[p.id]);

    const grouped = players.reduce((acc, p) => {
        const group = ['TW'].includes(p.position)
            ? 'Torhüter'
            : ['LV', 'IV', 'RV'].includes(p.position)
              ? 'Abwehr'
              : ['DM', 'LM', 'ZM', 'RM', 'OM'].includes(p.position)
                ? 'Mittelfeld'
                : 'Sturm';
        if (!acc[group]) acc[group] = [];
        acc[group].push(p);
        return acc;
    }, {});

    const groupOrder = ['Torhüter', 'Abwehr', 'Mittelfeld', 'Sturm'];

    return (
        <AuthenticatedLayout>
            <Head title="Trikotnummern" />

            <div className="max-w-[1400px] mx-auto space-y-12">
                <div className="border-b border-white/5 pb-12">
                    <PageHeader
                        eyebrow="Squad Management"
                        title="Trikotnummern"
                        actions={
                            clubs.length > 1 && (
                                <select
                                    value={activeClubId || ''}
                                    onChange={(e) =>
                                        router.get(route('players.numbers.index'), { club: e.target.value })
                                    }
                                    className="sim-select py-4 text-xs font-black uppercase tracking-widest"
                                >
                                    {clubs.map((c) => (
                                        <option key={c.id} value={c.id}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            )
                        }
                    />
                </div>

                <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div className="sim-card p-6">
                        <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2">
                            Gesamt
                        </div>
                        <div className="text-3xl font-black text-[var(--text-main)]">{players.length}</div>
                        <div className="text-[9px] text-[var(--text-muted)] font-bold uppercase mt-1">Spieler</div>
                    </div>
                    <div className="sim-card p-6">
                        <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2">
                            Vergeben
                        </div>
                        <div className="text-3xl font-black text-emerald-400">{assigned.length}</div>
                        <div className="text-[9px] text-[var(--text-muted)] font-bold uppercase mt-1">Nummern</div>
                    </div>
                    <div className="sim-card p-6">
                        <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2">
                            Ohne Nummer
                        </div>
                        <div className="text-3xl font-black text-amber-400">{unassigned.length}</div>
                        <div className="text-[9px] text-[var(--text-muted)] font-bold uppercase mt-1">Spieler</div>
                    </div>
                    <div className="sim-card p-6">
                        <div className="text-[10px] font-black text-[var(--text-muted)] uppercase tracking-widest mb-2">
                            Frei (1–99)
                        </div>
                        <div className="text-3xl font-black text-cyan-400">{99 - assigned.length}</div>
                        <div className="text-[9px] text-[var(--text-muted)] font-bold uppercase mt-1">Nummern</div>
                    </div>
                </div>

                {groupOrder.map((group) => {
                    const groupPlayers = grouped[group];
                    if (!groupPlayers?.length) return null;
                    return (
                        <section key={group} className="space-y-4">
                            <div className="flex items-center gap-4 pb-4 border-b border-white/5">
                                <div className="w-2 h-8 bg-[var(--accent-primary)] rounded-full" />
                                <h2 className="text-2xl font-black text-[var(--text-main)] uppercase italic tracking-tighter">
                                    {group}{' '}
                                    <span className="text-[var(--text-muted)] ml-2">[{groupPlayers.length}]</span>
                                </h2>
                            </div>
                            <div className="sim-card overflow-hidden">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b border-white/5">
                                            <th className="text-left p-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                                Pos
                                            </th>
                                            <th className="text-left p-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                                Spieler
                                            </th>
                                            <th className="text-center p-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                                OVR
                                            </th>
                                            <th className="text-center p-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                                <div className="flex items-center justify-center gap-1">
                                                    <TShirt size={12} />
                                                    Nummer
                                                </div>
                                            </th>
                                            <th className="text-center p-4 text-[9px] font-black text-[var(--text-muted)] uppercase tracking-widest">
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {groupPlayers.map((player, idx) => (
                                            <tr
                                                key={player.id}
                                                className={`border-b border-white/[0.03] last:border-0 transition-colors hover:bg-white/[0.02] ${
                                                    idx % 2 === 0 ? '' : 'bg-white/[0.01]'
                                                }`}
                                            >
                                                <td className="p-4">
                                                    <span className="text-[10px] font-black text-[var(--accent-primary)] uppercase tracking-widest">
                                                        {player.position}
                                                    </span>
                                                </td>
                                                <td className="p-4">
                                                    <div className="font-black text-[var(--text-main)] uppercase tracking-tight">
                                                        {player.last_name}
                                                    </div>
                                                    <div className="text-[9px] text-[var(--text-muted)] font-bold">
                                                        {player.first_name} · {player.age} J
                                                    </div>
                                                </td>
                                                <td className="p-4 text-center">
                                                    <span className="text-sm font-black text-[var(--text-main)]">
                                                        {player.overall}
                                                    </span>
                                                </td>
                                                <td className="p-4 text-center">
                                                    <div className="flex justify-center">
                                                        <NumberInput
                                                            player={{
                                                                ...player,
                                                                shirt_number: localNumbers[player.id],
                                                            }}
                                                            takenNumbers={currentTaken.filter(
                                                                (n) => n !== localNumbers[player.id],
                                                            )}
                                                            onSave={handleSave}
                                                        />
                                                    </div>
                                                </td>
                                                <td className="p-4 text-center">
                                                    {localNumbers[player.id] ? (
                                                        <span className="inline-flex items-center gap-1 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-emerald-300">
                                                            <CheckCircle size={10} weight="fill" />#{localNumbers[player.id]}
                                                        </span>
                                                    ) : (
                                                        <span className="inline-flex items-center gap-1 rounded-full border border-amber-400/20 bg-amber-400/10 px-2.5 py-1 text-[9px] font-black uppercase tracking-widest text-amber-300">
                                                            <Warning size={10} weight="fill" />
                                                            Offen
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    );
                })}

                {players.length === 0 && (
                    <div className="py-32 text-center">
                        <TShirt size={64} weight="thin" className="mx-auto text-slate-800 mb-6" />
                        <h3 className="text-2xl font-black text-slate-600 uppercase tracking-widest">
                            Kein Kader
                        </h3>
                        <p className="text-[var(--text-muted)] mt-2 font-bold uppercase tracking-widest text-xs">
                            Wähle einen Verein aus
                        </p>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
