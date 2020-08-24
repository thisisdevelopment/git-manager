<?php

use ThisIsDevelopment\GitManager\Models\Gitea\GiteaPlatform;
use ThisIsDevelopment\GitManager\Models\Gitlab\GitLabPlatform;

return [
    'dev' => [
        'type'                 => GiteaPlatform::class,
        'defaultTeamNamespace' => env('GITEA_API_ORG'),
        'defaultRepoNamespace' => env('GITEA_API_ORG'),
        'url'                  => env('GITEA_API_URL'),
        'auth'                 => env('GITEA_API_TOKEN'),
        'CICD'                 => [
            'type' => 'custom',
            'url'  => '',
            'auth' => '',
        ],
    ],
    'acc' => [
        'type'                 => GitLabPlatform::class,
        'defaultTeamNamespace' => env('GITLAB_API_TEAM_PREFIX'),
        'defaultRepoNamespace' => env('GITLAB_API_REPO_PREFIX'),
        'url'                  => env('GITLAB_API_URL'),
        'auth'                 => env('GITLAB_API_TOKEN'),
        'CICD'                 => [
            'type' => 'internal'
        ]
    ],
    'prd' => [
        'type'                 => GitLabPlatform::class,
        'defaultTeamNamespace' => env('GITLAB_API_TEAM_PREFIX'),
        'defaultRepoNamespace' => env('GITLAB_API_REPO_PREFIX'),
        'url'                  => env('GITLAB_API_URL'),
        'auth'                 => env('GITLAB_API_TOKEN'),
        'CICD'                 => [
            'type' => 'internal'
        ]
    ],
];
