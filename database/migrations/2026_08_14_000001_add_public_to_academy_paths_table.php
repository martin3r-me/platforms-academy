<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('academy_paths')) {
            return;
        }

        Schema::table('academy_paths', function (Blueprint $table) {
            if (!Schema::hasColumn('academy_paths', 'public')) {
                // Freigabe für die öffentliche Website. Getrennt vom Redaktions-
                // Status: nur Kurse mit status=published UND public=true werden
                // über die Public Course API ausgeliefert.
                $table->boolean('public')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('academy_paths')) {
            return;
        }

        Schema::table('academy_paths', function (Blueprint $table) {
            if (Schema::hasColumn('academy_paths', 'public')) {
                $table->dropColumn('public');
            }
        });
    }
};
