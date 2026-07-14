<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_quiz_options')) {
            return;
        }

        Schema::create('academy_quiz_options', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('academy_quiz_question_id')->constrained('academy_quiz_questions')->cascadeOnDelete();
            $table->text('label');
            $table->boolean('is_correct')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('academy_quiz_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_quiz_options');
    }
};
