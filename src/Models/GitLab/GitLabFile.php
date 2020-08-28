<?php

namespace ThisIsDevelopment\GitManager\Models\GitLab;

use Gitlab\Exception\ExceptionInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Models\GitFile;

class GitLabFile extends GitFile
{
    /**
     * @var GitLabClient
     */
    protected $client;
    /**
     * @var GitLabBranch
     */
    protected $branch;
    /**
     * @var GitLabRepository
     */
    protected $repository;

    public function __construct(GitLabClient $client, GitLabBranch $branch, array $properties)
    {
        $this->client = $client;
        parent::__construct($branch, $properties);
    }

    protected function doUpdate(string $message): void
    {
        $params = [
            'content'        => $this->contents,
            'file_path'      => $this->file,
            'branch'         => $this->branch->name,
            'commit_message' => $message
        ];

        try {
            if ($this->exists) {
                $this->client->repositoryFiles()->updateFile($this->repository->id, $params);
            } else {
                $this->client->repositoryFiles()->createFile($this->repository->id, $params);
            }
        } catch (ExceptionInterface $e) {
            throw new GitException(
                sprintf("Unable to %s file '{$this->file}': {$e->getMessage()}", $this->exists ? 'update' : 'create'),
                $e->getCode(),
                $e
            );
        }
    }

    public function remove(string $message): void
    {
        $params = [
            'file_path'      => $this->file,
            'branch'         => $this->branch->name,
            'commit_message' => $message
        ];

        try {
            $this->client->repositoryFiles()->deleteFile($this->repository->id, $params);
        } catch (ExceptionInterface $e) {
            throw new GitException(
                "Unable to remove file '{$this->file}': {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    public function rename(string $message, string $filename): void
    {
        $properties = $this->data;
        $properties['file'] = $filename;
        $properties['exists'] = false;

        //TODO: use commit api to make this a single commit
        $clone = new GitLabFile($this->client, $this->branch, $properties);
        $clone->update($message, []);

        $this->remove($message);

        $this->data['file'] = $filename;
        $this->data['exists'] = true;
    }
}
