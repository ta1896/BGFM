import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, Link, router } from '@inertiajs/react';
import { Trophy, Trash, ArrowLeft, FloppyDisk, CalendarPlus, ListChecks, Warning, PencilSimple } from '@phosphor-icons/react';
import PageHeader from '@/Components/PageHeader';
import { PageReveal } from '@/Components/PageReveal';
import SectionCard from '@/Components/SectionCard';

function Field({ label, error, children }) {
    return (
        <div className="space-y-2">
            <label className="px-1 text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{label}</label>
            {children}
            {error && <p className="text-[10px] font-bold text-rose-500">{error}</p>}
        </div>
    );
}

export default function Form({ competition, countries, availableSeasons }) {
    const isEdit = Boolean(competition);

    const { data, setData, post, processing, errors } = useForm({
        country_id: competition?.country_id || '',
        name: competition?.name || '',
        short_name: competition?.short_name || '',
        type: competition?.type || 'league',
        scope: competition?.scope || '',
        tier: competition?.tier || '',
        logo: null,
        is_active: competition ? Boolean(competition.is_active) : true,
    });

    const seasonForm = useForm({
        season_id: '',
        format: '',
    });

    const submit = (event) => {
        event.preventDefault();
        if (isEdit) {
            router.post(route('admin.competitions.update', competition.id), { _method: 'PUT', ...data }, { forceFormData: true });
            return;
        }
        post(route('admin.competitions.store'));
    };

    const addSeason = (event) => {
        event.preventDefault();
        seasonForm.post(route('admin.competitions.add-season', competition.id), {
            onSuccess: () => seasonForm.reset(),
        });
    };

    const deleteCompetition = () => {
        if (confirm('Moechtest du diesen Wettbewerb wirklich loeschen?')) {
            router.delete(route('admin.competitions.destroy', competition.id));
        }
    };

    return (
        <AdminLayout>
            <Head title={isEdit ? `${competition.name} bearbeiten` : 'Wettbewerb erstellen'} />

            <div className="mx-auto max-w-5xl space-y-8 pb-20">
                <PageHeader
                    eyebrow="Wettbewerbe"
                    title={isEdit ? 'Wettbewerb bearbeiten' : 'Neuer Wettbewerb'}
                    actions={
                        <Link href={route('admin.competitions.index')} className="inline-flex items-center gap-2 rounded-xl border border-[var(--border-pillar)] bg-[var(--bg-content)] px-4 py-3 text-sm font-black text-[var(--text-muted)] transition-colors hover:text-[var(--accent-primary)]">
                            <ArrowLeft size={20} weight="bold" />
                            Zurueck
                        </Link>
                    }
                />

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="space-y-8 lg:col-span-2">
                        <PageReveal>
                            <SectionCard title="Basis-Konfiguration" icon={Trophy} bodyClassName="space-y-6 p-6">
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <Field label="Land (optional)" error={errors.country_id}>
                                            <select className="sim-select w-full" value={data.country_id} onChange={(event) => setData('country_id', event.target.value)}>
                                                <option value="">- Kein Land (International) -</option>
                                                {countries.map((country) => <option key={country.id} value={country.id}>{country.name}</option>)}
                                            </select>
                                        </Field>

                                        <Field label="Vollstaendiger Name" error={errors.name}>
                                            <input type="text" className="sim-input w-full" value={data.name} onChange={(event) => setData('name', event.target.value)} required />
                                        </Field>

                                        <Field label="Kurzform (z.B. BL1)" error={errors.short_name}>
                                            <input type="text" className="sim-input w-full" value={data.short_name} onChange={(event) => setData('short_name', event.target.value)} />
                                        </Field>

                                        <Field label="Typ" error={errors.type}>
                                            <select className="sim-select w-full" value={data.type} onChange={(event) => setData('type', event.target.value)} required>
                                                <option value="league">Liga</option>
                                                <option value="cup">Pokal</option>
                                            </select>
                                        </Field>

                                        <Field label="Wettbewerbsebene" error={errors.scope}>
                                            <select className="sim-select w-full" value={data.scope} onChange={(event) => setData('scope', event.target.value)}>
                                                <option value="">Automatisch (nach Land)</option>
                                                <option value="national">National</option>
                                                <option value="international">International</option>
                                            </select>
                                        </Field>

                                        <Field label="Stufe (Tier)" error={errors.tier}>
                                            <input type="number" min="1" max="10" className="sim-input w-full" value={data.tier} onChange={(event) => setData('tier', event.target.value)} />
                                        </Field>

                                        <Field label="Logo Upload" error={errors.logo}>
                                            <div className="group relative flex items-center gap-4 rounded-xl border-2 border-dashed border-[var(--border-pillar)] bg-[var(--sim-shell-bg)]/50 p-4 transition hover:border-[var(--accent-primary)]/40">
                                                <input type="file" className="absolute inset-0 cursor-pointer opacity-0" onChange={(event) => setData('logo', event.target.files[0])} />
                                                {isEdit && competition.logo_url && !data.logo && <img src={competition.logo_url} alt={competition.name} className="h-12 w-12 object-contain" />}
                                                <div className="text-sm text-[var(--text-muted)]">{data.logo ? data.logo.name : 'Datei hierher ziehen oder klicken'}</div>
                                            </div>
                                        </Field>
                                    </div>

                                    <div className="flex items-center justify-between border-t border-[var(--border-muted)] pt-6">
                                        <label className="group flex cursor-pointer items-center gap-3">
                                            <div className={`h-6 w-10 rounded-full p-1 transition-colors ${data.is_active ? 'bg-cyan-500' : 'bg-slate-700'}`}>
                                                <div className={`h-4 w-4 rounded-full bg-white transition-transform ${data.is_active ? 'translate-x-4' : 'translate-x-0'}`} />
                                            </div>
                                            <input type="checkbox" className="hidden" checked={data.is_active} onChange={(event) => setData('is_active', event.target.checked)} />
                                            <span className="text-xs font-black uppercase tracking-widest text-[var(--text-muted)] transition-colors group-hover:text-[var(--text-main)]">Wettbewerb aktiv</span>
                                        </label>

                                        <button type="submit" disabled={processing} className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[var(--accent-primary)] to-[var(--accent-secondary)] px-8 py-3 font-black text-white transition-opacity disabled:opacity-50">
                                            <FloppyDisk size={18} weight="bold" />
                                            {isEdit ? 'Aenderungen speichern' : 'Wettbewerb anlegen'}
                                        </button>
                                    </div>
                                </form>
                            </SectionCard>
                        </PageReveal>

                        {isEdit && (
                            <PageReveal delay={80}>
                                <SectionCard title="Gefahrenzone" icon={Warning} bodyClassName="flex items-center justify-between bg-rose-500/[0.02] p-6">
                                    <div>
                                        <h3 className="text-lg font-bold text-[var(--text-main)]">Loeschen ist endgueltig</h3>
                                        <p className="mt-1 text-[10px] font-bold uppercase tracking-widest text-rose-200/50">Wettbewerb wird dauerhaft entfernt</p>
                                    </div>
                                    <button type="button" onClick={deleteCompetition} className="rounded-xl bg-rose-600 px-6 py-2 font-black text-white transition hover:bg-rose-700">
                                        Loeschen
                                    </button>
                                </SectionCard>
                            </PageReveal>
                        )}
                    </div>

                    <div className="space-y-8">
                        {isEdit && (
                            <PageReveal>
                                <SectionCard title="Saisons" icon={CalendarPlus} bodyClassName="space-y-6 p-6">
                                    <div className="space-y-3">
                                        {competition.competition_seasons.map((competitionSeason) => (
                                            <div key={competitionSeason.id} className="rounded-xl border border-[var(--border-muted)] bg-[var(--bg-content)]/20 p-4">
                                                <div className="mb-2 flex items-center justify-between">
                                                    <span className="text-lg font-black uppercase leading-none tracking-tighter text-cyan-400">{competitionSeason.season.name}</span>
                                                    <Link href={route('admin.competition-seasons.edit', competitionSeason.id)} className="rounded-lg p-1.5 text-[var(--text-muted)] transition hover:bg-slate-700 hover:text-white">
                                                        <PencilSimple size={14} />
                                                    </Link>
                                                </div>
                                                <div className="flex items-center justify-between">
                                                    <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">{competitionSeason.format}</span>
                                                    <button type="button" onClick={() => router.post(route('admin.competition-seasons.generate-fixtures', competitionSeason.id))} className="rounded border border-cyan-500/20 bg-cyan-500/5 px-2 py-1 text-[9px] font-black uppercase tracking-widest text-cyan-500 transition hover:text-cyan-400">
                                                        Fixture Gen
                                                    </button>
                                                </div>
                                            </div>
                                        ))}
                                        {competition.competition_seasons.length === 0 && (
                                            <p className="py-8 text-center text-xs italic text-slate-600">Noch keine Saisons zugeordnet.</p>
                                        )}
                                    </div>

                                    <div className="rounded-xl border border-[var(--border-muted)] border-dashed bg-[var(--sim-shell-bg)]/50 p-4">
                                        <h4 className="mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-[var(--text-main)]">Saison zuordnen</h4>
                                        <form onSubmit={addSeason} className="space-y-4">
                                            <select className="sim-select w-full text-xs" value={seasonForm.data.season_id} onChange={(event) => seasonForm.setData('season_id', event.target.value)} required>
                                                <option value="">Waehle Saison...</option>
                                                {availableSeasons.map((season) => <option key={season.id} value={season.id}>{season.name}</option>)}
                                            </select>
                                            <input type="text" placeholder="Format (z.B. league_18)" className="sim-input w-full text-xs" value={seasonForm.data.format} onChange={(event) => seasonForm.setData('format', event.target.value)} required />
                                            <button type="submit" disabled={seasonForm.processing} className="w-full rounded-lg bg-[var(--bg-content)] py-2 text-[10px] font-black uppercase tracking-widest text-cyan-400 transition hover:bg-slate-700">
                                                Zuordnen
                                            </button>
                                        </form>
                                    </div>
                                </SectionCard>
                            </PageReveal>
                        )}

                        <PageReveal delay={120}>
                            <SectionCard title="Infos und Hilfe" icon={ListChecks} bodyClassName="space-y-4 p-6">
                                <p className="text-xs leading-relaxed text-[var(--text-muted)]">
                                    Ligen werden automatisch der nationalen Ebene zugeordnet, wenn ein Land gewaehlt wurde.
                                    Internationale Pokale benoetigen kein Land.
                                </p>
                                <div className="rounded-lg border border-indigo-500/10 bg-indigo-500/5 p-3">
                                    <p className="mb-1 text-[10px] font-black uppercase tracking-widest text-indigo-400">Pro-Tipp</p>
                                    <p className="text-[11px] text-[var(--text-muted)]">Verwende einheitliche Kurznamen fuer eine bessere Uebersicht im System.</p>
                                </div>
                            </SectionCard>
                        </PageReveal>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
