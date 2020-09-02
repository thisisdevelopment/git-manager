<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use ThisIsDevelopment\GitManager\Contracts\GitWebHookInterface;
use ThisIsDevelopment\GitManager\Models\AbstractGitModel;

class GitLabWebHook extends AbstractGitModel implements GitWebHookInterface
{
    /**
     * @var GitLabClient
     */
    private $client;
    /**
     * @var GitLabRepository
     */
    private $repository;
    /**
     * @var array|string[]
     */
    protected static $properties = [
        'id',
        'url',
    ];

    public function __construct(GitLabClient $client, GitLabRepository $repository, array $properties)
    {
        $this->client = $client;
        $this->repository = $repository;

        $this->hydrate($properties);
    }
}
