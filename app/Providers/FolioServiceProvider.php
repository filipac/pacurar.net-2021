<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Folio\Folio;
use LaraWelP\Foundation\Events\WhenFolioRegisters;

class FolioServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Eager mounting replaces LaraWelP's catch-all route and bypasses WordPress pages.
        WhenFolioRegisters::provide(function () {
            Folio::path(resource_path('views/pages'))->middleware([
                '*' => [
                    //
                ],
            ]);
        });
    }
}
