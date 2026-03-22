import React, { useState, useEffect } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { Plus, Trash, Funnel, CaretDown, CaretRight } from '@phosphor-icons/react';
import { DroppableGroupBody } from './DroppableGroupBody';

const COMMON_PERMISSIONS = [
    'admin_access',
    'admin_system',
    'admin_settings',
    'admin_content',
    'admin_users',
    'admin_modules',
];

const CategorySection = ({ 
    group, 
    onUpdate, 
    onDestroy, 
    onToggleCollapse, 
    isCollapsed,
    localItems 
}) => {
    const [localValues, setLocalValues] = useState({
        label: group.label,
        sort_order: group.sort_order
    });

    useEffect(() => {
        setLocalValues({
            label: group.label,
            sort_order: group.sort_order
        });
    }, [group]);

    const handleBlur = (field) => {
        let value = localValues[field];
        if (field === 'sort_order') value = parseInt(value) || 0;
        onUpdate(group, field, value);
    };

    return (
        <div className="sim-card overflow-hidden">
            <div className={`bg-white/5 px-6 py-4 flex flex-wrap items-center justify-between border-b border-white/10 gap-4`}>
                <div className="flex items-center gap-4 flex-1">
                    <button 
                        onClick={() => onToggleCollapse(group.id)}
                        className="p-1 hover:bg-white/5 rounded text-[var(--text-muted)] hover:text-white transition-colors"
                        title={isCollapsed ? "Expandieren" : "Einklappen"}
                    >
                        {isCollapsed ? <CaretRight size={18} weight="bold" /> : <CaretDown size={18} weight="bold" />}
                    </button>
                    
                    <input 
                        type="text" 
                        value={localValues.label}
                        onChange={e => setLocalValues(prev => ({ ...prev, label: e.target.value }))}
                        onBlur={() => handleBlur('label')}
                        className="bg-transparent border-b border-transparent hover:border-white/20 focus:border-cyan-500 focus:bg-[var(--bg-content)]/50 px-2 py-1 outline-none text-sm font-black text-amber-500 uppercase tracking-tighter transition-all rounded"
                        title="Gruppen-Label bearbeiten"
                    />
                    <div className={`px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest ${
                        group.group === 'admin' ? 'bg-cyan-500/20 text-cyan-400' : 
                        group.group === 'manager_with_club' ? 'bg-emerald-500/20 text-emerald-400' : 
                        group.group === 'manager_without_club' ? 'bg-rose-500/20 text-rose-400' : 
                        'bg-amber-500/20 text-amber-400'
                    }`}>
                        {group.group === 'admin' ? 'Admin ACP' : 
                            group.group === 'manager' || group.group === 'manager_with_club' || group.group === 'manager_without_club' ? 'Manager' : group.group}
                    </div>
                </div>
                <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2">
                        <span className="text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-widest">Sortierung:</span>
                        <input 
                            type="number" 
                            value={localValues.sort_order}
                            onChange={e => setLocalValues(prev => ({ ...prev, sort_order: e.target.value }))}
                            onBlur={() => handleBlur('sort_order')}
                            className="w-16 bg-[var(--bg-content)] border border-[var(--border-pillar)] rounded px-2 py-1 outline-none text-xs font-bold text-white text-center focus:border-cyan-500 transition-colors"
                        />
                    </div>
                    <button 
                        onClick={(e) => { e.stopPropagation(); onDestroy(group.id, 'group'); }}
                        className="p-2 text-rose-500 hover:bg-rose-500/10 rounded-lg transition-colors"
                        title="Kategorie löschen"
                    >
                        <Trash size={16} />
                    </button>
                </div>
            </div>
            {!isCollapsed && (
                <div className="p-0 overflow-x-auto">
                    <table className="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr className="border-b border-white/5 text-[9px] font-black uppercase tracking-[0.2em] text-[var(--text-muted)] bg-[var(--bg-pillar)]/30">
                                <th className="px-3 py-3 w-10 text-center">Order</th>
                                <th className="px-6 py-3 w-48">Label</th>
                                <th className="px-6 py-3 w-48">Route</th>
                                <th className="px-6 py-3 w-48">Rechte (Permission)</th>
                                <th className="px-6 py-3 w-32">Kategorie</th>
                                <th className="px-6 py-3 w-24 text-center">Aktionen</th>
                            </tr>
                        </thead>
                        <DroppableGroupBody 
                            group={group} 
                            allGroups={localItems} 
                            updateItem={onUpdate} 
                            removeItem={onDestroy} 
                        />
                    </table>
                </div>
            )}
        </div>
    );
};

