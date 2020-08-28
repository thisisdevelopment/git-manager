<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use ThisIsDevelopment\GitManager\Contracts\GitBranchInterface;
use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Contracts\GitOwnerInterface;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTagInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitRepository;

class GiteaRepository extends GitRepository
{
    /**
     * @var GiteaClient
     */
    protected $client;
    /**
     * @var GitPlatformInterface
     */
    protected $platform;

    public function __construct(GiteaClient $client, GiteaPlatform $platform, array $properties)
    {
        parent::__construct($platform, $this->mapProperties($platform, $properties));
        $this->client = $client;
    }

    protected function mapProperties(GiteaPlatform $platform, $properties)
    {
        $properties['clone_url_http'] = $properties['clone_url'];
        $properties['clone_url_ssh'] = $properties['ssh_url'];

        [$properties['namespace'], $properties['name']] = $platform->splitPath($properties['full_name']);

        return $properties;
    }

    public function doUpdate(): void
    {
        // TODO: Implement doUpdate() method.
    }

    public function getFile(string $file, string $branch, $createIfNotExists = false): GitFileInterface
    {
        return $this->getBranch($branch)->getFile($file, $createIfNotExists);
    }

    /**
     * @return GitUserInterface[]
     */
    public function getUserAccessList(): array
    {
        // TODO: Implement getUserAccessList() method.
    }

    public function grantUserAccess(GitUserInterface $user): void
    {
        // TODO: Implement grantUserAccess() method.
    }

    public function revokeUserAccess(GitUserInterface $user): void
    {
        // TODO: Implement revokeUserAccess() method.
    }

    /**
     * @return GitTeamInterface[]
     */
    public function getTeamAccessList(): array
    {
        // TODO: Implement getTeamAccessList() method.
    }

    public function grantTeamAccess(GitTeamInterface $team): void
    {
        $name = urlencode($this->platform->normalizeRepoPath($this->name));
        $this->client->put("/teams/{$team->id}/repos/{$this->platform->defaultRepoNamespace}/{$name}");
    }

    public function revokeTeamAccess(GitTeamInterface $team): void
    {
        $name = urlencode($this->platform->normalizeRepoPath($this->name));
        $this->client->delete("/teams/{$team->id}/repos/{$this->platform->defaultRepoNamespace}/{$name}");
    }

    public function archive(): void
    {
        // TODO: Implement archive() method.
    }

    public function unArchive(): void
    {
        // TODO: Implement unArchive() method.
    }

    public function rename(string $name): void
    {
        // TODO: Implement rename() method.
    }

    public function remove(): void
    {
        // TODO: Implement remove() method.
    }

    /**
     * @return GitBranchInterface[]
     */
    public function getBranchList(): array
    {
        // TODO: Implement getBranchList() method.
    }

    public function addBranch(array $properties): GitBranchInterface
    {
        //TODO: not supported in gitea API???
    }

    public function getBranch(string $id): GitBranchInterface
    {
        try {
            $name = $this->platform->normalizeRepoPath($this->name);
            $url = "/repos/{$this->namespace}/{$name}/branches/{$id}";
            return $this->client->get(GiteaBranch::class, $url, $this);
        } catch (GitException $e) {
            if ($id === 'master' && $e->getCode() === 404) {
                return new GiteaBranch($this->client, $this, ['name' => $id]);
            }

            throw new GitException("Unable to get branch ({$id}): {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public function getOwner(): GitOwnerInterface
    {
        // TODO: Implement getOwner() method.
    }

    public function transferTo(GitOwnerInterface $owner): void
    {
        // TODO: Implement transferTo() method.
    }

    public function runCICD(string $commitRef, array $variables = []): void
    {
        // TODO: Implement runCICD() method.
    }

    public function getTagList(): array
    {
        $owner = $this->namespace;
        $repo = $this->platform->normalizeRepoPath($this->name);

        return $this->client->getAll(GiteaTag::class, "/repos/{$owner}/{$repo}/tags", $this);
    }

    public function getTag(string $name): ?GitTagInterface
    {
        $owner = $this->namespace;
        $repo = $this->platform->normalizeRepoPath($this->name);

        return $this->client->getFirst(GiteaTag::class, "/repos/{$owner}/{$repo}/git/refs/tags/{$name}", $this);
    }
}
