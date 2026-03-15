<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_modules', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 120)->unique();
            $table->string('name', 160);
            $table->string('version', 32)->default('1.0.0');
            $table->string('description', 255)->nullable();
            $table->boolean('enabled')->default(false);
            $table->string('module_path', 255);
            $table->json('config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_modules');
    }
};
