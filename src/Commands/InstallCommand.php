<?php

namespace Idkwhoami\FluxFiles\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'flux-files:install';

    protected $description = 'Install the Flux Files package';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->call('flux:icon', [
            'icons' => [
                'image', 'video', 'music', 'file-text', 'folder-archive', 'file-question-mark', 'house', 'layout-grid', 'table', 'arrow-down-wide-narrow', 'arrow-up-narrow-wide'
            ]
        ]);

        $this->call('vendor:publish', ['--tag' => 'flux-files-install']);
        $this->warn('Before you run these migrations, make sure the id_type in the config is set to your preferred type.');
    }
}
