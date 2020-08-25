<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Models\GitTeam;

class GiteaTeam extends GitTeam
{
    /**
     * @var GiteaClient
     */
    protected $client;

    public function __construct(GiteaClient $client, GiteaPlatform $platform, array $properties)
    {
        $this->client = $client;
        parent::__construct($platform, $properties);
    }

    public function remove(): void
    {
        $this->client->delete("/teams/{$this->id}");
    }

    /**
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(): array
    {
        return $this->client->getAll(GiteaRepository::class, "/teams/{$this->id}/repos", $this->platform);
    }

    protected function doUpdate(): void
    {
        $this->client->patch("/teams/{$this->id}", ['name' => $this->name, 'description' => $this->description]);
    }

    /**
     * @return GitUserInterface[]
     */
    public function getMemberList(): array
    {
        return $this->client->getAll(GiteaUser::class, "/teams/{$this->id}/members", $this->platform);
    }

    public function addMember(GitUserInterface $user): void
    {
        $this->client->put("/teams/{$this->id}/members/{$user->username}");
    }

    public function removeMember(GitUserInterface $user): void
    {
        $this->client->delete("/teams/{$this->id}/members/{$user->username}");
    }

    public function deActivate(): void
    {
        $this->client->patch("/teams/{$this->id}", ['permission' => 'read']);
    }

    public function reActivate(): void
    {
        $this->client->patch("/teams/{$this->id}", ['permission' => 'write']);
    }
}
