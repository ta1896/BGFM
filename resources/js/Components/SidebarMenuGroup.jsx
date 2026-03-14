import React, { useEffect, useState } from 'react';
import { Link } from '@inertiajs/react';
import { CaretDown } from '@phosphor-icons/react';

export default function SidebarMenuGroup({
    group,
    currentRoute,
    autoOpenActive = false,
    activeTextClassName,
    inactiveTextClassName,
    labelClassName,
    borderClassName = 'border-amber-500/10',
}) {
    const hasActiveItem = group.items.some(item => {
        if (item.active.endsWith('.*')) {
            return currentRoute ? currentRoute.startsWith(item.active.replace('.*', '')) : false;
        }

        return currentRoute === item.active;
    });

    const [isOpen, setIsOpen] = useState(() => (autoOpenActive ? hasActiveItem : false));

    useEffect(() => {
        if (!autoOpenActive) {
            return;
        }

        setIsOpen(hasActiveItem);
    }, [autoOpenActive, hasActiveItem]);

    return (
        <div className="mb-2">
            <button
                onClick={() => setIsOpen(open => !open)}
                className="flex w-full items-center justify-between px-3 py-2 text-[var(--text-muted)] transition-colors group/btn rounded-lg hover:bg-[var(--bg-content)]/50 focus:outline-none"
            >
                <span className={labelClassName}>
                    {group.label}
                </span>
                <CaretDown
                    size={14}
                    weight="bold"
                    className={`transition-transform duration-200 ${isOpen ? 'rotate-180 text-amber-500' : 'text-gray-600'}`}
                />
            </button>
            {isOpen && (
                <div className={`space-y-0.5 mt-1 pl-3 ml-2 border-l overflow-hidden ${borderClassName}`}>
                    {group.items.map((item, idx) => {
                        const isActive = item.active.endsWith('.*')
                            ? (currentRoute ? currentRoute.startsWith(item.active.replace('.*', '')) : false)
                            : currentRoute === item.active;

                        return (
                            <Link
                                key={idx}
                                href={route(item.route)}
                                className={`flex items-center gap-3 px-3 py-2 text-sm font-medium transition-[color,background-color] rounded-lg group ${
                                    isActive ? activeTextClassName : inactiveTextClassName
                                }`}
                            >
                                {isActive ? (
                                    <div className="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]" />
                                ) : (
                                    <div className="w-1.5 h-1.5 rounded-full bg-gray-800 group-hover:bg-amber-800 transition-colors" />
                                )}
                                {item.label}
                            </Link>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
