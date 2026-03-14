import React from 'react';

function initialsForName(name) {
    return (name ?? '')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(part => part[0]?.toUpperCase() ?? '')
        .join('') || '?';
}

export default function UserAvatar({ name, className = '', textClassName = '' }) {
    return (
        <div className={className} aria-hidden="true">
            <div className={`flex h-full w-full items-center justify-center rounded-full bg-gradient-to-br from-[#d9b15c] to-[#8d6e32] ${textClassName}`}>
                {initialsForName(name)}
            </div>
        </div>
    );
}
