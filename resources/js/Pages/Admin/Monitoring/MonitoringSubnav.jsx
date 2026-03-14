import React from 'react';
import { Link } from '@inertiajs/react';
import {
    Monitor,
    MagnifyingGlass,
    Flask,
    Timer,
    Gear,
    Scroll,
} from '@phosphor-icons/react';

const navItems = [
    { name: 'Uebersicht', icon: Monitor, href: 'admin.monitoring.index' },
    { name: 'Match-Analyse', icon: MagnifyingGlass, href: 'admin.monitoring.analysis' },
    { name: 'Match Lab', icon: Flask, href: 'admin.monitoring.lab' },
    { name: 'Scheduler', icon: Timer, href: 'admin.monitoring.scheduler' },
    { name: 'Internals', icon: Gear, href: 'admin.monitoring.internals' },
    { name: 'Logs', icon: Scroll, href: 'admin.monitoring.logs' },
];

export default function MonitoringSubnav({ activeRoute }) {
    return (
        <div className="flex flex-wrap gap-4 mb-2">
            {navItems.map((item) => {
                const Icon = item.icon;
                const isActive = item.href === activeRoute;

                return (
                    <Link
                        key={item.href}
                        href={route(item.href)}
                        className={`flex items-center gap-2 px-6 py-3 rounded-xl transition text-sm font-bold border ${
                            isActive
                                ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/20 border-emerald-500'
                                : 'bg-[var(--bg-content)] text-slate-300 hover:bg-slate-700 border-[var(--border-pillar)]'
                        }`}
                    >
                        <Icon size={20} />
                        <span>{item.name}</span>
                    </Link>
                );
            })}
        </div>
    );
}
