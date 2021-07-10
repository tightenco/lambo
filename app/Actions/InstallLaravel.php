<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;

class InstallLaravel
{
    use AbortsCommands;

    protected $shell;
    protected $consoleWriter;

    public function __construct(Shell $shell, ConsoleWriter $consoleWriter)
    {
        $this->shell = $shell;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $this->consoleWriter->logStep('Creating the new Laravel project');

        $process = $this->shell->execInRoot(sprintf(
            'composer create-project laravel/laravel %s%s --remove-vcs --prefer-dist %s',
            config('lambo.store.project_name'),
            config('lambo.store.dev') ? ' dev-master' : '',
            config('lambo.store.with_output') ? '' : '--quiet'
        ));

        $this->abortIf(! $process->isSuccessful(), 'The laravel installer did not complete successfully.', $process);

        $this->consoleWriter->success(sprintf(
            "A new application '%s' has been created from the %s branch.",
            config('lambo.store.project_name'),
            config('lambo.store.dev') ? 'develop' : 'release'
        ));
    }
}
