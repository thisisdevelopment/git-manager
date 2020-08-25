<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;

abstract class GitTeam extends AbstractGitModel implements GitTeamInterface
{
    /**
     * @var array|string[]
     */
    protected static $properties = [
        'id',
        'name',
        'description',
        'namespace',
    ];
    /**
     * @var array|bool[]
     */
    protected static $updatable = [
        'name' => true,
        'description' => false,
    ];
    /**
     * @var GitPlatformInterface
     */
    protected $platform;

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
