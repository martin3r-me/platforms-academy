<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academy_certificates')) {
            return;
        }

        Schema::create('academy_certificates', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('academy_path_id')->constrained('academy_paths')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->string('serial')->unique();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            // Ein Zertifikat pro User und Kurs.
            $table->unique(['user_id', 'academy_path_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_certificates');
    }
};
