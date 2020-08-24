<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitBranchInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;

abstract class GitBranch extends AbstractGitModel implements GitBranchInterface
{
    protected static array $properties = [
        'name',
    ];
    protected static array $updatable = [
        'name' => true,
        'ref' => true,
    ];
    protected GitRepositoryInterface $repository;

    public function __construct(GitRepositoryInterface $repository, array $properties)
    {
        $this->repository = $repository;
        $this->hydrate($properties);
    }

    public function getRepository(): GitRepositoryInterface
    {
        return $this->repository;
    }
}
