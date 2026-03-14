import React from 'react';

export default function AppHeader({ children }) {
    return (
        <header className="bg-[var(--bg-pillar)]/60 backdrop-blur-xl border-b border-[var(--border-muted)] shrink-0">
            <div className="px-6 py-4 min-h-[4.5rem] flex items-center justify-between">
                {children}
            </div>
            <div className="h-px w-full bg-gradient-to-r from-transparent via-amber-500/20 to-transparent" />
        </header>
    );
}
