import React, { useEffect, useMemo, useRef, useState } from 'react';
import { usePage } from '@inertiajs/react';
import { CSSTransition, SwitchTransition } from 'react-transition-group';

export default function PageTransition({ children, className = '' }) {
    const { url, component } = usePage();
    const nodeRefs = useRef(new Map());
    const [transitionsEnabled, setTransitionsEnabled] = useState(false);

    const transitionKey = useMemo(() => `${component}:${url}`, [component, url]);

    useEffect(() => {
        setTransitionsEnabled(true);
    }, []);

    if (!nodeRefs.current.has(transitionKey)) {
        nodeRefs.current.set(transitionKey, React.createRef());
    }

    const nodeRef = nodeRefs.current.get(transitionKey);

    if (!transitionsEnabled) {
        return (
            <div ref={nodeRef} className={className}>
                {children}
            </div>
        );
    }

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
