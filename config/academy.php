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
                    'label' => 'Kurse',
                    'route' => 'academy.paths.index',
                    'icon'  => 'heroicon-o-rectangle-stack',
                ],
                [
                    'label' => 'Themen',
                    'route' => 'academy.topics.index',
                    'icon'  => 'heroicon-o-squares-2x2',
                ],
            ],
        ],
    ],
];
