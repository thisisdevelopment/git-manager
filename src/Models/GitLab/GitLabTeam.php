<?php

namespace ThisIsDevelopment\GitManager\Models\Gitlab;

use Gitlab\Exception\ExceptionInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitRepository;
use ThisIsDevelopment\GitManager\Models\GitTeam;

class GitLabTeam extends GitTeam
{
    /**
     * @var GitLabClient
     */
    protected $client;
    /**
     * @var GitLabPlatform
     */
    protected $platform;

    protected $visibility = GitLabClient::VISIBILITY_PRIVATE;

    public function __construct(GitLabClient $client, GitLabPlatform $platform, array $properties)
    {
        [$properties['namespace'], $properties['name']] = $platform->splitPath($properties['full_path']);

        $this->client = $client;
        $this->visibility = $properties['visibility'];
        parent::__construct($platform, $properties);
    }

    protected function doUpdate(): void
    {
        try {
            $this->client->groups()->update($this->id, [
                'name' => $this->name,
                'description' => $this->description,
                'visibility' => $this->visibility,
            ]);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to update team ({$this->id}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function remove(): void
    {
        try {
            $this->client->groups()->remove($this->id);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to remove  team ({$this->id}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function getMemberList(): array
    {
        $members = $this->client->getAllData(
            GitLabClient::TYPE_GROUPS,
            'members',
            [$this->id]
        );

        $res = [];
        foreach ($members as $member) {
            $res[] = $this->platform->getUser($member['id']);
        }
        return $res;
    }

    public function addMember(GitUserInterface $user): void
    {
        try {
            $this->client->groups()->addMember($this->id, $user->id, GitLabClient::ACCESS_LEVEL_DEVELOPER);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to add member ({$user->id}) to the team ({$this->id}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function removeMember(GitUserInterface $user): void
    {
        try {
            $this->client->groups()->removeMember($this->id, $user->id);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to remove member ({$user->id}) from the team ({$this->id}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(): array
    {
        return $this->client->getAllModelInstances(
            GitLabClient::TYPE_PROJECTS,
            $this->platform,
            GitLabClient::TYPE_GROUPS,
            'projects',
            [$this->id]
        );
    }

    public function deActivate(): void
    {
        $this->client->groups()->update($this->id, ['membership_lock' => true]);
    }

    public function reActivate(): void
    {
        $this->client->groups()->update($this->id, ['membership_lock' => false]);
    }
}
