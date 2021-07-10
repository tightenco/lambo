<?php

namespace App;

use Illuminate\Contracts\Config\Repository;
use Symfony\Component\Process\Process;

class Shell
{
    protected $rootPath;
    protected $projectPath;
    protected $consoleWriter;

    private $useTTY = false;

    public function __construct(Repository $config, ConsoleWriter $consoleWriter)
    {
        $this->rootPath = $config->get('lambo.store.root_path');
        $this->projectPath = $config->get('lambo.store.project_path');
        $this->consoleWriter = $consoleWriter;
    }

    public function execInRoot($command)
    {
        return $this->exec("cd {$this->rootPath} && $command");
    }

    public function execInProject($command)
    {
        return $this->exec("cd {$this->projectPath} && $command");
    }

    public function execIn(string $directory, string $command)
    {
        return $this->exec("cd {$directory} && $command");
    }

    public function exec(string $command)
    {
        $process = Process::fromShellCommandline($command)
            ->setTty($this->useTTY)
            ->setTimeout(null)
            ->enableOutput();

        $this->consoleWriter->exec($command);
        $this->consoleWriter->newLine();

        $process->run(function ($type, $buffer) {
            if (empty(trim($buffer)) || $buffer === PHP_EOL) {
                return;
            }
            foreach (explode(PHP_EOL, trim($buffer)) as $line) {
                $this->consoleWriter->consoleOutput($line, $type);
            }
        });

        $this->consoleWriter->newLine();

        $this->useTTY = false;
        return $process;
    }

    public function execQuietly(string $command)
    {
        $process =  Process::fromShellCommandline($command)
            ->setTimeout(null)
            ->enableOutput();

        $process->run();

        return $process;
    }

    public function withTTY()
    {
        $this->useTTY = true;
        return $this;
    }
}
