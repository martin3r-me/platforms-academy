<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Platform\Academy\Services\AcademyCategoryService;

/**
 * Legt die 6 Standard-Kategorien ("Schools") automatisch fuer jedes Team an,
 * das bereits Academy-Inhalte (Kurse oder Themen) hat. Idempotent — nutzt
 * firstOrCreate je (team_id, slug), laesst bestehende Kategorien unangetastet.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('academy_categories')) {
            return;
        }

        $service = app(AcademyCategoryService::class);

        $teamIds = collect();
        if (Schema::hasTable('academy_paths')) {
            $teamIds = $teamIds->merge(DB::table('academy_paths')->distinct()->pluck('team_id'));
        }
        if (Schema::hasTable('academy_topics')) {
            $teamIds = $teamIds->merge(DB::table('academy_topics')->distinct()->pluck('team_id'));
        }

        $teamIds->filter()->unique()->each(function ($teamId) use ($service) {
            $service->seedDefaults((int) $teamId);
        });
    }

    public function down(): void
    {
        // Bewusst kein Loeschen: Kategorien koennen zwischenzeitlich Kursen
        // zugeordnet oder angepasst worden sein.
    }
};
