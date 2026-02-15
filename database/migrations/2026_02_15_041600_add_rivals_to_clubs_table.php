<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('rival_id_1')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('rival_id_2')->nullable()->constrained('clubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clubs', function (Blueprint $table) {
            $table->dropForeign(['rival_id_1']);
            $table->dropForeign(['rival_id_2']);
            $table->dropColumn(['rival_id_1', 'rival_id_2']);
        });
    }
};
