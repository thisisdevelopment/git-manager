<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;

abstract class GitUser extends AbstractGitModel implements GitUserInterface
{
    protected static array $properties = [
        'id',
        'name',
        'username',
        'email',
        'description',
        'created_at',
    ];

    protected static array $updatable = [
        'name' => true,
        'username' => true,
        'email' => true,
        'description' => false,
    ];
    protected GitPlatformInterface $platform;

    public function __construct(GitPlatform $platform, array $properties)
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
