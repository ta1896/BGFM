<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NavigationItem;
use Illuminate\Support\Facades\Cache;

class AddPatchlogsNavigationSeeder extends Seeder
{
    public function run(): void
    {
        $parent = NavigationItem::where('label', 'Buero')
            ->where('group', 'manager_with_club')
            ->first();

        if ($parent) {
            // Check if already exists
            $exists = NavigationItem::where('route', 'patchlogs.index')
                ->where('parent_id', $parent->id)
                ->exists();

            if (!$exists) {
                NavigationItem::create([
                    'label' => 'Patchlogs',
                    'route' => 'patchlogs.index',
                    'icon' => 'ClockClockwise',
                    'parent_id' => $parent->id,
                    'group' => 'manager',
                    'sort_order' => 10, // Put it at the end
                ]);
                
                // Clear the cache
                Cache::forget('navigation_manager');
            }
        }
    }
}
