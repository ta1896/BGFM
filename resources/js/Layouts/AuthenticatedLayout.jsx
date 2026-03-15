import React, { useState } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { CaretDown, Gear, SignOut, List, X, Bell, Users } from '@phosphor-icons/react';
import ThemeSwitcher from '@/Components/ThemeSwitcher';
import UserAvatar from '@/Components/UserAvatar';
import AppHeader from '@/Components/layout/AppHeader';
import LiveMatchesIndicator from '@/Components/layout/LiveMatchesIndicator';
import LayoutFrame from '@/Components/layout/LayoutFrame';
import SidebarBrand from '@/Components/layout/SidebarBrand';
import SidebarNavigation from '@/Components/layout/SidebarNavigation';
import { findActiveMenuLabel, getManagerMenuGroups, mergeMenuGroups } from '@/Layouts/navigation';

export default function AuthenticatedLayout({ header, children }) {
    const { auth, activeClub, userClubs = [], flash, live, modules = {} } = usePage().props;
    const currentTheme = auth.theme || 'catalyst';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [clubSelectorOpen, setClubSelectorOpen] = useState(false);
    const currentRoute = route().current();

    const hasManagedClub = auth.isAdmin || userClubs.length > 0;
    const menuGroups = mergeMenuGroups(
        getManagerMenuGroups({ hasManagedClub }),
        modules.manager_navigation,
    );
    const activeMenuLabel = findActiveMenuLabel(menuGroups, currentRoute, 'Dashboard');

    return (
        <LayoutFrame
            themeClassName={`theme-${currentTheme}`}
            sidebarOpen={sidebarOpen}
            onCloseSidebar={() => setSidebarOpen(false)}
            mobileTopbar={(
                <div className="lg:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3 bg-[var(--bg-pillar)]/80 backdrop-blur-xl border-b border-[var(--border-pillar)]">
                    <Link href={route('dashboard')} className="flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#d9b15c] to-[#8d6e32]">
                            <span className="text-xs font-bold text-black">NW</span>
                        </div>
                        <span className="font-bold text-white tracking-tight">OpenWS</span>
                    </Link>
                    <button onClick={() => setSidebarOpen((open) => !open)} className="p-2 text-[var(--text-muted)] hover:text-white">
                        {sidebarOpen ? <X size={24} /> : <List size={24} />}
                    </button>
                </div>
            )}
            sidebar={(
                <aside className={`${sidebarOpen ? 'translate-x-0' : '-translate-x-full'} lg:translate-x-0 sim-sidebar-floating shadow-2xl flex h-full flex-col overflow-hidden`}>
                    <SidebarBrand href={route('dashboard')} badge="NW" title="NewGen" subtitle="Management Suite" />

                    <SidebarNavigation
                        menuGroups={menuGroups}
                        currentRoute={currentRoute}
                        className="min-h-0 flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar"
                        activeTextClassName="text-[var(--text-main)] bg-[var(--bg-content)]/50"
                        inactiveTextClassName="text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--bg-content)]/30"
                        labelClassName="text-[10px] font-bold uppercase tracking-widest group-hover/btn:text-amber-500 transition-colors"
                    />

                    <div className="shrink-0 border-t border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 p-4">
                        {hasManagedClub && userClubs.length > 1 && (
                            <ClubSwitcher
                                activeClub={activeClub}
                                userClubs={userClubs}
                                open={clubSelectorOpen}
                                onToggle={() => setClubSelectorOpen((open) => !open)}
                                onSelectClub={(clubId) => {
                                    router.get(route('dashboard', { club: clubId }));
                                    setClubSelectorOpen(false);
                                }}
                            />
                        )}

                        {auth.isAdmin && (
                            <div className="px-4 mb-2">
                                <Link
                                    href={route('admin.dashboard')}
                                    className="flex w-full items-center justify-center gap-2 py-2.5 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-[10px] font-black uppercase tracking-widest text-amber-500 hover:bg-amber-500 hover:text-black transition-all shadow-lg"
                                >
                                    <Gear size={16} weight="bold" />
                                    Admin Control Panel
                                </Link>
                            </div>
                        )}

                        <div className="flex items-center gap-3 rounded-2xl p-2 transition hover:bg-[var(--bg-content)]/50 group border border-transparent hover:border-[var(--border-pillar)]/30">
                            <UserAvatar
                                name={auth.user.name}
                                className="h-9 w-9 overflow-hidden rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] flex-shrink-0 p-0.5"
                                textClassName="text-xs font-black text-black"
                            />
                            <div className="min-w-0 flex-1">
                                <p className="truncate text-sm font-semibold text-white leading-tight">{auth.user.name}</p>
                                <p className="truncate text-[10px] font-bold uppercase tracking-widest text-[var(--text-muted)] text-left">
                                    {auth.isAdmin ? 'Administrator' : 'Manager'}
                                </p>
                            </div>

                            <div className="flex items-center gap-1 opacity-60 group-hover:opacity-100 transition-opacity">
                                <Link
                                    href={route('profile.edit')}
                                    className="p-1.5 text-[var(--text-muted)] hover:text-amber-400 hover:bg-slate-700/50 rounded-lg transition"
                                    title="Profil"
                                >
                                    <Users size={18} />
                                </Link>

                                <button
                                    onClick={() => router.post(route('logout'))}
                                    className="p-1.5 text-[var(--text-muted)] hover:text-rose-400 hover:bg-slate-700/50 rounded-lg transition"
                                    title="Logout"
                                >
                                    <SignOut size={18} />
                                </button>
                            </div>
                        </div>
                    </div>
                </aside>
            )}
            header={(
                <AppHeader>
                    {header ? (
                        header
                    ) : (
                        <div className="flex items-center justify-between w-full text-left">
                            <div>
                                <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5">Scouting & Analysis</p>
                                <h1 className="text-xl font-black text-white italic uppercase tracking-tight leading-none">{activeMenuLabel}</h1>
                            </div>

                            <div className="flex items-center gap-4">
                                <LiveMatchesIndicator count={live?.matches_count ?? 0} />
                                <ThemeSwitcher />
                                <button className="relative p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition">
                                    <Bell size={20} />
                                    <span className="absolute top-2 right-2 h-2 w-2 rounded-full bg-amber-500 border border-black" />
                                </button>
                            </div>
                        </div>
                    )}
                </AppHeader>
            )}
            flashStatus={flash.status}
            mainClassName="lg:ml-80 pt-14 lg:pt-0"
        >
            {children}
        </LayoutFrame>
    );
}

