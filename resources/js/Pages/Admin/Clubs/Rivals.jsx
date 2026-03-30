import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { Sword, Check, X } from '@phosphor-icons/react';
import axios from 'axios';

function RivalRow({ club, allClubs }) {
    const [rival1, setRival1] = useState(club.rival_id_1 ?? '');
    const [rival2, setRival2] = useState(club.rival_id_2 ?? '');
    const [saved, setSaved] = useState(false);
    const [error, setError] = useState(null);

    const save = async () => {
        setError(null);
        try {
            await axios.patch(route('admin.clubs.rivals.update', club.id), {
                rival_id_1: rival1 || null,
                rival_id_2: rival2 || null,
            });
            setSaved(true);
            setTimeout(() => setSaved(false), 2000);
        } catch (e) {
            setError(e.response?.data?.message ?? 'Fehler beim Speichern');
        }
    };

    const isDirty = String(rival1) !== String(club.rival_id_1 ?? '') || String(rival2) !== String(club.rival_id_2 ?? '');

    const otherClubs = allClubs.filter((c) => c.id !== club.id);

    return (
        <div className="grid grid-cols-[2.5rem_1fr_1fr_1fr_6rem] gap-4 items-center border-b border-white/5 px-6 py-4 hover:bg-white/[0.02] transition-colors">
            <div className="flex justify-center">
                {club.logo_url
                    ? <img loading="lazy" src={club.logo_url} alt={club.name} className="h-8 w-8 object-contain" />
                    : <div className="h-8 w-8 rounded-lg bg-[var(--bg-content)] flex items-center justify-center text-[9px] font-black text-[var(--text-muted)]">{club.short_name?.slice(0,3)}</div>
                }
            </div>

            <div className="min-w-0">
                <div className="text-sm font-black uppercase tracking-tight text-[var(--text-main)] truncate">{club.name}</div>
            </div>

            <select
                value={rival1}
                onChange={(e) => { setRival1(e.target.value); setSaved(false); }}
                className="sim-select text-xs py-2"
            >
                <option value="">— Kein Rivale —</option>
                {otherClubs.map((c) => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                ))}
            </select>

            <select
                value={rival2}
                onChange={(e) => { setRival2(e.target.value); setSaved(false); }}
                className="sim-select text-xs py-2"
            >
                <option value="">— Kein Rivale —</option>
                {otherClubs.map((c) => (
                    <option key={c.id} value={c.id}>{c.name}</option>
                ))}
            </select>

            <div className="flex items-center justify-end gap-2">
                {error && (
                    <span className="text-[9px] font-bold text-rose-400 truncate max-w-[100px]">{error}</span>
                )}
                {saved && !isDirty && (
                    <span className="flex items-center gap-1 text-[9px] font-black text-emerald-400 uppercase">
                        <Check size={12} weight="bold" /> Gespeichert
                    </span>
                )}
                {isDirty && (
                    <button
                        onClick={save}
                        className="sim-btn-primary px-3 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-lg"
                    >
                        Speichern
                    </button>
                )}
            </div>
        </div>
    );
}

export default function Rivals({ clubs }) {
    return (
        <AuthenticatedLayout>
            <Head title="Rivalitäten" />

            <div className="max-w-[900px] mx-auto space-y-8">
                <PageHeader
                    eyebrow="Admin"
                    title="Rivalen-Verwaltung"
                    description="Weise jedem Verein bis zu zwei Erzrivalen zu. Derby-Spiele zwischen Rivalen werden besonders markiert."
                />

                <div className="sim-card overflow-hidden">
                    <div className="grid grid-cols-[2.5rem_1fr_1fr_1fr_6rem] gap-4 border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4 text-[9px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                        <div />
                        <div>Verein</div>
                        <div className="flex items-center gap-1"><Sword size={11} className="text-rose-400" /> Erzrivale 1</div>
                        <div className="flex items-center gap-1"><Sword size={11} className="text-rose-400" /> Erzrivale 2</div>
                        <div />
                    </div>

                    {clubs.map((club) => (
                        <RivalRow key={club.id} club={club} allClubs={clubs} />
                    ))}
                </div>

                <p className="text-[9px] font-bold text-[var(--text-muted)] uppercase tracking-widest text-center">
                    Rivalitäten sind unidirektional — beide Vereine müssen sich gegenseitig eintragen für volle Derby-Erkennung.
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
