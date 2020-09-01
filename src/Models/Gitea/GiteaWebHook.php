<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitWebHookInterface;
use ThisIsDevelopment\GitManager\Models\AbstractGitModel;

class GiteaWebHook extends AbstractGitModel implements GitWebHookInterface
{
    /**
     * @var GiteaClient
     */
    private $client;
    /**
     * @var GiteaRepository
     */
    private $repository;
    /**
     * @var array|string[]
     */
    protected static $properties = [
        'id',
        'url',
    ];

    public function __construct(GiteaClient $client, GiteaRepository $repository, array $properties)
    {
        $this->client = $client;
        $this->repository = $repository;

        $properties['url'] = $properties['config']['url'];
        $this->hydrate($properties);
    }
}
