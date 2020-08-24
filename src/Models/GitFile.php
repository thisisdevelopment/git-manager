<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitBranchInterface;
use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;

abstract class GitFile extends AbstractGitModel implements GitFileInterface
{
    protected static array $properties = [
        'file',
        'contents',
        'exists',
    ];
    protected static array $updatable = [
        'contents' => true,
    ];
    protected GitBranchInterface $branch;
    protected GitRepositoryInterface $repository;

    public function __construct(GitBranchInterface $branch, array $properties)
    {
        $this->branch  = $branch;
        $this->repository = $branch->getRepository();

        $this->hydrate($properties);
    }

    public function getBranch(): GitBranchInterface
    {
        return $this->branch;
    }

    public function getRepository(): GitRepositoryInterface
    {
        return $this->branch->getRepository();
    }

    public function update(string $message, array $properties): void
    {
        self::validateUpdate($properties);
        $this->hydrate($properties);
        $this->doUpdate($message);
        $this->data['exists'] = true;
    }

    abstract protected function doUpdate(string $message): void;
}
