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
        // Add full-text index to users table for search functionality
        Schema::table('users', function (Blueprint $table) {
            $table->fullText(['name', 'email'], 'users_search_fulltext');
            $table->index(['is_active', 'created_at'], 'users_status_created_idx');
        });

        // Add indexes to activity_log table for better search performance
        Schema::table('activity_log', function (Blueprint $table) {
            $table->fullText(['description'], 'activity_log_description_fulltext');
            $table->index(['created_at', 'log_name'], 'activity_log_created_name_idx');
            $table->index(['causer_type', 'causer_id', 'created_at'], 'activity_log_causer_created_idx');
        });

        // Add indexes to password_reset_tokens for performance
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->index('created_at', 'password_reset_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropFullText('users_search_fulltext');
            $table->dropIndex('users_status_created_idx');
        });

        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropFullText('activity_log_description_fulltext');
            $table->dropIndex('activity_log_created_name_idx');
            $table->dropIndex('activity_log_causer_created_idx');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropIndex('password_reset_created_idx');
        });
    }
};
