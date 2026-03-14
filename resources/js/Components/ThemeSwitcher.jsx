import React from 'react';
import { router, usePage } from '@inertiajs/react';
import { Sparkle, Crosshair, Crown, Sun } from '@phosphor-icons/react';

const themes = [
    {
        id: 'catalyst',
        name: 'The Catalyst',
        description: 'Luxury Gold & Deep Amber',
        icon: Crown,
        color: 'from-amber-400 to-amber-600',
    },
    {
        id: 'tactical',
        name: 'Tactical Blueprint',
        description: 'Tech Cyan & High Contrast',
        icon: Crosshair,
        color: 'from-cyan-400 to-blue-600',
    },
    {
        id: 'elite',
        name: 'Elite Suite',
        description: 'Silver Glass & Minimalist',
        icon: Sparkle,
        color: 'from-slate-200 to-slate-400',
    },
    {
        id: 'classic',
        name: 'Classic Light',
        description: 'Clean & Professional White',
        icon: Sun,
        color: 'from-blue-400 to-indigo-600',
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
        <div className="flex items-center gap-1.5 p-1 bg-black/20 rounded-xl border border-white/5 backdrop-blur-md">
            {themes.map((theme) => (
                <button
                    key={theme.id}
                    onClick={() => handleThemeChange(theme.id)}
                    title={theme.name}
                    className={`
                        relative flex items-center justify-center h-7 w-7 rounded-lg transition-all
                        ${currentTheme === theme.id 
                            ? 'bg-gradient-to-br ' + theme.color + ' text-black shadow-lg shadow-amber-900/10' 
                            : 'text-gray-500 hover:text-white hover:bg-white/5'}
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
