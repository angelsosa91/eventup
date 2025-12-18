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
        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_category_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('module')->nullable(); // Para ayuda contextual: 'sales', 'purchases', etc.
            $table->string('video_url')->nullable();
            $table->integer('order')->default(0);
            $table->integer('views')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('tags')->nullable();
            $table->timestamps();

            $table->index(['module', 'is_active']);
            $table->index('help_category_id');
        });

        Schema::create('help_article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_article_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamp('viewed_at');

            $table->index('help_article_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_article_views');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('help_categories');
    }
};
