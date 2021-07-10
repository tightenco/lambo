<?php

namespace App\Actions;

use App\ConsoleWriter;

class ValidateFrontendConfiguration
{
    private $consoleWriter;

    public function __construct(ConsoleWriter $consoleWriter)
    {
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        config(['lambo.store.frontend' => $this->getFrontendConfiguration()]);

        $this->checkTeamsConfiguration();
    }

    private function getFrontendConfiguration(): string
    {
        $inertia = config('lambo.store.inertia');
        $livewire = config('lambo.store.livewire');

        if ($inertia && $livewire) {
            return $this->chooseBetweenFrontends();
        }

        if (! $inertia && ! $livewire) {
            return 'none';
        }

        return $inertia ? 'inertia' : 'livewire';
    }

    private function chooseBetweenFrontends(): string
    {
        $this->consoleWriter->warn('Inertia and Livewire cannot be used together. ');

        $options = [
            'Use Inertia' => 'inertia',
            'Use Livewire' => 'livewire',
            'Continue without frontend scaffolding' => 'none',
        ];
        $choice = app('console')->choice('How would you like to proceed?', array_keys($options), 2);

        $this->consoleWriter->ok($choice);

        return $options[$choice];
    }

    private function checkTeamsConfiguration(): void
    {
        if ((config('lambo.store.frontend') === 'none') && config('lambo.store.teams')) {
            $this->consoleWriter->note('You specified --teams but neither inertia or livewire are being used. Skipping...');
        }
    }
}
