<?php

namespace App\Configuration;

use Illuminate\Support\Str;

abstract class LamboConfiguration
{
    public const EDITOR = 'editor';
    public const PROJECT_NAME = 'project_name';
    public const ROOT_PATH = 'root_path';
    public const WITH_OUTPUT = 'with_output';
    public const USE_DEVELOP_BRANCH = 'dev';
    public const CREATE_DATABASE = 'create_database';
    public const FORCE_CREATE = 'force_create';
    public const MIGRATE_DATABASE = 'migrate_database';
    public const DATABASE_HOST = 'database_host';
    public const DATABASE_PORT = 'database_port';
    public const DATABASE_NAME = 'database_name';
    public const DATABASE_USERNAME = 'database_username';
    public const DATABASE_PASSWORD = 'database_password';
    public const FRONTEND_FRAMEWORK = 'frontend';
    public const FULL = 'full';
    public const TLD = 'tld';
    public const COMMIT_MESSAGE = 'commit_message';
    public const BRANCH = 'branch';
    public const VALET_LINK = 'valet_link';
    public const VALET_SECURE = 'valet_secure';
    public const BROWSER = 'browser';
    public const INERTIA = 'inertia';
    public const LIVEWIRE = 'livewire';
    public const TEAMS = 'teams';
    public const INITIALIZE_GITHUB = 'initialize_github';
    public const GITHUB_PUBLIC = 'github_public';
    public const GITHUB_DESCRIPTION = 'github_description';
    public const GITHUB_HOMEPAGE = 'github_homepage';
    public const GITHUB_ORGANIZATION = 'github_organization';
    public const COMMAND = 'command';

    public function __construct(array $keyMap)
    {
        $settings = $this->getSettings();

        collect($keyMap)->each(function ($item, $key) use ($settings) {
            $this->$item = $this->get($key, $settings);
        });
    }

    abstract protected function getSettings(): array;

    protected function get(string $key, array $array)
    {
        if (array_key_exists($key, $array)) {
            if ($array[$key] === '') {
                return null;
            }

            if (in_array(Str::lower($array[$key]), ['1', 'true', 'on', 'yes'])) {
                return true;
            }

            if (in_array(Str::lower($array[$key]), ['0', 'false', 'off', 'no'])) {
                return false;
            }

            return $array[$key];
        }

        return null;
    }

    public function __get($name)
    {
        return null;
    }
}
