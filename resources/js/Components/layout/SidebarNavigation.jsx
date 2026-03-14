import React from 'react';
import SidebarMenuGroup from '@/Components/SidebarMenuGroup';

export default function SidebarNavigation({
    menuGroups,
    currentRoute,
    className,
    activeTextClassName,
    inactiveTextClassName,
    labelClassName,
}) {
    return (
        <nav className={className}>
            {Object.entries(menuGroups).map(([key, group]) => (
                <SidebarMenuGroup
                    key={key}
                    group={group}
                    currentRoute={currentRoute}
                    autoOpenActive
                    activeTextClassName={activeTextClassName}
                    inactiveTextClassName={inactiveTextClassName}
                    labelClassName={labelClassName}
                />
            ))}
        </nav>
    );
}
