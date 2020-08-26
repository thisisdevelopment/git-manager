<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitTagInterface;
use ThisIsDevelopment\GitManager\Models\AbstractGitModel;

class GiteaTag extends AbstractGitModel implements GitTagInterface
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
        'name',
        'commitHash'
    ];

    public function __construct(GiteaClient $client, GiteaRepository $repository, array $properties)
    {
        $this->client = $client;
        $this->repository = $repository;

        isset($properties['ref'])
            ? $this->mapRefProperties($properties)
            : $this->mapTagProperties($properties);

        $this->hydrate($properties);
    }

    private function mapRefProperties(array &$properties): void
    {
        $properties['name'] = str_replace('refs/tags/', '', $properties['ref']);
        $properties['commitHash'] = $properties['object']['sha'];
    }

    private function mapTagProperties(array &$properties): void
    {
        $properties['commitHash'] = $properties['id'];
    }
}
