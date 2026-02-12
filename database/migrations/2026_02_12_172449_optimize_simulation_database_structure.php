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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->char('iso_code', 2)->unique();
            $table->char('fifa_code', 3)->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('short_name', 16)->nullable();
            $table->enum('type', ['league', 'cup', 'national_team', 'friendly'])->default('league');
            $table->unsignedTinyInteger('tier')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['country_id', 'name', 'type']);
            $table->index(['type', 'is_active'], 'comp_type_active_idx');
        });

        Schema::create('seasons', function (Blueprint $table) {
            $table->id();
            $table->string('name', 32)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['is_current', 'start_date'], 'season_current_start_idx');
        });

        Schema::create('competition_seasons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->enum('format', ['round_robin', 'knockout', 'groups_knockout'])->default('round_robin');
            $table->unsignedTinyInteger('matchdays')->nullable();
            $table->unsignedTinyInteger('points_win')->default(3);
            $table->unsignedTinyInteger('points_draw')->default(1);
            $table->unsignedTinyInteger('points_loss')->default(0);
            $table->unsignedTinyInteger('promoted_slots')->default(0);
            $table->unsignedTinyInteger('relegated_slots')->default(0);
            $table->boolean('is_finished')->default(false);
            $table->timestamps();

            $table->unique(['competition_id', 'season_id']);
            $table->index(['season_id', 'is_finished'], 'compseason_season_finished_idx');
        });

        Schema::create('season_club_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('squad_limit')->nullable();
            $table->decimal('wage_cap', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['competition_season_id', 'club_id']);
        });

        Schema::create('season_club_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('matches_played')->default(0);
            $table->unsignedSmallInteger('wins')->default(0);
            $table->unsignedSmallInteger('draws')->default(0);
            $table->unsignedSmallInteger('losses')->default(0);
            $table->unsignedSmallInteger('goals_for')->default(0);
            $table->unsignedSmallInteger('goals_against')->default(0);
            $table->integer('goal_diff')->default(0);
            $table->unsignedSmallInteger('points')->default(0);
            $table->unsignedSmallInteger('home_points')->default(0);
            $table->unsignedSmallInteger('away_points')->default(0);
            $table->string('form_last5', 5)->nullable();
            $table->timestamps();

            $table->unique(['competition_season_id', 'club_id']);
            $table->index(['competition_season_id', 'points', 'goal_diff'], 'scs_comp_points_goal_idx');
        });

        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_season_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['league', 'cup', 'friendly'])->default('league');
            $table->string('stage', 64)->nullable();
            $table->unsignedSmallInteger('round_number')->nullable();
            $table->unsignedSmallInteger('matchday')->nullable();
            $table->dateTime('kickoff_at');
            $table->enum('status', ['scheduled', 'live', 'played', 'postponed', 'cancelled'])->default('scheduled');
            $table->foreignId('home_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('away_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('stadium_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->unsignedTinyInteger('home_score')->nullable();
            $table->unsignedTinyInteger('away_score')->nullable();
            $table->boolean('extra_time')->default(false);
            $table->unsignedTinyInteger('penalties_home')->nullable();
            $table->unsignedTinyInteger('penalties_away')->nullable();
            $table->unsignedInteger('attendance')->nullable();
            $table->string('weather', 40)->nullable();
            $table->unsignedInteger('simulation_seed')->nullable();
            $table->dateTime('played_at')->nullable();
            $table->timestamps();

            $table->index(['kickoff_at', 'status'], 'matches_kickoff_status_idx');
            $table->index(['home_club_id', 'away_club_id', 'status'], 'matches_home_away_status_idx');
            $table->index(['competition_season_id', 'matchday'], 'matches_compseason_matchday_idx');
        });

        Schema::create('match_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->unsignedTinyInteger('minute');
            $table->unsignedTinyInteger('second')->default(0);
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->foreignId('assister_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->enum('event_type', [
                'goal',
                'own_goal',
                'yellow_card',
                'red_card',
                'substitution',
                'injury',
                'penalty_scored',
                'penalty_missed',
                'save',
                'chance',
            ]);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['match_id', 'minute', 'event_type'], 'mevent_match_minute_type_idx');
        });

        Schema::create('match_player_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('lineup_role', ['starter', 'bench', 'sub_on', 'sub_off'])->default('starter');
            $table->string('position_code', 4)->nullable();
            $table->decimal('rating', 4, 2)->nullable();
            $table->unsignedTinyInteger('minutes_played')->default(0);
            $table->unsignedTinyInteger('goals')->default(0);
            $table->unsignedTinyInteger('assists')->default(0);
            $table->unsignedTinyInteger('yellow_cards')->default(0);
            $table->unsignedTinyInteger('red_cards')->default(0);
            $table->unsignedSmallInteger('shots')->default(0);
            $table->unsignedSmallInteger('passes_completed')->default(0);
            $table->unsignedSmallInteger('passes_failed')->default(0);
            $table->unsignedSmallInteger('tackles_won')->default(0);
            $table->unsignedSmallInteger('tackles_lost')->default(0);
            $table->unsignedSmallInteger('saves')->default(0);
            $table->timestamps();

            $table->unique(['match_id', 'player_id']);
            $table->index(['club_id', 'rating'], 'mplayerstats_club_rating_idx');
        });

        Schema::create('player_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->decimal('wage', 10, 2);
            $table->decimal('bonus_goal', 10, 2)->default(0);
            $table->date('signed_on');
            $table->date('starts_on')->nullable();
            $table->date('expires_on');
            $table->decimal('release_clause', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['player_id', 'is_active'], 'pcontract_player_active_idx');
            $table->index(['club_id', 'expires_on'], 'pcontract_club_expires_idx');
        });

        Schema::create('transfer_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('seller_club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->foreignId('listed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('min_price', 12, 2);
            $table->decimal('buy_now_price', 12, 2)->nullable();
            $table->dateTime('listed_at');
            $table->dateTime('expires_at');
            $table->enum('status', ['open', 'closed', 'cancelled', 'sold'])->default('open');
            $table->timestamps();

            $table->index(['status', 'expires_at'], 'tlisting_status_expires_idx');
            $table->index(['player_id', 'status'], 'tlisting_player_status_idx');
        });

        Schema::create('transfer_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_listing_id')->constrained('transfer_listings')->cascadeOnDelete();
            $table->foreignId('bidder_club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('bidder_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->foreignId('offer_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->string('message', 255)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->dateTime('decided_at')->nullable();
            $table->timestamps();

            $table->index(['transfer_listing_id', 'amount'], 'tbid_listing_amount_idx');
            $table->index(['status', 'created_at'], 'tbid_status_created_idx');
        });

        Schema::create('club_financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('context_type', [
                'match_income',
                'transfer',
                'salary',
                'sponsor',
                'stadium',
                'training',
                'admin_adjustment',
                'other',
            ]);
            $table->enum('direction', ['income', 'expense']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2)->nullable();
            $table->string('reference_type', 64)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->dateTime('booked_at');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['club_id', 'booked_at', 'context_type'], 'cfinance_club_booked_context_idx');
        });

        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['fitness', 'tactics', 'technical', 'recovery', 'friendly'])->default('fitness');
            $table->enum('intensity', ['low', 'medium', 'high'])->default('medium');
            $table->enum('focus_position', ['GK', 'DEF', 'MID', 'FWD'])->nullable();
            $table->date('session_date');
            $table->smallInteger('morale_effect')->default(0);
            $table->smallInteger('stamina_effect')->default(0);
            $table->smallInteger('form_effect')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['club_id', 'session_date'], 'tsession_club_date_idx');
        });

        Schema::create('training_session_player', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->enum('role', ['participant', 'rest', 'injured'])->default('participant');
            $table->smallInteger('stamina_delta')->default(0);
            $table->smallInteger('morale_delta')->default(0);
            $table->smallInteger('overall_delta')->default(0);
            $table->timestamps();

            $table->unique(['training_session_id', 'player_id']);
        });

        Schema::create('game_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('club_id')->nullable()->constrained('clubs')->nullOnDelete();
            $table->string('type', 64);
            $table->string('title', 120);
            $table->text('message');
            $table->string('action_url', 255)->nullable();
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'seen_at', 'created_at'], 'gnotify_user_seen_created_idx');
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->foreignId('league_id')->nullable()->after('league')->constrained('competitions')->nullOnDelete();
            $table->string('slug', 140)->nullable()->after('name')->unique();
            $table->unsignedInteger('fanbase')->default(0)->after('fan_mood');
            $table->unsignedTinyInteger('board_confidence')->default(50)->after('fanbase');
            $table->unsignedTinyInteger('training_level')->default(1)->after('board_confidence');

            $table->index(['league_id', 'reputation'], 'clubs_league_rep_idx');
            $table->index(['board_confidence', 'fan_mood'], 'clubs_board_fanmood_idx');
        });

        Schema::table('players', function (Blueprint $table) {
            $table->enum('preferred_foot', ['right', 'left', 'both'])->default('right')->after('position');
            $table->unsignedTinyInteger('potential')->default(60)->after('overall');
            $table->enum('status', ['active', 'injured', 'suspended', 'transfer_listed', 'youth'])->default('active')->after('morale');
            $table->date('contract_expires_on')->nullable()->after('salary');
            $table->timestamp('last_training_at')->nullable()->after('contract_expires_on');

            $table->index(['club_id', 'status', 'overall'], 'players_club_status_ovr_idx');
            $table->index(['contract_expires_on', 'status'], 'players_contract_status_idx');
        });

        Schema::table('lineups', function (Blueprint $table) {
            $table->foreignId('match_id')->nullable()->after('club_id')->constrained('matches')->nullOnDelete();
            $table->boolean('is_template')->default(true)->after('is_active');
            $table->enum('tactical_style', ['balanced', 'offensive', 'defensive', 'counter'])->default('balanced')->after('formation');

            $table->index(['match_id', 'is_active'], 'lineups_match_active_idx');
        });

        Schema::table('lineup_player', function (Blueprint $table) {
            $table->boolean('is_captain')->default(false)->after('sort_order');
            $table->boolean('is_set_piece_taker')->default(false)->after('is_captain');

            $table->index(['player_id', 'sort_order'], 'lineupplayer_player_sort_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lineup_player', function (Blueprint $table) {
            $table->dropIndex('lineupplayer_player_sort_idx');
            $table->dropColumn(['is_captain', 'is_set_piece_taker']);
        });

        Schema::table('lineups', function (Blueprint $table) {
            $table->dropIndex('lineups_match_active_idx');
            $table->dropConstrainedForeignId('match_id');
            $table->dropColumn(['is_template', 'tactical_style']);
        });

        Schema::table('players', function (Blueprint $table) {
            $table->dropIndex('players_club_status_ovr_idx');
            $table->dropIndex('players_contract_status_idx');
            $table->dropColumn([
                'preferred_foot',
                'potential',
                'status',
                'contract_expires_on',
                'last_training_at',
            ]);
        });

        Schema::table('clubs', function (Blueprint $table) {
            $table->dropIndex('clubs_league_rep_idx');
            $table->dropIndex('clubs_board_fanmood_idx');
            $table->dropUnique('clubs_slug_unique');
            $table->dropConstrainedForeignId('league_id');
            $table->dropColumn(['slug', 'fanbase', 'board_confidence', 'training_level']);
        });

        Schema::dropIfExists('game_notifications');
        Schema::dropIfExists('training_session_player');
        Schema::dropIfExists('training_sessions');
        Schema::dropIfExists('club_financial_transactions');
        Schema::dropIfExists('transfer_bids');
        Schema::dropIfExists('transfer_listings');
        Schema::dropIfExists('player_contracts');
        Schema::dropIfExists('match_player_stats');
        Schema::dropIfExists('match_events');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('season_club_statistics');
        Schema::dropIfExists('season_club_registrations');
        Schema::dropIfExists('competition_seasons');
        Schema::dropIfExists('seasons');
        Schema::dropIfExists('competitions');
        Schema::dropIfExists('countries');
    }
};
