<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyEnrollmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class EnrollInPathTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.enrollments.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/enrollments - Schreibt den aktuellen User in einen Kurs (Lernpfad) ein. ERFORDERLICH: path_id.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
            ],
            'required' => ['path_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $path = AcademyPath::where('team_id', $resolved['team_id'])->find((int) ($arguments['path_id'] ?? 0));
            if (!$path) {
                return ToolResult::error('NOT_FOUND', 'Lernpfad nicht gefunden.');
            }

            $enrollment = app(AcademyEnrollmentService::class)->enroll($context->user->id, $path);

            return ToolResult::success([
                'enrollment_id' => $enrollment->id,
                'path_id' => $path->id,
                'path_title' => $path->title,
                'status' => $enrollment->status,
                'message' => "Eingeschrieben in '{$path->title}'.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'enrollments', 'enroll'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => true,
        ];
    }
}
