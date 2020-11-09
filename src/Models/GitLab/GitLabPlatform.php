<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use Gitlab\Exception\ExceptionInterface;
use Gitlab\Exception\RuntimeException;
use Illuminate\Support\Str;
use ThisIsDevelopment\GitManager\Contracts\GitOwnerInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitPlatform;
use ThisIsDevelopment\GitManager\Models\GitRepository;

class GitLabPlatform extends GitPlatform
{
    /**
     * @var GitLabClient
     */
    protected $client;

    public function __construct(array $config)
    {
        $this->client = (new GitLabClient())
            ->setUrl($config['url'])
            ->authenticate($config['auth'], GitLabClient::AUTH_HTTP_TOKEN);

        parent::__construct($config);
    }

    public function enableLogging(): void
    {
        $this->client->enableLogging();
    }

    public function getCurrentUser(): GitUserInterface
    {
        try {
            return new GitLabUser($this->client, $this, $this->client->users()->me());
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to get current user: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function getRepositoryList(string $namespace = null): array
    {
        $namespace = $namespace ?? $this->defaultRepoNamespace;

        return array_filter(
            $this->client->getAllModelInstances(GitLabClient::TYPE_PROJECTS, $this),
            static function ($repo) use ($namespace) {
                return $repo->namespace === $namespace;
            }
        );
    }

    public function getRepository(string $idOrName, string $namespace = null): GitRepositoryInterface
    {
        $namespace = $namespace ?? $this->defaultRepoNamespace;
        if (!is_numeric($idOrName) && $namespace !== '') {
            $idOrName = $this->defaultRepoNamespace.'/'.$idOrName;
        }

        try {
            return $this->client->getModelInstance(GitLabClient::TYPE_PROJECTS, $idOrName, $this);
        } catch (ExceptionInterface $e) {
            throw new GitException('Unable to get repository '.$e->getMessage(), $e->getCode(), $e);
        }
    }

    public function addRepository(array $properties, ?GitOwnerInterface $owner = null): GitRepositoryInterface
    {
        $currentUser = $this->getCurrentUser();
        $owner = $owner ?? $currentUser;
        $namespace = $properties['namespace'] ?? $this->defaultRepoNamespace;
        unset($properties['namespace']);

        try {
            GitRepository::validateAdd($properties);

            $path = ltrim("{$namespace}/{$properties['name']}", '/');
            $prefix = dirname($path);
            $name = basename($path);
            $parent = $this->findOrCreateGroup($prefix);

            $properties['name'] = $name;
            $properties['path'] = Str::slug($name);
            $properties['auto_devops_enabled'] = false;
            $properties['visibility'] = GitLabClient::VISIBILITY_PRIVATE;
            $properties['namespace_id'] = $parent;

            /** @var GitLabRepository $repo */
            $repo = $this->client->addModelInstance(
                GitLabClient::TYPE_PROJECTS,
                [$name],
                $properties,
                $this
            );

            if ($owner instanceof GitTeamInterface) {
                if ($owner->id !== $parent) {
                    $repo->grantTeamAccess($owner);
                }
            } elseif ($owner->id !== $currentUser->id) {
                $repo->grantUserAccess($owner);
            }

            return $repo;
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to add repository: {$e->getMessage()}", $e->getCode(), $e);
        } finally {
            $this->client->sudo(null);
        }
    }

    public function getUserList($onlyActive = true): array
    {
        $filter = [];
        if ($onlyActive) {
            $filter['active'] = true;
        }

        try {
            return $this->client->getAllModelInstances(
                GitLabClient::TYPE_USERS,
                $this,
                GitLabClient::TYPE_USERS,
                'all',
                [$filter]
            );
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to get userList: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public function getUser(string $idOrName): GitUserInterface
    {
        return $this->client->getModelInstance(GitLabClient::TYPE_USERS, $idOrName, $this);
    }

    public function addUser(array $properties): GitUserInterface
    {
        GitLabUser::validateAdd($properties);
        $properties['reset_password'] = true;
        $properties['skip_confirmation'] = true;
        $properties['force_random_password'] = true;
        return $this->client->addModelInstance(
            GitLabClient::TYPE_USERS,
            [
                $properties['email'],
                ''
            ],
            $properties,
            $this
        );
    }

    public function getTeamList(string $namespace = null): array
    {
        $namespace = $namespace ?? $this->defaultTeamNamespace;

        return array_filter(
            $this->client->getAllModelInstances(GitLabClient::TYPE_GROUPS, $this),
            function ($team) use ($namespace) {
                return $team->namespace === $namespace;
            }
        );
    }

    public function getTeam(string $teamId, string $namespace = null): GitTeamInterface
    {
        $namespace = $namespace ?? $this->defaultTeamNamespace;
        if (!is_numeric($teamId) && $namespace !== '') {
            $teamId = "{$namespace}/{$teamId}";
        }

        try {
            return $this->client->getModelInstance(GitLabClient::TYPE_GROUPS, $teamId, $this);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to get team: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public function addTeam(array $properties): GitTeamInterface
    {
        try {
            $namespace = $properties['namespace'] ?? $this->defaultTeamNamespace;
            unset($properties['namespace']);

            $path = ltrim("{$namespace}/{$properties['name']}", '/');
            $prefix = dirname($path);
            $name = basename($path);
            $properties['name'] = $name;

            $parent = ($prefix !== '.') ? $this->findOrCreateGroup($prefix) : null;

            GitLabTeam::validateAdd($properties);
            return $this->client->addModelInstance(
                GitLabClient::TYPE_GROUPS,
                [
                    $name,
                    Str::slug($properties['name']),
                    $properties['description'] ?? null,
                    GitLabClient::VISIBILITY_PRIVATE,
                    null,
                    null,
                    $parent
                ],
                null,
                $this
            );
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to addTeam: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public function findOrCreateGroup(string $name): int
    {
        if (empty($name)) {
            throw new GitException('Group should not be empty');
        }

        try {
            $group = $this->client->getModelInstance(GitLabClient::TYPE_GROUPS, $name, $this);
        } catch (RuntimeException $e) {
            if ($e->getCode() === 404) {
                $group = $this->addTeam([
                    'namespace' => '/',
                    'name' => $name
                ]);
            } else {
                throw $e;
            }
        }

        return $group->id;
    }

    public function splitPath(string $path): array
    {
        if (Str::startsWith($path, "{$this->defaultRepoNamespace}/")) {
            return [$this->defaultRepoNamespace, substr($path, strlen($this->defaultRepoNamespace) + 1)];
        }

        if (Str::startsWith($path, "{$this->defaultTeamNamespace}/")) {
            return [$this->defaultTeamNamespace, substr($path, strlen($this->defaultTeamNamespace) + 1)];
        }

        return ['', $path];
    }

    public function getOAuthProviderName(): string
    {
        return 'gitlab';
    }
}
