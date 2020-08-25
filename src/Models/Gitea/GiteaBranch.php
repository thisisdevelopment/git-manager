<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Models\GitBranch;

class GiteaBranch extends GitBranch
{
    /**
     * @var GiteaClient
     */
    protected $client;

    public function __construct(GiteaClient $client, GiteaRepository $repository, array $properties)
    {
        $properties['commitHash'] = $properties['commit']['id'];

        $this->client = $client;
        parent::__construct($repository, $properties);
    }

    public function getFile(string $file, $createIfNotExists = false): GitFileInterface
    {
        $fileInfo = [
            'file'     => $file,
            'contents' => '',
            'exists'   => false,
        ];

        return new GiteaFile($this->client, $this, $fileInfo);
    }

    public function remove(): void
    {
        // TODO: Implement remove() method.
    }
}
