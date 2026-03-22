import React, { useState } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { SignOut, List, X, ArrowLeft } from '@phosphor-icons/react';
import ThemeSwitcher from '@/Components/ThemeSwitcher';
import UserAvatar from '@/Components/UserAvatar';
import AppHeader from '@/Components/layout/AppHeader';
import LiveMatchesIndicator from '@/Components/layout/LiveMatchesIndicator';
import LayoutFrame from '@/Components/layout/LayoutFrame';
import SidebarBrand from '@/Components/layout/SidebarBrand';
import SidebarNavigation from '@/Components/layout/SidebarNavigation';
import { findActiveMenuLabel, transformDynamicNavigation } from '@/Layouts/navigation';

export default function AdminLayout({ header, children }) {
    const { auth, flash, live, modules = {}, navigation = {} } = usePage().props;
    const currentTheme = auth.theme || 'catalyst';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentRoute = route().current();

    const dynamicAdminNav = transformDynamicNavigation(navigation.admin);
    const activeMenuLabel = findActiveMenuLabel(dynamicAdminNav, currentRoute, 'Admin Dashboard');

    return (
        <LayoutFrame
            themeClassName={`theme-${currentTheme}`}
            sidebarOpen={sidebarOpen}
            onCloseSidebar={() => setSidebarOpen(false)}
            sidebar={(
                <aside className={`${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 sim-sidebar-floating shadow-2xl flex h-full flex-col overflow-hidden`}>
                    <SidebarBrand href={route('admin.dashboard')} badge="ACP" title="Control Panel" subtitle="System Admin" interactive={false} />

                    <div className="px-5 py-6">
                        <Link
                            href={route('dashboard')}
                            className="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl bg-amber-500/10 hover:bg-amber-500 border border-amber-500/20 text-xs font-bold uppercase tracking-widest text-amber-500 hover:text-black transition-all group shadow-[0_0_15px_rgba(217,177,92,0.05)]"
                        >
                            <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
                            Manager Modus
                        </Link>
                    </div>

                    <SidebarNavigation
                        menuGroups={dynamicAdminNav}
                        currentRoute={currentRoute}
                        className="min-h-0 flex-1 overflow-y-auto px-3 py-2 space-y-1 custom-scrollbar"
                        activeTextClassName="text-white bg-[var(--bg-content)]/50"
                        inactiveTextClassName="text-[var(--text-muted)] hover:text-white hover:bg-[var(--bg-content)]/30"
                        labelClassName="text-[10px] font-black uppercase tracking-[0.2em] group-hover/btn:text-amber-500 transition-colors"
                    />

                    <div className="shrink-0 border-t border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 p-4">
                        <div className="flex items-center gap-3">
                            <UserAvatar
                                name={auth.user.name}
                                className="h-9 w-9 overflow-hidden rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] flex-shrink-0 p-0.5"
                                textClassName="text-xs font-black text-black"
                            />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold text-white leading-tight">{auth.user.name}</p>
                                <p className="truncate text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)]">System Admin</p>
                            </div>
                            <button
                                onClick={() => router.post(route('logout'))}
                                className="p-1.5 text-[var(--text-muted)] hover:text-rose-400 hover:bg-slate-700/50 rounded-lg transition"
                            >
                                <SignOut size={18} />
                            </button>
                        </div>
                    </div>
                </aside>
            )}
            header={(
                <AppHeader>
                    {header ? (
                        header
                    ) : (
                        <>
                            <div className="text-left">
                                <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 mb-0.5">Control Panel Administration</p>
                                <h1 className="text-xl font-black text-white italic uppercase tracking-tight leading-none">{activeMenuLabel}</h1>
                            </div>

                            <div className="flex items-center gap-4">
                                <LiveMatchesIndicator count={live?.matches_count ?? 0} />
                                <ThemeSwitcher />
                                <button className="lg:hidden p-2 text-[var(--text-muted)]" onClick={() => setSidebarOpen((open) => !open)}>
                                    {sidebarOpen ? <X size={24} /> : <List size={24} />}
                                </button>
                            </div>
                        </>
                    )}
                </AppHeader>
            )}
            flashStatus={flash.status}
        >
            {children}
        </LayoutFrame>
    );
}
