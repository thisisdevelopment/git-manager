<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $name
 */
interface GitBranchInterface
{
    public function getRepository(): GitRepositoryInterface;

    public function getFile(string $file, $createIfNotExists = false): GitFileInterface;

    public function remove(): void;
}
