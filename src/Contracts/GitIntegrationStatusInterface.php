<?php

namespace ThisIsDevelopment\GitManager\Contracts;

interface GitIntegrationStatusInterface
{
    //TODO: convert this to enum?
    public const STATUS_NONE     = 'none';
    public const STATUS_ARCHIVED = 'archived';
    public const STATUS_CREATING = 'creating';
    public const STATUS_CREATED  = 'created';
    public const STATUS_FAILED   = 'failed';
}
