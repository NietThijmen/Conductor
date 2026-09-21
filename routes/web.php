<?php

use App\Livewire\Changelogs;
use App\Livewire\Packages;
use App\Livewire\Public\ChangelogDetail;
use App\Livewire\Public\PackageDetail;
use App\Livewire\Public\Search;
use App\Livewire\Registries;
use Illuminate\Support\Facades\Route;

Route::get('/', Search::class)->name('home');

Route::get('/packages/{vendor}/{name}', PackageDetail::class)->name('packages.show');
Route::get('/packages/{vendor}/{name}/{new_version}', ChangelogDetail::class)->name('changelogs.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('registries', Registries::class)->name('registries');
    Route::get('packages', Packages::class)->name('packages');
    Route::get('changelogs', Changelogs::class)->name('changelogs');
});

require __DIR__.'/settings.php';
