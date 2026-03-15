<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->boolean('role_override_active')->default(false)->after('expected_playtime');
            $table->timestamp('role_override_set_at')->nullable()->after('role_override_active');
        });
    }

    public function down(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->dropColumn([
                'role_override_active',
                'role_override_set_at',
            ]);
        });
    }
};
