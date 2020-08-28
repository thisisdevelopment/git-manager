```
$platform = new \ThisIsDevelopment\GitManager\Models\Gitea\GiteaPlatform(['defaultTeamNamespace' => env('GITEA_TEAM'), 'defaultRepoNamespace' => env('GITEA_TEAM'), 'url' => env('GITEA_URI'), 'auth' => env('GITEA_ACCESS_TOKEN')]);
$platform = new \ThisIsDevelopment\GitManager\Models\GitLab\GitLabPlatform(['defaultTeamNamespace' => env('GITLAB_TEAM'), 'defaultRepoNamespace' => env('GITLAB_REPO'), 'url' => env('GITLAB_URI'), 'auth' => env('GITLAB_ACCESS_TOKEN')]);
```
