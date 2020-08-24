<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitBranchInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;

abstract class GitBranch extends AbstractGitModel implements GitBranchInterface
{
    protected static $properties = [
        'name',
    ];

    protected static $updatable = [
        'name' => true,
        'ref'  => true,
    ];

    /**
     * @var GitRepositoryInterface
     */
    protected $repository;

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
