<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->foreignId('training_type_id')->nullable()->after('created_by_user_id')->constrained('training_types')->nullOnDelete();
            $table->string('training_type_name', 80)->nullable()->after('training_type_id');
            $table->json('effect_blueprint')->nullable()->after('unit_groups');
        });

        Schema::table('training_session_player', function (Blueprint $table): void {
            $table->json('attribute_deltas')->nullable()->after('overall_delta');
        });
    }

    public function down(): void
    {
        Schema::table('training_session_player', function (Blueprint $table): void {
            $table->dropColumn('attribute_deltas');
        });

        Schema::table('training_sessions', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('training_type_id');
            $table->dropColumn([
                'training_type_name',
                'effect_blueprint',
            ]);
        });
    }
};