export default function Index({ items }) {
    const [filterGroup, setFilterGroup] = useState('all');
    const [isCreatingCategory, setIsCreatingCategory] = useState(false);
    const [isFormCollapsed, setIsFormCollapsed] = useState(true);
    const [collapsedGroups, setCollapsedGroups] = useState(new Set());
    
    // Local copy of items
    const [localItems, setLocalItems] = useState(items);

    useEffect(() => {
        setLocalItems(items);
    }, [items]);

    const { data, setData, post, reset, processing, errors } = useForm({
        label: '',
        route: '',
        permission: '',
        sort_order: 0,
        parent_id: null,
        group: 'admin',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('admin.navigation.store'), {
            onSuccess: () => {
                reset();
                setIsFormCollapsed(true);
            },
        });
    };

    const updateItem = (item, field, value) => {
        if (item[field] === value) return; // skip if unchanged
        
        const updateData = { ...item, [field]: value };
        const itemId = parseInt(item.id);

        // If parent_id changes, we MUST sync the group (context) of the item to match the new parent category
        if (field === 'parent_id') {
            const targetGroupId = value ? parseInt(value) : null;
            if (targetGroupId) {
                const targetGroup = localItems.find(g => parseInt(g.id) === targetGroupId);
                if (targetGroup) {
                    updateData.group = targetGroup.group;
                }
            }
        }
        
        // Optimistic update
        setLocalItems(prev => {
            if (field === 'parent_id') {
                const targetGroupId = value ? parseInt(value) : null;
                const newItems = [...prev];
                
                // Find old and new group indices
                const oldGroupIndex = newItems.findIndex(g => parseInt(g.id) === parseInt(item.parent_id));
                const newGroupIndex = targetGroupId ? newItems.findIndex(g => parseInt(g.id) === targetGroupId) : -1;
                
                if (oldGroupIndex !== -1 && newGroupIndex !== -1) {
                    const activeIndex = newItems[oldGroupIndex].children?.findIndex(i => parseInt(i.id) === itemId);
                    if (activeIndex !== -1 && activeIndex !== undefined) {
                        const activeChildren = [...newItems[oldGroupIndex].children];
                        const overChildren = [...(newItems[newGroupIndex].children || [])];
                        const [draggedItem] = activeChildren.splice(activeIndex, 1);
                        
                        overChildren.push({ 
                            ...draggedItem, 
                            [field]: value,
                            group: updateData.group || draggedItem.group
                        });
                        
                        newItems[oldGroupIndex] = { ...newItems[oldGroupIndex], children: activeChildren };
                        newItems[newGroupIndex] = { ...newItems[newGroupIndex], children: overChildren };
                    }
                }
                return newItems;
            } else {
                // Update property in place
                return prev.map(group => {
                    // Is it the group itself?
                    if (parseInt(group.id) === itemId) return { ...group, [field]: value };
                    
                    // Is it one of the group's children?
                    const itemIndex = group.children?.findIndex(i => parseInt(i.id) === itemId);
                    if (itemIndex !== -1 && itemIndex !== undefined) {
                        const newChildren = [...group.children];
                        newChildren[itemIndex] = { ...newChildren[itemIndex], [field]: value };
                        return { ...group, children: newChildren };
                    }
                    return group;
                });
            }
        });

        router.put(route('admin.navigation.update', item.id), updateData, {
            preserveScroll: true,
        });
    };

    const removeItem = (id, type) => {
        if (!window.confirm('Möchtest du diesen Eintrag wirklich löschen?')) return;

        const numericId = parseInt(id);

        setLocalItems(prev => {
            if (type === 'group') {
                return prev.filter(g => parseInt(g.id) !== numericId);
            } else {
                return prev.map(g => ({
                    ...g,
                    children: g.children?.filter(i => parseInt(i.id) !== numericId)
                }));
            }
        });

        router.delete(route('admin.navigation.destroy', id), {
            preserveScroll: true,
        });
    };

    const toggleCollapse = (id) => {
        setCollapsedGroups(prev => {
            const next = new Set(prev);
            if (next.has(id)) next.delete(id);
            else next.add(id);
            return next;
        });
    };

    return (
        <AdminLayout
            header={<h2 className="text-xl font-semibold leading-tight text-white">Navigations-Menü verwalten</h2>}
        >
            <Head title="Navigation verwalten" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-8">
                    
                    <datalist id="permissions-list">
                        {COMMON_PERMISSIONS.map(p => <option key={p} value={p} />)}
                    </datalist>

                    {/* Filter and Creation Mode Toggle */}
                    <div className="flex flex-col md:flex-row gap-4 items-center justify-between bg-white/5 border border-white/10 p-4 rounded-xl">
                        <div className="flex items-center gap-3">
                            <Funnel size={20} className="text-[var(--text-muted)]" />
                            <select 
                                value={filterGroup} 
                                onChange={e => setFilterGroup(e.target.value)}
                                className="bg-[var(--bg-pillar)] border border-[var(--border-pillar)] text-white text-sm font-bold rounded-lg px-4 py-2 outline-none focus:border-cyan-500 transition-colors"
                            >
                                <option value="all">Alle Kontexte anzeigen</option>
                                <option value="manager">Manager (Alle)</option>
                                <option value="admin">Admin (ACP)</option>
                            </select>
                        </div>
                        <div className="flex items-center gap-2 bg-[var(--bg-pillar)] p-1 rounded-lg border border-[var(--border-pillar)]">
                            <button 
                                onClick={() => { setIsCreatingCategory(false); setIsFormCollapsed(false); setData('parent_id', localItems[0]?.id || null); }}
                                className={`px-4 py-1.5 rounded text-xs font-black uppercase tracking-widest transition-all ${!isCreatingCategory ? 'bg-cyan-500 text-white shadow-lg' : 'text-[var(--text-muted)] hover:text-white'}`}
                            >
                                Menüpunkt erstellen
                            </button>
                            <button 
                                onClick={() => { setIsCreatingCategory(true); setIsFormCollapsed(false); setData('parent_id', null); }}
                                className={`px-4 py-1.5 rounded text-xs font-black uppercase tracking-widest transition-all ${isCreatingCategory ? 'bg-amber-500 text-white shadow-lg' : 'text-[var(--text-muted)] hover:text-white'}`}
                            >
                                Kategorie erstellen
                            </button>
                        </div>
                    </div>

                    {/* Add New Item */}
                    <div className="sim-card border-t-4 border-cyan-500 overflow-hidden">
                        <div 
                            className="p-6 flex items-center justify-between cursor-pointer hover:bg-white/5 transition-colors"
                            onClick={() => setIsFormCollapsed(!isFormCollapsed)}
                        >
                            <h3 className="text-lg font-black uppercase tracking-tight text-white italic flex items-center gap-3">
                                {isFormCollapsed ? <Plus size={20} weight="bold" className="text-cyan-500" /> : <CaretDown size={20} weight="bold" className="text-cyan-500" />}
                                {isCreatingCategory ? 'Neue Kategorie (Gruppe) erstellen' : 'Neuen Menüpunkt erstellen'}
                            </h3>
                            <div className="text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)]">
                                {isFormCollapsed ? 'Zum Erstellen klicken' : 'Einklappen'}
                            </div>
                        </div>

                        {!isFormCollapsed && (
                            <div className="px-6 pb-6 pt-2 border-t border-white/5">
                                <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Label</label>
                                        <input
                                            type="text"
                                            value={data.label}
                                            onChange={e => setData('label', e.target.value)}
                                            className="sim-input w-full"
                                            placeholder="z.B. Spieler"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Route (optional)</label>
                                        <input
                                            type="text"
                                            value={data.route}
                                            onChange={e => setData('route', e.target.value)}
                                            className="sim-input w-full"
                                            placeholder="admin.players.index"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Rechte (Permission)</label>
                                        <input
                                            type="text"
                                            list="permissions-list"
                                            value={data.permission}
                                            onChange={e => setData('permission', e.target.value)}
                                            className="sim-input w-full"
                                            placeholder="Ohne Einschränkung"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Sortierung</label>
                                        <input
                                            type="number"
                                            value={data.sort_order}
                                            onChange={e => setData('sort_order', parseInt(e.target.value) || 0)}
                                            className="sim-input w-full"
                                        />
                                    </div>
                                    {!isCreatingCategory && (
                                        <div>
                                            <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-1">Gehört zu Kategorie</label>
                                            <select
                                                value={data.parent_id || ''}
                                                onChange={e => setData('parent_id', e.target.value || null)}
                                                className="sim-input w-full"
                                                required
                                            >
                                                <option value="" disabled>Bitte wählen...</option>
                                                {localItems.filter(i => {
                                                    const isManagerGroup = (g) => ['manager', 'manager_with_club', 'manager_without_club'].includes(g);
                                                    return data.group === 'manager' ? isManagerGroup(i.group) : i.group === data.group;
                                                }).map(item => (
                                                    <option key={item.id} value={item.id}>{item.label}</option>
                                                ))}
                                            </select>
                                        </div>
                                    )}
                                    <div className="col-span-full border-t border-white/5 pt-4 mt-2">
                                        <label className="block text-[10px] font-black uppercase tracking-widest text-[var(--text-muted)] mb-2">Kontext (Anzeigebereich)</label>
                                        <div className="flex flex-wrap items-center gap-4">
                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" value="manager" checked={data.group === 'manager'} onChange={e => setData('group', 'manager')} className="accent-amber-500 w-4 h-4 cursor-pointer" />
                                                <span className="text-sm font-bold text-white">Manager (Bereich)</span>
                                            </label>
                                            <label className="flex items-center gap-2 cursor-pointer">
                                                <input type="radio" value="admin" checked={data.group === 'admin'} onChange={e => setData('group', 'admin')} className="accent-cyan-500 w-4 h-4 cursor-pointer" />
                                                <span className="text-sm font-bold text-white">Admin (ACP)</span>
                                            </label>
                                        </div>
                                    </div>
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="sim-button-primary flex items-center justify-center gap-2"
                                    >
                                        <Plus size={16} weight="bold" />
                                        Hinzufügen
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>

                    {/* Groups List */}
                    <div className="space-y-6">
                        {localItems
                            .filter(group => {
                                if (filterGroup === 'all') return true;
                                if (filterGroup === 'manager') return ['manager', 'manager_with_club', 'manager_without_club'].includes(group.group);
                                return group.group === filterGroup;
                            })
                            .sort((a, b) => (parseInt(a.sort_order) || 0) - (parseInt(b.sort_order) || 0))
                            .map(group => (
                            <CategorySection 
                                key={group.id} 
                                group={group} 
                                onUpdate={updateItem} 
                                onDestroy={removeItem} 
                                localItems={localItems}
                                isCollapsed={collapsedGroups.has(group.id)}
                                onToggleCollapse={toggleCollapse}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
