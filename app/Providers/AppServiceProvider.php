<?php

namespace App\Providers;

use App\Http\Integrations\Composer\Composer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use NietThijmen\ComposerChangelog\Contracts\FileSystemDiffer as FileSystemDifferContract;
use NietThijmen\ComposerChangelog\Contracts\PackageDownloader as PackageDownloaderContract;
use NietThijmen\ComposerChangelog\Diff\FileSystemDiffer;
use NietThijmen\ComposerChangelog\Downloader\PackageDownloader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PackageDownloaderContract::class, fn () => new PackageDownloader(new Composer));
        $this->app->singleton(FileSystemDifferContract::class, FileSystemDiffer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
