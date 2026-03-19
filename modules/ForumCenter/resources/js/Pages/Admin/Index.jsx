import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Gear, Plus, Folder, Chats, ArrowsOutCardinal, FloppyDisk } from '@phosphor-icons/react';

export default function Index({ categories }) {
    const [localCategories, setLocalCategories] = useState(categories);
    const [isDirty, setIsDirty] = useState(false);
    const [dragOverId, setDragOverId] = useState(null);
    const [dragOverType, setDragOverType] = useState(null);
    
    const categoryForm = useForm({ name: '' });
    const forumForm = useForm({ forum_category_id: '', name: '', description: '' });
    const { post: submitOrder, processing: savingOrder } = useForm();

    const createCategory = (e) => {
        e.preventDefault();
        categoryForm.post(route('admin.forum.categories.store'), {
            onSuccess: () => categoryForm.reset(),
        });
    };

    const handleDragStart = (e, type, id, categoryId = null) => {
        e.dataTransfer.setData('type', type);
        e.dataTransfer.setData('id', id);
        if (categoryId) e.dataTransfer.setData('categoryId', categoryId);
    };

    const handleDrop = (e, targetType, targetId, targetCategoryId = null) => {
        e.preventDefault();
        setDragOverId(null);
        setDragOverType(null);

        const draggedType = e.dataTransfer.getData('type');
        const draggedId = parseInt(e.dataTransfer.getData('id'));
        const sourceCategoryId = parseInt(e.dataTransfer.getData('categoryId'));

        if (!draggedId) return;

        let newCategories = JSON.parse(JSON.stringify(localCategories));

        if (draggedType === 'category' && targetType === 'category') {
            const draggedIdx = newCategories.findIndex(c => c.id === draggedId);
            const targetIdx = newCategories.findIndex(c => c.id === targetId);
            if (draggedIdx !== targetIdx) {
                const [draggedItem] = newCategories.splice(draggedIdx, 1);
                newCategories.splice(targetIdx, 0, draggedItem);
            }
        } else if (draggedType === 'forum') {
            const sourceCat = newCategories.find(c => c.id === sourceCategoryId);
            const draggedIdx = sourceCat.forums.findIndex(f => f.id === draggedId);
            const [draggedItem] = sourceCat.forums.splice(draggedIdx, 1);

            if (targetType === 'category') {
                const targetCat = newCategories.find(c => c.id === targetId);
                targetCat.forums.push(draggedItem);
            } else if (targetType === 'forum') {
                const targetCat = newCategories.find(c => c.id === targetCategoryId);
                const targetIdx = targetCat.forums.findIndex(f => f.id === targetId);
                targetCat.forums.splice(targetIdx, 0, draggedItem);
            }
        }

        setLocalCategories(newCategories);
        setIsDirty(true);
    };

    const saveOrder = () => {
        const payload = {
            categories: localCategories.map((cat, catIdx) => ({
                id: cat.id,
                sort_order: catIdx + 1,
                forums: cat.forums.map((forum, forumIdx) => ({
                    id: forum.id,
                    sort_order: forumIdx + 1
                }))
            }))
        };

        submitOrder(route('admin.forum.reorder'), {
            data: payload,
            onSuccess: () => setIsDirty(false),
        });
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between w-full">
                    <div>
                        <p className="text-[10px] font-black uppercase tracking-[0.2em] text-amber-500/90 mb-0.5 italic">Administration</p>
                        <h1 className="text-2xl font-black text-white italic uppercase tracking-tight leading-none">Forum Management</h1>
                    </div>
                    {isDirty && (
                        <button 
                            onClick={saveOrder}
                            disabled={savingOrder}
                            className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-500 text-black text-xs font-black uppercase tracking-widest hover:bg-emerald-400 transition-all shadow-lg animate-pulse"
                        >
                            <FloppyDisk size={18} weight="bold" />
                            {savingOrder ? 'Speichert...' : 'Struktur Speichern'}
                        </button>
                    )}
                </div>
            }
        >
            <Head title="Forum Admin" />

            <div className="max-w-7xl mx-auto pb-12 px-4 sm:px-6 lg:px-8 space-y-8">
                {/* Create Section */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 backdrop-blur-md">
                        <h2 className="text-sm font-black uppercase tracking-widest text-white mb-6 flex items-center gap-2">
                            <Folder size={18} /> Neue Kategorie
                        </h2>
                        <form onSubmit={createCategory} className="space-y-4">
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Name</label>
                                <input
                                    type="text"
                                    value={categoryForm.data.name}
                                    onChange={(e) => categoryForm.setData('name', e.target.value)}
                                    className="w-full bg-[var(--bg-content)] border border-[var(--border-pillar)] rounded-xl px-4 py-2.5 text-white text-sm"
                                    required
                                />
                            </div>
                            <button className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-amber-500 text-black text-xs font-black uppercase tracking-widest hover:bg-amber-400 transition-all">
                                <Plus size={16} weight="bold" /> Kategorie Erstellen
                            </button>
                        </form>
                    </div>

                    <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 p-6 backdrop-blur-md">
                        <h2 className="text-sm font-black uppercase tracking-widest text-white mb-6 flex items-center gap-2">
                            <Chats size={18} /> Neues Forum
                        </h2>
                        <form onSubmit={(e) => {
                            e.preventDefault();
                            forumForm.post(route('admin.forum.forums.store'), {
                                onSuccess: () => forumForm.reset(),
                            });
                        }} className="space-y-4">
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Kategorie</label>
                                <select
                                    value={forumForm.data.forum_category_id}
                                    onChange={(e) => forumForm.setData('forum_category_id', e.target.value)}
                                    className="w-full bg-[var(--bg-content)] border border-[var(--border-pillar)] rounded-xl px-4 py-2.5 text-white text-sm"
                                    required
                                >
                                    <option value="">Wählen...</option>
                                    {localCategories.map(cat => <option key={cat.id} value={cat.id}>{cat.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Name</label>
                                <input
                                    type="text"
                                    value={forumForm.data.name}
                                    onChange={(e) => forumForm.setData('name', e.target.value)}
                                    className="w-full bg-[var(--bg-content)] border border-[var(--border-pillar)] rounded-xl px-4 py-2.5 text-white text-sm"
                                    required
                                />
                            </div>
                            <button className="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-amber-500 text-black text-xs font-black uppercase tracking-widest hover:bg-amber-400 transition-all">
                                <Plus size={16} weight="bold" /> Forum Erstellen
                            </button>
                        </form>
                    </div>
                </div>

                {/* Structure Overview */}
                <div className="overflow-hidden rounded-2xl border border-[var(--border-pillar)] bg-[var(--bg-pillar)]/40 backdrop-blur-md shadow-xl">
                    <div className="bg-slate-800/50 px-6 py-4 border-b border-[var(--border-pillar)] flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            <Folder size={20} weight="fill" className="text-amber-500" />
                            <h2 className="text-sm font-black uppercase tracking-widest text-white">Struktur</h2>
                        </div>
                        <p className="text-[10px] font-bold text-amber-500/50 uppercase tracking-widest italic">Tipp: Elemente zum Sortieren ziehen</p>
                    </div>
                    <div className="divide-y divide-[var(--border-muted)]">
                        {localCategories.map(category => (
                            <div 
                                key={category.id} 
                                className={`p-6 transition-all relative ${dragOverId === category.id && dragOverType === 'category' ? 'bg-amber-500/5 ring-2 ring-amber-500/20' : 'hover:bg-white/[0.01]'}`}
                                draggable={true}
                                onDragStart={(e) => handleDragStart(e, 'category', category.id)}
                                onDragOver={(e) => {
                                    e.preventDefault();
                                    if (dragOverId !== category.id) {
                                        setDragOverId(category.id);
                                        setDragOverType('category');
                                    }
                                }}
                                onDragLeave={() => {
                                    setDragOverId(null);
                                    setDragOverType(null);
                                }}
                                onDrop={(e) => handleDrop(e, 'category', category.id)}
                            >
                                <div className="flex items-center gap-2 mb-4 group pointer-events-none">
                                    <ArrowsOutCardinal size={16} className="text-white/20 group-hover:text-amber-500 transition-colors cursor-move" />
                                    <h3 className="text-base font-black text-amber-500 uppercase tracking-widest">{category.name}</h3>
                                </div>
                                <div className="ml-6 space-y-3">
                                    {category.forums.map(forum => (
                                        <div 
                                            key={forum.id} 
                                            className={`flex items-center justify-between p-3 rounded-xl border transition-all group ${dragOverId === forum.id && dragOverType === 'forum' ? 'bg-amber-500/10 border-amber-500 shadow-lg scale-[1.02]' : 'bg-white/5 border-white/5 hover:border-amber-500/30'}`}
                                            draggable={true}
                                            onDragStart={(e) => {
                                                e.stopPropagation();
                                                handleDragStart(e, 'forum', forum.id, category.id);
                                            }}
                                            onDragOver={(e) => {
                                                e.preventDefault();
                                                e.stopPropagation();
                                                if (dragOverId !== forum.id) {
                                                    setDragOverId(forum.id);
                                                    setDragOverType('forum');
                                                }
                                            }}
                                            onDragLeave={(e) => {
                                                e.stopPropagation();
                                                setDragOverId(null);
                                                setDragOverType(null);
                                            }}
                                            onDrop={(e) => {
                                                e.stopPropagation();
                                                handleDrop(e, 'forum', forum.id, category.id);
                                            }}
                                        >
                                            <div className="flex items-center gap-3 pointer-events-none">
                                                <ArrowsOutCardinal size={14} className="text-white/10 group-hover:text-amber-500 transition-colors cursor-move" />
                                                <div>
                                                    <p className="text-sm font-bold text-white group-hover:text-amber-100 transition-colors">{forum.name}</p>
                                                    <p className="text-[10px] text-[var(--text-muted)] line-clamp-1">{forum.description || 'Keine Beschreibung'}</p>
                                                </div>
                                            </div>
                                            <div className="flex gap-2 relative z-10">
                                                <button className="px-3 py-1.5 rounded-lg bg-white/5 text-[10px] font-black uppercase tracking-widest text-gray-500 hover:text-white hover:bg-white/10 transition-all">Bearbeiten</button>
                                                <button className="px-3 py-1.5 rounded-lg bg-rose-500/5 text-[10px] font-black uppercase tracking-widest text-rose-500/50 hover:text-rose-500 hover:bg-rose-500/10 transition-all">Löschen</button>
                                            </div>
                                        </div>
                                    ))}
                                    {category.forums.length === 0 && (
                                        <div 
                                            className={`p-4 rounded-xl border border-dashed transition-all text-center ${dragOverId === category.id && dragOverType === 'category' ? 'border-amber-500' : 'border-white/5'}`}
                                        >
                                            <p className="text-[10px] font-bold uppercase tracking-widest text-white/20 italic">Keine Foren in dieser Kategorie</p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
