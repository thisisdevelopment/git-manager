<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $name
 * @property-read string $commitHash
 */
interface GitBranchInterface
{
    public function getRepository(): GitRepositoryInterface;

    public function getFile(string $file, $createIfNotExists = false): GitFileInterface;

    public function remove(): void;
}
