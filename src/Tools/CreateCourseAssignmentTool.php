<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyPath;
use Platform\Academy\Services\AcademyAssignmentService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;
use Platform\Core\Registry\AudienceResolverRegistry;

class CreateCourseAssignmentTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.assignments.POST';
    }

    public function getDescription(): string
    {
        return 'POST /academy/assignments - Weist einen Kurs (Path) einem Ziel zu und macht ihn optional zur Pflicht, mit Start/Fällig-Datum. Das Ziel wird sofort zu Personen aufgeloest (Auto-Enroll). ERFORDERLICH: path_id, target_type (user|team|org_entity|org_role), target_id. Optional: target_options (z.B. {"include_subteams":true,"include_descendants":true}), is_mandatory (Default true), starts_at, due_at (YYYY-MM-DD), note. Hinweis: org_entity/org_role sind nur verfuegbar, wenn das Organisation-Modul installiert ist.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'path_id' => ['type' => 'integer'],
                'target_type' => ['type' => 'string', 'enum' => ['user', 'team', 'org_entity', 'org_role']],
                'target_id' => ['type' => 'integer'],
                'target_options' => ['type' => 'object', 'description' => 'Ziel-Optionen, z.B. include_subteams / include_descendants.'],
                'is_mandatory' => ['type' => 'boolean', 'description' => 'Pflicht (Default true) vs. Empfehlung.'],
                'starts_at' => ['type' => 'string', 'description' => 'Ab wann relevant (YYYY-MM-DD).'],
                'due_at' => ['type' => 'string', 'description' => 'Deadline (YYYY-MM-DD).'],
                'note' => ['type' => 'string'],
            ],
            'required' => ['path_id', 'target_type', 'target_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $path = AcademyPath::where('team_id', $resolved['team_id'])->find((int) ($arguments['path_id'] ?? 0));
            if (!$path) {
                return ToolResult::error('NOT_FOUND', 'Kurs (Path) nicht gefunden.');
            }

            $targetType = (string) ($arguments['target_type'] ?? '');
            $registry = app(AudienceResolverRegistry::class);
            if (!$registry->supports($targetType)) {
                return ToolResult::error('UNSUPPORTED_TARGET', "Ziel-Typ '{$targetType}' ist nicht verfuegbar. Verfuegbar: " . implode(', ', $registry->supportedTypes()) . '.');
            }

            $targetId = (int) ($arguments['target_id'] ?? 0);
            if ($targetId <= 0) {
                return ToolResult::error('VALIDATION_ERROR', 'target_id ist erforderlich.');
            }

            $rule = app(AcademyAssignmentService::class)->assign(
                $path,
                $targetType,
                $targetId,
                is_array($arguments['target_options'] ?? null) ? $arguments['target_options'] : [],
                $context->user->id ?? null,
                [
                    'is_mandatory' => $arguments['is_mandatory'] ?? true,
                    'starts_at' => $arguments['starts_at'] ?? null,
                    'due_at' => $arguments['due_at'] ?? null,
                    'note' => $arguments['note'] ?? null,
                ],
            );

            $count = $rule->userAssignments()->count();

            return ToolResult::success([
                'id' => $rule->id,
                'uuid' => $rule->uuid,
                'path_id' => $path->id,
                'target' => $registry->label($targetType, $targetId, $resolved['team_id']) ?? ($targetType . ' #' . $targetId),
                'is_mandatory' => (bool) $rule->is_mandatory,
                'due_at' => $rule->due_at?->toDateString(),
                'assigned_persons' => $count,
                'message' => "Kurs '{$path->title}' zugewiesen an {$count} Person(en).",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'assignments', 'create'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
