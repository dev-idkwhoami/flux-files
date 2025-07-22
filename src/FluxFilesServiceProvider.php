<?php

namespace Idkwhoami\FluxFiles;

use Idkwhoami\FluxFiles\Commands\InstallCommand;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class FluxFilesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBlueprintMacros();
    }

    public function boot(): void
    {
        $this->prepareConfig();
        $this->prepareLocalization();
        $this->prepareCommands();
        $this->prepareModels();

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
            InstallCommand::class
        ]);
    }

    /**
     * @return void
     */
    private function prepareModels(): void
    {
        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], [
            'flux-files-install',
            'flux-files-migrations',
            'flux-files'
        ]);

        $this->publishes([
            __DIR__.'/App/Models' => app_path('Models'),
        ], [
            'flux-files-install',
            'flux-files-models',
            'flux-files'
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

    /**
     * Register Blueprint macros for dynamic ID columns
     */
    private function registerBlueprintMacros(): void
    {
        Blueprint::macro('fluxFilesId', function (string $column = 'id') {
            $idType = config('flux-files.id_type', 'bigint');

            return match($idType) {
                'ulid' => $this->ulid($column)->primary(),
                'uuid' => $this->uuid($column)->primary(),
                default => $this->id($column),
            };
        });

        Blueprint::macro('fluxFilesForeignId', function (string $column) {
            $idType = config('flux-files.id_type', 'bigint');

            return match($idType) {
                'ulid' => $this->ulid($column),
                'uuid' => $this->uuid($column),
                default => $this->unsignedBigInteger($column),
            };
        });

        Blueprint::macro('nullableFluxFilesForeignId', function (string $column) {
            $idType = config('flux-files.id_type', 'bigint');

            return match($idType) {
                'ulid' => $this->ulid($column)->nullable(),
                'uuid' => $this->uuid($column)->nullable(),
                default => $this->unsignedBigInteger($column)->nullable(),
            };
        });
    }


}
