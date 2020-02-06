<?php

namespace Tests\Feature;

use App\Actions\InitializeGitRepo;
use App\Shell\Shell;
use Illuminate\Support\Facades\Config;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

class InitializeGitRepoTest extends TestCase
{
    /** @test */
    public function it_initialises_the_projects_git_repository()
    {
        $this->fakeLamboConsole();

        $commitMessage = 'Initial commit';
        Config::set('lambo.store.commit_message', $commitMessage);

        $this->mock(Shell::class, function($shell) use ($commitMessage) {
            $shell->shouldReceive('execInProject')
                ->with('git init')
                ->once()
                ->andReturn(FakeProcess::success());

            $shell->shouldReceive('execInProject')
                ->with('git add .')
                ->once()
                ->andReturn(FakeProcess::success());

            $shell->shouldReceive('execInProject')
                ->with('git commit -m "' . $commitMessage . '"')
                ->once()
                ->andReturn(FakeProcess::success());
        });

        app(InitializeGitRepo::class)();
    }

    /** @test */
    public function it_throws_an_exception_if_git_init_fails()
    {
        $this->fakeLamboConsole();

        $command = 'git init';
        $this->mock(Shell::class, function($shell) use ($command) {
            $shell->shouldReceive('execInProject')
                ->with($command)
                ->once()
                ->andReturn(FakeProcess::fail($command));
        });

        $this->expectExceptionMessage("Initialization of git repository did not complete successfully.\n  Failed to run: '{$command}'");

        app(InitializeGitRepo::class)();
    }

    /** @test */
    public function it_throws_an_exception_if_git_add_fails()
    {
        $this->fakeLamboConsole();

        $command = 'git add .';
        $this->mock(Shell::class, function($shell) use ($command) {
            $shell->shouldReceive('execInProject')
                ->with('git init')
                ->once()
                ->andReturn(FakeProcess::success());

            $shell->shouldReceive('execInProject')
                ->with($command)
                ->once()
                ->andReturn(FakeProcess::fail($command));
        });

        $this->expectExceptionMessage("Initialization of git repository did not complete successfully.\n  Failed to run: '{$command}'");

        app(InitializeGitRepo::class)();
    }

    /** @test */
    public function it_throws_an_exception_if_git_commit_fails()
    {
        $this->fakeLamboConsole();

        $commitMessage = 'Initial commit';
        Config::set('lambo.store.commit_message', $commitMessage);


        $command = 'git commit -m "' . $commitMessage . '"';
        $this->mock(Shell::class, function($shell) use ($command) {
            $shell->shouldReceive('execInProject')
                ->with('git init')
                ->once()
                ->andReturn(FakeProcess::success());

            $shell->shouldReceive('execInProject')
                ->with('git add .')
                ->once()
                ->andReturn(FakeProcess::success());

            $shell->shouldReceive('execInProject')
                ->with($command)
                ->once()
                ->andReturn(FakeProcess::fail($command));
        });

        $this->expectExceptionMessage("Initialization of git repository did not complete successfully.\n  Failed to run: '{$command}'");

        app(InitializeGitRepo::class)();
    }
}