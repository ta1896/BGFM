import React, { useMemo, useRef } from 'react';
import { usePage } from '@inertiajs/react';
import { CSSTransition, SwitchTransition } from 'react-transition-group';

export default function PageTransition({ children, className = '' }) {
    const { url, component } = usePage();
    const nodeRefs = useRef(new Map());

    const transitionKey = useMemo(() => `${component}:${url}`, [component, url]);

    if (!nodeRefs.current.has(transitionKey)) {
        nodeRefs.current.set(transitionKey, React.createRef());
    }

    const nodeRef = nodeRefs.current.get(transitionKey);

    return (
        <SwitchTransition mode="out-in">
            <CSSTransition key={transitionKey} nodeRef={nodeRef} classNames="page-shell" timeout={420} unmountOnExit>
                <div ref={nodeRef} className={`page-shell ${className}`}>
                    {children}
                </div>
            </CSSTransition>
        </SwitchTransition>
    );
}
