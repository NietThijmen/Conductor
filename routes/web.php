<?php

use App\Livewire\Changelogs;
use App\Livewire\Packages;
use App\Livewire\Registries;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::get('registries', Registries::class)->name('registries');
    Route::get('packages', Packages::class)->name('packages');
    Route::get('changelogs', Changelogs::class)->name('changelogs');
});

require __DIR__.'/settings.php';
