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
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('short_name', 12)->nullable();
            $table->string('country', 80)->default('Deutschland');
            $table->string('league', 120)->default('Amateurliga');
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->unsignedTinyInteger('reputation')->default(50);
            $table->unsignedTinyInteger('fan_mood')->default(50);
            $table->decimal('budget', 12, 2)->default(500000);
            $table->decimal('wage_budget', 12, 2)->default(250000);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clubs');
    }
};
