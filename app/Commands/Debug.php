<?php

namespace App\Commands;

use App\ConsoleWriter;
use Carbon\Carbon;
use Dotenv\Dotenv;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use IntlTimeZone;

trait Debug
{
    protected function arrayToTable(array $data, array $filter = null, string $keyPrefix = '', array $headers = null): void
    {
        if (empty($data)) {
            return;
        }

        $rows = collect($data)
            ->filter(function ($value, $key) use ($filter) {
                return is_null($filter) || in_array($key, $filter);
            })
            ->map(function ($value, $key) use ($keyPrefix) {
                $type = gettype($value);

                if (is_string($value)) {
                    $value =  empty($value)
                        ? ConsoleWriter::formatString('""', ConsoleWriter::BLUE)
                        : ConsoleWriter::formatString($value, ConsoleWriter::BLUE);
                }

                if (is_integer($value)) {
                    $value =  ConsoleWriter::formatString($value, ConsoleWriter::MAGENTA);
                }

                if (is_bool($value)) {
                    $value =  $value
                        ? ConsoleWriter::formatString('true', ConsoleWriter::GREEN)
                        : ConsoleWriter::formatString('false', ConsoleWriter::RED);
                }

                if (is_null($value)) {
                    $value = '';
                }

                return [$keyPrefix . $key, "({$type})", $value];
            })->values()->toArray();

        $this->consoleWriter->table($headers ?: ['key', 'type', 'value'], $rows);
    }

    protected function debugReport(): void
    {
        $this->consoleWriter->panel('Debug', 'Start', 'fg=black;bg=white');

        $this->consoleWriter->sectionTitle('Computed configuration');
        $this->consoleWriter->text([
            'The following is the configuration lambo has computed by merging:',
        ]);
        $this->consoleWriter->listing([
            'command line parameters',
            'saved configuration',
            'shell environment variables.',
        ]);

        $this->configToTable();

        $this->consoleWriter->sectionTitle('Pre-flight Configuration');

        $this->consoleWriter->text('Command line arguments:');
        $this->arrayToTable($this->arguments());

        $this->consoleWriter->text('Command line options:');
        $this->arrayToTable(
            $this->options(),
            [
                'editor',
                'message',
                'path',
                'browser',
                'frontend',
                'dbport',
                'dbname',
                'dbuser',
                'dbpassword',
                'create-db',
                'link',
                'secure',
                'quiet',
                'with-output',
                'dev',
                'full',
                'inertia',
                'livewire',
                'with-teams',
                'projectName',
            ],
            '--'
        );

        $this->consoleWriter->text('Saved configuration:');

        $savedConfig = [];
        if (File::isFile(config('home_dir') . '/.lambo/config')) {
            $savedConfig = Dotenv::createMutable(config('home_dir') . '/.lambo', 'config')->load();
        }
        $this->arrayToTable(
            $savedConfig,
            [
                'CODEEDITOR',
                'MESSAGE',
                'PROJECTPATH',
                'BROWSER',
                'FRONTEND',
                'DB_PORT',
                'DB_NAME',
                'DB_USERNAME',
                'DB_PASSWORD',
                'CREATE_DATABASE',
                'LINK',
                'SECURE',
                'QUIET',
                'WITH_OUTPUT',
                'DEVELOP',
                'FULL',
                'WITH_TEAMS',
            ]
        );

        $this->consoleWriter->text('Shell environment variables:');
        $this->arrayToTable($_SERVER, ['EDITOR'], '$');

        $this->logTimezoneData();

        $this->consoleWriter->panel('Debug', 'End', 'fg=black;bg=white');
    }

    protected function configToTable(): void
    {
        $config = Arr::prepend(config('lambo.store'), config('home_dir'), 'home_dir');
        $this->arrayToTable($this->dotFlatten('lambo.store', $config));
    }

    private function dotFlatten($prefix, $array): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->dotFlatten($prefix . '.' . $key, $value));
            } else {
                $result[$prefix . '.' . $key] = $value;
            }
        }
        return $result;
    }

    protected function logTimezoneData()
    {
        $this->consoleWriter->sectionTitle('Timezone configuration');
        $this->consoleWriter->newLine();
        $this->consoleWriter->text('System settings');
        $this->arrayToTable([
            'OS Config ("/etc/localtime")' => exec('/bin/ls -l /etc/localtime|/usr/bin/cut -d"/" -f8-'),
            "ini_get('date.timezone')" => ini_get('date.timezone') ?: 'Not configured',
            'IntlTimeZone::createDefault()' => IntlTimeZone::createDefault()->getID(),
            'date_default_timezone_get()' => date_default_timezone_get(),
            'config->get("app.timezone")' => config()->get('app.timezone'),
        ]);

        $this->consoleWriter->text('Carbon');
        $this->arrayToTable([
            // UTC, GMT, Atlantic/Azores
            'Carbon (Timezone identifier)' => Carbon::now()->format('e'),

            // 1 if Daylight Saving Time, 0 otherwise.
            'Carbon (Daylight savings)' => (bool)Carbon::now()->format('I'),

            // Difference to Greenwich time (GMT)
            'Carbon (Difference to GMT w/O)' => Carbon::now()->format('O'), // +0200'Carbon (Difference to GMT w/P)' => Carbon::now()->format('P'), // +02:00

            // Examples: EST, MDT
            'Carbon (tz abbreviation)' => Carbon::now()->format('T'),
        ]);
    }
}
