<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;

class InitializeGitRepository
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
        $this->consoleWriter->logStep('Initializing git repository');

        $this->exec(sprintf(
            'git init%s%s',
            config('lambo.store.with_output') ? '' : ' --quiet',
            $this->getBranchOption()
        ));

        $this->exec('git add .');

        $this->exec(sprintf(
            "git commit%s -m '%s'",
            config('lambo.store.with_output') ? '' : ' --quiet',
            config('lambo.store.commit_message')
        ));

        $this->consoleWriter->success('New git repository initialized.');
    }

    public function exec($command)
    {
        $process = $this->shell->execInProject($command);
        $this->abortIf(! $process->isSuccessful(), 'Initialization of git repository did not complete successfully.', $process);
    }

    private function getBranchOption(): string
    {
        return config('lambo.store.branch') ? ' --initial-branch=' . config('lambo.store.branch') : '';
    }
}
