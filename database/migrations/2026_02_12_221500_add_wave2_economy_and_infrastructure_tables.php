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
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120)->unique();
            $table->enum('tier', ['local', 'regional', 'national', 'global'])->default('local');
            $table->unsignedTinyInteger('reputation_min')->default(1);
            $table->decimal('base_weekly_amount', 12, 2);
            $table->decimal('signing_bonus_min', 12, 2)->default(0);
            $table->decimal('signing_bonus_max', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tier', 'reputation_min', 'is_active'], 'sponsors_tier_rep_active_idx');
        });

        Schema::create('sponsor_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('signed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('weekly_amount', 12, 2);
            $table->decimal('signing_bonus', 12, 2)->default(0);
            $table->date('starts_on');
            $table->date('ends_on');
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->date('last_payout_on')->nullable();
            $table->json('objectives')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status', 'ends_on'], 'scontract_club_status_ends_idx');
            $table->index(['sponsor_id', 'status'], 'scontract_sponsor_status_idx');
        });

        Schema::create('stadiums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('name', 120);
            $table->unsignedInteger('capacity')->default(18000);
            $table->unsignedInteger('covered_seats')->default(9000);
            $table->unsignedInteger('vip_seats')->default(900);
            $table->decimal('ticket_price', 8, 2)->default(18);
            $table->decimal('maintenance_cost', 12, 2)->default(25000);
            $table->unsignedTinyInteger('facility_level')->default(1);
            $table->unsignedTinyInteger('pitch_quality')->default(60);
            $table->unsignedTinyInteger('fan_experience')->default(60);
            $table->unsignedTinyInteger('security_level')->default(55);
            $table->unsignedTinyInteger('environment_level')->default(55);
            $table->timestamp('last_maintenance_at')->nullable();
            $table->timestamps();

            $table->index(['capacity', 'ticket_price'], 'stadiums_capacity_ticket_idx');
        });

        Schema::create('stadium_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stadium_id')->constrained()->cascadeOnDelete();
            $table->enum('project_type', ['capacity', 'pitch', 'facility', 'security', 'environment', 'vip']);
            $table->unsignedTinyInteger('level_from')->nullable();
            $table->unsignedTinyInteger('level_to')->nullable();
            $table->decimal('cost', 12, 2);
            $table->date('started_on');
            $table->date('completes_on');
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['stadium_id', 'status', 'completes_on'], 'sproject_stadium_status_complete_idx');
        });

        Schema::create('training_camps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name', 120);
            $table->enum('focus', ['fitness', 'tactics', 'technical', 'team_building']);
            $table->enum('intensity', ['low', 'medium', 'high'])->default('medium');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('cost', 12, 2)->default(0);
            $table->smallInteger('stamina_effect')->default(0);
            $table->smallInteger('morale_effect')->default(0);
            $table->smallInteger('overall_effect')->default(0);
            $table->enum('status', ['planned', 'active', 'completed', 'cancelled'])->default('planned');
            $table->timestamp('applied_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'status', 'starts_on', 'ends_on'], 'tcamp_club_status_dates_idx');
        });

        Schema::create('match_financial_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete()->unique();
            $table->decimal('home_income', 12, 2)->default(0);
            $table->decimal('home_expense', 12, 2)->default(0);
            $table->decimal('away_income', 12, 2)->default(0);
            $table->decimal('away_expense', 12, 2)->default(0);
            $table->timestamp('processed_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_financial_settlements');
        Schema::dropIfExists('training_camps');
        Schema::dropIfExists('stadium_projects');
        Schema::dropIfExists('stadiums');
        Schema::dropIfExists('sponsor_contracts');
        Schema::dropIfExists('sponsors');
    }
};
