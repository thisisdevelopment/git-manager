<?php

namespace ThisIsDevelopment\GitManager\Models\Gitea;

use Illuminate\Support\Str;
use ThisIsDevelopment\GitManager\Contracts\GitOwnerInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;
use ThisIsDevelopment\GitManager\Contracts\GitTeamInterface;
use ThisIsDevelopment\GitManager\Contracts\GitUserInterface;
use ThisIsDevelopment\GitManager\Models\GitPlatform;

class GiteaPlatform extends GitPlatform
{
    /**
     * @var GiteaClient
     */
    protected $client;

    public function __construct(array $config)
    {
        $this->client = (new GiteaClient())
            ->setUrl($config['url'])
            ->authenticate($config['auth']);

        parent::__construct($config);
    }

    public function getRepositoryList(string $namespace = null): array
    {
        $namespace = $namespace ?? $this->defaultRepoNamespace;
        return $this->client->getAll(GiteaRepository::class, "/orgs/{$namespace}/repos", $this);
    }

    public function getRepository(string $idOrName, string $namespace = null): GitRepositoryInterface
    {
        $namespace = $namespace ?? $this->defaultRepoNamespace;

        if (is_numeric($idOrName)) {
            $url = "/repositories/{$idOrName}";
        } else {
            $url = "/repos/{$namespace}/{$idOrName}";
        }

        return $this->client->get(GiteaRepository::class, $url, $this);
    }

    public function addRepository(array $properties, ?GitOwnerInterface $owner = null): GitRepositoryInterface
    {
        $namespace = $properties['namespace'] ?? $this->defaultRepoNamespace;

        $properties['name'] = $this->normalizeRepoPath($properties['name']);

        //TODO: note /org instead of /orgs!!
        return $this->client->post(GiteaRepository::class, "/org/{$namespace}/repos", $this, $properties);
    }

    public function getUserList(bool $onlyActive = true): array
    {
        $namespace = $this->defaultTeamNamespace;
        return $this->client->getAll(GiteaUser::class, "/orgs/{$namespace}/members", $this);
    }

    public function getUser(string $idOrName): GitUserInterface
    {
        if (is_numeric($idOrName)) {
            return $this->client->getFirst(GiteaUser::class, "/users/search?uid={$idOrName}", $this);
        }

        return $this->client->get(GiteaUser::class, "/users/{$idOrName}", $this);
    }

    public function getUserAsAdmin(string $idOrName): GitUserInterface
    {
        if (is_numeric($idOrName)) {
            return $this->client->getFirst(GiteaUser::class, "/users/search?uid={$idOrName}", $this);
        }

        return $this->client->get(GiteaUser::class, "/users/{$idOrName}", $this);
    }

    public function addUser(array $properties): GitUserInterface
    {
        return $this->client->post(GiteaUser::class, "/admin/users", $this, $properties);
    }

    public function getCurrentUser(): GitUserInterface
    {
        return $this->client->get(GiteaUser::class, "/user", $this);
    }

    public function getTeamList(string $namespace = null): array
    {
        $namespace = $namespace ?? $this->defaultTeamNamespace;

        return array_filter(
            $this->client->getAll(GiteaTeam::class, "/orgs/{$namespace}/teams", $this),
            static function (GitTeamInterface $team) {
                return ($team->name !== 'Owners');
            }
        );
    }

    public function getTeam(string $idOrName, string $namespace = null): GitTeamInterface
    {
        $namespace = $namespace ?? $this->defaultTeamNamespace;
        if (!is_numeric($idOrName) && $namespace !== '') {
            $idOrName = "{$namespace}/{$idOrName}";
        }

        //TODO: teams are only retrievable by id, not by name/path
        return $this->client->get(GiteaTeam::class, "/teams/{$idOrName}", $this);
    }

    public function addTeam(array $properties): GitTeamInterface
    {
        $namespace = $properties['namespace'] ?? $this->defaultTeamNamespace;
        $properties['permission'] = 'write';
        return $this->client->post(GiteaTeam::class, "/orgs/{$namespace}/teams", $this, $properties);
    }

    public function splitPath($path)
    {
        if (Str::startsWith($path, "{$this->defaultRepoNamespace}/")) {
            $res = [$this->defaultRepoNamespace, substr($path, strlen($this->defaultRepoNamespace) + 1)];
        } elseif (Str::startsWith($path, "{$this->defaultTeamNamespace}/")) {
            $res = [$this->defaultTeamNamespace, substr($path, strlen($this->defaultTeamNamespace) + 1)];
        } else {
            $res = ['', $path];
        }

        $res[1] = $this->deNormalizeRepoPath($res[1]);

        return $res;
    }

    public function normalizeRepoPath($path)
    {
        return str_replace('/', '--', $path);
    }

    public function deNormalizeRepoPath($path)
    {
        return str_replace('--', '/', $path);
    }

    public function getOAuthProviderName(): string
    {
        return 'gitea';
    }
}
