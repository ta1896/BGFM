import React from 'react';
import { SortableItem } from './SortableItem';

export function DroppableGroupBody({ group, allGroups, updateItem, removeItem }) {
    return (
        <tbody className="divide-y divide-white/5 min-h-[50px] table-row-group">
            {[...(group.children || [])]
                .sort((a, b) => (parseInt(a.sort_order) || 0) - (parseInt(b.sort_order) || 0))
                .map(item => (
                <SortableItem 
                    key={item.id} 
                    item={item} 
                    group={group} 
                    allGroups={allGroups} 
                    updateItem={updateItem} 
                    removeItem={removeItem} 
                />
            ))}
            {(!group.children || group.children.length === 0) && (
                <tr className="bg-transparent pointer-events-none">
                    <td colSpan="6" className="px-6 py-8 text-center text-xs text-[var(--text-muted)] italic">
                        Noch keine Unterpunkte in dieser Gruppe.
                    </td>
                </tr>
            )}
        </tbody>
    );
}
