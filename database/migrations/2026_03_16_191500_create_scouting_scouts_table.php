<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scouting_scouts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('level')->default('experienced');
            $table->string('specialty')->default('general');
            $table->string('region')->default('domestic');
            $table->string('status')->default('available');
            $table->unsignedTinyInteger('workload')->default(0);
            $table->foreignId('active_watchlist_id')->nullable()->constrained('scouting_watchlists')->nullOnDelete();
            $table->timestamp('available_at')->nullable();
            $table->timestamps();
        });

        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            $table->foreignId('scout_id')->nullable()->after('player_id')->constrained('scouting_scouts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scouting_watchlists', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('scout_id');
        });

        Schema::dropIfExists('scouting_scouts');
    }
};
