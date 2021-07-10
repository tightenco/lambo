<?php

namespace Tests\Feature;

use App\Actions\PushToGitHub;
use App\Shell;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

/**
 * @group git-and-github
 */
class PushToGitHubTest extends TestCase
{
    /** @test */
    function it_pushes_to_github()
    {
        config(['lambo.store.push_to_github' => true]);
        config(['lambo.store.branch' => 'branch']);

        $this->shell->shouldReceive('execInProject')
            ->with('git push -u origin branch')
            ->once()
            ->andReturn(FakeProcess::success());

        app(PushToGitHub::class)();
    }

    /** @test */
    function it_skips_pushing_to_github()
    {
        $shell = $this->spy(Shell::class);

        config(['lambo.store.branch' => 'main']);
        $pushCommand = 'git push -u origin ' . config('lambo.store.branch');

        config(['lambo.store.push_to_github' => null]);
        app(PushToGitHub::class)();
        $shell->shouldNotHaveReceived('execInProject', [$pushCommand]);

        config(['lambo.store.push_to_github' => false]);
        app(PushToGitHub::class)();
        $shell->shouldNotHaveReceived('execInProject', [$pushCommand]);
    }
}
