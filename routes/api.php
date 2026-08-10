<?php

use Illuminate\Support\Facades\Route;
use Platform\Academy\Http\Controllers\Api\CourseController;

/**
 * Academy API Routes
 *
 * Prefix `/api/academy`, Middleware `['api', 'api.auth']` (Bearer-Token via Passport)
 * werden von ModuleRouter::apiGroup('academy', ...) im ServiceProvider gesetzt.
 *
 * Kurskatalog für die öffentliche Website. Team ergibt sich aus dem Token-User.
 */
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/health', [CourseController::class, 'health']);
Route::get('/courses/{uuid}', [CourseController::class, 'show']);
