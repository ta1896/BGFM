import React from 'react';

export function PageReveal({ as: Component = 'div', delay = 0, className = '', children, ...props }) {
    return (
        <Component
            className={`page-reveal ${className}`.trim()}
            style={{ '--reveal-delay': `${delay}ms`, ...props.style }}
            {...props}
        >
            {children}
        </Component>
    );
}

export function StaggerGroup({ as: Component = 'div', className = '', children, ...props }) {
    return (
        <Component className={`stagger-group ${className}`.trim()} {...props}>
            {children}
        </Component>
    );
}
