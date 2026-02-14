<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'OpenWS Laravell') }}</title>
        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sim-shell font-sans text-slate-100 antialiased selection:bg-cyan-500/30 selection:text-cyan-100 flex items-center justify-center min-h-screen p-4">
        
        <div class="w-full max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
            
            <!-- Hero / Demo Section (Hidden on Mobile) -->
            <div class="hidden lg:block space-y-8">
                <div>
                     <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-cyan-400 mb-3">Football Management Simulation</p>
                    <h1 class="text-5xl font-black text-white leading-tight tracking-tight">
                        Build Your <br> 
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-indigo-500">Perfect Legacy.</span>
                    </h1>
                    <p class="mt-6 text-lg text-slate-400 leading-relaxed max-w-md">
                        Take control of your favorite club. Manage tactics, transfers, and finances in a real-time simulation universe.
                    </p>
                </div>

                <!-- Live Match Card Demo -->
                <div class="sim-card p-6 border-slate-700/60 bg-slate-900/80 backdrop-blur-xl relative overflow-hidden group">
                     <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-500/10 blur-3xl group-hover:bg-cyan-500/15 transition duration-1000"></div>
                     
                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <span class="flex items-center gap-2">
                                <span class="relative flex h-2.5 w-2.5">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500"></span>
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Live Match</span>
                            </span>
                            <span class="text-xs font-mono text-cyan-400">84'</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-700"></div>
                                <span class="font-bold text-white">United</span>
                            </div>
                            <span class="text-3xl font-black text-white tracking-widest">2-1</span>
                             <div class="flex items-center gap-3">
                                <span class="font-bold text-white">City</span>
                                <div class="h-10 w-10 rounded-full bg-slate-800 border border-slate-700"></div>
                            </div>
                        </div>
                        
                        <div class="mt-6 space-y-2">
                            <div class="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-cyan-500 w-2/3"></div>
                            </div>
                             <div class="flex justify-between text-[10px] uppercase font-bold text-slate-500">
                                <span>Possession</span>
                                <span>64%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Login/Content Card -->
            <div class="w-full max-w-md mx-auto">
                <div class="sim-card p-8 sm:p-10 shadow-2xl shadow-cyan-900/20 border-slate-700/60 bg-slate-900/90 backdrop-blur-xl">
                    <div class="mb-8 flex items-center justify-between">
                        <a href="/" class="flex items-center gap-2 group">
                             <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-cyan-400 to-indigo-600 flex items-center justify-center text-white font-bold text-xs shadow-lg shadow-cyan-500/20 group-hover:scale-105 transition">OW</div>
                             <span class="font-bold text-xl text-white tracking-tight">OpenWS</span>
                        </a>
                        <a href="/" class="text-xs font-bold uppercase tracking-wide text-slate-500 hover:text-white transition">Back</a>
                    </div>
                    
                    {{ $slot }}
                </div>
                
                <div class="mt-6 text-center text-xs text-slate-500">
                    &copy; {{ date('Y') }} OpenWS Laravell. Simulating greatness.
                </div>
            </div>

        </div>
    </body>
</html>
