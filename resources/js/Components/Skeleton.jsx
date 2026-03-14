import React from 'react';

const Skeleton = ({ className = '', variant = 'rect' }) => {
    const baseClasses = "bg-white/5 animate-pulse overflow-hidden relative";
    
    const variantClasses = {
        circle: "rounded-full",
        rect: "rounded-lg",
        text: "rounded h-4 w-full",
    };

    return (
        <div className={`${baseClasses} ${variantClasses[variant]} ${className}`}>
            <div className="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-white/5 to-transparent"></div>
        </div>
    );
};

export default Skeleton;
