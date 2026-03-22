<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\NavigationItem;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
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
            $parent = NavigationItem::updateOrCreate(
                ['label' => $group['label'], 'group' => 'admin', 'parent_id' => null],
                ['sort_order' => $order++]
            );

            foreach ($group['items'] as $itemOrder => $item) {
                NavigationItem::updateOrCreate(
                    ['label' => $item['label'], 'route' => $item['route'], 'group' => 'admin', 'parent_id' => $parent->id],
                    ['icon' => $item['icon'], 'sort_order' => $itemOrder]
                );
            }
        }

        \Illuminate\Support\Facades\Cache::forget('navigation_admin');
        \Illuminate\Support\Facades\Cache::forget('navigation_manager');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NavigationItem::where('group', 'admin')->delete();
    }
};
