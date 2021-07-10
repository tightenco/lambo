<?php

namespace App\Actions;

use App\ConsoleWriter;
use App\Shell;

class CompileAssets
{
    use AbortsCommands;

    protected $shell;
    protected $npm;
    protected $consoleWriter;

    public function __construct(Shell $shell, SilenceNpm $silenceNpm, ConsoleWriter $consoleWriter)
    {
        $this->shell = $shell;
        $this->npm = $silenceNpm;
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $this->consoleWriter->logStep('Compiling project assets');

        $this->npm->silence();
        $process = $this->shell->execInProject("npm run dev{$this->extraOptions()}");
        $this->abortIf(! $process->isSuccessful(), 'Compilation of project assets did not complete successfully', $process);
        $this->npm->unsilence();

        $this->consoleWriter->success('Project assets compiled successfully.');
    }

    public function extraOptions(): string
    {
        return config('lambo.store.with_output') ? '' : ' --silent';
    }
}
