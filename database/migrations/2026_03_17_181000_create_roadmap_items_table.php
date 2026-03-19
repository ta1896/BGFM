<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_items', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 180)->unique();
            $table->string('title', 160);
            $table->text('summary');
            $table->string('status', 32)->default('planned');
            $table->string('category', 32)->default('mid');
            $table->string('size_bucket', 32)->default('small');
            $table->unsignedTinyInteger('priority')->default(3);
            $table->unsignedTinyInteger('effort')->default(3);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_items');
    }
};
