<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use ThisIsDevelopment\GitManager\Contracts\GitTagInterface;
use ThisIsDevelopment\GitManager\Models\AbstractGitModel;

class GitLabTag extends AbstractGitModel implements GitTagInterface
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
        'name',
        'commitHash'
    ];

    public function __construct(GitLabClient $client, GitLabRepository $repository, array $properties)
    {
        $this->client = $client;
        $this->repository = $repository;

        $properties['commitHash'] = $properties['target'];

        $this->hydrate($properties);
    }
}
