<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table): void {
            $table->json('tags')->nullable()->after('size_bucket');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_items', function (Blueprint $table): void {
            $table->dropColumn('tags');
        });
    }
};
