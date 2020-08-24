<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $file
 * @property-read string $contents
 * @property-read string $exists
 */
interface GitFileInterface
{
    public function getBranch(): gitBranchInterface;

    public function remove(string $message): void;

    public function rename(string $message, string $filename): void;

    public function update(string $message, array $properties): void;
}
