<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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

        $order = 0;
        foreach ($managerGroups as $groupKey => $groupData) {
            $parent = NavigationItem::updateOrCreate(
                ['label' => $groupData['label'], 'group' => $groupData['group'], 'parent_id' => null],
                ['sort_order' => $order++]
            );

            foreach ($groupData['items'] as $itemOrder => $item) {
                NavigationItem::updateOrCreate(
                    ['label' => $item['label'], 'route' => $item['route'], 'parent_id' => $parent->id],
                    ['icon' => $item['icon'], 'sort_order' => $itemOrder, 'group' => $item['group']]
                );
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
        NavigationItem::whereIn('group', ['manager', 'manager_with_club', 'manager_without_club'])->delete();
    }
};
