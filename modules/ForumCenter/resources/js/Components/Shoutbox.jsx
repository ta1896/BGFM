import React, { useState, useEffect, useRef } from 'react';
import { PaperPlaneTilt, ChatCenteredText, Clock, User } from '@phosphor-icons/react';
import axios from 'axios';

export default function Shoutbox() {
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [isLoading, setIsLoading] = useState(true);
    const [isSending, setIsSending] = useState(false);
    const scrollRef = useRef(null);

    const fetchMessages = async () => {
        try {
            const response = await axios.get(route('forum.shoutbox.index'));
            setMessages(response.data);
            setIsLoading(false);
        } catch (error) {
            console.error('Failed to fetch shoutbox messages', error);
        }
    };

    const handleSendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || isSending) return;

        setIsSending(true);
        try {
            const response = await axios.post(route('forum.shoutbox.store'), {
                content: newMessage
            });
            setMessages([...messages, response.data].slice(-25));
            setNewMessage('');
            scrollToBottom();
        } catch (error) {
            console.error('Failed to send message', error);
        } finally {
            setIsSending(false);
        }
    };

    const scrollToBottom = () => {
        if (scrollRef.current) {
            scrollRef.current.scrollTop = scrollRef.current.scrollHeight;
        }
    };

    useEffect(() => {
        fetchMessages();
        const interval = setInterval(fetchMessages, 10000); // Poll every 10 seconds
        return () => clearInterval(interval);
    }, []);

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    return (
        <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-xl flex flex-col h-[400px]">
            {/* Header */}
            <div className="bg-gradient-to-r from-amber-500/10 to-transparent px-6 py-4 border-b border-[var(--border-pillar)] flex items-center justify-between">
                <div className="flex items-center gap-3">
                    <ChatCenteredText size={20} weight="fill" className="text-amber-500" />
                    <h2 className="text-sm font-black uppercase tracking-widest text-white italic">Shoutbox</h2>
                </div>
                <div className="flex items-center gap-2">
                    <div className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse" />
                    <span className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">Live</span>
                </div>
            </div>

            {/* Messages List */}
            <div 
                ref={scrollRef}
                className="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar scroll-smooth"
            >
                {isLoading ? (
                    <div className="h-full flex items-center justify-center italic text-sm text-[var(--text-muted)] uppercase tracking-widest opacity-50">
                        Lädt Nachrichten...
                    </div>
                ) : messages.length === 0 ? (
                    <div className="h-full flex flex-col items-center justify-center text-center p-8 opacity-40">
                        <ChatCenteredText size={48} weight="thin" className="mb-2" />
                        <p className="text-xs font-black uppercase tracking-widest">Noch keine Nachrichten.</p>
                        <p className="text-[10px] uppercase tracking-tight mt-1">Sei der Erste, der etwas schreibt!</p>
                    </div>
                ) : (
                    messages.map((msg) => (
                        <div key={msg.id} className="group animate-in fade-in slide-in-from-bottom-1 duration-300">
                            <div className="flex items-baseline gap-2 mb-0.5">
                                <span className="text-[11px] font-black uppercase tracking-tighter text-amber-500/90 hover:text-amber-400 transition-colors cursor-default">
                                    {msg.user_name}
                                </span>
                                <span className="text-[9px] font-bold uppercase tracking-tight text-[var(--text-muted)] opacity-50">
                                    {msg.created_at}
                                </span>
                            </div>
                            <div className="text-sm text-gray-200 leading-relaxed bg-white/[0.03] rounded-lg px-3 py-2 border border-white/[0.05] group-hover:bg-white/[0.05] transition-all">
                                {msg.content}
                            </div>
                        </div>
                    ))
                )}
            </div>

            {/* Input Area */}
            <form onSubmit={handleSendMessage} className="p-4 bg-black/20 border-t border-[var(--border-pillar)]">
                <div className="relative">
                    <input
                        type="text"
                        value={newMessage}
                        onChange={(e) => setNewMessage(e.target.value)}
                        placeholder="Deine Nachricht..."
                        disabled={isSending}
                        className="w-full bg-[var(--bg-content)] border border-[var(--border-pillar)] rounded-xl px-4 py-3 pr-12 text-sm text-white placeholder-[var(--text-muted)]/50 focus:outline-none focus:ring-2 focus:ring-amber-500/50 transition-all disabled:opacity-50"
                        maxLength={255}
                    />
                    <button
                        type="submit"
                        disabled={!newMessage.trim() || isSending}
                        className="absolute right-2 top-1/2 -translate-y-1/2 p-2 rounded-lg bg-amber-500 text-black hover:bg-amber-400 disabled:opacity-50 disabled:hover:bg-amber-500 transition-all shadow-[0_0_15px_rgba(217,177,92,0.3)]"
                    >
                        <PaperPlaneTilt size={20} weight="bold" className={isSending ? 'animate-pulse' : ''} />
                    </button>
                </div>
                <div className="mt-2 flex justify-between items-center px-1">
                    <span className="text-[9px] font-bold uppercase tracking-widest text-[var(--text-muted)] opacity-50 italic">
                        Max. 255 Zeichen
                    </span>
                    {isSending && (
                        <span className="text-[9px] font-black uppercase tracking-widest text-amber-500 animate-pulse italic">
                            Wird gesendet...
                        </span>
                    )}
                </div>
            </form>
        </div>
    );
}
