<?php

namespace App\Actions\Concerns;

use App\Configuration\LamboConfiguration;
use App\LamboException;

trait InteractsWithGitHub
{
    protected function shouldCreateRepository(): bool
    {
        return $this->gitHubInitializationRequested() && $this->gitHubToolingInstalled();
    }

    protected function gitHubInitializationRequested(): bool
    {
        return config('lambo.store.' . LamboConfiguration::INITIALIZE_GITHUB) === true;
    }

    protected function getDescription(): string
    {
        $description = config('lambo.store.' . LamboConfiguration::GITHUB_DESCRIPTION);

        if (is_null($description)) {
            return '';
        }

        return sprintf(' --description="%s"', $description);
    }

    protected function getHomepage(): string
    {
        $homepage = config('lambo.store.' . LamboConfiguration::GITHUB_HOMEPAGE);

        if (is_null($homepage)) {
            return '';
        }

        return sprintf(' --homepage="%s"', $homepage);
    }

    /**
     * @throws LamboException
     */
    protected function getCommand(): string
    {
        if ($this->ghInstalled()) {
            return sprintf(
                'gh repo create%s --confirm %s%s%s',
                $this->getRepositoryName(),
                config('lambo.store.github_public') ? ' --public' : ' --private',
                $this->getDescription(),
                $this->getHomepage(),
            );
        }

        if ($this->hubInstalled()) {
            return sprintf(
                'hub create %s%s%s%s',
                config('lambo.store.github_public') ? '' : '--private ',
                $this->getDescription(),
                $this->getHomepage(),
                $this->getRepositoryName()
            );
        }

        throw new LamboException("Missing tool. Expected one of 'gh' or 'hub' to be installed but none found.");
    }

    protected function getRepositoryName(): string
    {
        $name = config('lambo.store.project_name');
        $organization = config('lambo.store.' . LamboConfiguration::GITHUB_ORGANIZATION);

        return $organization ? " {$organization}/{$name}" : " {$name}";
    }

    public static function ghInstalled(): bool
    {
        return config('lambo.store.tools.gh') === true;
    }

    public static function hubInstalled(): bool
    {
        return config('lambo.store.tools.hub') === true;
    }

    public function gitHubToolingInstalled(): bool
    {
        return $this->ghInstalled() || $this->hubInstalled();
    }
}
