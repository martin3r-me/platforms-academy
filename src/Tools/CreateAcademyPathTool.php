<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Services\AcademyPathService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class CreateAcademyPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.paths.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/paths - Erstellt einen neuen Lernpfad (kuratierte Lesson-Reihenfolge). Lessons werden separat via attach hinzugefuegt.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'academy_category_id' => ['type' => 'integer', 'description' => 'Kategorie/"School" des Kurses (bestimmt Cover-Farbe).'],
                'code' => ['type' => 'string', 'description' => 'Kurs-Code, z.B. "AI-101". Wird bei Kollision eindeutig gemacht.'],
                'level' => ['type' => 'string', 'enum' => ['beginner', 'intermediate', 'advanced'], 'description' => 'Schwierigkeitsgrad.'],
                'description' => ['type' => 'string'],
                'target_audience' => ['type' => 'string', 'description' => 'z.B. "Sales", "Dev", "Operations".'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                'public' => ['type' => 'boolean', 'description' => 'Website-Freigabe. Nur Kurse mit status=published UND public=true werden über die Public Course API ausgeliefert. Default: false.'],
                'icon' => ['type' => 'string'],
                'color' => ['type' => 'string', 'description' => 'Optionaler Cover-Farb-Override (Hex). Sonst erbt der Kurs die Kategorie-Farbe.'],
                'slug' => ['type' => 'string'],
                'sort_order' => ['type' => 'integer'],
            ],
            'required' => ['title'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $title = trim((string) ($arguments['title'] ?? ''));
            if ($title === '') {
                return ToolResult::error('VALIDATION_ERROR', 'title ist erforderlich.');
            }

            $path = app(AcademyPathService::class)->create(
                $resolved['team_id'],
                $context->user->id,
                array_merge($arguments, ['title' => $title]),
            );

            return ToolResult::success([
                'id' => $path->id,
                'uuid' => $path->uuid,
                'slug' => $path->slug,
                'title' => $path->title,
                'code' => $path->code,
                'level' => $path->level,
                'academy_category_id' => $path->academy_category_id,
                'status' => $path->status,
                'public' => $path->public,
                'message' => "Lernpfad '{$path->title}' erstellt (Status: {$path->status}).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'paths', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
