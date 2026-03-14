import React, { Suspense, lazy, useRef, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import { Atom } from '@phosphor-icons/react';
import axios from 'axios';
import MonitoringSubnav from '@/Pages/Admin/Monitoring/MonitoringSubnav';
import LabConfigPanel from '@/Pages/Admin/Monitoring/LabConfigPanel';

const LabResultViews = lazy(() => import('@/Pages/Admin/Monitoring/LabResultViews'));

export default function Lab({ clubs }) {
    const [mode, setMode] = useState('single');
    const [loading, setLoading] = useState(false);
    const [result, setResult] = useState(null);
    const resultsRef = useRef(null);

    const [forms, setForms] = useState({
        single: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '' },
        batch: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '', iterations: 50 },
        ab: {
            home_club_id: clubs[0]?.id || '',
            away_club_id: clubs[1]?.id || clubs[0]?.id || '',
            config_a: { aggression: 'normal' },
            config_b: { aggression: 'high' },
        },
        season: {},
        tactics: { home_club_id: clubs[0]?.id || '', away_club_id: clubs[1]?.id || clubs[0]?.id || '' },
    });

    const handleFormChange = (formMode, field, value) => {
        setForms((previous) => ({
            ...previous,
            [formMode]: {
                ...previous[formMode],
                [field]: value,
            },
        }));
    };

    const handleNestedChange = (formMode, parent, field, value) => {
        setForms((previous) => ({
            ...previous,
            [formMode]: {
                ...previous[formMode],
                [parent]: {
                    ...previous[formMode][parent],
                    [field]: value,
                },
            },
        }));
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);
        setResult(null);

        try {
            const response = await axios.post(route('admin.monitoring.lab.run'), {
                mode,
                ...forms[mode],
            });

            if (!response.data.success) {
                alert(`Fehler: ${response.data.message}`);
                return;
            }

            setResult({ mode, data: response.data.data });

            setTimeout(() => {
                resultsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        } catch (error) {
            console.error(error);
            alert(`Ein Fehler ist aufgetreten: ${error.response?.data?.message || error.message}`);
        } finally {
            setLoading(false);
        }
    };

    return (
        <AdminLayout
            header={(
                <div className="flex items-center justify-between">
                    <div>
                        <p className="sim-section-title text-emerald-400">System Monitoring</p>
                        <h1 className="mt-1 text-2xl font-bold text-white">Match Lab (Sandbox)</h1>
                        <p className="mt-2 text-sm text-slate-300">Testumgebung fuer die Match-Engine Logik.</p>
                    </div>
                    <Link href={route('admin.monitoring.index')} className="sim-btn-muted">Zur Uebersicht</Link>
                </div>
            )}
        >
            <Head title="Match Lab" />

            <div className="space-y-8">
                <MonitoringSubnav activeRoute="admin.monitoring.lab" />

                <div className="flex flex-col lg:flex-row gap-8 items-start">
                    <LabConfigPanel
                        clubs={clubs}
                        mode={mode}
                        setMode={setMode}
                        forms={forms}
                        loading={loading}
                        onFormChange={handleFormChange}
                        onNestedChange={handleNestedChange}
                        onSubmit={handleSubmit}
                    />

                    <main className="flex-1 min-w-0 w-full space-y-8" ref={resultsRef}>
                        {!result && !loading && <EmptyState />}
                        {loading && <LoadingState />}

                        {result && (
                            <Suspense fallback={<LoadingState />}>
                                <div className="space-y-8 animate-in fade-in slide-in-from-bottom-6">
                                    <LabResultViews result={result} />
                                </div>
                            </Suspense>
                        )}
                    </main>
                </div>
            </div>
        </AdminLayout>
    );
}

function EmptyState() {
    return (
        <div className="sim-card p-16 text-center border-2 border-dashed border-white/5 bg-[var(--bg-pillar)]/20 rounded-[3rem]">
            <div className="text-8xl mb-10 opacity-10 filter grayscale transform -rotate-12">LAB</div>
            <h3 className="text-3xl font-black text-white mb-4 tracking-tight uppercase">Experimentelle Sandbox</h3>
            <p className="text-[var(--text-muted)] max-w-xl mx-auto leading-relaxed text-sm font-medium">
                Hier koennen Simulationen durchgefuehrt werden, ohne Daten in die Datenbank zu schreiben.
                Ideal zum Testen von Engine-Updates, Taktik-Einflussen oder neuen Match-Events.
            </p>
        </div>
    );
}

function LoadingState() {
    return (
        <div className="sim-card p-20 flex flex-col items-center justify-center space-y-6">
            <div className="relative">
                <div className="w-20 h-20 border-4 border-indigo-500/20 rounded-full" />
                <div className="w-20 h-20 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin absolute top-0" />
                <Atom size={40} className="absolute inset-0 m-auto text-indigo-400 animate-pulse" />
            </div>
            <div className="text-center">
                <h3 className="text-xl font-black text-white uppercase tracking-widest">Simulation laeuft</h3>
                <p className="text-xs text-[var(--text-muted)] mt-2 font-mono">Engine is processing tactical algorithms...</p>
            </div>
        </div>
    );
}
