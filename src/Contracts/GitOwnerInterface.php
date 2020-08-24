<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read mixed $id
 * @property-read string $name
 * @property-read string $description
 */
interface GitOwnerInterface
{
    public function update(array $properties): void;

    public function remove(): void;

    /**
     * @return GitRepositoryInterface[]
     */
    public function getRepositoryList(): array;

    public function addRepository(array $properties): GitRepositoryInterface;

    public function deActivate(): void;

    public function reActivate(): void;
}
