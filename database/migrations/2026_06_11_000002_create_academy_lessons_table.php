<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_lessons')) {
            return;
        }

        Schema::create('academy_lessons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('academy_topic_id')->constrained('academy_topics')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slug');
            $table->string('title');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['academy_topic_id', 'slug']);
            $table->index(['team_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_lessons');
    }
};
