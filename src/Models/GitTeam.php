<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;

abstract class GitTeam extends AbstractGitModel implements GitTeamInterface
{
    protected static array $properties = [
        'id',
        'name',
        'description',
        'namespace',
    ];
    protected static array $updatable = [
        'name' => true,
        'description' => false,
    ];
    protected GitPlatformInterface $platform;

    public function __construct(GitPlatformInterface $platform, array $properties)
    {
        $this->platform = $platform;
        $this->hydrate($properties);
    }

    public function update(array $properties): void
    {
        self::validateUpdate($properties);
        $this->hydrate($properties);
        $this->doUpdate();
    }

    abstract protected function doUpdate(): void;

    public function addRepository(array $properties): GitRepositoryInterface
    {
        return $this->platform->addRepository($properties, $this);
    }
}
