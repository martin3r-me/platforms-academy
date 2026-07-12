# Academy Module

Lightweight learning platform for team-internal knowledge work.

## Concepts

- **Category** — Kurs-Kategorie / "School" (z.B. "AI & Automation", "Frontend & Engineering", "Value Stream"). Hat eine Signalfarbe (`color`) und ein `code_prefix`. Treibt Kurs-Cover, Chips und Code-Vorschläge.
- **Topic** — Themen-Cluster (z.B. "Prompting Basics", "Agentic Workflows", "Tool-Use") — Themen-Bibliothek im Hintergrund
- **Lesson** — Einzelne Lern-Einheit (Markdown-Content, `summary` = beschreibender Text) — gehört zu einem Topic
- **Path (= Kurs)** — Die abonnierbare Kurs-Einheit: kuratierte Reihenfolge von Lessons, mit `code` (z.B. "AI-101"), `level` (beginner/intermediate/advanced) und `category`. Lessons können in mehreren Paths auftauchen
- **Enrollment** — Pro User pro Path: bewusstes Einschreiben (`active` / `completed`), mit Resume-Punkt (`last_lesson_id`) für "Meine Academy"
- **Progress** — Pro User pro Lesson: `in_progress` / `completed`. Speist den Kurs-Fortschritt und die automatische Abschluss-Erkennung von Enrollments

## Cover-Design

Kurse haben **keine Bild-Uploads**. Das Cover ist typografisch: der Kurs-`code` groß in JetBrains Mono auf einem aus der Kategorie-`color` abgeleiteten Verlauf. Siehe `resources/views/partials/course-cover.blade.php` (Größen: `card` / `rail` / `hero`).

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
