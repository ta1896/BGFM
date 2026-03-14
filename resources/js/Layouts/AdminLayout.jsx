import React, { useState } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import { 
    House, Gear, Trophy, Calendar, BuildingOffice, Users, 
    FileText, Bell, SignOut, List, X, CaretDown, CaretRight,
    ArrowLeft
} from '@phosphor-icons/react';

const MenuGroup = ({ group, currentRoute }) => {
    const [isOpen, setIsOpen] = useState(true);
    
    return (
        <div className="mb-2">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className="flex w-full items-center justify-between px-3 py-2 text-slate-400 hover:text-white transition group/btn rounded-lg hover:bg-slate-800/50 focus:outline-none"
            >
                <span className="text-[10px] font-bold uppercase tracking-widest group-hover/btn:text-cyan-400 transition-colors">
                    {group.label}
                </span>
                <CaretDown 
                    size={14} 
                    weight="bold"
                    className={`transition-transform duration-200 ${isOpen ? 'rotate-180 text-cyan-400' : 'text-slate-600'}`}
                />
            </button>
            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.2 }}
                        className="space-y-0.5 mt-1 pl-3 ml-2 border-l-2 border-slate-800/30 overflow-hidden"
                    >
                        {group.items.map((item, idx) => {
                            const isActive = item.active.endsWith('.*') 
                                ? currentRoute.startsWith(item.active.replace('.*', ''))
                                : currentRoute === item.active;
                            
                            return (
                                <Link
                                    key={idx}
                                    href={route(item.route)}
                                    className={`flex items-center gap-3 px-3 py-2 text-sm font-medium transition-all rounded-lg group ${
                                        isActive 
                                            ? 'text-white bg-slate-800/50' 
                                            : 'text-slate-400 hover:text-white hover:bg-slate-800/30'
                                    }`}
                                >
                                    {isActive ? (
                                        <div className="w-1.5 h-1.5 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.6)]" />
                                    ) : (
                                        <div className="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-slate-500 transition-colors" />
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
};

export default function AdminLayout({ header, children }) {
    const { auth, flash } = usePage().props;
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
        <div className="min-h-screen bg-[#0f172a] text-slate-100 font-sans">
            {/* Sidebar */}
            <aside className={`
                fixed inset-y-0 left-0 z-50 w-72 transform bg-slate-900/70 backdrop-blur-2xl border-r border-slate-800/30 transition-transform duration-300 ease-in-out lg:translate-x-0
                ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}
            `}>
                {/* Branding */}
                <div className="flex h-16 shrink-0 items-center px-6 border-b border-slate-800/30">
                    <Link href={route('admin.dashboard')} className="flex items-center gap-3">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-cyan-400 to-indigo-600">
                            <span className="text-sm font-bold text-white">ACP</span>
                        </div>
                        <div className="min-w-0">
                            <p className="font-bold text-white leading-tight tracking-tight">Admin Area</p>
                            <p className="text-[10px] font-bold uppercase tracking-widest text-cyan-400">Control Panel</p>
                        </div>
                    </Link>
                </div>

                {/* Return to Manager Button */}
                <div className="px-5 py-6">
                    <Link 
                        href={route('dashboard')}
                        className="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl bg-slate-800/50 hover:bg-slate-700/50 border border-slate-700/30 text-xs font-bold uppercase tracking-widest text-slate-300 hover:text-white transition group"
                    >
                        <ArrowLeft size={16} className="group-hover:-translate-x-1 transition-transform" />
                        Zurück zum Manager
                    </Link>
                </div>

                {/* Navigation */}
                <nav className="flex-1 overflow-y-auto px-3 py-2 space-y-1">
                    {Object.entries(menuGroups).map(([key, group]) => (
                        <MenuGroup key={key} group={group} currentRoute={currentRoute} />
                    ))}
                </nav>

                {/* User Info */}
                <div className="absolute bottom-0 left-0 right-0 border-t border-slate-800/30 bg-slate-900/50 p-4">
                    <div className="flex items-center gap-3">
                         <div className="h-9 w-9 overflow-hidden rounded-full border border-slate-700 bg-slate-800 flex-shrink-0 p-0.5">
                             <img 
                                src={`https://ui-avatars.com/api/?name=${encodeURIComponent(auth.user.name)}&background=0f172a&color=cbd5e1`} 
                                alt={auth.user.name}
                                className="w-full h-full rounded-full"
                             />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="truncate text-sm font-semibold text-white leading-tight">{auth.user.name}</p>
                            <p className="truncate text-[10px] font-bold uppercase tracking-widest text-slate-500">System Admin</p>
                        </div>
                        <button 
                            onClick={() => router.post(route('logout'))}
                            className="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-700/50 rounded-lg transition"
                        >
                            <SignOut size={18} />
                        </button>
                    </div>
                </div>
            </aside>

            {/* Main Content Area */}
            <div className="flex min-h-screen flex-1 flex-col lg:pl-72">
                <header className="sticky top-0 z-40 bg-slate-900/60 backdrop-blur-xl border-b border-slate-800/30">
                    <div className="px-6 py-4 flex items-center justify-between min-h-[4.5rem]">
                        {header ? header : (
                            <div>
                                <p className="text-[10px] font-bold uppercase tracking-widest text-cyan-500/80 mb-0.5">Administration</p>
                                <h1 className="text-xl font-bold text-white tracking-tight">{activeMenuLabel}</h1>
                            </div>
                        )}
                        
                        <div className="flex items-center gap-4">
                            <button className="lg:hidden p-2 text-slate-400" onClick={() => setSidebarOpen(!sidebarOpen)}>
                                {sidebarOpen ? <X size={24} /> : <List size={24} />}
                            </button>
                        </div>
                    </div>
                </header>

                <main className="flex-1 px-4 py-8 sm:px-6 lg:px-8 max-w-[1600px] mx-auto w-full">
                    {flash.status && (
                        <div className="mb-8 rounded-2xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400 shadow-xl shadow-emerald-500/5">
                            {flash.status}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}
