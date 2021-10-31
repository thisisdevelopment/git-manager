<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $username
 * @property-read string $email
 * @property-read string $created_at
 * @property-read string $last_sign_in_at
 * @property-read string $last_activity_on
 */
interface GitUserInterface extends GitOwnerInterface
{
    /**
     * @return GitTeamInterface[]
     */
    public function getTeamList(): array;
}
