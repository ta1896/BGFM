<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->integer('sort_order')->default(0);
            $blueprint->timestamps();
        });

        Schema::create('forum_forums', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('forum_category_id')->constrained()->cascadeOnDelete();
            $blueprint->string('name');
            $blueprint->string('slug')->unique();
            $blueprint->text('description')->nullable();
            $blueprint->string('icon')->nullable();
            $blueprint->integer('sort_order')->default(0);
            $blueprint->boolean('is_locked')->default(false);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_forums');
        Schema::dropIfExists('forum_categories');
    }
};
