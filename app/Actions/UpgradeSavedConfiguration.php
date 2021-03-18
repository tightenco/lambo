<?php

namespace App\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UpgradeSavedConfiguration
{
    // IMPORTANT NOTE: Every time we make *any* changes to configuration, we need
    // to increment this configurationVersion so that users get upgraded config
    private $configurationVersion = 1;
    private $configDir;
    private $configFilePath;
    private $lastVersionUpdateFilePath;
    private $commented = [];
    private $removedConfigurationKeys = [
        'NODE',
        'MIX',
        'AUTH',
        'FRONTEND',
    ];
    private $newConfiguration = [
        'MIGRATE_DATABASE' => [
            'commented' => false,
            'default' => 'false',
            'description' => [
                'Run the standard Laravel database migrations.',
                'Possible values:',
                '  true, 1, "yes" or "on"',
                '  false (default), 0, "no" or "off"',
            ],
        ],
        'DB_HOST' => [
            'commented' => true,
            'default' => '127.0.0.1',
            'description' => [
                'The database host. Defaults to 127.0.0.1.',
            ]
        ],
        'DB_PORT' => [
            'commented' => true,
            'default' => '',
            'description' => [
                'The database port. Defaults to 3306.',
            ],
        ],
        'DB_NAME' => [
            'commented' => true,
            'default' => '',
            'description' => [
                'The database name. Defaults to the project name.',
            ],
        ],
    ];

    public function __construct()
    {
        $this->configDir = config('home_dir') . '/.lambo';
        $this->configFilePath = "{$this->configDir}/config";
        $this->lastVersionUpdateFilePath = "{$this->configDir}/.last_version_update";
    }

    public function __invoke(): bool
    {
        if (! $this->shouldUpgrade()) {
            return false;
        }

        $savedConfiguration = File::get($this->configFilePath);
        File::move($this->configFilePath, $this->configFilePath . '.' . Carbon::now()->toDateTimeLocalString());

        File::put($this->configFilePath, $this->upgrade($savedConfiguration, $this->removedConfigurationKeys, $this->newConfiguration));

        File::delete($this->lastVersionUpdateFilePath);
        File::put($this->lastVersionUpdateFilePath, $this->configurationVersion);

        return true;
    }

    private function shouldUpgrade(): bool
    {
        if (! File::isFile($this->configFilePath)) {
            return false;
        }

        $localVersion = File::isFile($this->lastVersionUpdateFilePath)
            ? (int)File::get($this->lastVersionUpdateFilePath)
            : 0;

        if ($localVersion < $this->configurationVersion) {
            return true;
        }

        return false;
    }

    public function upgrade(string $savedConfiguration, array $removedConfigurationKeys, array $newConfiguration = []): string
    {
        return implode(PHP_EOL, [
            $this->commentRemovedConfiguration($savedConfiguration, $removedConfigurationKeys),
            "\n# ------------------------------------------------------------------------------",
            '# ' . Carbon::now()->format('j-M-Y g:i a') . ' (auto-generated by Lambo):',
            '# ------------------------------------------------------------------------------',
            $this->summarizeComments(),
            $this->addNewConfiguration($newConfiguration),
        ]);
    }

    private function commentRemovedConfiguration(string $savedConfiguration, array $oldConfigurationKeys): string
    {
        return collect(explode("\n", $savedConfiguration))->transform(function ($item) use ($oldConfigurationKeys) {
            $matched = collect($oldConfigurationKeys)->reduce(function ($carry, $oldKey) use ($item) {
                return $carry || Str::of($item)->startsWith($oldKey);
            }, false);
            if ($matched) {
                $this->commented[] = $item;
                return "#{$item}";
            }
            return $item;
        })->implode("\n");
    }

    private function summarizeComments(): string
    {
        if (count($this->commented) < 1) {
            return '';
        }

        return implode(PHP_EOL, [
            '# Lambo has commented out the following configuration items as they',
            '# are no-longer used. You may safely remove them:',
            collect($this->commented)->reduce(function ($carry, $item) {
                return "$carry#   {$item}\n";
            }, '')
        ]);
    }

    private function addNewConfiguration(array $newConfiguration): string
    {
        if (count($newConfiguration) < 1) {
            return '';
        }

        return collect(array_keys($newConfiguration))->reduce(function ($carry, $key) use ($newConfiguration) {
            $description = collect($newConfiguration[$key]['description'])->reduce(function ($carry, $item) {
                return "$carry# {$item}\n";
            }, '');

            $configurationItem = sprintf(
                "%s%s=%s\n\n",
                $newConfiguration[$key]['commented'] ? '#' : '',
                $key,
                $newConfiguration[$key]['default']
            );

            return "{$carry}{$description}{$configurationItem}";
        }, "# Lambo has introduced new configuration options. They have been added here\n# with sensible defaults; however, you should review them.\n#\n");
    }
}
