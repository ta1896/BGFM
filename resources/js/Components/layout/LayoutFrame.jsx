import React from 'react';
import FlashStatus from '@/Components/layout/FlashStatus';
import PageTransition from '@/Components/PageTransition';

export default function LayoutFrame({
    themeClassName,
    sidebarOpen,
    onCloseSidebar,
    mobileTopbar,
    sidebar,
    header,
    flashStatus,
    children,
    mainClassName = 'lg:ml-80',
    contentClassName = 'flex-1 overflow-y-auto px-4 py-8 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full custom-scrollbar',
}) {
    return (
        <div className={`min-h-screen bg-[var(--sim-shell-bg)] text-[var(--text-main)] font-sans lg:p-4 flex gap-4 transition-all duration-500 ${themeClassName}`}>
            {mobileTopbar}
            {sidebar}

            <div className={`flex-1 flex flex-col transition-all duration-300 ${mainClassName}`}>
                <div className="sim-content-floating lg:h-[calc(100vh-2rem)] flex flex-col relative">
                    {header}

                    <main className={contentClassName}>
                        <FlashStatus status={flashStatus} />
                        <PageTransition>
                            {children}
                        </PageTransition>
                    </main>
                </div>
            </div>

            {sidebarOpen && (
                <div
                    onClick={onCloseSidebar}
                    className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                />
            )}

            <style dangerouslySetInnerHTML={{ __html: `
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #334155; }
            `}} />
        </div>
    );
}
