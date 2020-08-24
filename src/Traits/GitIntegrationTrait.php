<?php

namespace ThisIsDevelopment\GitManager\Traits;

use Illuminate\Database\Eloquent\Builder;
use ThisIsDevelopment\GitManager\Contracts\GitIntegrationStatusInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitIntegrationDisabled;

/**
 * @property bool git_enabled
 * @property string git_status
 * @property mixed git_ref
 */
trait GitIntegrationTrait
{
    public function isGitIntegrationEnabled(): bool
    {
        return (bool) $this->git_enabled;
    }

    public function hasActiveGitIntegration(): bool
    {
        return $this->isGitIntegrationEnabled() && $this->git_status === GitIntegrationStatusInterface::STATUS_CREATED;
    }

    public function hasFailedGitIntegration(): bool
    {
        return $this->isGitIntegrationEnabled() && $this->git_status === GitIntegrationStatusInterface::STATUS_FAILED;
    }

    public function canActivateGitIntegration(): bool
    {
        return !$this->isGitIntegrationEnabled() && $this->git_status === null;
    }

    public function canStartGitIntegration(): bool
    {
        return $this->isGitIntegrationEnabled() && $this->git_status === null;
    }

    public function getGitPlatform(): string
    {
        if (!$this->isGitIntegrationEnabled()) {
            throw new GitIntegrationDisabled('Git integration is not enabled');
        }

        return config('site.deployment.git_platform');
    }

    /**
     * Scopes
     */

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGitEnabled(Builder $query): Builder
    {
        return $query->where($this->getTable() . '.git_enabled', true);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeGitActive(Builder $query): Builder
    {
        return $query->where($this->getTable() . '.git_status', GitIntegrationStatusInterface::STATUS_CREATED);
    }
}
