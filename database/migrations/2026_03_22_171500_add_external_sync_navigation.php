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
        $engineGroup = NavigationItem::where('label', 'Engine & Tools')
            ->where('group', 'admin')
            ->whereNull('parent_id')
            ->first();

        if ($engineGroup) {
            NavigationItem::updateOrCreate(
                ['route' => 'admin.external-sync.index', 'group' => 'admin'],
                [
                    'label' => 'Externer Sync',
                    'icon' => 'Globe',
                    'parent_id' => $engineGroup->id,
                    'sort_order' => 10 // Put it at the end of the section
                ]
            );
        }

        Cache::forget('navigation_admin');
        Cache::forget('navigation_manager');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NavigationItem::where('route', 'admin.external-sync.index')->delete();
    }
};
