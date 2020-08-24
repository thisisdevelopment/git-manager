<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $username
 * @property-read string $email
 * @property-read string $created_at
 */
interface GitUserInterface extends GitOwnerInterface
{
    /**
     * @return GitTeamInterface[]
     */
    public function getTeamList(): array;
}
