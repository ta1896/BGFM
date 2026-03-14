import React from 'react';
import { router, usePage } from '@inertiajs/react';
import { Sparkle, Crosshair, Flame, SunHorizon } from '@phosphor-icons/react';

const themes = [
    {
        id: 'catalyst',
        name: 'Solar Forge',
        description: 'Volcanic ember and molten brass',
        icon: Flame,
        color: 'from-orange-400 to-amber-300',
    },
    {
        id: 'tactical',
        name: 'Tidal Vector',
        description: 'Aqua neon and tactical depth',
        icon: Crosshair,
        color: 'from-cyan-300 to-teal-500',
    },
    {
        id: 'elite',
        name: 'Nocturne Luxe',
        description: 'Electric orchid and glass haze',
        icon: Sparkle,
        color: 'from-fuchsia-300 to-cyan-300',
    },
    {
        id: 'classic',
        name: 'Dune Ledger',
        description: 'Warm paper and terracotta ink',
        icon: SunHorizon,
        color: 'from-orange-300 to-rose-400',
    }
];

export default function ThemeSwitcher() {
    const { auth } = usePage().props;
    const currentTheme = auth.theme || 'catalyst';
    
    const handleThemeChange = (themeId) => {
        if (themeId === currentTheme) return;
        
        router.patch(route('settings.update'), { theme: themeId }, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="flex items-center gap-1.5 p-1 rounded-xl border border-[var(--border-muted)] bg-[var(--bg-pillar)]/55 backdrop-blur-md">
            {themes.map((theme) => (
                <button
                    key={theme.id}
                    onClick={() => handleThemeChange(theme.id)}
                    title={`${theme.name} · ${theme.description}`}
                    className={`
                        relative flex items-center justify-center h-7 w-7 rounded-lg transition-all
                        ${currentTheme === theme.id 
                            ? 'bg-gradient-to-br ' + theme.color + ' text-black shadow-lg shadow-black/20 scale-105' 
                            : 'text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-white/5'}
                    `}
                >
                    <theme.icon size={14} weight={currentTheme === theme.id ? 'fill' : 'bold'} />
                    
                    {currentTheme === theme.id && (
                        <span className="absolute -bottom-0.5 h-0.5 w-0.5 rounded-full bg-current opacity-50" />
                    )}
                </button>
            ))}
        </div>
    );
}
