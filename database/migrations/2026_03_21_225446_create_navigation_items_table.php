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
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('navigation_items')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->string('permission')->nullable();
            $table->string('group')->default('main'); // e.g. 'main', 'footer', 'acp'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigation_items');
    }
};
