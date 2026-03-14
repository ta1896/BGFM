import React from 'react';

const variants = {
    info: 'border-cyan-500/20 bg-cyan-500/10 text-cyan-200',
    success: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-200',
    warning: 'border-amber-500/20 bg-amber-500/10 text-amber-200',
    error: 'border-rose-500/20 bg-rose-500/10 text-rose-200',
};

export default function StatusMessage({ children, variant = 'info', className = '' }) {
    if (!children) {
        return null;
    }

    return (
        <div className={`rounded-2xl border px-4 py-3 text-sm font-medium ${variants[variant] ?? variants.info} ${className}`}>
            {children}
        </div>
    );
}
