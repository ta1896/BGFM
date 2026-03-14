import React from 'react';

export default function AuthField({
    label,
    icon: Icon,
    error,
    className = '',
    inputClassName = '',
    ...props
}) {
    return (
        <div className={className}>
            <label className="mb-2 block text-xs font-black uppercase tracking-widest text-[var(--text-muted)]">
                {label}
            </label>
            <div className="relative">
                {Icon && (
                    <div className="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-[var(--text-muted)]">
                        <Icon size={20} weight="duotone" />
                    </div>
                )}
                <input
                    {...props}
                    className={`w-full rounded-xl border border-white/5 bg-[var(--bg-content)]/50 py-4 pr-4 text-white transition-all font-medium focus:border-cyan-500/50 focus:outline-none focus:ring-2 focus:ring-cyan-500/50 ${Icon ? 'pl-12' : 'pl-4'} ${inputClassName}`}
                />
            </div>
            {error && (
                <div className="mt-2 text-xs font-bold text-rose-400">
                    {error}
                </div>
            )}
        </div>
    );
}
