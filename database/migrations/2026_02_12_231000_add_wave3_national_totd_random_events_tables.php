<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('national_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('short_name', 24)->nullable();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('reputation')->default(60);
            $table->enum('tactical_style', ['balanced', 'offensive', 'defensive', 'counter'])->default('balanced');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('country_id');
            $table->index(['manager_user_id', 'reputation'], 'nteam_manager_rep_idx');
        });

        Schema::create('national_team_callups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('national_team_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('called_up_on');
            $table->date('released_on')->nullable();
            $table->enum('role', ['starter', 'bench', 'reserve'])->default('reserve');
            $table->enum('status', ['active', 'released', 'injured'])->default('active');
            $table->timestamps();

            $table->index(['national_team_id', 'status', 'role'], 'ncallup_team_status_role_idx');
            $table->index(['player_id', 'status'], 'ncallup_player_status_idx');
        });

        Schema::create('team_of_the_days', function (Blueprint $table) {
            $table->id();
            $table->date('for_date');
            $table->string('label', 120);
            $table->string('formation', 20)->default('4-3-3');
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('generation_context', 32)->default('matches');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('for_date');
            $table->index(['generated_by_user_id', 'for_date'], 'totd_user_date_idx');
        });

        Schema::create('team_of_the_day_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_of_the_day_id')->constrained('team_of_the_days')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->string('position_code', 8);
            $table->decimal('rating', 4, 2)->nullable();
            $table->json('stats_snapshot')->nullable();
            $table->timestamps();

            $table->unique(['team_of_the_day_id', 'position_code'], 'totd_pos_unique');
            $table->index(['player_id', 'rating'], 'totdp_player_rating_idx');
        });

        Schema::create('random_event_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->enum('category', ['finance', 'player', 'club', 'discipline', 'medical'])->default('club');
            $table->enum('rarity', ['common', 'uncommon', 'rare', 'epic'])->default('common');
            $table->unsignedTinyInteger('min_reputation')->nullable();
            $table->unsignedTinyInteger('max_reputation')->nullable();
            $table->integer('budget_delta_min')->default(0);
            $table->integer('budget_delta_max')->default(0);
            $table->smallInteger('morale_delta')->default(0);
            $table->smallInteger('stamina_delta')->default(0);
            $table->smallInteger('overall_delta')->default(0);
            $table->smallInteger('fan_mood_delta')->default(0);
            $table->smallInteger('board_confidence_delta')->default(0);
            $table->unsignedSmallInteger('probability_weight')->default(100);
            $table->boolean('is_active')->default(true);
            $table->string('description_template', 255)->nullable();
            $table->timestamps();

            $table->index(['is_active', 'category', 'rarity'], 'reventtpl_active_cat_rare_idx');
        });

        Schema::create('random_event_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('random_event_templates')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('triggered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'applied', 'discarded'])->default('pending');
            $table->string('title', 140);
            $table->text('message');
            $table->date('happened_on');
            $table->dateTime('applied_at')->nullable();
            $table->json('effect_payload')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status', 'happened_on'], 'revent_club_status_date_idx');
            $table->index(['player_id', 'status'], 'revent_player_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('random_event_occurrences');
        Schema::dropIfExists('random_event_templates');
        Schema::dropIfExists('team_of_the_day_players');
        Schema::dropIfExists('team_of_the_days');
        Schema::dropIfExists('national_team_callups');
        Schema::dropIfExists('national_teams');
    }
};
