<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('player_injuries', function (Blueprint $table): void {
            $table->string('rehab_intensity', 16)->nullable()->after('source');
            $table->string('return_phase', 24)->default('recovery')->after('rehab_intensity');
            $table->unsignedTinyInteger('setback_risk')->default(15)->after('return_phase');
            $table->text('notes')->nullable()->after('setback_risk');
        });
    }

    public function down(): void
    {
        Schema::table('player_injuries', function (Blueprint $table): void {
            $table->dropColumn([
                'rehab_intensity',
                'return_phase',
                'setback_risk',
                'notes',
            ]);
        });
    }
};
