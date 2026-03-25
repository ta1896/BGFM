import React from 'react';
import { 
    Shield, 
    Target, 
    Lightning, 
    Strategy, 
    Users, 
    Footprints,
    ArrowsOut,
    HandsClapping,
    Clock,
    X
} from '@phosphor-icons/react';

const INSTRUCTIONS = {
    DEFENSIVE: [
        { id: 'stay_back', label: 'Hinten bleiben', icon: Shield, color: 'text-rose-500' },
        { id: 'tight_marking', label: 'Enge Deckung', icon: Users, color: 'text-rose-400' },
        { id: 'join_attack', label: 'Vorstossen', icon: Strategy, color: 'text-amber-400' },
    ],
    MIDFIELD: [
        { id: 'playmaker', label: 'Spielmacher', icon: Target, color: 'text-cyan-400' },
        { id: 'box_to_box', label: 'Box-to-Box', icon: Footprints, color: 'text-emerald-400' },
        { id: 'safe_passing', label: 'Sicherer Pass', icon: HandsClapping, color: 'text-slate-400' },
    ],
    ATTACKING: [
        { id: 'shoot_on_sight', label: 'Sofort Schuss', icon: Target, color: 'text-amber-500' },
        { id: 'run_behind', label: 'Abgang', icon: Lightning, color: 'text-yellow-400' },
        { id: 'target_man', label: 'Zielspieler', icon: ArrowsOut, color: 'text-orange-400' },
        { id: 'dribble_more', label: 'Dribbling', icon: Footprints, color: 'text-pink-400' },
    ]
};

export const INSTRUCTION_LABELS = Object.values(INSTRUCTIONS)
    .flat()
    .reduce((map, instruction) => ({
        ...map,
        [instruction.id]: instruction.label,
    }), {});

const RadialMenu = ({
    isOpen,
    onClose,
    onSelect,
    onToggleInstruction,
    activeInstructions = [],
    selectedInstructions = [],
    playerPosition = 'MID',
    position,
}) => {
    if (!isOpen) return null;

    // Determine which instructions to show based on position group
    const normalizedPosition = position || playerPosition;
    let currentInstructions = INSTRUCTIONS.MIDFIELD;
    if (normalizedPosition === 'GK') currentInstructions = []; // No special instructions for GK for now
    else if (normalizedPosition === 'DEF') currentInstructions = [ ...INSTRUCTIONS.DEFENSIVE, ...INSTRUCTIONS.MIDFIELD.slice(-1) ];
    else if (normalizedPosition === 'FWD') currentInstructions = [ ...INSTRUCTIONS.ATTACKING, ...INSTRUCTIONS.MIDFIELD.slice(-1) ];

    const resolvedInstructions = activeInstructions.length > 0 ? activeInstructions : selectedInstructions;
    const handleSelect = onSelect || onToggleInstruction;

    return (
        <div className="fixed inset-0 z-[100] flex items-center justify-center animate-in fade-in zoom-in duration-200">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onClose} />
            
            <div className="relative w-80 h-80 flex items-center justify-center">
                {/* Center Close Button */}
                <button 
                    onClick={onClose}
                    className="z-50 w-12 h-12 rounded-full bg-[#1a1a1a] border-2 border-white/10 flex items-center justify-center text-white hover:bg-white/10 transition-colors shadow-2xl"
                >
                    <X size={20} weight="bold" />
                </button>

                {/* Instruction Items */}
                {currentInstructions.map((item, index) => {
                    const angle = (index / currentInstructions.length) * 360;
                    const radius = 110; // distance from center
                    const x = Math.cos((angle - 90) * (Math.PI / 180)) * radius;
                    const y = Math.sin((angle - 90) * (Math.PI / 180)) * radius;
                    
                    const isActive = resolvedInstructions.includes(item.id);

                    return (
                        <button
                            key={item.id}
                            onClick={() => handleSelect?.(item.id)}
                            className={`absolute z-40 group flex flex-col items-center justify-center transition-all duration-300 transform hover:scale-110`}
                            style={{ 
                                left: `calc(50% + ${x}px)`, 
                                top: `calc(50% + ${y}px)`,
                                transform: 'translate(-50%, -50%)'
                            }}
                        >
                            <div className={`w-14 h-14 rounded-2xl flex items-center justify-center transition-all mb-1 ${
                                isActive 
                                    ? 'bg-amber-500 border-2 border-white/20 shadow-[0_0_20px_rgba(245,158,11,0.4)]' 
                                    : 'bg-[#0f172a] border border-white/5 hover:border-white/20'
                            }`}>
                                <item.icon 
                                    size={24} 
                                    weight={isActive ? "bold" : "regular"}
                                    className={isActive ? 'text-white' : item.color} 
                                />
                            </div>
                            <span className={`text-[9px] font-black uppercase tracking-tighter whitespace-nowrap px-2 py-0.5 rounded-full ${
                                isActive ? 'bg-amber-500 text-white' : 'bg-black/40 text-slate-400'
                            }`}>
                                {item.label}
                            </span>
                        </button>
                    );
                })}

                {/* Decorative Rings */}
                <div className="absolute inset-4 rounded-full border border-dashed border-white/5 pointer-events-none" />
                <div className="absolute inset-12 rounded-full border border-white/5 pointer-events-none" />
            </div>
        </div>
    );
};

export default RadialMenu;
