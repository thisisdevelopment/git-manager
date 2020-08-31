<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use Gitlab\Exception\ExceptionInterface;
use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitBranch;

class GitLabBranch extends GitBranch
{
    /**
     * @var GitLabClient
     */
    protected $client;

    public function __construct(GitLabClient $client, GitLabRepository $repository, array $properties)
    {
        $properties['commitHash'] = $properties['commit']['id'] ?? null;

        $this->client = $client;
        parent::__construct($repository, $properties);
    }

    public function remove(): void
    {
        try {
            $this->client->repositories()->deleteBranch($this->repository->id, $this->name);
        } catch (ExceptionInterface $e) {
            throw new GitException("Unable to remove branch: {$e->getMessage()}", $e->getCode(), $e);
        }
    }

    public function getFile(string $file, $createIfNotExists = false): GitFileInterface
    {
        try {
            $fileInfo = [
                'file'     => $file,
                'contents' => $this->client->repositoryFiles()->getRawFile($this->repository->id, $file, $this->name),
                'exists'   => true
            ];
        } catch (ExceptionInterface $e) {
            if ($createIfNotExists && $e->getCode() === 404) {
                $fileInfo = [
                    'file'     => $file,
                    'contents' => '',
                    'exists'   => false,
                ];
            } else {
                throw new GitException("Unable to get file \"{$file}\" {$e->getMessage()}", $e->getCode(), $e);
            }
        }

        return new GitLabFile($this->client, $this, $fileInfo);
    }
}
