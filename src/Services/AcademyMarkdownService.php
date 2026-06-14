<?php

namespace Platform\Academy\Services;

use Illuminate\Support\Str;

class AcademyMarkdownService
{
    /**
     * Render Markdown to HTML with academy enhancements.
     */
    public function render(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        $html = (string) Str::of($markdown)->markdown();
        $html = $this->transformAlerts($html);

        return $html;
    }

    /**
     * Convert GitHub-style alerts in rendered HTML to styled callout boxes.
     *
     * Markdown:
     *   > [!INFO]
     *   > Some info text
     *
     * Supported: INFO, TIP, WARNING, NOTE, IMPORTANT, CAUTION
     */
    protected function transformAlerts(string $html): string
    {
        $pattern = '/<blockquote>\s*<p>\[!(INFO|TIP|WARNING|NOTE|IMPORTANT|CAUTION)\](?:\s*<br\s*\/?>)?\s*([\s\S]*?)<\/p>\s*<\/blockquote>/i';

        return preg_replace_callback($pattern, function ($match) {
            $type = strtolower($match[1]);
            $content = trim($match[2]);
            return $this->renderAlert($type, $content);
        }, $html);
    }

    protected function renderAlert(string $type, string $content): string
    {
        $config = $this->alertConfig()[$type] ?? $this->alertConfig()['note'];

        return sprintf(
            '<div class="academy-alert academy-alert-%s">'
            . '<div class="academy-alert-label">%s<span>%s</span></div>'
            . '<div class="academy-alert-body">%s</div>'
            . '</div>',
            e($type),
            $config['icon'],
            e($config['label']),
            $content
        );
    }

    protected function alertConfig(): array
    {
        $icon = fn (string $svg) => '<svg class="academy-alert-icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">' . $svg . '</svg>';

        return [
            'info' => [
                'label' => 'Info',
                'icon' => $icon('<path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM9 9a1 1 0 0 0 0 2v3a1 1 0 0 0 1 1h1a1 1 0 1 0 0-2v-3a1 1 0 0 0-1-1H9Z" clip-rule="evenodd" />'),
            ],
            'tip' => [
                'label' => 'Tipp',
                'icon' => $icon('<path d="M11 3a1 1 0 1 0-2 0v1a1 1 0 1 0 2 0V3ZM15.657 5.757a1 1 0 0 0-1.414-1.414l-.707.707a1 1 0 0 0 1.414 1.414l.707-.707ZM18 10a1 1 0 0 1-1 1h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1ZM5.05 6.464A1 1 0 1 0 6.464 5.05l-.707-.707a1 1 0 0 0-1.414 1.414l.707.707ZM5 10a1 1 0 0 1-1 1H3a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1ZM8 16v-1h4v1a2 2 0 1 1-4 0ZM12 14c.015-.34.208-.646.477-.859a4 4 0 1 0-4.954 0c.27.213.462.519.476.859h4.002Z" />'),
            ],
            'warning' => [
                'label' => 'Warnung',
                'icon' => $icon('<path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />'),
            ],
            'note' => [
                'label' => 'Notiz',
                'icon' => $icon('<path d="M3.5 2.75a.75.75 0 0 0-1.5 0v14.5a.75.75 0 0 0 1.5 0v-4.392l1.657-.348a6.449 6.449 0 0 1 4.271.572 7.948 7.948 0 0 0 5.965.524l2.078-.64A.75.75 0 0 0 18 12.25v-8.5a.75.75 0 0 0-.904-.734l-2.38.501a7.25 7.25 0 0 1-4.186-.363l-.502-.2a8.75 8.75 0 0 0-5.053-.439L3.5 2.879V2.75Z" />'),
            ],
            'important' => [
                'label' => 'Wichtig',
                'icon' => $icon('<path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-5a1 1 0 0 0-2 0v6a1 1 0 1 0 2 0V5Zm-1 9a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z" clip-rule="evenodd" />'),
            ],
            'caution' => [
                'label' => 'Vorsicht',
                'icon' => $icon('<path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z" clip-rule="evenodd" />'),
            ],
        ];
    }
}
