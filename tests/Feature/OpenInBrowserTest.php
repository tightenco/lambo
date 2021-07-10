<?php

namespace Tests\Feature;

use App\Actions\OpenInBrowser;
use App\Environment;
use App\Shell;
use Tests\TestCase;

class OpenInBrowserTest extends TestCase
{
    private $environment;

    public function setUp(): void
    {
        parent::setUp();
        $this->environment = $this->mock(Environment::class);
    }

    /** @test */
    function it_uses_the_open_command_on_mac_when_a_browser_is_specified()
    {
        config(['lambo.store.browser' => '/Applications/my/browser.app']);
        config(['lambo.store.project_url' => 'http://my-project.test']);

        $this->environment->shouldReceive('isMac')
            ->once()
            ->andReturn(true);

        $this->shell->shouldReceive('execInProject')
            ->once()
            ->with('open -a "/Applications/my/browser.app" "http://my-project.test"');

        app(OpenInBrowser::class)();
    }

    /** @test */
    function it_uses_valet_open_on_mac_when_no_browser_is_specified()
    {
        $this->assertEmpty(config('lambo.store.browser'));

        $this->environment->shouldReceive('isMac')
            ->once()
            ->andReturn(true);

        $this->shell->shouldReceive('execInProject')
            ->once()
            ->with('valet open');

        app(OpenInBrowser::class)();
    }

    /** @test */
    function it_uses_valet_open_when_not_running_on_mac()
    {
        $this->environment->shouldReceive('isMac')
            ->once()
            ->andReturn(false);

        $this->shell->shouldReceive('execInProject')
            ->once()
            ->with('valet open');

        app(OpenInBrowser::class)();
    }

    /** @test */
    function it_ignores_the_specified_browser_when_not_running_on_mac()
    {
        config(['lambo.store.browser' => '/path/to/a/browser']);
        config(['lambo.store.project_url' => 'http://my-project.test']);

        $this->environment->shouldReceive('isMac')
            ->once()
            ->andReturn(false);

        $this->shell->shouldReceive('execInProject')
            ->once()
            ->with('valet open');

        app(OpenInBrowser::class)();
    }

    /** @test */
    function it_skips_opening_the_site()
    {
        $shell = $this->spy(Shell::class);

        config(['lambo.store.no_browser' => false]);

        app(OpenInBrowser::class);

        $shell->shouldNotHaveReceived('execInProject');
    }
}
