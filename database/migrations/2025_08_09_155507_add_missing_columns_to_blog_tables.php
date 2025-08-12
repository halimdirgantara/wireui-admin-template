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
        // Add missing columns to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->string('color', 7)->default('#3b82f6')->after('sort_order');
            $table->string('icon', 100)->nullable()->after('color');
            $table->string('image')->nullable()->after('icon');
            $table->string('meta_keywords')->nullable()->after('seo_description');
        });

        // Add missing columns to posts table
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'reading_time')) {
                $table->integer('reading_time')->default(1)->after('views_count');
            }
            if (!Schema::hasColumn('posts', 'meta_keywords')) {
                $table->text('meta_keywords')->nullable()->after('seo_description');
            }
            if (!Schema::hasColumn('posts', 'og_image')) {
                $table->string('og_image')->nullable()->after('meta_keywords');
            }
            if (!Schema::hasColumn('posts', 'twitter_card')) {
                $table->string('twitter_card')->default('summary_large_image')->after('og_image');
            }
            if (!Schema::hasColumn('posts', 'custom_css')) {
                $table->longText('custom_css')->nullable()->after('twitter_card');
            }
            if (!Schema::hasColumn('posts', 'custom_js')) {
                $table->longText('custom_js')->nullable()->after('custom_css');
            }
        });

        // Ensure tags table has all necessary columns
        if (!Schema::hasColumn('tags', 'meta_keywords')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->string('meta_keywords')->nullable()->after('description');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['color', 'icon', 'image', 'meta_keywords']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['reading_time', 'meta_keywords', 'og_image', 'twitter_card', 'custom_css', 'custom_js']);
        });

        if (Schema::hasColumn('tags', 'meta_keywords')) {
            Schema::table('tags', function (Blueprint $table) {
                $table->dropColumn('meta_keywords');
            });
        }
    }
};