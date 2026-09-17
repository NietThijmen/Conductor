<?php

use App\Http\Controllers\Api\ChangelogController;
use App\Http\Controllers\Api\PackageController;
use Illuminate\Support\Facades\Route;

Route::get('/packages', [PackageController::class, 'index'])->name('api.packages.index');
Route::get('/packages/{vendor}/{name}', [PackageController::class, 'show'])->name('api.packages.show');

Route::get('/packages/{vendor}/{name}/changelogs', [ChangelogController::class, 'index'])->name('api.packages.changelogs.index');
Route::get('/packages/{vendor}/{name}/changelogs/{new_version}', [ChangelogController::class, 'show'])->name('api.packages.changelogs.show');
