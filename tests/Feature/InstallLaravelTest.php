<?php

namespace Tests\Feature;

use App\Actions\InstallLaravel;
use App\LamboException;
use Tests\Feature\Fakes\FakeProcess;
use Tests\TestCase;

class InstallLaravelTest extends TestCase
{
    /** @test */
    function it_installs_laravel()
    {
        collect([
            ['lambo.store.dev' => false, 'lambo.store.with_output' => false],
            ['lambo.store.dev' => false, 'lambo.store.with_output' => true],
            ['lambo.store.dev' => true, 'lambo.store.with_output' => false],
            ['lambo.store.dev' => true, 'lambo.store.with_output' => true],
        ])->each(function ($options) {
            config(['lambo.store.project_name' => 'my-project']);
            config(['lambo.store.dev' => $options['lambo.store.dev']]);
            config(['lambo.store.with_output' => $options['lambo.store.with_output']]);
            $this->shell->shouldReceive('execInRoot')
                ->with(sprintf(
                    'composer create-project laravel/laravel %s%s --remove-vcs --prefer-dist %s',
                    config('lambo.store.project_name'),
                    config('lambo.store.dev') ? ' dev-master' : '',
                    config('lambo.store.with_output') ? '' : '--quiet'
                ))
                ->once()
                ->andReturn(FakeProcess::success());

            app(InstallLaravel::class)();
        });
    }

    /** @test */
    function it_throws_an_exception_if_laravel_fails_to_install()
    {
        config(['lambo.store.project_name' => 'my-project']);
        config(['lambo.store.dev' => false]);
        config(['lambo.store.with_output' => false]);

        $this->shell->shouldReceive('execInRoot')
            ->andReturn(FakeProcess::fail('failed command'));

        $this->expectException(LamboException::class);

        app(InstallLaravel::class)();
    }
}
