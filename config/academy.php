<?php

return [
    'routing' => [
        'mode' => env('ACADEMY_MODE', 'path'),
        'prefix' => 'academy',
    ],

    'guard' => 'web',

    'navigation' => [
        'route' => 'academy.dashboard',
        'icon'  => 'heroicon-o-academic-cap',
        'order' => 95,
    ],

    'sidebar' => [
        [
            'group' => 'Übersicht',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'academy.dashboard',
                    'icon'  => 'heroicon-o-home',
                ],
                [
                    'label' => 'Kurse (geführt)',
                    'route' => 'academy.paths.index',
                    'icon'  => 'heroicon-o-rectangle-stack',
                ],
                [
                    'label' => 'Bibliothek (frei)',
                    'route' => 'academy.topics.index',
                    'icon'  => 'heroicon-o-book-open',
                ],
            ],
        ],
    ],
];
