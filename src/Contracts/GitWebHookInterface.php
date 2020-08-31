<?php

namespace ThisIsDevelopment\GitManager\Contracts;

/**
 * @property-read string $type
 */
interface GitWebHookInterface
{
    public const EVENT_PUSH = 'event_push';
    public const EVENT_PUSH_TAG = 'event_push_tag';
}
