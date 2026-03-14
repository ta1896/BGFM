import React, { useState, useEffect } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    House, Tray, Bank, Briefcase, BuildingOffice, UsersThree,
    Users, GraduationCap, Tent, Calendar, Trophy, HandPeace,
    Star, FileText,
    MagnifyingGlass, Gear, CaretDown, SignOut, List, X,
    CaretRight, Bell
} from '@phosphor-icons/react';
import ThemeSwitcher from '@/Components/ThemeSwitcher';

const MenuGroup = React.memo(({ group, currentRoute }) => {
    const [isOpen, setIsOpen] = useState(false);

    // Check if any item in this group is active
    useEffect(() => {
        const hasActive = group.items.some(item => {
            if (item.active.endsWith('.*')) {
                const base = item.active.replace('.*', '');
                return currentRoute ? currentRoute.startsWith(base) : false;
            }
            return currentRoute === item.active;
        });
        if (hasActive) setIsOpen(true);
    }, [currentRoute, group.items]);

    return (
        <div className="mb-2">
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="flex w-full items-center justify-between px-3 py-2 text-[var(--text-muted)] hover:text-[var(--text-main)] transition-colors group/btn rounded-lg hover:bg-[var(--bg-content)]/50 focus:outline-none"
            >
                <span className="text-[10px] font-bold uppercase tracking-widest group-hover/btn:text-amber-500 transition-colors">
                    {group.label}
                </span>
                <CaretDown
                    size={14}
                    weight="bold"
                    className={`transition-transform duration-200 ${isOpen ? 'rotate-180 text-amber-500' : 'text-gray-600'}`}
                />
            </button>
            <AnimatePresence initial={false}>
                {isOpen && (
                    <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.2, ease: "easeInOut" }}
                        className="space-y-0.5 mt-1 pl-3 ml-2 border-l border-amber-500/10 overflow-hidden"
                    >
                        {group.items.map((item, idx) => {
                            const isActive = item.active.endsWith('.*')
                                ? (currentRoute ? currentRoute.startsWith(item.active.replace('.*', '')) : false)
                                : currentRoute === item.active;

                            return (
                                <Link
                                    key={idx}
                                    href={route(item.route)}
                                    className={`flex items-center gap-3 px-3 py-2 text-sm font-medium transition-[color,background-color] rounded-lg group ${
                                        isActive
                                            ? 'text-[var(--text-main)] bg-[var(--bg-content)]/50'
                                            : 'text-[var(--text-muted)] hover:text-[var(--text-main)] hover:bg-[var(--bg-content)]/30'
                                    }`}
                                >
                                    {isActive ? (
                                        <div className="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]" />
                                    ) : (
                                        <div className="w-1.5 h-1.5 rounded-full bg-gray-800 group-hover:bg-amber-800 transition-colors" />
                                    )}
                                    {item.label}
                                </Link>
                            );
                        })}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
});

export default function AuthenticatedLayout({ header, children }) {
    const { auth, activeClub, userClubs, flash } = usePage().props;
    const currentTheme = auth.theme || 'catalyst';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [clubSelectorOpen, setClubSelectorOpen] = useState(false);
    const currentRoute = route().current();

    const hasManagedClub = auth.isAdmin || (userClubs && userClubs.length > 0);

    const getMenuGroups = () => {
        const groups = {};

        if (hasManagedClub) {
            groups.bg_buro = {
                label: 'Büro',
                items: [
                    { route: 'dashboard', label: 'Dashboard', active: 'dashboard', icon: House },
                    { route: 'notifications.index', label: 'Postfach', active: 'notifications.*', icon: Tray },
                    { route: 'finances.index', label: 'Finanzen', active: 'finances.*', icon: Bank },
                    { route: 'sponsors.index', label: 'Sponsoren', active: 'sponsors.*', icon: Briefcase },
                    { route: 'stadium.index', label: 'Stadion', active: 'stadium.*', icon: BuildingOffice },
                ]
            };

            groups.bg_team = {
                label: 'Team',
                items: [
                    { route: 'lineups.index', label: 'Aufstellung', active: 'lineups.*', icon: UsersThree },
                    { route: 'players.index', label: 'Kader', active: 'players.*', icon: Users },
                    { route: 'training.index', label: 'Training', active: 'training.*', icon: GraduationCap },
                    { route: 'training-camps.index', label: 'Trainingslager', active: 'training-camps.*', icon: Tent },
                ]
            };

            groups.bg_wettbewerb = {
                label: 'Wettbewerb',
                items: [
                    { route: 'league.matches', label: 'Spiele', active: 'league.matches', icon: Calendar },
                    { route: 'league.table', label: 'Tabelle', active: 'league.table', icon: Trophy },
                    { route: 'statistics.index', label: 'Statistiken', active: 'statistics.*', icon: Star },
                    { route: 'teams.compare', label: 'Vergleich', active: 'teams.*', icon: MagnifyingGlass },
                    { route: 'team-of-the-day.index', label: 'Team der Woche', active: 'team-of-the-day.*', icon: Star },
                    { route: 'friendlies.index', label: 'Freundschaft', active: 'friendlies.*', icon: HandPeace },
                ]
            };

            groups.bg_markt = {
                label: 'Markt',
                items: [
                    { route: 'contracts.index', label: 'Verträge', active: 'contracts.*', icon: FileText },
                    { route: 'clubs.index', label: 'Vereins-Suche', active: 'clubs.*', icon: MagnifyingGlass },
                ]
            };
        } else {
            groups.bg_start = {
                label: 'Start',
                items: [
                    { route: 'dashboard', label: 'Dashboard', active: 'dashboard', icon: House },
                    { route: 'clubs.free', label: 'Verein wählen', active: 'clubs.free', icon: MagnifyingGlass },
                    { route: 'profile.edit', label: 'Profil', active: 'profile.*', icon: Users },
                ]
            };
        }

        if (auth.isAdmin) {
            // Admin links are now ONLY in the Admin Panel
        }

        return groups;
    };

    const menuGroups = getMenuGroups();

    // Find active menu label
    let activeMenuLabel = 'Dashboard';
    Object.values(menuGroups).forEach(group => {
        group.items.forEach(item => {
            if (item.active.endsWith('.*')) {
                if (currentRoute?.startsWith(item.active.replace('.*', ''))) activeMenuLabel = item.label;
            } else if (currentRoute === item.active) {
                activeMenuLabel = item.label;
            }
        });
    });

    return (
        <div className={`min-h-screen bg-[var(--sim-shell-bg)] text-[var(--text-main)] font-sans lg:p-4 flex gap-4 transition-all duration-500 theme-${currentTheme}`}>
            {/* Mobile Sidebar Toggle */}
            <div className="lg:hidden fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-3 bg-[var(--bg-pillar)]/80 backdrop-blur-xl border-b border-[var(--border-pillar)]">
                <Link href={route('dashboard')} className="flex items-center gap-2">
                    <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#d9b15c] to-[#8d6e32]">
                        <span className="text-xs font-bold text-black">NW</span>
                    </div>
                    <span className="font-bold text-white tracking-tight">OpenWS</span>
                </Link>
                <button onClick={() => setSidebarOpen(!sidebarOpen)} className="p-2 text-[var(--text-muted)] hover:text-white">
                    {sidebarOpen ? <X size={24} /> : <List size={24} />}
                </button>
            </div>

            {/* Sidebar */}
            <aside className={`
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
                lg:translate-x-0 sim-sidebar-floating shadow-2xl
            `}>
                {/* Branding */}
                <div className="flex h-16 shrink-0 items-center px-6 border-b border-gray-800/40">
                    <Link href={route('dashboard')} className="flex items-center gap-3 group rounded-lg py-1 pr-2 -m-1 transition-colors hover:bg-white/5">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#d9b15c] to-[#8d6e32] shadow-md shadow-amber-900/10 transition group-hover:shadow-amber-500/20">
                            <span className="text-sm font-black text-black">NW</span>
                        </div>
                        <div className="min-w-0">
                            <p className="font-black text-white leading-tight tracking-[0.05em] uppercase truncate">NewGen</p>
                            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500 group-hover:text-amber-500 transition-colors">Management Suite</p>
                        </div>
                    </Link>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto px-3 py-4 space-y-1 custom-scrollbar" style={{ maxHeight: 'calc(100vh - 160px)' }}>
                    {Object.entries(menuGroups).map(([key, group]) => (
                        <MenuGroup key={key} group={group} currentRoute={currentRoute} />
                    ))}
                </nav>

                {/* User Profile Footer */}
                <div className="absolute bottom-0 left-0 right-0 border-t border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 p-4">
                    {hasManagedClub && userClubs.length > 1 && (
                        <div className="relative mb-3">
                            <button 
                                onClick={() => setClubSelectorOpen(!clubSelectorOpen)}
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
                                    <div className="text-sm font-medium text-slate-300 px-2 py-1">Verein wählen</div>
                                )}
                                <CaretDown size={14} className={`transition-transform duration-200 ${clubSelectorOpen ? 'rotate-180' : ''}`} />
                            </button>

                            <AnimatePresence>
                                {clubSelectorOpen && (
                                    <motion.div
                                        initial={{ opacity: 0, y: 10, scale: 0.95 }}
                                        animate={{ opacity: 1, y: 0, scale: 1 }}
                                        exit={{ opacity: 0, y: 10, scale: 0.95 }}
                                        className="absolute bottom-full left-0 mb-2 w-full rounded-xl bg-[var(--bg-content)] border border-[var(--border-pillar)] shadow-2xl overflow-hidden z-[60] max-h-64 overflow-y-auto custom-scrollbar"
                                    >
                                        <div className="p-1.5 space-y-1">
                                            {userClubs.map(club => (
                                                <button
                                                    key={club.id}
                                                    onClick={() => {
                                                        router.get(route('dashboard', { club: club.id }));
                                                        setClubSelectorOpen(false);
                                                    }}
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
                                    </motion.div>
                                )}
                            </AnimatePresence>
                        </div>
                    )}

                {/* ACP Switcher for Admins */}
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
                    <div className="h-9 w-9 overflow-hidden rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] flex-shrink-0 p-0.5">
                         <img loading="lazy" 
                            src={`https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=0a0b0d&color=d9b15c`} 
                            alt={auth.user.name}
                            className="w-full h-full rounded-full"
                         />
                    </div>
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

        <div className="flex-1 flex flex-col transition-all duration-300 lg:ml-80 pt-14 lg:pt-0">
            <div className="sim-content-floating lg:h-[calc(100vh-2rem)] flex flex-col relative">
                {/* Header */}
                <header className="bg-[var(--bg-pillar)]/60 backdrop-blur-xl border-b border-[var(--border-muted)] shrink-0">
                    <div className="px-6 py-4 min-h-[4.5rem] flex items-center justify-between">
                        {header ? (
                            header
                        ) : (
                            <div className="flex items-center justify-between w-full text-left">
                                <div>
                                    <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5">Scouting & Analysis</p>
                                    <h1 className="text-xl font-black text-white italic uppercase tracking-tight leading-none">{activeMenuLabel}</h1>
                                </div>
                                
                                <div className="flex items-center gap-4">
                                    <ThemeSwitcher />
                                    <button className="relative p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition">
                                        <Bell size={20} />
                                        <span className="absolute top-2 right-2 h-2 w-2 rounded-full bg-amber-500 border border-black" />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                    {/* Progress indicator gradient line */}
                    <div className="h-px w-full bg-gradient-to-r from-transparent via-amber-500/20 to-transparent" />
                </header>

                <main className="flex-1 overflow-y-auto px-4 py-8 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full custom-scrollbar">
                    {flash.status && (
                        <motion.div 
                            initial={{ opacity: 0, y: -20 }}
                            animate={{ opacity: 1, y: 0 }}
                            className="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 shadow-xl shadow-emerald-500/5 text-left"
                        >
                            {flash.status}
                        </motion.div>
                    )}
                    
                    {children}
                </main>
            </div>
        </div>

            {/* Backdrop for mobile */}
            <AnimatePresence>
                {sidebarOpen && (
                    <motion.div 
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        onClick={() => setSidebarOpen(false)}
                        className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
                    />
                )}
            </AnimatePresence>
            
            <style dangerouslySetInnerHTML={{ __html: `
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #334155; }
            `}} />
        </div>
    );
}
