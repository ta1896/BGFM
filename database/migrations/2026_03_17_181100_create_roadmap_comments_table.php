<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_comments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('roadmap_item_id');
            $table->unsignedBigInteger('user_id');
            $table->text('body');
            $table->timestamps();
        });

        Schema::table('roadmap_comments', function (Blueprint $table): void {
            $table->index('roadmap_item_id');
            $table->index('user_id');
            $table->foreign('roadmap_item_id')->references('id')->on('roadmap_items')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_comments');
    }
};
