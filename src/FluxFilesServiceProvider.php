<?php

namespace Idkwhoami\FluxFiles;

use Illuminate\Support\ServiceProvider;

class FluxFilesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'flux-files');

        $this->loadViewComponentsAs('flux-files', [
            //
        ]);
    }

    /**
     * @return void
     */
    private function prepareCommands(): void
    {
        $this->commands([
            //
        ]);
    }

    /**
     * @return void
     */
    private function prepareConfig(): void
    {
        $this->publishes([
            __DIR__.'/../config/flux-files.php' => config_path('flux-files.php'),
        ], [
            'flux-files-config',
            'flux-files'
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/flux-files.php',
            'flux-files'
        );
    }

    /**
     * @return void
     */
    public function prepareLocalization(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'flux-files');

        $this->publishes([
            __DIR__.'/../lang' => lang_path('vendor/flux-files'),
        ], [
            'flux-files-lang',
            'flux-files'
        ]);
    }

}
