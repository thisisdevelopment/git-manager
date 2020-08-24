<?php

namespace ThisIsDevelopment\GitManager\Models\Gitlab;

use Gitlab\Exception\ExceptionInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitRepository;
use ThisIsDevelopment\GitManager\Models\GitUser;

class GitLabUser extends GitUser
{
    protected GitLabClient $client;
    protected GitLabPlatform $platform;

    public function __construct(GitLabClient $client, GitLabPlatform $platform, array $properties)
    {
        $this->client = $client;
        parent::__construct($platform, $properties);
    }

    protected function doUpdate(): void
    {
        try {
            $this->client->users()->update($this->id, [
                'username'    => $this->username,
                'email'       => $this->email,
                'name'        => $this->name,
                'description' => $this->description
            ]);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to update user: {$this->id}");
        }
    }

    public function remove(): void
    {
        try {
            $this->client->users()->remove($this->id);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to remove user: {$this->id}");
        }
    }

    /**
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(): array
    {
        $this->client->sudo($this->id);
        try {
            return $this->client->getAllModelInstances(GitLabClient::TYPE_PROJECTS, $this->platform);
        } finally {
            $this->client->sudo(null);
        }
    }

    /**
     * @return GitTeamInterface[]
     */
    public function getTeamList(): array
    {
        $this->client->sudo($this->id);
        try {
            return $this->client->getAllModelInstances(GitLabClient::TYPE_GROUPS, $this->platform);
        } finally {
            $this->client->sudo(null);
        }
    }

    public function deActivate(): void
    {
        try {
            $this->client->users()->block($this->id);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to de-activate user: {$this->id}");
        }
    }

    public function reActivate(): void
    {
        try {
            $this->client->users()->unblock($this->id);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to re-activate user: {$this->id}");
        }
    }
}