function ClubSwitcher({ activeClub, userClubs, open, onToggle, onSelectClub }) {
    return (
        <div className="relative mb-3">
            <button
                onClick={onToggle}
                className="flex w-full items-center gap-3 rounded-lg bg-[var(--bg-content)]/60 p-2 text-left hover:bg-[var(--bg-content)] transition border border-[var(--border-muted)] overflow-hidden"
            >
                {activeClub ? (
                    <>
                        <div className="h-8 w-8 rounded-full overflow-hidden bg-[var(--bg-pillar)] border border-slate-600 flex-shrink-0">
                            <img loading="lazy" src={activeClub.logo_url} className="h-full w-full object-contain" alt={activeClub.name} />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-xs text-[var(--text-muted)] uppercase tracking-wider font-bold">Aktiver Verein</p>
                            <p className="truncate text-sm font-bold text-white leading-tight">{activeClub.name}</p>
                        </div>
                    </>
                ) : (
                    <div className="text-sm font-medium text-slate-300 px-2 py-1">Verein waehlen</div>
                )}
                <CaretDown size={14} className={`transition-transform duration-200 ${open ? 'rotate-180' : ''}`} />
            </button>

            {open && (
                <div className="absolute bottom-full left-0 mb-2 w-full rounded-xl bg-[var(--bg-content)] border border-[var(--border-pillar)] shadow-2xl overflow-hidden z-[60] max-h-64 overflow-y-auto custom-scrollbar">
                    <div className="p-1.5 space-y-1">
                        {userClubs.map((club) => (
                            <button
                                key={club.id}
                                onClick={() => onSelectClub(club.id)}
                                className={`w-full flex items-center gap-3 rounded-lg px-2 py-2 text-sm transition group ${
                                    activeClub?.id === club.id
                                        ? 'bg-indigo-600/20 text-indigo-300'
                                        : 'text-slate-300 hover:bg-slate-700/60 hover:text-white'
                                }`}
                            >
                                <div className="h-7 w-7 rounded-full overflow-hidden bg-[var(--bg-pillar)] border border-[var(--border-pillar)] flex-shrink-0">
                                    <img loading="lazy" src={club.logo_url} className="h-full w-full object-contain group-hover:scale-110 transition" alt={club.name} />
                                </div>
                                <span className="truncate flex-1 text-left font-medium">{club.name}</span>
                                {activeClub?.id === club.id && (
                                    <div className="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(217,177,92,0.6)]" />
                                )}
                            </button>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
}
