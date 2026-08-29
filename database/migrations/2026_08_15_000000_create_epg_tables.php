<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epg_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('url');
            $table->boolean('enabled')->default(true)->index();
            $table->string('format', 30)->default('xmltv');
            $table->string('timezone', 64)->default('UTC');
            $table->unsignedInteger('refresh_interval')->default(360);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error')->nullable();
            $table->uuid('active_sync_generation')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('epg_channels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('epg_source_id')->constrained('epg_sources')->cascadeOnDelete();
            $table->string('external_id');
            $table->string('name')->nullable();
            $table->string('display_name');
            $table->text('icon_url')->nullable();
            $table->string('language', 16)->nullable();
            $table->string('country', 8)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['epg_source_id', 'external_id']);
        });

        Schema::create('epg_programmes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('epg_channel_id')->constrained('epg_channels')->cascadeOnDelete();
            $table->string('external_id')->nullable();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->text('icon_url')->nullable();
            $table->string('language', 16)->nullable();
            $table->timestamp('start_at')->index();
            $table->timestamp('end_at')->index();
            $table->json('metadata')->nullable();
            $table->uuid('sync_generation');
            $table->timestamps();
            $table->index(['epg_channel_id', 'start_at', 'end_at']);
            $table->unique(['epg_channel_id', 'external_id', 'sync_generation']);
            $table->index(['sync_generation', 'epg_channel_id']);
        });

        Schema::table('iptv_channels', function (Blueprint $table): void {
            $table->foreignId('epg_channel_id')->nullable()->after('group_id')
                ->constrained('epg_channels')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('iptv_channels', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('epg_channel_id');
        });
        Schema::dropIfExists('epg_programmes');
        Schema::dropIfExists('epg_channels');
        Schema::dropIfExists('epg_sources');
    }
};
