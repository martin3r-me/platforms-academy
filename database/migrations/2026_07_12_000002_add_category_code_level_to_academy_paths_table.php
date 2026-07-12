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
            if (!Schema::hasColumn('academy_paths', 'academy_category_id')) {
                $table->foreignId('academy_category_id')
                    ->nullable()
                    ->after('team_id')
                    ->constrained('academy_categories')
                    ->nullOnDelete();
            }
            if (!Schema::hasColumn('academy_paths', 'code')) {
                $table->string('code', 32)->nullable()->after('title');
            }
            if (!Schema::hasColumn('academy_paths', 'level')) {
                $table->string('level', 16)->nullable()->after('code');
            }
        });

        // Unique-Index fuer Kurs-Codes je Team (nur einmal anlegen).
        if (!$this->indexExists('academy_paths', 'academy_paths_team_id_code_unique')) {
            Schema::table('academy_paths', function (Blueprint $table) {
                $table->unique(['team_id', 'code'], 'academy_paths_team_id_code_unique');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('academy_paths')) {
            return;
        }

        Schema::table('academy_paths', function (Blueprint $table) {
            if ($this->indexExists('academy_paths', 'academy_paths_team_id_code_unique')) {
                $table->dropUnique('academy_paths_team_id_code_unique');
            }
            if (Schema::hasColumn('academy_paths', 'academy_category_id')) {
                $table->dropConstrainedForeignId('academy_category_id');
            }
            if (Schema::hasColumn('academy_paths', 'code')) {
                $table->dropColumn('code');
            }
            if (Schema::hasColumn('academy_paths', 'level')) {
                $table->dropColumn('level');
            }
        });
    }

    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $schemaManager = method_exists($connection, 'getDoctrineSchemaManager')
            ? $connection->getDoctrineSchemaManager()
            : null;

        if ($schemaManager) {
            return array_key_exists($index, $schemaManager->listTableIndexes($table));
        }

        // Fallback fuer neuere Laravel-Versionen ohne Doctrine.
        return collect(Schema::getIndexes($table))
            ->contains(fn ($i) => ($i['name'] ?? null) === $index);
    }
};
