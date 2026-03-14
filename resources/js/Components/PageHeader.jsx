import React from 'react';
import { PageReveal } from '@/Components/PageReveal';

export default function PageHeader({ eyebrow, title, actions = null, className = '' }) {
    return (
        <PageReveal className={`flex flex-col gap-4 md:flex-row md:items-end md:justify-between ${className}`.trim()}>
            <div>
                {eyebrow && <p className="sim-section-title">{eyebrow}</p>}
                <h1 className="text-4xl font-black tracking-tighter text-[var(--text-main)]">{title}</h1>
            </div>
            {actions}
        </PageReveal>
    );
}
