<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_course_assignments')) {
            return;
        }

        Schema::create('academy_course_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->foreignId('academy_path_id')->constrained('academy_paths')->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()
                ->constrained('users')->nullOnDelete();
            $table->string('target_type', 32);            // user | team | org_entity | org_role
            $table->unsignedBigInteger('target_id');
            $table->json('target_options')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->date('starts_at')->nullable();
            $table->date('due_at')->nullable();
            $table->text('note')->nullable();
            $table->string('status', 16)->default('active'); // active | archived
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['team_id', 'academy_path_id']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_course_assignments');
    }
};
