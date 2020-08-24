<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Models\GitUser;

class GiteaUser extends GitUser
{
    protected GiteaClient $client;

    protected function mapProperties(array $properties)
    {
        $properties['name'] = $properties['full_name'] ?: $properties['login'];
        $properties['username'] = $properties['login'];
        return $properties;
    }

    public function __construct(GiteaClient $client, GiteaPlatform $platform, array $properties)
    {
        $this->client = $client;
        parent::__construct($platform, $this->mapProperties($properties));
    }

    public function remove(): void
    {
        $this->client->delete("/admin/users/{$this->username}");
    }

    /**
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(): array
    {
        $this->client->sudo($this->username);
        try {
            return $this->client->getAll(GiteaRepository::class, "/user/repos", $this->platform);
        } finally {
            $this->client->sudo(null);
        }
    }

    protected function doUpdate(): void
    {
        // TODO: Implement doUpdate() method.
    }

    /**
     * @return GitTeamInterface[]
     */
    public function getTeamList(): array
    {
        $this->client->sudo($this->username);
        try {
            return array_filter(
                $this->client->getAll(GiteaTeam::class, "/user/teams", $this->platform),
                static fn(GitTeamInterface $team) => ($team->name !== 'Owners')
            );
        } finally {
            $this->client->sudo(null);
        }
    }

    public function deActivate(): void
    {
        // TODO: Implement deActivate() method.
    }

    public function reActivate(): void
    {
        // TODO: Implement reActivate() method.
    }
}
