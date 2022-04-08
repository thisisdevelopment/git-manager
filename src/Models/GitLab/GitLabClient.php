<?php
namespace ThisIsDevelopment\GitManager\Models\GitLab;

use Gitlab\Client;
use Gitlab\Exception\ExceptionInterface;
use Gitlab\HttpClient\Builder;
use Gitlab\ResultPager;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitLab\Plugins\GitLabLog;
use ThisIsDevelopment\GitManager\Models\GitLab\Plugins\GitLabSudo;

class GitLabClient extends Client
{
    public const TYPE_USERS = 'users';
    public const TYPE_PROJECTS = 'projects';
    public const TYPE_GROUPS = 'groups';
    public const TYPE_REPOSITORIES = 'repositories';
    public const TYPE_BRANCHES = 'branches';
    public const TYPE_TAGS = 'tags';
    public const TYPE_HOOKS = 'hooks';

    public const TYPE_MODEL_MAPPING = [
        self::TYPE_USERS    => GitLabUser::class,
        self::TYPE_PROJECTS => GitLabRepository::class,
        self::TYPE_GROUPS   => GitLabTeam::class,
        self::TYPE_BRANCHES => GitLabBranch::class,
        self::TYPE_TAGS => GitLabTag::class,
        self::TYPE_HOOKS => GitLabWebHook::class,
    ];

    public const ACCESS_LEVEL_GUEST = 10;
    public const ACCESS_LEVEL_REPORTER = 20;
    public const ACCESS_LEVEL_DEVELOPER = 30;
    public const ACCESS_LEVEL_MAINTAINER = 40;
    public const ACCESS_LEVEL_OWNER = 50;

    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_INTERNAL = 'internal';
    public const VISIBILITY_PUBLIC = 'public';

    /**
     * @var Builder
     */
    protected $gitlabHttpClientBuilder;

    public function __construct()
    {
        $this->gitlabHttpClientBuilder = new Builder();
        parent::__construct($this->gitlabHttpClientBuilder);
    }

    private function makeModelForType(string $type, array $properties, $parent = null)
    {
        $model = self::TYPE_MODEL_MAPPING[$type];
        return new $model($this, $parent, $properties);
    }

    public function getModelInstance($type, $id, $parent)
    {
        return $this->makeModelForType($type, $this->{$type}()->show($id), $parent);
    }

    public function getAllData($api, $method = 'all', $methodParameters = []): array
    {
        try {
            return (new ResultPager($this))->fetchAll($this->{$api}(), $method, $methodParameters);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to fetch all data for {$api}->{$method}: {$e->getMessage()}", $e->getCode(), $e
            );
        }
    }

    public function getAllModelInstances(
        $type,
        $parent = null,
        $api = null,
        $method = 'all',
        $methodParameters = []
    ): array {
        $res = [];
        foreach ($this->getAllData($api ?? $type, $method, $methodParameters) as $user) {
            $res[] = $this->makeModelForType($type, $user, $parent);
        }
        return $res;
    }

    public function addModelInstance($type, $requiredProperties, $extraProperties = null, $parent = null)
    {
        $api = $this->{$type}();
        $params = $requiredProperties;
        if ($extraProperties) {
            $params[] = $extraProperties;
        }

        try {
            $data = $api->create(...$params);
        } catch (ExceptionInterface $e) {
            throw new GitException("Failure calling {$type}->create: {$e->getMessage()}", $e->getCode(), $e);
        }

        return $this->makeModelForType($type, $data, $parent);
    }

    public function sudo(?int $user): void
    {
        $this->gitlabHttpClientBuilder->removePlugin(GitLabSudo::class);
        $this->gitlabHttpClientBuilder->addPlugin(new GitLabSudo($user));
    }

    public function enableLogging(): void
    {
        $this->gitlabHttpClientBuilder->addPlugin(new GitLabLog());
    }
}
