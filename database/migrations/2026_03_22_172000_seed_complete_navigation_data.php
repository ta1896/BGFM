<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate to ensure a clean state
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        NavigationItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // --- ADMIN NAVIGATION ---
        $adminGroups = [
            'bg_main' => [
                'label' => 'System',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'ACP Uebersicht', 'icon' => 'Gauge'],
                    ['route' => 'admin.navigation.index', 'label' => 'Navigation', 'icon' => 'List'],
                    ['route' => 'admin.modules.index', 'label' => 'Module', 'icon' => 'Package'],
                ],
            ],
            'bg_data' => [
                'label' => 'Datenpflege',
                'items' => [
                    ['route' => 'admin.competitions.index', 'label' => 'Wettbewerbe', 'icon' => 'Trophy'],
                    ['route' => 'admin.seasons.index', 'label' => 'Saisons', 'icon' => 'Calendar'],
                    ['route' => 'admin.clubs.index', 'label' => 'Vereine', 'icon' => 'Shield'],
                    ['route' => 'admin.players.index', 'label' => 'Spieler', 'icon' => 'Users'],
                    ['route' => 'admin.data-center.league-importer.index', 'label' => 'Liga-Importer', 'icon' => 'Download'],
                    ['route' => 'admin.external-sync.index', 'label' => 'Externer Sync', 'icon' => 'Globe'],
                ],
            ],
            'bg_forum' => [
                'label' => 'Forum Management',
                'items' => [
                    ['route' => 'admin.forum.categories.index', 'label' => 'Kategorien & Foren', 'icon' => 'Folders'],
                ],
            ],
            'bg_engine' => [
                'label' => 'Engine & Tools',
                'items' => [
                    ['route' => 'admin.ticker-templates.index', 'label' => 'Ticker Vorlagen', 'icon' => 'FileText'],
                    ['route' => 'admin.match-engine.index', 'label' => 'Match Engine', 'icon' => 'Engine'],
                    ['route' => 'admin.monitoring.index', 'label' => 'Monitoring & Debug', 'icon' => 'Activity'],
                ],
            ],
        ];

        $order = 0;
        foreach ($adminGroups as $groupKey => $group) {
            $parent = NavigationItem::create([
                'label' => $group['label'],
                'group' => 'admin',
                'sort_order' => $order++,
            ]);

            foreach ($group['items'] as $itemOrder => $item) {
                NavigationItem::create([
                    'label' => $item['label'],
                    'route' => $item['route'],
                    'icon' => $item['icon'],
                    'parent_id' => $parent->id,
                    'group' => 'admin',
                    'sort_order' => $itemOrder,
                ]);
            }
        }

        // --- MANAGER NAVIGATION ---
        $managerGroups = [
            'bg_start' => [
                'label' => 'Start',
                'group' => 'manager_without_club',
                'items' => [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'Gauge', 'group' => 'manager'],
                    ['route' => 'clubs.free', 'label' => 'Verein waehlen', 'icon' => 'ShieldPlus', 'group' => 'manager_without_club'],
                    ['route' => 'profile.edit', 'label' => 'Profil', 'icon' => 'User', 'group' => 'manager'],
                ],
            ],
            'bg_community' => [
                'label' => 'Community',
                'group' => 'manager',
                'items' => [
                    ['route' => 'forum.index', 'label' => 'Forum', 'icon' => 'Chats', 'group' => 'manager'],
                ],
            ],
            'bg_buro' => [
                'label' => 'Buero',
                'group' => 'manager_with_club',
                'items' => [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'Gauge', 'group' => 'manager'],
                    ['route' => 'notifications.index', 'label' => 'Postfach', 'icon' => 'Envelope', 'group' => 'manager'],
                    ['route' => 'finances.index', 'label' => 'Finanzen', 'icon' => 'Bank', 'group' => 'manager'],
                    ['route' => 'sponsors.index', 'label' => 'Sponsoren', 'icon' => 'Handshake', 'group' => 'manager'],
                    ['route' => 'stadium.index', 'label' => 'Stadion', 'icon' => 'Stadium', 'group' => 'manager'],
                ],
            ],
            'bg_team' => [
                'label' => 'Team',
                'group' => 'manager_with_club',
                'items' => [
                    ['route' => 'lineups.index', 'label' => 'Aufstellung', 'icon' => 'Layout', 'group' => 'manager'],
                    ['route' => 'players.index', 'label' => 'Kader', 'icon' => 'Users', 'group' => 'manager'],
                    ['route' => 'squad-hierarchy.index', 'label' => 'Hierarchie', 'icon' => 'TreeStructure', 'group' => 'manager'],
                    ['route' => 'training.index', 'label' => 'Training', 'icon' => 'Dumbbell', 'group' => 'manager'],
                    ['route' => 'training-camps.index', 'label' => 'Trainingslager', 'icon' => 'Tent', 'group' => 'manager'],
                ],
            ],
            'bg_wettbewerb' => [
                'label' => 'Wettbewerb',
                'group' => 'manager_with_club',
                'items' => [
                    ['route' => 'league.matches', 'label' => 'Spiele', 'icon' => 'SoccerBall', 'group' => 'manager'],
                    ['route' => 'league.table', 'label' => 'Tabelle', 'icon' => 'ListNumbers', 'group' => 'manager'],
                    ['route' => 'statistics.index', 'label' => 'Statistiken', 'icon' => 'ChartLine', 'group' => 'manager'],
                    ['route' => 'teams.compare', 'label' => 'Vergleich', 'icon' => 'Scales', 'group' => 'manager'],
                    ['route' => 'team-of-the-day.index', 'label' => 'Team der Woche', 'icon' => 'Star', 'group' => 'manager'],
                    ['route' => 'friendlies.index', 'label' => 'Freundschaft', 'icon' => 'Handshake', 'group' => 'manager'],
                ],
            ],
            'bg_markt' => [
                'label' => 'Markt',
                'group' => 'manager_with_club',
                'items' => [
                    ['route' => 'contracts.index', 'label' => 'Vertraege', 'icon' => 'FileText', 'group' => 'manager'],
                    ['route' => 'clubs.index', 'label' => 'Vereins-Suche', 'icon' => 'MagnifyingGlass', 'group' => 'manager'],
                ],
            ],
        ];

        foreach ($managerGroups as $groupKey => $groupData) {
            $parent = NavigationItem::create([
                'label' => $groupData['label'],
                'group' => $groupData['group'],
                'sort_order' => $order++,
            ]);

            foreach ($groupData['items'] as $itemOrder => $item) {
                NavigationItem::create([
                    'label' => $item['label'],
                    'route' => $item['route'],
                    'icon' => $item['icon'],
                    'parent_id' => $parent->id,
                    'group' => $item['group'],
                    'sort_order' => $itemOrder,
                ]);
            }
        }

        Cache::forget('navigation_admin');
        Cache::forget('navigation_manager');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        NavigationItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
