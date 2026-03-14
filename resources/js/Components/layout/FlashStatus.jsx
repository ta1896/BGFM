import React from 'react';

export default function FlashStatus({ status }) {
    if (!status) {
        return null;
    }

    return (
        <div className="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 shadow-xl shadow-emerald-500/5 text-left">
            {status}
        </div>
    );
}
