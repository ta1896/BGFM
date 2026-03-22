<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\NavigationItem;

class NavigationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
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
        foreach ($groups as $groupKey => $group) {
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
    }
}
