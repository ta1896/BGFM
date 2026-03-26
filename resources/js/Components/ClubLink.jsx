import React from 'react';
import { Link } from '@inertiajs/react';

export default function ClubLink({ id, name, className = '', title }) {
    if (!name) return null;
    if (!id) return <span className={className}>{name}</span>;

    return (
        <Link
            href={route('clubs.show', id)}
            className={`${className} transition-colors hover:text-[var(--accent-primary)]`}
            title={title || `${name} ansehen`}
        >
            {name}
        </Link>
    );
}
