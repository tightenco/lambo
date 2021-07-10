<?php

namespace App;

class Options
{
    protected $options = [
        /** Parameters first, then flags */
        [
            'short' => 'e',
            'long' => 'editor',
            'param_description' => 'EDITOR',
            'cli_description' => "Specify an editor to run <info>'EDITOR .'</info> with after",
        ],
        [
            'short' => 'p',
            'long' => 'path',
            'param_description' => 'PATH',
            'cli_description' => 'Customize the path in which the new project will be created',
        ],
        [
            'short' => 'm',
            'long' => 'message',
            'param_description' => 'MESSAGE',
            'cli_description' => 'Customize the initial commit message (wrap with quotes!)',
        ],
        [
            'short' => 'B',
            'long' => 'branch',
            'param_description' => 'BRANCH',
            'cli_description' => 'The branch that should be created for a new repository <comment>[default: "main"]</comment>',
        ],
        [
            'short' => 'g',
            'long' => 'github',
            'cli_description' => 'Initialize a new private GitHub repository',
        ],
        [
            'long' => 'gh-public',
            'cli_description' => 'Make the new GitHub repository public',
        ],
        [
            'long' => 'gh-description',
            'param_description' => 'DESCRIPTION',
            'cli_description' => 'Initialize the new GitHub repository with the provided <info>DESCRIPTION</info>',
        ],
        [
            'long' => 'gh-homepage',
            'param_description' => 'URL',
            'cli_description' => 'Initialize the new GitHub repository with the provided homepage <info>URL</info>',
        ],
        [
            'long' => 'gh-org',
            'param_description' => 'ORG',
            'cli_description' => 'Initialize the new GitHub repository for <info>ORG</info>/project',
        ],
        [
            'short' => 'b',
            'long' => 'browser',
            'param_description' => 'BROWSER',
            'cli_description' => 'Open the site in the specified <info>BROWSER</info>. E.g. <info>firefox</info>',
        ],
        [
            'long' => 'dbhost',
            'param_description' => 'HOST',
            'cli_description' => 'Specify the database <info>HOST</info>',
        ],
        [
            'long' => 'dbport',
            'param_description' => 'PORT',
            'cli_description' => 'Specify the database <info>PORT</info>',
        ],
        [
            'long' => 'dbname',
            'param_description' => 'NAME',
            'cli_description' => 'Specify the database <info>NAME</info>',
        ],
        [
            'long' => 'dbuser',
            'param_description' => 'USERNAME',
            'cli_description' => 'Specify the database <info>USERNAME</info>',
        ],
        [
            'long' => 'dbpassword',
            'param_description' => 'PASSWORD',
            'cli_description' => 'Specify the database <info>PASSWORD</info>',
        ],
        [
            'long' => 'create-db',
            'cli_description' => 'Create a new MySQL database',
        ],
        [
            'short' => 'f',
            'long' => 'force',
            'cli_description' => 'Force install even if the directory already exists',
        ],
        [
            'long' => 'migrate-db',
            'cli_description' => 'Run database migrations',
        ],
        [
            'long' => 'inertia',
            'cli_description' => 'Use inertia frontend scaffolding',
        ],
        [
            'long' => 'livewire',
            'cli_description' => 'Use livewire frontend scaffolding',
        ],
        [
            'long' => 'teams',
            'cli_description' => 'Use team features with inertia or livewire',
        ],
        [
            'short' => 'l',
            'long' => 'link',
            'cli_description' => 'Create a Valet link to the project directory',
        ],
        [
            'short' => 's',
            'long' => 'secure',
            'cli_description' => 'Generate and use an HTTPS cert with Valet',
        ],
        [
            'short' => 'd',
            'long' => 'dev',
            'cli_description' => 'Install Laravel using the develop branch',
        ],
        [
            'long' => 'full',
            'cli_description' => 'Shortcut of --create-db --migrate-db --link --secure',
        ],
        [
            'short' => 'q',
            'long' => 'quiet',
            'cli_description' => 'Do not output to the console (except for user input)',
        ],
    ];

    public function all(): array
    {
        return $this->options;
    }
}
