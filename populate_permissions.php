<?php

use App\Models\NavigationItem;

$map = [
    // --- Admin ---
    'System' => 'admin_system',
    'ACP Uebersicht' => 'admin_system',
    'Navigation' => 'admin_settings',
    'Module' => 'admin_settings',
    
    'Datenpflege' => 'admin_content',
    'Wettbewerbe' => 'admin_content',
    'Saisons' => 'admin_content',
    'Vereine' => 'admin_content',
    'Spieler' => 'admin_content',
    
    'Engine & Tools' => 'admin_engine',
    'Ticker Vorlagen' => 'admin_engine',
    'Match Engine' => 'admin_engine',
    'Monitoring & Debug' => 'admin_system',

    // --- Manager ---
    'Start' => 'user_access',
    'Dashboard' => 'user_access',
    'Verein waehlen' => 'find_club',
    'Profil' => 'user_access',
    
    'Buero' => 'manage_club',
    'Postfach' => 'user_access',
    'Finanzen' => 'manage_club',
    'Sponsoren' => 'manage_club',
    'Stadion' => 'manage_club',
    
    'Team' => 'manage_team',
    'Aufstellung' => 'manage_team',
    'Kader' => 'manage_team',
    'Hierarchie' => 'manage_team',
    'Training' => 'manage_training',
    'Trainingslager' => 'manage_training',
    
    'Wettbewerb' => 'view_competitions',
    'Spiele' => 'view_competitions',
    'Tabelle' => 'view_competitions',
    'Statistiken' => 'view_competitions',
    'Vergleich' => 'view_competitions',
    'Team der Woche' => 'view_competitions',
    'Freundschaft' => 'manage_team',
    
    'Markt' => 'manage_contracts',
    'Vertraege' => 'manage_contracts',
    'Vereins-Suche' => 'find_club',
];

$items = NavigationItem::all();
$updated = 0;

foreach ($items as $item) {
    if (isset($map[$item->label])) {
        $item->update(['permission' => $map[$item->label]]);
        $updated++;
    } elseif ($item->group === 'admin') {
        $item->update(['permission' => 'admin_access']);
        $updated++;
    } else {
        $item->update(['permission' => 'user_access']);
        $updated++;
    }
}

echo "Assigned permissions to {$updated} items.\n";
