<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('epg_channels', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->index()->after('metadata');
            $table->uuid('pending_sync_generation')->nullable()->index()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('epg_channels', function (Blueprint $table): void {
            $table->dropColumn(['is_active', 'pending_sync_generation']);
        });
    }
};
