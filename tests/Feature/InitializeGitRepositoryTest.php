<?php

namespace Tests\Feature;

use App\Actions\InitializeGitRepository;
use App\LamboException;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

class InitializeGitRepositoryTest extends TestCase
{
    /** @test */
    function it_initializes_git_using_the_default_branch_name()
    {
        config(['lambo.store.commit_message' => 'Initial commit']);

        $this->shell->shouldReceive('execInProject')
            ->with('git init --quiet')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with('git add .')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with("git commit --quiet -m 'Initial commit'")
            ->once()
            ->andReturn(FakeProcess::success());

        app(InitializeGitRepository::class)();
    }

    /** @test */
    function it_initializes_git_with_the_specified_branch_name()
    {
        config(['lambo.store.commit_message' => 'Initial commit']);
        config(['lambo.store.branch' => 'foo-branch']);

        $this->shell->shouldReceive('execInProject')
            ->with('git init --quiet --initial-branch=foo-branch')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with('git add .')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with("git commit --quiet -m 'Initial commit'")
            ->once()
            ->andReturn(FakeProcess::success());

        app(InitializeGitRepository::class)();
    }

    /** @test */
    function it_throws_an_exception_if_git_init_fails()
    {
        config(['lambo.store.branch' => 'main']);

        $this->shell->shouldReceive('execInProject')
            ->with('git init --quiet --initial-branch=main')
            ->once()
            ->andReturn(FakeProcess::fail('git init'));

        $this->expectException(LamboException::class);

        app(InitializeGitRepository::class)();
    }

    /** @test */
    function it_throws_an_exception_if_git_add_fails()
    {
        config(['lambo.store.branch' => 'main']);

        $this->shell->shouldReceive('execInProject')
            ->with('git init --quiet --initial-branch=main')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with('git add .')
            ->once()
            ->andReturn(FakeProcess::fail('git add .'));

        $this->expectException(LamboException::class);

        app(InitializeGitRepository::class)();
    }

    /** @test */
    function it_throws_an_exception_if_git_commit_fails()
    {
        config(['lambo.store.commit_message' => 'Initial commit']);
        config(['lambo.store.branch' => 'main']);

        $command = 'git init --quiet --initial-branch=main';
        $this->shell->shouldReceive('execInProject')
            ->with($command)
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with('git add .')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with("git commit --quiet -m 'Initial commit'")
            ->once()
            ->andReturn(FakeProcess::fail('git commit -m "Initial commit"'));

        $this->expectException(LamboException::class);

        app(InitializeGitRepository::class)();
    }

    /** @test */
    function it_removes_the_quiet_flag_when_show_output_is_enabled()
    {
        config(['lambo.store.commit_message' => 'Initial commit']);
        config(['lambo.store.with_output' => true]);

        $this->shell->shouldReceive('execInProject')
            ->with('git init')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with('git add .')
            ->once()
            ->andReturn(FakeProcess::success());

        $this->shell->shouldReceive('execInProject')
            ->with("git commit -m 'Initial commit'")
            ->once()
            ->andReturn(FakeProcess::success());

        app(InitializeGitRepository::class)();
    }
}
