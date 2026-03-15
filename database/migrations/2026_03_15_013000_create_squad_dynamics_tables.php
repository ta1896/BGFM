<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('players', function (Blueprint $table): void {
            $table->string('squad_role', 32)->default('rotation')->after('status');
            $table->string('leadership_level', 32)->default('regular')->after('squad_role');
            $table->string('team_status', 32)->default('core')->after('leadership_level');
            $table->unsignedTinyInteger('expected_playtime')->default(50)->after('team_status');
            $table->unsignedTinyInteger('happiness')->default(60)->after('expected_playtime');
            $table->smallInteger('happiness_trend')->default(0)->after('happiness');
            $table->unsignedTinyInteger('fatigue')->default(25)->after('happiness_trend');
            $table->unsignedTinyInteger('sharpness')->default(60)->after('fatigue');
            $table->unsignedTinyInteger('injury_proneness')->default(20)->after('sharpness');
            $table->unsignedTinyInteger('match_load')->default(0)->after('injury_proneness');
            $table->unsignedTinyInteger('training_load')->default(0)->after('match_load');
            $table->string('medical_status', 32)->default('fit')->after('training_load');
            $table->string('last_morale_reason', 160)->nullable()->after('medical_status');
        });

        Schema::create('player_playtime_promises', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('promise_type', 32);
            $table->unsignedTinyInteger('expected_minutes_share')->default(50);
            $table->timestamp('deadline_at')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedTinyInteger('fulfilled_ratio')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('player_injuries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->string('injury_type', 64);
            $table->string('body_area', 64)->nullable();
            $table->string('severity', 16)->default('minor');
            $table->timestamp('started_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->timestamp('actual_return_at')->nullable();
            $table->string('status', 32)->default('active');
            $table->string('source', 32)->default('match');
            $table->timestamps();
        });

        Schema::create('player_recovery_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->date('day');
            $table->unsignedTinyInteger('training_load')->default(0);
            $table->unsignedTinyInteger('match_load')->default(0);
            $table->unsignedTinyInteger('fatigue_before')->default(0);
            $table->unsignedTinyInteger('fatigue_after')->default(0);
            $table->unsignedTinyInteger('sharpness_before')->default(0);
            $table->unsignedTinyInteger('sharpness_after')->default(0);
            $table->unsignedTinyInteger('injury_risk')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_recovery_logs');
        Schema::dropIfExists('player_injuries');
        Schema::dropIfExists('player_playtime_promises');

        Schema::table('players', function (Blueprint $table): void {
            $table->dropColumn([
                'squad_role',
                'leadership_level',
                'team_status',
                'expected_playtime',
                'happiness',
                'happiness_trend',
                'fatigue',
                'sharpness',
                'injury_proneness',
                'match_load',
                'training_load',
                'medical_status',
                'last_morale_reason',
            ]);
        });
    }
};
