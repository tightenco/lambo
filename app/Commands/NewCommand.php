<?php

namespace App\Commands;

use App\Actions\ConfigureFrontendFramework;
use App\Actions\CreateDatabase;
use App\Actions\CustomizeDotEnv;
use App\Actions\DisplayHelpScreen;
use App\Actions\DisplayLamboWelcome;
use App\Actions\EditConfigFile;
use App\Actions\GenerateAppKey;
use App\Actions\InitializeGitHubRepository;
use App\Actions\InitializeGitRepository;
use App\Actions\InstallLaravel;
use App\Actions\MigrateDatabase;
use App\Actions\OpenInBrowser;
use App\Actions\OpenInEditor;
use App\Actions\PushToGitHub;
use App\Actions\RunAfterScript;
use App\Actions\UpgradeSavedConfiguration;
use App\Actions\ValetLink;
use App\Actions\ValetSecure;
use App\Actions\ValidateConfiguration;
use App\Actions\VerifyDependencies;
use App\Actions\VerifyPathAvailable;
use App\Configuration\CommandLineConfiguration;
use App\Configuration\LamboConfiguration;
use App\Configuration\SavedConfiguration;
use App\Configuration\SetConfig;
use App\Configuration\ShellConfiguration;
use App\ConsoleWriter;
use App\LamboException;
use App\Options;

class NewCommand extends LamboCommand
{
    use Debug;

    protected $signature;
    protected $description = 'Creates a fresh Laravel application';
    protected $consoleWriter;

    public function __construct()
    {
        $this->signature = $this->buildSignature();

        parent::__construct();

        app()->bind('console', function () {
            return $this;
        });
    }

    public function buildSignature()
    {
        return collect((new Options())->all())->reduce(
            function ($carry, $option) {
                return $carry . $this->buildSignatureOption($option);
            },
            "new\n{projectName? : Name of the Laravel project}"
        );
    }

    public function buildSignatureOption($option): string
    {
        $commandlineOption = isset($option['short']) ? ($option['short'] . '|' . $option['long']) : $option['long'];

        if (isset($option['param_description'])) {
            $commandlineOption .= '=' . ($option['default'] ?? '');
        }

        return "\n{--{$commandlineOption} : {$option['cli_description']}}";
    }

    public function handle()
    {
        app(DisplayLamboWelcome::class)();

        if (! $this->argument('projectName')) {
            app(DisplayHelpScreen::class)();
            exit;
        }

        $this->setConsoleWriter();
        $this->setConfig();

        if (app(UpgradeSavedConfiguration::class)()) {
            $this->consoleWriter->newLine();
            $this->consoleWriter->note('Your Lambo configuration (~/.lambo/config) has been updated.');
            $this->consoleWriter->note('Please review the changes then run lambo again.');
            if ($this->confirm(sprintf('Review the changes now in %s?', config('lambo.store.editor')))) {
                app(EditConfigFile::class)('config');
            }
            return;
        }

        $this->consoleWriter->sectionTitle("Creating a new Laravel app '{$this->argument('projectName')}'");

        try {
            app(VerifyDependencies::class)();
            app(ValidateConfiguration::class)();
            app(VerifyPathAvailable::class)();
            app(InstallLaravel::class)();
            app(CustomizeDotEnv::class)();
            app(GenerateAppKey::class)();
            app(CreateDatabase::class)();
            app(ConfigureFrontendFramework::class)();
            app(MigrateDatabase::class)();
            app(InitializeGitRepository::class)();
            app(RunAfterScript::class)();
            app(InitializeGitHubRepository::class)();
            app(PushToGitHub::class)();
            app(ValetLink::class)();
            app(ValetSecure::class)();
            app(OpenInEditor::class)();
            app(OpenInBrowser::class)();
        } catch (LamboException $e) {
            $this->consoleWriter->exception($e->getMessage());
            exit;
        }

        $this->consoleWriter->newLine();
        $this->consoleWriter->text([
            '<fg=green>Done, happy coding!</>',
            'Lambo is brought to you by the lovely folks at <fg=blue;href=https://tighten.co/>Tighten</>.',
        ]);
        $this->consoleWriter->newLine();
    }

    protected function setConsoleWriter()
    {
        $this->consoleWriter = app(ConsoleWriter::class);
    }

