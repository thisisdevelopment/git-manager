<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $name
 * @property-read string defaultTeamNamespace
 * @property-read string defaultRepoNamespace
 */
interface GitPlatformInterface
{
    public function __construct(array $config);

    public function getOAuthProviderName(): string;

    /**
     * @param string|null $namespace
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(string $namespace = null): array;

    public function getRepository(string $idOrName, string $namespace = null): GitRepositoryInterface;

    public function addRepository(array $properties, ?GitOwnerInterface $owner = null): GitRepositoryInterface;

    /**
     * @param bool $onlyActive
     * @return GitUserInterface[]
     */
    public function getUserList($onlyActive = true): array;

    public function getUser(string $idOrName): GitUserInterface;

    public function addUser(array $properties): GitUserInterface;

    public function getCurrentUser(): GitUserInterface;

    /**
     * @param string|null $namespace
     * @return GitTeamInterface[]
     */
    public function getTeamList(string $namespace = null): array;

    public function getTeam(string $idOrName, string $namespace = null): GitTeamInterface;

    public function addTeam(array $properties): GitTeamInterface;
}
