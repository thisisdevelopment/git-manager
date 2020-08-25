<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitOwnerInterface;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;

abstract class GitPlatform extends AbstractGitModel implements GitPlatformInterface
{
    /**
     * @var array|string[]
     */
    protected static $properties = [
        'name',
        'defaultTeamNamespace',
        'defaultRepoNamespace',
    ];

    public function __construct(array $config)
    {
        $this->hydrate($config);
    }
}
