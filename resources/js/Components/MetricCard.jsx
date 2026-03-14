import React from 'react';

export default function MetricCard({ title, value, unit, icon: Icon, accentClass = 'text-[var(--accent-primary)]', footer = null, className = '' }) {
    return (
        <div className={`sim-card p-6 relative overflow-hidden group border-[var(--border-muted)] ${className}`.trim()}>
            {Icon && (
                <div className={`absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity ${accentClass}`}>
                    <Icon size={80} weight="fill" />
                </div>
            )}
            <p className="mb-2 text-xs font-bold uppercase tracking-[0.2em] text-[var(--text-muted)]">{title}</p>
            <div className="flex items-baseline gap-2">
                <p className="text-3xl font-black tracking-tighter text-[var(--text-main)]">{value}</p>
                {unit && <span className="text-sm font-bold uppercase text-[var(--text-muted)]">{unit}</span>}
            </div>
            {footer}
        </div>
    );
}
