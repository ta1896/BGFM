import React, { useState } from 'react';
import { Sidebar } from './Sidebar';
import { Menu, Search } from 'lucide-react';

export function Layout({ children }) {
  const [mobileOpen, setMobileOpen] = useState(false);

  return (
    <div className="min-h-screen bg-[#0f172a] text-slate-100 font-sans flex overflow-hidden">
      <Sidebar mobileOpen={mobileOpen} setMobileOpen={setMobileOpen} />
      
      <div className="flex flex-col flex-1 min-w-0 lg:pl-72 transition-all duration-300 relative">
        <header className="sticky top-0 z-30 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
          <div className="flex items-center gap-4">
            <button 
              className="lg:hidden p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition"
              onClick={() => setMobileOpen(true)}
            >
              <Menu size={20} />
            </button>
            <div className="hidden sm:block">
              <h1 className="text-lg font-bold text-white tracking-tight">OpenWS Dokumentation</h1>
            </div>
          </div>
          
          <div className="flex items-center gap-4">
            <div className="relative group bg-slate-800/50 rounded-lg p-1.5 border border-slate-700/50 hover:bg-slate-800 transition">
               <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-hover:text-slate-300" />
               <input 
                 type="text" 
                 placeholder="Suchen..." 
                 className="bg-transparent border-none focus:outline-none focus:ring-0 text-sm pl-8 pr-3 w-40 sm:w-64 text-slate-200 placeholder:text-slate-500"
               />
            </div>
          </div>
        </header>

        <main className="flex-1 overflow-y-auto w-full custom-scrollbar" id="main-scroll">
          <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            {children}
          </div>
        </main>
      </div>
    </div>
  );
}
