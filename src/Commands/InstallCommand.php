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
                'image',
                'video',
                'music',
                'file-text',
                'folder-archive',
                'file-question-mark',
                'trash-2',
                'pencil',
                'move',
                'house',
                'layout-grid',
                'table',
                'arrow-down-wide-narrow',
                'arrow-up-narrow-wide',
                'settings-2',
                'ellipsis-vertical',
                'triangle-alert',
                'upload'
            ]
        ]);

        $this->call('vendor:publish', ['--tag' => 'flux-files-install']);
        $this->warn('⚠️ Before you run these migrations, make sure the id_type in the config is set to your preferred type.');

        $this->info('⚠️ Please add the following into your app.css:');
        $this->comment('⚠️ @import "../../vendor/idkwhoami/flux-files/dist/flux-files.css";');
    }
}
