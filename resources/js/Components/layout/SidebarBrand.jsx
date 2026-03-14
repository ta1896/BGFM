import React from 'react';
import { Link } from '@inertiajs/react';

export default function SidebarBrand({ href, badge, title, subtitle, className = '', interactive = true }) {
    return (
        <div className={`flex h-16 shrink-0 items-center px-6 border-b border-gray-800/40 ${className}`}>
            <Link
                href={href}
                className={`flex items-center gap-3 ${interactive ? 'group rounded-lg py-1 pr-2 -m-1 transition-colors hover:bg-white/5' : ''}`}
            >
                <div className={`flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#d9b15c] to-[#8d6e32] shadow-md shadow-amber-900/10 ${interactive ? 'transition group-hover:shadow-amber-500/20' : ''}`}>
                    <span className="text-sm font-black text-black">{badge}</span>
                </div>
                <div className="min-w-0">
                    <p className="font-black text-white leading-tight tracking-[0.05em] uppercase truncate">{title}</p>
                    <p className={`text-[10px] font-black uppercase tracking-[0.2em] ${interactive ? 'text-gray-500 group-hover:text-amber-500 transition-colors' : 'text-amber-500'}`}>
                        {subtitle}
                    </p>
                </div>
            </Link>
        </div>
    );
}
