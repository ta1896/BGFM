import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import { BracketsCurly, Layout, PlugCharging, ShieldCheck, SquaresFour, ToggleLeft, ToggleRight } from '@phosphor-icons/react';
import AdminLayout from '@/Layouts/AdminLayout';
import PageHeader from '@/Components/PageHeader';
import SectionCard from '@/Components/SectionCard';
import StatusMessage from '@/Components/StatusMessage';

function StatPill({ label, value, tone = 'default' }) {
    const tones = {
        default: 'border-[var(--border-muted)] bg-[var(--bg-content)]/50 text-[var(--text-muted)]',
        success: 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300',
        warning: 'border-amber-500/30 bg-amber-500/10 text-amber-300',
        info: 'border-cyan-500/30 bg-cyan-500/10 text-cyan-300',
    };

    return (
        <div className={`rounded-xl border px-3 py-2 ${tones[tone] ?? tones.default}`}>
            <p className="text-[10px] font-black uppercase tracking-[0.22em]">{label}</p>
            <p className="mt-1 text-base font-black leading-none text-[var(--text-main)]">{value}</p>
        </div>
    );
}

export default function Index({ modules }) {
    const { flash } = usePage().props;

    const enabledCount = modules.filter((module) => module.enabled).length;
    const disabledCount = modules.length - enabledCount;

    const toggleModule = (module) => {
        router.patch(route('admin.modules.update', module.key), {
            enabled: !module.enabled,
        }, {
            preserveScroll: true,
        });
    };

    return (
        <AdminLayout>
            <Head title="Module" />

            <div className="space-y-6 pb-12">
                <PageHeader
                    eyebrow="ACP / Erweiterungen"
                    title="Modulverwaltung"
                    actions={(
                        <div className="grid grid-cols-2 gap-3 md:grid-cols-4">
                            <StatPill label="Module" value={modules.length} tone="info" />
                            <StatPill label="Aktiv" value={enabledCount} tone="success" />
                            <StatPill label="Inaktiv" value={disabledCount} tone="warning" />
                            <StatPill
                                label="Widgets"
                                value={modules.reduce((sum, module) => sum + module.dashboard_widget_count, 0)}
                            />
                        </div>
                    )}
                />

                <StatusMessage variant="info">
                    Module steuern Navigation, Widgets und optionale Erweiterungen. Aktivierungswechsel greifen ab dem naechsten Request.
                </StatusMessage>
                <StatusMessage variant="success">{flash?.status}</StatusMessage>

                <SectionCard title="Verfuegbare Module" icon={SquaresFour} bodyClassName="p-5">
                    <div className="grid gap-4 xl:grid-cols-2">
                        {modules.map((module) => (
                            <div
                                key={module.key}
                                className="rounded-3xl border border-[var(--border-muted)] bg-[var(--bg-content)]/30 p-4"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div className="min-w-0">
                                        <div className="flex items-center gap-3">
                                            <div className={`rounded-xl border px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] ${
                                                module.enabled
                                                    ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                                                    : 'border-slate-500/30 bg-slate-500/10 text-slate-300'
                                            }`}>
                                                {module.enabled ? 'Aktiv' : 'Inaktiv'}
                                            </div>
                                            <div className="rounded-xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/60 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-[var(--text-muted)]">
                                                v{module.version}
                                            </div>
                                        </div>
                                        <h2 className="mt-2 text-xl font-black tracking-tight text-[var(--text-main)]">{module.name}</h2>
                                        <p className="mt-1.5 max-w-2xl text-sm leading-6 text-[var(--text-muted)]">
                                            {module.description || 'Kein Beschreibungstext im Manifest hinterlegt.'}
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => toggleModule(module)}
                                        className={`inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-[11px] font-black uppercase tracking-[0.18em] transition ${
                                            module.enabled
                                                ? 'border-rose-500/30 bg-rose-500/10 text-rose-300 hover:bg-rose-500/15'
                                                : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300 hover:bg-emerald-500/15'
                                        }`}
                                    >
                                        {module.enabled ? <ToggleRight size={18} weight="bold" /> : <ToggleLeft size={18} weight="bold" />}
                                        {module.enabled ? 'Deaktivieren' : 'Aktivieren'}
                                    </button>
                                </div>

                                <div className="mt-4 grid gap-2 sm:grid-cols-2 xl:grid-cols-5">
                                    <StatPill label="Quelle" value={module.source} />
                                    <StatPill label="Provider" value={module.provider_count} />
                                    <StatPill label="Manager Navi" value={module.manager_navigation_groups} />
                                    <StatPill label="Admin Navi" value={module.admin_navigation_groups} />
                                    <StatPill label="Widgets" value={module.dashboard_widget_count} />
                                    <StatPill label="Settings" value={module.settings_section_count} />
                                    <StatPill label="Player Actions" value={module.player_action_count} />
                                    <StatPill label="Matchcenter" value={module.matchcenter_panel_count} />
                                    <StatPill label="Notify" value={module.notification_hook_count} />
                                    <StatPill label="Standard" value={module.enabled_by_default ? 'Ja' : 'Nein'} />
                                </div>

                                <div className="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-4 py-3 text-sm">
                                    <div className="inline-flex items-center gap-2 text-[var(--text-main)]">
                                        <BracketsCurly size={16} className="text-cyan-300" />
                                        <span className="font-black">Routen:</span>
                                        <span className="text-[var(--text-muted)]">{module.has_routes ? 'vorhanden' : 'keine'}</span>
                                    </div>
                                    <div className="inline-flex items-center gap-2 text-[var(--text-main)]">
                                        <ShieldCheck size={16} className="text-emerald-300" />
                                        <span className="font-black">Migrationen:</span>
                                        <span className="text-[var(--text-muted)]">{module.has_migrations ? 'vorhanden' : 'keine'}</span>
                                    </div>
                                    <div className="inline-flex min-w-0 flex-1 items-center gap-2 text-[var(--text-main)]">
                                        <Layout size={16} className="shrink-0 text-amber-300" />
                                        <span className="font-black">Pfad:</span>
                                        <span className="truncate text-[var(--text-muted)]">{module.module_path}</span>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>

                    {!modules.length && (
                        <div className="rounded-3xl border border-dashed border-[var(--border-muted)] bg-[var(--bg-content)]/20 px-6 py-10 text-center">
                            <PlugCharging size={32} className="mx-auto text-[var(--text-muted)]" />
                            <p className="mt-3 text-sm font-medium text-[var(--text-muted)]">Es wurden keine Module im Verzeichnis `modules/` gefunden.</p>
                        </div>
                    )}
                </SectionCard>
            </div>
        </AdminLayout>
    );
}
