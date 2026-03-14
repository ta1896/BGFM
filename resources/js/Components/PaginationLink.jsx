import React from 'react';
import { Link } from '@inertiajs/react';

export default function PaginationLink({ link, className, disabledClassName, ...props }) {
    if (!link?.url) {
        return (
            <span
                dangerouslySetInnerHTML={{ __html: link?.label ?? '' }}
                className={disabledClassName}
                aria-disabled="true"
                {...props}
            />
        );
    }

    return (
        <Link
            href={link.url}
            dangerouslySetInnerHTML={{ __html: link.label }}
            className={className}
            {...props}
        />
    );
}