    private function setConfig(): void
    {
        config(['lambo.store' => []]); // @todo remove if debug code is removed.

        $commandLineConfiguration = new CommandLineConfiguration([
            'editor' => LamboConfiguration::EDITOR,
            'message' => LamboConfiguration::COMMIT_MESSAGE,
            'branch' => LamboConfiguration::BRANCH,
            'path' => LamboConfiguration::ROOT_PATH,
            'browser' => LamboConfiguration::BROWSER,
            'frontend' => LamboConfiguration::FRONTEND_FRAMEWORK,
            'dbhost' => LamboConfiguration::DATABASE_HOST,
            'dbport' => LamboConfiguration::DATABASE_PORT,
            'dbname' => LamboConfiguration::DATABASE_NAME,
            'dbuser' => LamboConfiguration::DATABASE_USERNAME,
            'dbpassword' => LamboConfiguration::DATABASE_PASSWORD,
            'create-db' => LamboConfiguration::CREATE_DATABASE,
            'force' => LamboConfiguration::FORCE_CREATE,
            'migrate-db' => LamboConfiguration::MIGRATE_DATABASE,
            'link' => LamboConfiguration::VALET_LINK,
            'secure' => LamboConfiguration::VALET_SECURE,
            'with-output' => LamboConfiguration::WITH_OUTPUT,
            'dev' => LamboConfiguration::USE_DEVELOP_BRANCH,
            'full' => LamboConfiguration::FULL,
            'teams' => LamboConfiguration::TEAMS,
            'inertia' => LamboConfiguration::INERTIA,
            'livewire' => LamboConfiguration::LIVEWIRE,
            'github' => LamboConfiguration::INITIALIZE_GITHUB,
            'gh-public' => LamboConfiguration::GITHUB_PUBLIC,
            'gh-description' => LamboConfiguration::GITHUB_DESCRIPTION,
            'gh-homepage' => LamboConfiguration::GITHUB_HOMEPAGE,
            'gh-org' => LamboConfiguration::GITHUB_ORGANIZATION,
            'projectName' => LamboConfiguration::PROJECT_NAME,
        ]);

        $savedConfiguration = new SavedConfiguration([
            'PROJECTPATH' => LamboConfiguration::ROOT_PATH,
            'MESSAGE' => LamboConfiguration::COMMIT_MESSAGE,
            'BRANCH' => LamboConfiguration::BRANCH,
            'DEVELOP' => LamboConfiguration::USE_DEVELOP_BRANCH,
            'CODEEDITOR' => LamboConfiguration::EDITOR,
            'BROWSER' => LamboConfiguration::BROWSER,
            'DB_HOST' => LamboConfiguration::DATABASE_HOST,
            'DB_PORT' => LamboConfiguration::DATABASE_PORT,
            'DB_NAME' => LamboConfiguration::DATABASE_NAME,
            'DB_USERNAME' => LamboConfiguration::DATABASE_USERNAME,
            'DB_PASSWORD' => LamboConfiguration::DATABASE_PASSWORD,
            'CREATE_DATABASE' => LamboConfiguration::CREATE_DATABASE,
            'MIGRATE_DATABASE' => LamboConfiguration::MIGRATE_DATABASE,
            'LINK' => LamboConfiguration::VALET_LINK,
            'SECURE' => LamboConfiguration::VALET_SECURE,
        ]);

        $shellConfiguration = new ShellConfiguration([
            'EDITOR' => LamboConfiguration::EDITOR,
        ]);

        (new SetConfig(
            $commandLineConfiguration,
            $savedConfiguration,
            $shellConfiguration,
            $this->consoleWriter
        ))([
            LamboConfiguration::COMMAND => self::class,
            LamboConfiguration::EDITOR => 'nano',
            LamboConfiguration::COMMIT_MESSAGE => 'Initial commit',
            LamboConfiguration::BRANCH => 'main',
            LamboConfiguration::ROOT_PATH => getcwd(),
            LamboConfiguration::BROWSER => null,
            LamboConfiguration::DATABASE_HOST => '127.0.0.1',
            LamboConfiguration::DATABASE_PORT => 3306,
            LamboConfiguration::DATABASE_NAME => $this->argument('projectName'),
            LamboConfiguration::DATABASE_USERNAME => 'root',
            LamboConfiguration::DATABASE_PASSWORD => '',
            LamboConfiguration::CREATE_DATABASE => false,
            LamboConfiguration::FORCE_CREATE => false,
            LamboConfiguration::MIGRATE_DATABASE => false,
            LamboConfiguration::VALET_LINK => false,
            LamboConfiguration::VALET_SECURE => false,
            LamboConfiguration::WITH_OUTPUT => false,
            LamboConfiguration::USE_DEVELOP_BRANCH => false,
            LamboConfiguration::FULL => false,
            LamboConfiguration::INERTIA => false,
            LamboConfiguration::LIVEWIRE => false,
            LamboConfiguration::TEAMS => false,
            LamboConfiguration::INITIALIZE_GITHUB => false,
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::PROJECT_NAME => null,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => null,
            LamboConfiguration::TLD => null,
        ]);

        if ($this->consoleWriter->isDebug()) {
            $this->debugReport();
        }
    }
}
