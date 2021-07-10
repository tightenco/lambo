<?php

namespace App\Actions;

use App\Actions\Concerns\InteractsWithGitHub;
use App\ConsoleWriter;
use App\Shell;

class InitializeGitHubRepository
{
    use AbortsCommands;
    use InteractsWithGitHub;

    public const WARNING_FAILED_TO_CREATE_REPOSITORY = 'Failed to create new GitHub repository';

    protected $shell;
    protected $consoleWriter;

    public function __construct(Shell $shell, ConsoleWriter $consoleWriter)
    {
        $this->shell = $shell;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        if (! $this->gitHubInitializationRequested()) {
            return;
        }

        $this->consoleWriter->logStep('Initializing GitHub repository');

        $process = $this->shell->execInProject($this->getCommand());

        if (! $process->isSuccessful()) {
            $this->consoleWriter->warn(self::WARNING_FAILED_TO_CREATE_REPOSITORY);
            $this->consoleWriter->warnCommandFailed($process->getCommandLine());
            $this->consoleWriter->showOutputErrors($process->getErrorOutput());
            config(['lambo.store.push_to_github' => false]);

            return;
        }
        config(['lambo.store.push_to_github' => true]);

        $this->consoleWriter->success('Successfully created new GitHub repository');
    }
}
