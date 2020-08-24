<?php

namespace ThisIsDevelopment\GitManager\Models\Gitlab\Plugins;

use Http\Client\Common\Plugin;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\RequestInterface;

class GitLabLog implements Plugin
{
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        Log::debug("=> {$request->getMethod()} {$request->getUri()}");
        Log::debug("=> {$request->getBody()}");

        return $next($request);
    }
}
