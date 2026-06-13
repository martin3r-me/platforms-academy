<?php

namespace Platform\Academy\Tools;

use Platform\Academy\Models\AcademyLesson;
use Platform\Academy\Services\AcademyLessonService;
use Platform\Academy\Tools\Concerns\ResolvesAcademyTeam;
use Platform\Core\Contracts\ToolContract;
use Platform\Core\Contracts\ToolContext;
use Platform\Core\Contracts\ToolMetadataContract;
use Platform\Core\Contracts\ToolResult;

class UpdateAcademyLessonTool implements ToolContract, ToolMetadataContract
{
    use ResolvesAcademyTeam;

    public function getName(): string
    {
        return 'academy.lessons.PUT';
    }

    public function getDescription(): string
    {
        return 'PUT /academy/lessons - Aktualisiert eine Lesson. ERFORDERLICH: lesson_id. Optional: title, summary, content, status, estimated_minutes, sort_order. Content-Operationen via op: append, prepend, replace_exact, upsert_heading.';
    }

    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'team_id' => ['type' => 'integer'],
                'lesson_id' => ['type' => 'integer'],
                'title' => ['type' => 'string'],
                'summary' => ['type' => 'string'],
                'content' => ['type' => 'string', 'description' => 'Ersetzt komplett. Wird ignoriert wenn op gesetzt.'],
                'status' => ['type' => 'string', 'enum' => ['draft', 'published', 'archived']],
                'estimated_minutes' => ['type' => 'integer'],
                'sort_order' => ['type' => 'integer'],
                'op' => [
                    'type' => 'string',
                    'enum' => ['append', 'prepend', 'replace_exact', 'upsert_heading'],
                    'description' => 'Content-Operation statt vollstaendigem Ersetzen.',
                ],
                'text' => ['type' => 'string', 'description' => 'Text fuer op=append/prepend/upsert_heading.'],
                'old' => ['type' => 'string', 'description' => 'Alter Text fuer op=replace_exact.'],
                'new' => ['type' => 'string', 'description' => 'Neuer Text fuer op=replace_exact.'],
                'heading' => ['type' => 'string', 'description' => 'Heading-Text fuer op=upsert_heading.'],
                'level' => ['type' => 'integer', 'description' => 'Heading-Level 1-6 fuer op=upsert_heading. Default 2.'],
                'mode' => ['type' => 'string', 'enum' => ['append', 'replace'], 'description' => 'Modus fuer op=upsert_heading.'],
            ],
            'required' => ['lesson_id'],
        ];
    }

    public function execute(array $arguments, ToolContext $context): ToolResult
    {
        try {
            $resolved = $this->resolveTeam($arguments, $context);
            if ($resolved['error']) return $resolved['error'];

            $lessonId = (int) ($arguments['lesson_id'] ?? 0);
            $lesson = AcademyLesson::where('team_id', $resolved['team_id'])->find($lessonId);
            if (!$lesson) {
                return ToolResult::error('NOT_FOUND', 'Lesson nicht gefunden.');
            }

            $service = app(AcademyLessonService::class);
            $payload = [];

            $op = $arguments['op'] ?? null;
            if ($op !== null && $op !== '') {
                $result = $service->applyContentOp($lesson, (string) $op, $arguments);
                if (!$result['success']) {
                    return ToolResult::error('VALIDATION_ERROR', $result['error']);
                }
                if ((string) $result['content'] === (string) ($lesson->content ?? '')) {
                    return ToolResult::error('NO_CHANGE', 'Keine Aenderung am Content.');
                }
                $payload['content'] = (string) $result['content'];
            }

            foreach (['title', 'summary', 'estimated_minutes', 'status', 'sort_order'] as $field) {
                if (array_key_exists($field, $arguments) && $arguments[$field] !== null) {
                    $payload[$field] = $arguments[$field];
                }
            }
            if ($op === null && array_key_exists('content', $arguments) && $arguments['content'] !== null) {
                $payload['content'] = (string) $arguments['content'];
            }

            if (empty($payload)) {
                return ToolResult::error('NO_CHANGE', 'Keine Aenderungen uebergeben.');
            }

            $lesson = $service->update($lesson, $payload);

            return ToolResult::success([
                'id' => $lesson->id,
                'uuid' => $lesson->uuid,
                'title' => $lesson->title,
                'status' => $lesson->status,
                'message' => "Lesson '{$lesson->title}' aktualisiert.",
            ]);
        } catch (\Throwable $e) {
            return ToolResult::error('EXECUTION_ERROR', 'Fehler: ' . $e->getMessage());
        }
    }

    public function getMetadata(): array
    {
        return [
            'category' => 'action', 'tags' => ['academy', 'lessons', 'update'],
            'read_only' => false, 'requires_auth' => true, 'requires_team' => true,
            'risk_level' => 'write', 'idempotent' => false,
        ];
    }
}
