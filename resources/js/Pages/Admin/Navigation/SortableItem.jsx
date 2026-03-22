import React, { useState, useEffect } from 'react';
import { Trash } from '@phosphor-icons/react';

export function SortableItem({ item, group, allGroups, updateItem, removeItem }) {
    const [localValues, setLocalValues] = useState({
        label: item.label,
        route: item.route || '',
        permission: item.permission || '',
        sort_order: item.sort_order,
        parent_id: item.parent_id
    });

    // Synchronize local state with props when props change (e.g., from server or optimistic update elsewhere)
    useEffect(() => {
        setLocalValues({
            label: item.label,
            route: item.route || '',
            permission: item.permission || '',
            sort_order: item.sort_order,
            parent_id: item.parent_id
        });
    }, [item]);

    const handleBlur = (field) => {
        let value = localValues[field];
        if (field === 'sort_order') value = parseInt(value) || 0;
        updateItem(item, field, value);
    };

    const handleChange = (field, value) => {
        setLocalValues(prev => ({ ...prev, [field]: value }));
    };

    return (
        <tr className="group/row hover:bg-white/5 transition-colors bg-[var(--bg-content)]">
            <td className="px-3 py-2 text-center w-10">
                <input 
                    type="number" 
                    value={localValues.sort_order}
                    onChange={e => handleChange('sort_order', e.target.value)}
                    onBlur={() => handleBlur('sort_order')}
                    className="w-16 bg-transparent border border-transparent hover:border-white/10 focus:border-cyan-500 px-2 py-1 rounded outline-none text-xs text-white text-center font-bold transition-all"
                />
            </td>
            <td className="px-6 py-2">
                <input 
                    type="text" 
                    value={localValues.label}
                    onChange={e => handleChange('label', e.target.value)}
                    onBlur={() => handleBlur('label')}
                    className="w-full bg-transparent border border-transparent hover:border-white/10 focus:border-cyan-500 px-2 py-1.5 rounded outline-none text-sm text-white font-bold transition-all focus:bg-[var(--bg-content)]"
                />
            </td>
            <td className="px-6 py-2">
                <input 
                    type="text" 
                    value={localValues.route}
                    onChange={e => handleChange('route', e.target.value)}
                    onBlur={() => handleBlur('route')}
                    className="w-full bg-transparent border border-transparent hover:border-white/10 focus:border-cyan-500 px-2 py-1.5 rounded outline-none text-xs text-[var(--text-muted)] font-mono transition-all focus:bg-[var(--bg-content)]"
                    placeholder="Keine Route"
                />
            </td>
            <td className="px-6 py-2">
                <input 
                    type="text" 
                    list="permissions-list"
                    value={localValues.permission}
                    onChange={e => handleChange('permission', e.target.value)}
                    onBlur={() => handleBlur('permission')}
                    className="w-full bg-transparent border border-transparent hover:border-white/10 focus:border-cyan-500 px-2 py-1.5 rounded outline-none text-xs text-amber-500 font-mono transition-all focus:bg-[var(--bg-content)]"
                    placeholder="Ohne Einschränkung"
                />
            </td>
            <td className="px-6 py-2">
                <select
                    value={localValues.parent_id || ''}
                    onChange={e => {
                        const val = e.target.value || null;
                        handleChange('parent_id', val);
                        updateItem(item, 'parent_id', val);
                    }}
                    className="w-full bg-transparent border border-transparent hover:border-white/10 focus:border-cyan-500 px-2 py-1.5 rounded outline-none text-xs text-white transition-all focus:bg-[var(--bg-content)] cursor-pointer appearance-none"
                >
                    <option value="" className="bg-[var(--bg-pillar)]">Hauptkategorie (Gruppe)</option>
                    {allGroups.filter(i => {
                         const isManagerGroup = (g) => ['manager', 'manager_with_club', 'manager_without_club'].includes(g);
                         return group.group === 'manager' ? isManagerGroup(i.group) : i.group === group.group;
                    }).map(parent => (
                        <option key={parent.id} value={parent.id} className="bg-[var(--bg-pillar)]">{parent.label}</option>
                    ))}
                </select>
            </td>
            <td className="px-6 py-2 text-center">
                <button 
                    onClick={(e) => { e.stopPropagation(); removeItem(item.id, 'item'); }}
                    className="p-1.5 text-rose-500/50 hover:text-rose-500 hover:bg-rose-500/10 rounded-lg transition-all"
                    title="Menüpunkt löschen"
                >
                    <Trash size={16} />
                </button>
            </td>
        </tr>
    );
}
