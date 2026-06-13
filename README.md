# Academy Module

Lightweight learning platform for team-internal knowledge work.

## Concepts

- **Topic** — Themen-Cluster (z.B. "Prompting Basics", "Agentic Workflows", "Tool-Use")
- **Lesson** — Einzelne Lern-Einheit (Markdown-Content) — gehört zu einem Topic
- **Path** — Kuratierte Reihenfolge von Lessons (z.B. "Onboarding für Sales", "Dev-Path") — Lessons können in mehreren Paths auftauchen
- **Progress** — Pro User pro Lesson: `in_progress` / `completed`

## Architecture

Folgt dem Platform-Modul-Pattern:
- `src/Models/` — Eloquent Models mit UuidV7
- `src/Services/` — Business Logic, dünne Livewire Components
- `src/Livewire/` — Read-Views + Mark-Complete
- Team-scoped via `team_id` + `created_by_user_id`
- Markdown-Content, sauber entkoppelt von der UI

## Namespace
- PHP: `Platform\Academy\...`
- Views: `academy::livewire.xxx`
- Routes: `academy.xxx`
