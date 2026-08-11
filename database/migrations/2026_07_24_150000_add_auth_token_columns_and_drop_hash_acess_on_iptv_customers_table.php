<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('iptv_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('iptv_customers', 'auth_token_id')) {
                $table->string('auth_token_id', 64)->nullable()->unique()->after('username');
            }

            if (! Schema::hasColumn('iptv_customers', 'auth_token_hash')) {
                $table->string('auth_token_hash')->nullable()->after('auth_token_id');
            }

            if (! Schema::hasColumn('iptv_customers', 'auth_token_last_used_at')) {
                $table->timestamp('auth_token_last_used_at')->nullable()->after('auth_token_hash');
            }

            if (! Schema::hasColumn('iptv_customers', 'auth_token_expires_at')) {
                $table->timestamp('auth_token_expires_at')->nullable()->after('auth_token_last_used_at');
            }

            if (! Schema::hasColumn('iptv_customers', 'auth_token_revoked_at')) {
                $table->timestamp('auth_token_revoked_at')->nullable()->after('auth_token_expires_at');
            }
        });

        if (Schema::hasColumn('iptv_customers', 'hash_acess')) {
            Schema::table('iptv_customers', function (Blueprint $table) {
                $table->dropColumn('hash_acess');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (! Schema::hasColumn('iptv_customers', 'hash_acess')) {
            Schema::table('iptv_customers', function (Blueprint $table) {
                $table->string('hash_acess')->nullable()->after('username');
            });
        }

        Schema::table('iptv_customers', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('iptv_customers', 'auth_token_id')) {
                $columns[] = 'auth_token_id';
            }
            if (Schema::hasColumn('iptv_customers', 'auth_token_hash')) {
                $columns[] = 'auth_token_hash';
            }
            if (Schema::hasColumn('iptv_customers', 'auth_token_last_used_at')) {
                $columns[] = 'auth_token_last_used_at';
            }
            if (Schema::hasColumn('iptv_customers', 'auth_token_expires_at')) {
                $columns[] = 'auth_token_expires_at';
            }
            if (Schema::hasColumn('iptv_customers', 'auth_token_revoked_at')) {
                $columns[] = 'auth_token_revoked_at';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
