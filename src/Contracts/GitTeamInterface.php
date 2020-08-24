<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $namespace
 */
interface GitTeamInterface extends GitOwnerInterface
{
    /**
     * @return GitUserInterface[]
     */
    public function getMemberList(): array;

    public function addMember(GitUserInterface $user): void;

    public function removeMember(GitUserInterface $user): void;
}
