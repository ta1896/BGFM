<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('club_financial_transactions', function (Blueprint $table): void {
            $table->string('asset_type', 16)->default('budget');
        });
    }

    public function down(): void
    {
        Schema::table('club_financial_transactions', function (Blueprint $table): void {
            $table->dropColumn('asset_type');
        });
    }
};
