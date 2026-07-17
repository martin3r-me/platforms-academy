<?php

use Platform\Academy\Livewire\Dashboard;
use Platform\Academy\Livewire\Topic\Index as TopicIndex;
use Platform\Academy\Livewire\Topic\Show as TopicShow;
use Platform\Academy\Livewire\Lesson\Show as LessonShow;
use Platform\Academy\Livewire\Path\Index as PathIndex;
use Platform\Academy\Livewire\Path\Show as PathShow;
use Platform\Academy\Livewire\Certificate\Show as CertificateShow;
use Platform\Academy\Livewire\Assignment\Index as AssignmentIndex;

Route::get('/', Dashboard::class)->name('academy.dashboard');

Route::get('/topics', TopicIndex::class)->name('academy.topics.index');
Route::get('/topics/{uuid}', TopicShow::class)->name('academy.topics.show');

Route::get('/paths', PathIndex::class)->name('academy.paths.index');
Route::get('/paths/{uuid}', PathShow::class)->name('academy.paths.show');

Route::get('/lessons/{uuid}', LessonShow::class)->name('academy.lessons.show');

Route::get('/assignments', AssignmentIndex::class)->name('academy.assignments.index');

Route::get('/certificates/{uuid}', CertificateShow::class)->name('academy.certificates.show');
