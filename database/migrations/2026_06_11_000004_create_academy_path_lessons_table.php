<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_path_lessons')) {
            return;
        }

        Schema::create('academy_path_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academy_path_id')->constrained('academy_paths')->cascadeOnDelete();
            $table->foreignId('academy_lesson_id')->constrained('academy_lessons')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['academy_path_id', 'academy_lesson_id']);
            $table->index(['academy_path_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_path_lessons');
    }
};
