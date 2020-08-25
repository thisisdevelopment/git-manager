<?php

namespace ThisIsDevelopment\GitManager\Models\Gitlab\Plugins;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

class GitLabSudo implements Plugin
{
    /**
     * @var int|null
     */
    protected $sudo;

    public function __construct(?int $userId)
    {
        $this->sudo = $userId;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        if ($this->sudo !== null) {
            $request = $request->withHeader('SUDO', $this->sudo);
        }

        return $next($request);
    }
}
