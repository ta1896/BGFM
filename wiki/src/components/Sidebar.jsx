import React, { useState } from 'react';
import { NavLink } from 'react-router-dom';
import { 
  BookOpen, Terminal, X, Layers, GitBranch,
  Cpu, ShieldCheck, Zap, CheckCircle, Clock,
  AlertCircle, BarChart2, Wrench, Database, FileCode
} from 'lucide-react';

const navGroups = [
  {
    label: 'Projekt',
    items: [
      { to: '/', label: 'Übersicht', icon: BookOpen },
      { to: '/installation', label: 'Installation', icon: Terminal },
      { to: '/architecture', label: 'Architektur', icon: Layers },
    ]
  },
  {
    label: 'Parity Plan',
    items: [
      { to: '/parity/welle-1', label: 'Welle 1 – Core', icon: GitBranch },
      { to: '/parity/welle-2', label: 'Welle 2 – Team Ops', icon: GitBranch },
      { to: '/parity/welle-3', label: 'Welle 3 – Erweiterte Systeme', icon: GitBranch },
      { to: '/parity/welle-4', label: 'Welle 4 – Community', icon: GitBranch },
      { to: '/parity/welle-5', label: 'Welle 5 – Plattform', icon: GitBranch },
    ]
  },
  {
    label: 'Gap Backlog – P0',
    items: [
      { to: '/backlog/p0/simulation-engine', label: 'Simulations-Engine', icon: Cpu },
      { to: '/backlog/p0/match-logic', label: 'Match-Logik', icon: Cpu },
      { to: '/backlog/p0/live-eingriffe', label: 'Live-Eingriffe', icon: Zap },
      { to: '/backlog/p0/positions-malus', label: 'Positions-Malus', icon: BarChart2 },
      { to: '/backlog/p0/spieler-datenmodell', label: 'Spieler-Datenmodell', icon: Database },
      { to: '/backlog/p0/idempotenz', label: 'Match-Idempotenz', icon: ShieldCheck },
      { to: '/backlog/p0/wettbewerbskontext', label: 'Wettbewerbskontext', icon: Layers },
      { to: '/backlog/p0/statistiken', label: 'Statistiken SSOT', icon: BarChart2 },
      { to: '/backlog/p0/betrieb', label: 'Betriebs-Hardening', icon: AlertCircle },
    ]
  },
  {
    label: 'Gap Backlog – P1',
    items: [
      { to: '/backlog/p1/pokal', label: 'Pokal-Regelwerk', icon: CheckCircle },
      { to: '/backlog/p1/vereins-modell', label: 'Vereins-Datenmodell', icon: Database },
      { to: '/backlog/p1/live-state', label: 'Live-State-Persistenz', icon: Cpu },
      { to: '/backlog/p1/cron-sicherheit', label: 'Cron/Queue-Sicherheit', icon: ShieldCheck },
    ]
  },
  {
    label: 'Gap Backlog – P2',
    items: [
      { to: '/backlog/p2/admin-config', label: 'Admin-Sim-Konfiguration', icon: Wrench },
      { to: '/backlog/p2/nachwirkungen', label: 'Nachwirkungen', icon: Clock },
    ]
  },
  {
    label: 'Umsetzungsstand',
    items: [
      { to: '/status/p0', label: 'P0 Status', icon: CheckCircle },
      { to: '/status/p1', label: 'P1 Status', icon: CheckCircle },
      { to: '/status/p2', label: 'P2 Status', icon: CheckCircle },
    ]
  },
  {
    label: 'Code-Erklärungen',
    items: [
      { to: '/code/match-simulation', label: 'Match Engine', icon: FileCode },
      { to: '/code/simulation-settings', label: 'Simulation Settings', icon: FileCode },
      { to: '/code/player-position', label: 'Positions-Fit', icon: FileCode },
      { to: '/code/competition-context', label: 'Wettbewerbskontext', icon: FileCode },
    ]
  },
];

export function Sidebar({ mobileOpen, setMobileOpen }) {
  return (
    <>
      <div className={`fixed inset-y-0 left-0 z-50 w-72 transform bg-slate-900 border-r border-slate-800 transition-transform duration-300 ease-in-out lg:translate-x-0 ${mobileOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="flex h-16 shrink-0 items-center px-6 border-b border-slate-800 justify-between">
          <div className="flex items-center gap-3">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-[#aa3bff] to-indigo-600 shadow-md">
              <span className="text-sm font-bold text-white">W</span>
            </div>
            <div>
              <p className="font-bold text-white leading-tight tracking-tight">OpenWS Wiki</p>
              <p className="text-[10px] font-bold uppercase tracking-widest text-[#aa3bff]">Dokumentation</p>
            </div>
          </div>
          <button className="lg:hidden text-slate-400 hover:text-white" onClick={() => setMobileOpen(false)}>
            <X size={20} />
          </button>
        </div>
        
        <nav className="flex-1 overflow-y-auto px-4 py-6 space-y-6" style={{ height: 'calc(100vh - 64px)', overflowY: 'scroll' }}>
          {navGroups.map((group, idx) => (
            <div key={idx}>
              <h3 className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 px-2">{group.label}</h3>
              <div className="space-y-1">
                {group.items.map((item, itemIdx) => (
                  <NavLink
                    key={itemIdx}
                    to={item.to}
                    onClick={() => setMobileOpen(false)}
                    className={({ isActive }) =>
                      `flex items-center gap-3 px-3 py-2 text-sm font-medium transition-all rounded-lg group ${
                        isActive 
                          ? 'text-white bg-slate-800/80 shadow-sm border border-slate-700/50' 
                          : 'text-slate-400 hover:text-white hover:bg-slate-800/40'
                      }`
                    }
                  >
                    {({ isActive }) => (
                      <>
                        <item.icon size={15} className={isActive ? "text-[#aa3bff]" : "text-slate-500 group-hover:text-slate-300 transition-colors"} />
                        {item.label}
                      </>
                    )}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
        </nav>
      </div>
      
      {mobileOpen && (
        <div 
          className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden transition-opacity"
          onClick={() => setMobileOpen(false)}
        />
      )}
    </>
  );
}
