<?php

namespace App\Actions;

use App\Commands\Debug;
use App\ConsoleWriter;

class ValidateConfiguration
{
    use Debug;

    protected $consoleWriter;

    public function __construct(ConsoleWriter $consoleWriter)
    {
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $this->consoleWriter->logStep('Validating configuration');

        app(ValidateFrontendConfiguration::class)();
        app(ValidateGitHubConfiguration::class)();

        $this->consoleWriter->success('Configuration is valid.');

        if ($this->consoleWriter->isDebug()) {
            $this->debugReport();
        }
    }

    protected function debugReport(): void
    {
        $this->consoleWriter->panel('Debug', 'Start', 'fg=black;bg=white');

        $this->consoleWriter->text([
            'Configuration may have changed after validation',
            'Configuration is now as follows:',
        ]);
        $this->configToTable();

        $this->consoleWriter->panel('Debug', 'End', 'fg=black;bg=white');
    }
}
