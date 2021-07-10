<?php

namespace Tests\Feature;

use App\Actions\Concerns\InteractsWithGitHub;
use App\Actions\InitializeGitHubRepository;
use App\Configuration\LamboConfiguration;
use App\ConsoleWriter;
use App\LamboException;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

/**
 * @group git-and-github
 */
class InitializeGitHubRepositoryTest extends TestCase
{
    use InteractsWithGitHub;

    protected $toolConfigurations = [
        ['gh' => true, 'hub' => true],
        ['gh' => true, 'hub' => false],
        ['gh' => false, 'hub' => true],
        ['gh' => false, 'hub' => false],
    ];

    protected $gitHubConfigurations = [
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => false,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => null,
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => null,
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => null,
        ],
        [
            LamboConfiguration::GITHUB_PUBLIC => true,
            LamboConfiguration::GITHUB_DESCRIPTION => 'My awesome project',
            LamboConfiguration::GITHUB_HOMEPAGE => 'https://example.com',
            LamboConfiguration::GITHUB_ORGANIZATION => 'org',
        ],
    ];

    /** @test */
    function it_manages_new_repository_initialization()
    {
        foreach ([true, false] as $initializeGitHub) {
            foreach ($this->toolConfigurations as $toolConfiguration) {
                foreach ($this->gitHubConfigurations as $gitHubConfiguration) {
                    config(['lambo.store.project_name' => 'name']);
                    config(['lambo.store.' . LamboConfiguration::INITIALIZE_GITHUB => $initializeGitHub]);
                    config(['lambo.store.push_to_github' => false]);
                    config(['lambo.store.tools' => $toolConfiguration]);
                    config(['lambo.store' => array_merge(config('lambo.store'), $gitHubConfiguration)]);

                    if ($this->shouldCreateRepository()) {
                        $this->shell->shouldReceive('execInProject', [$this->getCommand()])
                            ->andReturn(FakeProcess::success());
                    }

                    if (! $this->gitHubToolingInstalled()) {
                        $this->expectException(LamboException::class);
                    }

                    app(InitializeGitHubRepository::class)();

                    if ($this->shouldCreateRepository()) {
                        $this->assertTrue(config('lambo.store.push_to_github'));
                    }
                }
            }
        }
    }

    /** @test */
    function it_warns_the_user_if_repository_creation_fails()
    {
        $consoleWriter = $this->mock(ConsoleWriter::class);
        $consoleWriter->shouldReceive('logStep');

        config(['lambo.store.project_name' => 'name']);
        config(['lambo.store.' . LamboConfiguration::INITIALIZE_GITHUB => true]);
        config(['lambo.store.push_to_github' => false]);
        config(['lambo.store.tools.gh' => true]);

        $failedCommandOutput = 'Failed command output';

        $this->shell->shouldReceive('execInProject')
            ->with($this->getCommand())
            ->once()
            ->andReturn(FakeProcess::fail($this->getCommand())->withErrorOutput($failedCommandOutput));

        $consoleWriter->shouldReceive('warn')
            ->with(InitializeGitHubRepository::WARNING_FAILED_TO_CREATE_REPOSITORY)
            ->globally()
            ->ordered();

        $consoleWriter->shouldReceive('warnCommandFailed')
            ->with($this->getCommand())
            ->globally()
            ->ordered();

        $consoleWriter->shouldReceive('showOutputErrors')
            ->with($failedCommandOutput)
            ->globally()
            ->ordered();

        app(InitializeGitHubRepository::class)();
    }
}
