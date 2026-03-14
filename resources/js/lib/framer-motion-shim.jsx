import React from 'react';

const MOTION_PROPS = new Set([
    'animate',
    'exit',
    'initial',
    'layout',
    'layoutId',
    'transition',
    'variants',
    'viewport',
    'whileFocus',
    'whileHover',
    'whileInView',
    'whileTap',
]);

function stripMotionProps(props) {
    const nextProps = {};

    Object.entries(props).forEach(([key, value]) => {
        if (key === 'children' || !MOTION_PROPS.has(key)) {
            nextProps[key] = value;
        }
    });

    return nextProps;
}

export const motion = new Proxy(
    {},
    {
        get(_target, tag) {
            return function MotionShimComponent(props) {
                const { children } = props;
                const elementProps = stripMotionProps(props);
                return React.createElement(tag, elementProps, children);
            };
        },
    },
);

export function AnimatePresence({ children }) {
    return <>{children}</>;
}
