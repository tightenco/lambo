<?php

namespace App\Actions;

use App\Options;

class DisplayHelpScreen
{
    public function __invoke()
    {
        app('console-writer')->text("\n <comment>Usage:</comment>{$this->createCliStringForCommandUsage()}");
        app('console-writer')->text("\n <comment>Options (lambo new myApplication):</comment>{$this->createCliStringForOptionDescriptions()}");
    }

    public function createCliStringForOptionDescriptions(): string
    {
        return collect((new Options())->all())->reduce(function ($carry, $option) {
            $flag = isset($option['short'])
                ? '-' . $option['short'] . ', --' . $option['long']
                : '    --' . $option['long'];

            $flag .= isset($option['param_description'])
                ? "={$option['param_description']}"
                : '';

            return $carry . sprintf("\n   <info>%-33s</info>%s", $flag, $option['cli_description']);
        });
    }

    private function createCliStringForCommandUsage(): string
    {
        return collect([
            [
                'usage' => 'lambo edit-config [--editor=<editor>]',
                'description' => 'Create or edit your <comment>~/.lambo/config</comment> file',
            ],
            [
                'usage' => 'lambo edit-after [--editor=<editor>]',
                'description' => 'Create or edit your <comment>~/.lambo/after</comment> file',
            ],
            [
                'usage' => 'lambo new myApplication [options]',
                'description' => 'Scaffold a new Laravel application',
            ],
        ])->reduce(function ($carry, $command) {
                return $carry . sprintf("\n   <info>%-40s</info> %s", $command['usage'], $command['description']);
        });
    }
}
