import React from 'react';
import { Link } from '@inertiajs/react';

export default function PlayerLink({ id, name, className = '', title }) {
    if (!name) return null;
    if (!id) return <span className={className}>{name}</span>;

    return (
        <Link
            href={route('players.show', id)}
            className={`${className} underline decoration-white/10 underline-offset-4 transition-colors hover:text-white hover:decoration-current`}
            title={title || `${name} ansehen`}
        >
            {name}
        </Link>
    );
}
