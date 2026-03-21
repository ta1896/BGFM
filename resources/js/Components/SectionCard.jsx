import React from 'react';

export default function SectionCard({ title, icon: Icon, children, className = '', bodyClassName = '', headerAction = null }) {
    return (
        <div className={`sim-card border-[var(--border-muted)] ${className}`.trim()}>
            {title && (
                <div className="flex items-center justify-between border-b border-[var(--border-muted)] bg-[var(--bg-pillar)]/40 px-6 py-4">
                    <div className="flex items-center gap-3">
                        {Icon && <Icon size={20} weight="duotone" className="text-[var(--accent-primary)]" />}
                        <h2 className="text-lg font-black uppercase tracking-tight text-[var(--text-main)]">{title}</h2>
                    </div>
                    {headerAction && (
                        <div className="flex items-center">
                            {headerAction}
                        </div>
                    )}
                </div>
            )}

            <div className={bodyClassName}>{children}</div>
        </div>
    );
}
