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
        $this->call('vendor:publish', ['--tag' => 'flux-files-install']);

    }
}
