<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Models\GitFile;

class GiteaFile extends GitFile
{
    /**
     * @var GiteaClient
     */
    protected $client;

    public function __construct(GiteaClient $client, GiteaBranch $branch, array $properties)
    {
        $this->client = $client;
        parent::__construct($branch, $properties);
    }

    protected function doUpdate(string $message): void
    {
        // TODO: Implement doUpdate() method.
    }

    public function remove(string $message): void
    {
        // TODO: Implement remove() method.
    }

    public function rename(string $message, string $filename): void
    {
        // TODO: Implement rename() method.
    }
}
