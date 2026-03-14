import React from 'react';
import { Head } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { RocketLaunch, CheckCircle, Sparkle } from '@phosphor-icons/react';

export default function Test() {
    return (
        <div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 overflow-hidden">
            <Head title="Tech Stack Test" />

            <motion.div 
                initial={{ opacity: 0, y: 50, scale: 0.9 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                transition={{ 
                    duration: 0.8,
                    ease: [0, 0.71, 0.2, 1.01],
                    scale: {
                        type: "spring",
                        damping: 10,
                        stiffness: 100,
                        restDelta: 0.001
                    }
                }}
                class="max-w-xl w-full sim-card p-10 border-t-4 border-indigo-500 shadow-2xl shadow-indigo-500/10 relative"
            >
                <div class="absolute -top-12 -left-12 opacity-10 blur-3xl w-48 h-48 bg-indigo-500 rounded-full"></div>
                
                <div class="flex items-center gap-4 mb-8">
                    <div class="bg-indigo-500/20 p-3 rounded-2xl border border-indigo-500/30">
                        <RocketLaunch size={32} weight="duotone" class="text-indigo-400" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight">Tech Stack <span class="text-indigo-400">Deployed</span></h1>
                        <p class="text-slate-400 text-sm font-medium">Laravel + React + Framer Motion + Phosphor Icons</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <motion.div 
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.5 }}
                        class="flex items-start gap-3"
                    >
                        <CheckCircle size={24} class="text-emerald-400 mt-0.5 shrink-0" weight="fill" />
                        <div>
                            <p class="text-white font-bold">Inertia.js V2 Bridge</p>
                            <p class="text-slate-400 text-xs">Nahtlose Verbindung zwischen Laravel PHP und React UI Komponenten.</p>
                        </div>
                    </motion.div>

                    <motion.div 
                        initial={{ opacity: 0, x: -20 }}
                        animate={{ opacity: 1, x: 0 }}
                        transition={{ delay: 0.7 }}
                        class="flex items-start gap-3"
                    >
                        <Sparkle size={24} class="text-cyan-400 mt-0.5 shrink-0" weight="fill" />
                        <div>
                            <p class="text-white font-bold">Animations & Icons</p>
                            <p class="text-slate-400 text-xs">Premium Micro-Interactions mit Framer Motion und Phosphor Icon Library.</p>
                        </div>
                    </motion.div>
                </div>

                <motion.div 
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.98 }}
                    class="mt-10"
                >
                    <a href="/" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-indigo-600/30">
                        Back to Home
                    </a>
                </motion.div>
            </motion.div>
        </div>
    );
}
