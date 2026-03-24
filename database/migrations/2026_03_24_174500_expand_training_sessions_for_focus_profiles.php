<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->string('team_focus', 32)->nullable()->after('type');
            $table->string('unit_focus', 32)->nullable()->after('team_focus');
            $table->json('unit_groups')->nullable()->after('focus_position');
        });

        Schema::table('training_session_player', function (Blueprint $table): void {
            $table->string('focus_group', 8)->nullable()->after('role');
            $table->string('primary_focus', 32)->nullable()->after('focus_group');
            $table->string('secondary_focus', 32)->nullable()->after('primary_focus');
            $table->string('individual_intensity', 16)->nullable()->after('secondary_focus');
        });
    }

    public function down(): void
    {
        Schema::table('training_session_player', function (Blueprint $table): void {
            $table->dropColumn([
                'focus_group',
                'primary_focus',
                'secondary_focus',
                'individual_intensity',
            ]);
        });

        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->dropColumn([
                'team_focus',
                'unit_focus',
                'unit_groups',
            ]);
        });
    }
};
