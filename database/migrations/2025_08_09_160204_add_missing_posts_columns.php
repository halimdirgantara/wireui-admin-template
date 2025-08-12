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
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('reading_time')->default(1)->after('views_count');
            $table->text('meta_keywords')->nullable()->after('seo_description');
            $table->string('og_image')->nullable()->after('meta_keywords');
            $table->string('twitter_card')->default('summary_large_image')->after('og_image');
            $table->longText('custom_css')->nullable()->after('twitter_card');
            $table->longText('custom_js')->nullable()->after('custom_css');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['reading_time', 'meta_keywords', 'og_image', 'twitter_card', 'custom_css', 'custom_js']);
        });
    }
};