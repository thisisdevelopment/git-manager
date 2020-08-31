<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use Gitlab\Exception\ExceptionInterface;
use Illuminate\Support\Str;
use ThisIsDevelopment\GitManager\Contracts\GitBranchInterface;
use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Contracts\GitOwnerInterface;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTagInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Contracts\GitWebHookInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitRepository;

class GitLabRepository extends GitRepository
{
    /**
     * @var GitLabClient
     */
    protected $client;
    /**
     * @var GitPlatformInterface
     */
    protected $platform;
    /**
     * @var array|mixed
     */
    protected $groupAccess;
    /**
     * @var string
     */
    protected $ownerType;
    /**
     * @var int|mixed
     */
    protected $ownerId;

    public function __construct(GitLabClient $client, GitLabPlatform $platform, array $properties)
    {
        parent::__construct($platform, $this->mapProperties($platform, $properties));
        $this->groupAccess = $properties['shared_with_groups'];
        $this->ownerId = ($properties['namespace']['kind'] === 'group') ?
            $properties['namespace']['id'] : $properties['owner']['id'];
        $this->ownerType = ($properties['namespace']['kind'] === 'group') ? 'team' : 'user';
        $this->client = $client;
    }

    public function getFile(string $file, string $branch, $createIfNotExists = false): GitFileInterface
    {
        return $this->getBranch($branch)->getFile($file, $createIfNotExists);
    }

    /**
     * @return GitTeamInterface[]
     */
    public function getTeamAccessList(): array
    {
        //TODO: currently the access level is not retrievable, if we want this, we should add a GitMember interface
        $res = [];
        if ($this->ownerType === 'team') {
            $res[] = $this->getOwner();
        }

        foreach ($this->groupAccess as $member) {
            $res[] = $this->platform->getTeam($member['group_id']);
        }
        return $res;
    }

