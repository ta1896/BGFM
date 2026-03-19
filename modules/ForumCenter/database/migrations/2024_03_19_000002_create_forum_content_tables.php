<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('forum_id')->constrained('forum_forums')->cascadeOnDelete();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->string('title');
            $blueprint->string('slug')->unique();
            $blueprint->boolean('is_pinned')->default(false);
            $blueprint->boolean('is_locked')->default(false);
            $blueprint->integer('views_count')->default(0);
            $blueprint->timestamp('last_post_at')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('forum_posts', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignId('user_id')->constrained()->cascadeOnDelete();
            $blueprint->text('content');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_posts');
        Schema::dropIfExists('forum_threads');
    }
};
