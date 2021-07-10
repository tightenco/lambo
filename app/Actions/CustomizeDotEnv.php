<?php

namespace App\Actions;

use App\ConsoleWriter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class CustomizeDotEnv
{
    protected $consoleWriter;

    public function __construct(ConsoleWriter $consoleWriter)
    {
        $this->consoleWriter = $consoleWriter;
    }

    public function __invoke()
    {
        $this->consoleWriter->logStep('Customizing .env and .env.example');

        $filePath = config('lambo.store.project_path') . '/.env.example';

        $output = $this->customize(File::get($filePath));

        File::put($filePath, $output);
        File::put(str_replace('.env.example', '.env', $filePath), $output);

        $this->consoleWriter->success('.env files configured.');
    }

    public function customize($contents): string
    {
        return collect(explode("\n", $contents))->transform(function ($item) {
            $parts = explode('=', $item, 2);

            // Line doesn't contain an equal sign (=); return without modification
            if (count($parts) < 2) {
                return $item;
            }

            [$envKey, $envVal] = $parts;

            $replace = $this->value($envKey, $envVal);

            return "{$envKey}={$replace}";
        })->implode("\n");
    }

    public function value($key, $fallback)
    {
        $replacements = [
            'APP_NAME' => config('lambo.store.project_name'),
            'APP_URL' => config('lambo.store.project_url'),
            'DB_PORT' => config('lambo.store.database_port'),
            'DB_DATABASE' => config('lambo.store.database_name'),
            'DB_USERNAME' => config('lambo.store.database_username'),
            'DB_PASSWORD' => config('lambo.store.database_password'),
        ];

        return Arr::get($replacements, $key, $fallback);
    }
}