    public function grantTeamAccess(GitTeamInterface $team): void
    {
        $params = [
            'group_id' => $team->id,
            'group_access' => GitLabClient::ACCESS_LEVEL_DEVELOPER
        ];

        try {
            $this->client->projects()->addShare(
                $this->id,
                $params
            );
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to grant access to this repository ({$this->name}) for team '{$team->id}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }

        $this->groupAccess[] = $params;
    }

    public function revokeTeamAccess(GitTeamInterface $team): void
    {
        try {
            if ($this->ownerType === 'team' && $team->id === $this->ownerId) {
                throw new GitException(
                    "Unable to revoke team access for this team ({$this->id}), as it owns the repository"
                );
            }
            $this->client->projects()->removeShare(
                $this->id,
                $team->id
            );

            $this->groupAccess = collect($this->groupAccess)
                ->reject(static function ($item) use ($team) {
                    return $item['group_id'] === $team->id;
                });
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to revoke access to this repository ({$this->name}) for team '{$team->id}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @return GitUserInterface[]
     */
    public function getUserAccessList(): array
    {
        //TODO: currently the access level is not retrievable, if we want this, we should add a GitMember interface
        $members = $this->client->getAllData(
            GitLabClient::TYPE_PROJECTS,
            'members',
            [$this->id]
        );

        $res = [];
        foreach ($members as $member) {
            $res[] = $this->platform->getUser($member['id']);
        }
        return $res;
    }

    public function grantUserAccess(GitUserInterface $user): void
    {
        try {
            $this->client->projects()->addMember($this->id, $user->id, GitLabClient::ACCESS_LEVEL_DEVELOPER);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to grant access to this repository ({$this->name}) for user '{$user->id}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function revokeUserAccess(GitUserInterface $user): void
    {
        try {
            $this->client->projects()->removeMember($this->id, $user->id);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to revoke access to this repository ({$this->name}) for user '{$user->id}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function archive(): void
    {
        try {
            $this->client->projects()->archive($this->id);
            $this->data['archived'] = true;
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to archive this repository ({$this->name}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function unArchive(): void
    {
        try {
            $this->client->projects()->unarchive($this->id);
            $this->data['archived'] = false;
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to un-archive this repository ({$this->name}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function rename(string $name): void
    {
        try {
            $this->client->projects()->update($this->id, [
                'name' => $name,
                'path' => Str::slug($name),
            ]);
            $this->data['name'] = $name;
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to rename this repository ({$this->name}) to {$name}: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function remove(): void
    {
        try {
            $this->client->projects()->remove($this->id);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to remove this repository ({$this->name}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function doUpdate(): void
    {
        try {
            $this->client->projects()->update($this->id, [
                'name' => $this->name,
                'path' => Str::slug($this->name),
                'description' => $this->description,
            ]);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to update this repository ({$this->name}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    private function mapProperties(GitLabPlatform $platform, array $properties): array
    {
        $properties['clone_url_http'] = $properties['http_url_to_repo'];
        $properties['clone_url_ssh'] = $properties['ssh_url_to_repo'];

        [$properties['namespace'], $properties['name']] =
            $platform->splitPath($properties['path_with_namespace']);

        return $properties;
    }

    /**
     * @return GitBranchInterface[]
     */
    public function getBranchList(): array
    {
        $branches = $this->client->getAllData(
            GitLabClient::TYPE_REPOSITORIES,
            'branches',
            ['project_id' => $this->id]
        );

        $res = [];
        foreach ($branches as $branch) {
            $res[] = new GitLabBranch($this->client, $this, $branch);
        }

        return $res;
    }

    public function addBranch(array $properties): GitBranchInterface
    {
        try {
            GitLabBranch::validateAdd($properties);
            $branch = $this->client->repositories()->createBranch($this->id, $properties['name'], $properties['ref']);
            return new GitLabBranch(
                $this->client,
                $this,
                $branch
            );
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to add branch ({$properties['name']}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function getBranch(string $id): GitBranchInterface
    {
        try {
            return new GitLabBranch($this->client, $this, $this->client->repositories()->branch($this->id, $id));
        } catch (ExceptionInterface $e) {
            if ($id === 'master' && $e->getCode() === 404) {
                return new GitLabBranch($this->client, $this, ['name' => $id]);
            }

            throw new GitException(
                "Unable to get branch ({$id}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function getOwner(): GitOwnerInterface
    {
        if ($this->ownerType === 'user') {
            return $this->platform->getUser($this->ownerId);
        } else {
            return $this->platform->getTeam($this->ownerId);
        }
    }

    public function transferTo(GitOwnerInterface $owner): void
    {
        try {
            $this->client->projects()->transfer($this->id, $owner->id);
            $this->ownerId = $owner->id;
            $this->ownerType = ($owner instanceof GitTeamInterface) ? 'team' : 'user';
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to transfer this repository ({$this->name}) to {$owner->id}: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function runCICD(string $commitRef, array $variables = []): void
    {
        try {
            $tmp = [];
            foreach ($variables as $key => $value) {
                $tmp[]['key'] = $key;
                $tmp[]['variable_type'] = 'env_var';
                $tmp[]['value'] = $value;
            }

            $this->client->projects()->createPipeline($this->id, $commitRef, $tmp);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to run CI/CD pipeline for this repository ({$this->name}@{$commitRef}): {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function getTagList(): array
    {
        $tags = $this->client->tags()->all($this->id);
        $gitTags = [];

        foreach ($tags as $tag) {
            $gitTags[] = new GitLabTag($this->client, $this, $tag);
        }

        return $gitTags;
    }

    public function getTag(string $name): ?GitTagInterface
    {
        return new GitLabTag($this->client, $this, $this->client->tags()->show($this->id, $name));
    }

    public function getWebHookList(): array
    {
        $res = $this->client->projects()->hooks($this->id);

        $hooks = [];

        foreach ($res as $hook) {
           $hooks[] = new GitLabWebHook($this->client, $this, $hook);
        }

        return $hooks;
    }

    public function getWebHook(int $id): GitWebHookInterface
    {
        $res = $this->client->projects()->hook($this->id, $id);

        return new GitLabWebHook($this->client, $this, $res);
    }

    public function addWebHook(string $callbackUri, $parameters = []): GitWebHookInterface
    {
        $res = $this->client->projects()->addHook($this->id, $callbackUri, $parameters);

        return new GitLabWebHook($this->client, $this, $res);
    }

    public function editWebHook(int $id, array $parameters = []): void
    {
        $this->client->projects()->updateHook($this->id, $id, $parameters);
    }

    public function deleteWebHook(int $id): void
    {
        $this->client->projects()->removeHook($this->id, $id);
    }
}
