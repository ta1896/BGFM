<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('training_camps');
    }

    public function down(): void
    {
        Schema::create('training_camps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('focus', 32);
            $table->string('intensity', 16)->default('medium');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('cost', 12, 2)->default(0);
            $table->smallInteger('stamina_effect')->default(2);
            $table->smallInteger('morale_effect')->default(1);
            $table->smallInteger('overall_effect')->default(0);
            $table->string('status', 16)->default('planned');
            $table->timestamps();

            $table->index(['club_id', 'status', 'starts_on', 'ends_on'], 'tcamp_club_status_dates_idx');
        });
    }
};
