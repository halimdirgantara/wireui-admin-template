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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type', 100)->index(); // e.g., 'login', 'logout', 'profile_update', 'password_change'
            $table->text('description');
            $table->json('meta')->nullable(); // Store additional metadata
            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at')->nullable();

            // Composite indexes for common queries
            $table->index(['user_id', 'created_at'], 'activities_user_created_idx');
            $table->index(['type', 'created_at'], 'activities_type_created_idx');
            $table->fullText(['description'], 'activities_description_fulltext');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
