<?php

namespace App\Console\Commands;

use App\Models\NavigationItem;
use App\Modules\ModuleManager;
use Illuminate\Console\Command;

class SyncNavigationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nav:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync hardcoded base navigation and module navigation into the navigation_items table.';

    /**
     * Execute the console command.
     */
    public function handle(ModuleManager $moduleManager)
    {
        $this->info('Starting Navigation Sync...');

        $baseManagerGroups = [
            'bg_start' => [
                'label' => 'Start',
                'items' => [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'group' => 'manager'],
                    ['route' => 'clubs.free', 'label' => 'Verein waehlen', 'group' => 'manager_without_club'],
                    ['route' => 'profile.edit', 'label' => 'Profil', 'group' => 'manager'],
                ],
            ],
            'bg_buro' => [
                'label' => 'Buero',
                'items' => [
                    ['route' => 'dashboard', 'label' => 'Dashboard', 'group' => 'manager'],
                    ['route' => 'notifications.index', 'label' => 'Postfach', 'group' => 'manager'],
                    ['route' => 'finances.index', 'label' => 'Finanzen', 'group' => 'manager_with_club'],
                    ['route' => 'sponsors.index', 'label' => 'Sponsoren', 'group' => 'manager_with_club'],
                    ['route' => 'stadium.index', 'label' => 'Stadion', 'group' => 'manager_with_club'],
                ],
            ],
            'bg_team' => [
                'label' => 'Team',
                'items' => [
                    ['route' => 'lineups.index', 'label' => 'Aufstellung', 'group' => 'manager_with_club'],
                    ['route' => 'players.index', 'label' => 'Kader', 'group' => 'manager_with_club'],
                    ['route' => 'squad-hierarchy.index', 'label' => 'Hierarchie', 'group' => 'manager_with_club'],
                    ['route' => 'training.index', 'label' => 'Training', 'group' => 'manager_with_club'],
                    ['route' => 'training-camps.index', 'label' => 'Trainingslager', 'group' => 'manager_with_club'],
                ],
            ],
            'bg_wettbewerb' => [
                'label' => 'Wettbewerb',
                'items' => [
                    ['route' => 'league.matches', 'label' => 'Spiele', 'group' => 'manager_with_club'],
                    ['route' => 'league.table', 'label' => 'Tabelle', 'group' => 'manager_with_club'],
                    ['route' => 'statistics.index', 'label' => 'Statistiken', 'group' => 'manager_with_club'],
                    ['route' => 'teams.compare', 'label' => 'Vergleich', 'group' => 'manager_with_club'],
                    ['route' => 'team-of-the-day.index', 'label' => 'Team der Woche', 'group' => 'manager_with_club'],
                    ['route' => 'friendlies.index', 'label' => 'Freundschaft', 'group' => 'manager_with_club'],
                ],
            ],
            'bg_markt' => [
                'label' => 'Markt',
                'items' => [
                    ['route' => 'contracts.index', 'label' => 'Vertraege', 'group' => 'manager_with_club'],
                    ['route' => 'clubs.index', 'label' => 'Vereins-Suche', 'group' => 'manager'],
                ],
            ],
        ];

        $baseAdminGroups = [
            'bg_main' => [
                'label' => 'System',
                'items' => [
                    ['route' => 'admin.dashboard', 'label' => 'ACP Uebersicht'],
                    ['route' => 'admin.navigation.index', 'label' => 'Navigation'],
                    ['route' => 'admin.modules.index', 'label' => 'Module'],
                ],
            ],
            'bg_data' => [
                'label' => 'Datenpflege',
                'items' => [
                    ['route' => 'admin.competitions.index', 'label' => 'Wettbewerbe'],
                    ['route' => 'admin.seasons.index', 'label' => 'Saisons'],
                    ['route' => 'admin.clubs.index', 'label' => 'Vereine'],
                    ['route' => 'admin.players.index', 'label' => 'Spieler'],
                ],
            ],
            'bg_engine' => [
                'label' => 'Engine & Tools',
                'items' => [
                    ['route' => 'admin.ticker-templates.index', 'label' => 'Ticker Vorlagen'],
                    ['route' => 'admin.match-engine.index', 'label' => 'Match Engine'],
                    ['route' => 'admin.monitoring.index', 'label' => 'Monitoring & Debug'],
                ],
            ],
        ];

        // Process Manager Array
        $this->syncGroups('manager', $baseManagerGroups);
        
        // Process Admin Array
        $this->syncGroups('admin', $baseAdminGroups);

        // Process Module Registry
        $registry = $moduleManager->frontendRegistry();
        
        $this->syncModuleGroups('manager', $registry['manager_navigation'] ?? []);
        $this->syncModuleGroups('manager_with_club', $registry['manager_with_club_navigation'] ?? []);
        $this->syncModuleGroups('manager_without_club', $registry['manager_without_club_navigation'] ?? []);
        $this->syncModuleGroups('admin', $registry['admin_navigation'] ?? []);

        $this->info('Navigation Sync Complete!');
    }

    private function syncGroups(string $groupType, array $groups)
    {
        $groupSort = 0;
        foreach ($groups as $groupKey => $groupData) {
            $parent = NavigationItem::firstOrCreate([
                'label' => $groupData['label'],
                'group' => $groupType,
                'parent_id' => null,
            ], [
                'sort_order' => $groupSort,
            ]);
            
            $groupSort += 10;
            $itemSort = 0;

            foreach ($groupData['items'] as $itemData) {
                $targetGroup = $itemData['group'] ?? $groupType;
                
                // Try to find existing by route and old group type first to update it
                $existing = NavigationItem::where('route', $itemData['route'])
                    ->whereIn('group', ['manager', 'manager_with_club', 'manager_without_club', 'admin'])
                    ->first();

                if ($existing) {
                    $existing->update([
                        'label' => $itemData['label'],
                        'group' => $targetGroup,
                        'parent_id' => $parent->id,
                        'sort_order' => $itemSort,
                    ]);
                } else {
                    NavigationItem::create([
                        'route' => $itemData['route'],
                        'group' => $targetGroup,
                        'parent_id' => $parent->id,
                        'label' => $itemData['label'],
                        'sort_order' => $itemSort,
                    ]);
                }
                $itemSort += 10;
            }
        }
    }

    private function syncModuleGroups(string $groupType, array $groups)
    {
        // Modules structure is slightly different: $group['target_group'] might be present.
        // It's mapped as keys. E.g. "bg_main" => ['target_group' => 'bg_main', 'items' => [...]]
        
        $groupSort = 100; // Put module groups at the end normally
        foreach ($groups as $groupKey => $groupData) {
            
            $targetLabel = $groupData['label'] ?? 'Module Group';
            
            // If the module targets an existing group (using target_group which points to bg_buro, bg_main etc)
            // we should lookup its label. But since it"s dynamic, let"s try to find an existing parent by route fallback or name.
            $targetGroupKey = $groupData['target_group'] ?? null;
            
            // We just use the module"s provided label as the parent. Usually modules create new groups or append.
            $parent = NavigationItem::firstOrCreate([
                'label' => $targetLabel,
                'group' => $groupType,
                'parent_id' => null,
            ], [
                'sort_order' => $groupSort,
            ]);
            
            $groupSort += 10;
            $itemSort = 100;

            foreach ($groupData['items'] as $itemData) {
                NavigationItem::firstOrCreate([
                    'route' => $itemData['route'],
                    'group' => $groupType,
                    'parent_id' => $parent->id,
                ], [
                    'label' => $itemData['label'],
                    'sort_order' => $itemSort,
                ]);
                $itemSort += 10;
            }
        }
    }
}
