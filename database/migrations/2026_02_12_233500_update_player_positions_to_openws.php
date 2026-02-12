<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->mapLegacyPositions();
        $this->updateEnum();
    }

    public function down(): void
    {
        $this->mapOpenWsPositions();
        $this->restoreLegacyEnum();
    }

    private function mapLegacyPositions(): void
    {
        DB::table('players')->where('position', 'GK')->update(['position' => 'TW']);
        DB::table('players')->where('position', 'DEF')->update(['position' => 'IV']);
        DB::table('players')->where('position', 'MID')->update(['position' => 'ZM']);
        DB::table('players')->where('position', 'FWD')->update(['position' => 'ST']);
    }

    private function mapOpenWsPositions(): void
    {
        DB::table('players')->where('position', 'TW')->update(['position' => 'GK']);
        DB::table('players')->whereIn('position', ['LV', 'IV', 'RV', 'LWB', 'RWB'])->update(['position' => 'DEF']);
        DB::table('players')->whereIn('position', ['LM', 'ZM', 'RM', 'DM', 'OM', 'LAM', 'ZOM', 'RAM'])->update(['position' => 'MID']);
        DB::table('players')->whereIn('position', ['LS', 'MS', 'RS', 'LW', 'RW', 'ST'])->update(['position' => 'FWD']);
    }

    private function updateEnum(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE players MODIFY position ENUM('TW','LV','IV','RV','LWB','RWB','LM','ZM','RM','DM','OM','LAM','ZOM','RAM','LS','MS','RS','LW','RW','ST') NOT NULL"
        );
    }

    private function restoreLegacyEnum(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE players MODIFY position ENUM('GK','DEF','MID','FWD') NOT NULL");
    }
};
