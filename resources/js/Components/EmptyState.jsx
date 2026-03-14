import React from 'react';

export default function EmptyState({
    icon: Icon,
    title,
    description,
    action = null,
    className = '',
    compact = false,
}) {
    return (
        <div className={`flex flex-col items-center justify-center text-center ${compact ? 'p-10' : 'p-20'} ${className}`}>
            {Icon ? (
                <div className={`mb-6 flex items-center justify-center rounded-3xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)] text-slate-700 shadow-inner ${compact ? 'h-16 w-16' : 'h-24 w-24'}`}>
                    <Icon size={compact ? 32 : 48} weight="thin" />
                </div>
            ) : null}
            <h3 className="mb-2 text-2xl font-black uppercase tracking-tighter text-[var(--text-main)]">{title}</h3>
            {description ? (
                <p className="max-w-md font-medium text-[var(--text-muted)]">
                    {description}
                </p>
            ) : null}
            {action ? <div className="mt-6">{action}</div> : null}
        </div>
    );
}
