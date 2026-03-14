import React, { useState } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    House, Gear, Trophy, Calendar, BuildingOffice, Users, 
    FileText, Bell, SignOut, List, X, CaretDown, CaretRight,
    ArrowLeft
} from '@phosphor-icons/react';
import ThemeSwitcher from '@/Components/ThemeSwitcher';

const MenuGroup = React.memo(({ group, currentRoute }) => {
    const [isOpen, setIsOpen] = useState(true);
    
    return (
        <div className="mb-2">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className="flex w-full items-center justify-between px-3 py-2 text-[var(--text-muted)] hover:text-white transition-colors group/btn rounded-lg hover:bg-[var(--bg-content)]/50 focus:outline-none"
            >
                <span className="text-[10px] font-black uppercase tracking-[0.2em] group-hover/btn:text-amber-500 transition-colors">
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
                                ? currentRoute.startsWith(item.active.replace('.*', ''))
                                : currentRoute === item.active;
                            
                            return (
                                <Link
                                    key={idx}
                                    href={route(item.route)}
                                    className={`flex items-center gap-3 px-3 py-2 text-sm font-medium transition-[color,background-color] rounded-lg group ${
                                        isActive 
                                            ? 'text-white bg-[var(--bg-content)]/50' 
                                            : 'text-[var(--text-muted)] hover:text-white hover:bg-[var(--bg-content)]/30'
                                    }`}
                                >
                                    {isActive ? (
                                        <div className="w-1.5 h-1.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]" />
                                    ) : (
                                        <div className="w-1.5 h-1.5 rounded-full bg-gray-800 group-hover:bg-amber-800 transition-colors" />
                                    )}
                                    {isActive ? item.label : item.label}
                                </Link>
                            );
                        })}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
});

export default function AdminLayout({ header, children }) {
    const { auth, flash } = usePage().props;
    const currentTheme = auth.theme || 'catalyst';
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const currentRoute = route().current();

    const menuGroups = {
        bg_main: {
            label: 'System',
            items: [
                { route: 'admin.dashboard', label: 'ACP Übersicht', active: 'admin.dashboard', icon: Gear },
            ]
        },
        bg_data: {
            label: 'Datenpflege',
            items: [
                { route: 'admin.competitions.index', label: 'Wettbewerbe', active: 'admin.competitions.*', icon: Trophy },
                { route: 'admin.seasons.index', label: 'Saisons', active: 'admin.seasons.*', icon: Calendar },
                { route: 'admin.clubs.index', label: 'Vereine', active: 'admin.clubs.*', icon: BuildingOffice },
                { route: 'admin.players.index', label: 'Spieler', active: 'admin.players.*', icon: Users },
            ]
        },
        bg_engine: {
            label: 'Engine & Tools',
            items: [
                { route: 'admin.ticker-templates.index', label: 'Ticker Vorlagen', active: 'admin.ticker-templates.*', icon: FileText },
                { route: 'admin.match-engine.index', label: 'Match Engine', active: 'admin.match-engine.*', icon: Gear },
                { route: 'admin.monitoring.index', label: 'Monitoring & Debug', active: 'admin.monitoring.*', icon: Gear },
            ]
        }
    };

    // Find active menu label
    let activeMenuLabel = 'Admin Dashboard';
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
            {/* Sidebar */}
            <aside className={`
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
                lg:translate-x-0 sim-sidebar-floating shadow-2xl
            `}>
                {/* Branding */}
                <div className="flex h-16 shrink-0 items-center px-6 border-b border-gray-800/40">
                    <Link href={route('admin.dashboard')} className="flex items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#d9b15c] to-[#8d6e32] shadow-md shadow-amber-900/10">
                            <span className="text-sm font-black text-black">ACP</span>
                        </div>
                        <div className="min-w-0">
                            <p className="font-black text-white leading-tight tracking-[0.05em] uppercase truncate">Control Panel</p>
                            <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500">System Admin</p>
                        </div>
                    </Link>
                </div>
                {/* Return to Manager Button */}
                <div className="px-5 py-6">
                    <Link 
                        href={route('dashboard')}
                        className="flex items-center justify-center gap-2 w-full py-2.5 rounded-2xl bg-amber-500/10 hover:bg-amber-500 border border-amber-500/20 text-xs font-bold uppercase tracking-widest text-amber-500 hover:text-black transition-all group shadow-[0_0_15px_rgba(217,177,92,0.05)]"
                    >
                        <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
                        Manager Modus
                    </Link>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto px-3 py-2 space-y-1">
                    {Object.entries(menuGroups).map(([key, group]) => (
                        <MenuGroup key={key} group={group} currentRoute={currentRoute} />
                    ))}
                </nav>

                {/* User Info */}
                <div className="absolute bottom-0 left-0 right-0 border-t border-[var(--border-muted)] bg-[var(--bg-pillar)]/50 p-4">
                    <div className="flex items-center gap-3">
                         <div className="h-9 w-9 overflow-hidden rounded-full border border-[var(--border-pillar)] bg-[var(--bg-content)] flex-shrink-0 p-0.5">
                             <img loading="lazy" 
                                src={`https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=0a0b0d&color=d9b15c`} 
                                alt={auth.user.name}
                                className="w-full h-full rounded-full"
                             />
                        </div>
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

            {/* Main Content Area */}
            <div className="flex-1 flex flex-col transition-all duration-300 lg:ml-80">
                <div className="sim-content-floating lg:h-[calc(100vh-2rem)] flex flex-col relative">
                <header className="bg-[var(--bg-pillar)]/60 backdrop-blur-xl border-b border-[var(--border-muted)] shrink-0">
                    <div className="px-6 py-4 flex items-center justify-between min-h-[4.5rem]">
                        {header ? header : (
                            <div className="text-left">
                                <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500 mb-0.5">Control Panel Administration</p>
                                <h1 className="text-xl font-black text-white italic uppercase tracking-tight leading-none">{activeMenuLabel}</h1>
                            </div>
                        )}
                        
                        <div className="flex items-center gap-4">
                            <ThemeSwitcher />
                            <button className="lg:hidden p-2 text-[var(--text-muted)]" onClick={() => setSidebarOpen(!sidebarOpen)}>
                                {sidebarOpen ? <X size={24} /> : <List size={24} />}
                            </button>
                        </div>
                    </div>
                </header>

                    <main className="flex-1 overflow-y-auto px-4 py-8 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full custom-scrollbar">
                        {flash.status && (
                            <div className="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 shadow-xl shadow-emerald-500/5 text-left">
                                {flash.status}
                            </div>
                        )}
                        {children}
                    </main>
                </div>
            </div>
        </div>
    );
}
