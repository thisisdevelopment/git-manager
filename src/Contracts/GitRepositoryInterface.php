<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read mixed $id
 * @property-read string $name
 * @property-read string $description
 * @property-read string $namespace
 * @property-read bool $archived
 * @property-read string $clone_url_ssh
 * @property-read string $clone_url_http
 */
interface GitRepositoryInterface
{
    public function getFile(string $file, string $branch, $createIfNotExists = false): GitFileInterface;

    public function addFile(string $file, string $branch, string $message, string $contents): GitFileInterface;

    /**
     * @return GitUserInterface[]
     */
    public function getUserAccessList(): array;

    public function grantUserAccess(GitUserInterface $user): void;

    public function revokeUserAccess(GitUserInterface $user): void;

    /**
     * @return GitTeamInterface[]
     */
    public function getTeamAccessList(): array;

    public function grantTeamAccess(GitTeamInterface $team): void;

    public function revokeTeamAccess(GitTeamInterface $team): void;

    public function archive(): void;

    public function unArchive(): void;

    public function rename(string $name): void;

    public function remove(): void;

    public function update(array $properties): void;

    /**
     * @return GitBranchInterface[]
     */
    public function getBranchList(): array;

    public function addBranch(array $properties): GitBranchInterface;

    public function getBranch(string $id): GitBranchInterface;

    public function getOwner(): GitOwnerInterface;

    public function transferTo(GitOwnerInterface $owner): void;

    public function runCICD(string $commitRef, array $variables = []): void;
}
