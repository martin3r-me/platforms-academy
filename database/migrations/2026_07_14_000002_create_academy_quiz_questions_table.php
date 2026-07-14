<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_quiz_questions')) {
            return;
        }

        Schema::create('academy_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('academy_quiz_id')->constrained('academy_quizzes')->cascadeOnDelete();
            $table->string('type', 16)->default('single'); // single | multiple
            $table->text('prompt'); // Markdown
            $table->text('explanation')->nullable(); // Erklaerung nach dem Beantworten
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('academy_quiz_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_quiz_questions');
    }
};
