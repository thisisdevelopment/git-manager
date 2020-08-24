<?php

namespace ThisIsDevelopment\GitManager\Tests\Integration;

use Illuminate\Support\Str;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Exceptions\GitException;
use ThisIsDevelopment\GitManager\Services\GitPlatformManager;
use Tests\TestCase;

class GitPlatformTest extends TestCase
{
    /** @var GitPlatformInterface */
    protected $platform;

    const TEST_PREFIX = 'test-';

    private static function getPlatform()
    {
        $res = app(GitPlatformManager::class)->platform(config('site.deployment.git_platform'));
        $res->enableLogging();
        return $res;
    }

    public function testAll(): void
    {
        $this->markTestSkipped('Disabled by default, due to local gitlab requirements');
        $this->platform = static::getPlatform();


        $currentUser = $this->platform->getCurrentUser();
        $this->assertNotEmpty($currentUser->username);
        $this->assertNotEmpty($currentUser->name);

        foreach ($this->platform->getUserList(true) as $user) {
            if (Str::startsWith($user->name, 'test-user-')) {
                $user->remove();
            }
        }

        foreach ($this->platform->getTeamList() as $team) {
            if (Str::startsWith($team->name, 'test-team-')) {
                $team->remove();
            }
        }

        //TODO: gitlab has async deletes .. so we need to wait here.
        sleep(3);

        $user1 = $this->platform->addUser([
            'name' => 'test-user-1',
            'email' => 'a1@b.com',
            'username' => 'test-user-1'
        ]);

        $user2 = $this->platform->addUser([
            'name' => 'test-user-2',
            'email' => 'b@b.com',
            'username' => 'test-user-2'
        ]);

        $user3 = $this->platform->addUser([
            'name' => 'test-user-3',
            'email' => 'a3@b.com',
            'username' => 'test-user-3'
        ]);

        $team1 = $this->platform->addTeam(['name' => static::TEST_PREFIX . 'team-1']);
        $team2 = $this->platform->addTeam(['name' => static::TEST_PREFIX . 'team-2']);
        $team3 = $this->platform->addTeam(['name' => static::TEST_PREFIX . 'team-3']);

        $team1->addMember($user1);
        $team2->addMember($user2);
        $team3->addMember($user3);

        $numExisting = count($this->platform->getRepositoryList());

        $repo1 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-1'], $team1);
        $repo2 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-2'], $team2);
        $repo3 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-3'], $team3);

        $repo4 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-4'], $user1);
        $repo5 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-5'], $user2);
        $repo6 = $this->platform->addRepository(['name' => static::TEST_PREFIX . 'repo-6'], $user3);

        $this->assertCount(6 + $numExisting, $this->platform->getRepositoryList());

        $this->assertCount(2, $user1->getRepositoryList());
        $this->assertCount(2, $user2->getRepositoryList());
        $this->assertCount(2, $user3->getRepositoryList());

        $this->assertCount(6 + $numExisting, $this->platform->getRepositoryList());

        $this->assertCount(0, $repo1->getUserAccessList());
        $this->assertCount(1, $repo1->getTeamAccessList());

        $this->assertCount(0, $repo2->getUserAccessList());
        $this->assertCount(1, $repo2->getTeamAccessList());

        $this->assertCount(0, $repo3->getUserAccessList());
        $this->assertCount(1, $repo3->getTeamAccessList());

        $this->assertCount(1, $repo4->getUserAccessList());
        $this->assertCount(0, $repo4->getTeamAccessList());

        $this->assertCount(1, $repo5->getUserAccessList());
        $this->assertCount(0, $repo5->getTeamAccessList());

        $this->assertCount(1, $repo6->getUserAccessList());
        $this->assertCount(0, $repo6->getTeamAccessList());

        $repo1->grantTeamAccess($team2);

        $this->assertCount(2, $repo1->getTeamAccessList());

        $repo1->grantTeamAccess($team3);

        $this->assertCount(3, $repo1->getTeamAccessList());

        $this->assertCount(2, $team2->getRepositoryList());
        $this->assertCount(2, $team3->getRepositoryList());

        $this->assertCount(3, $user2->getRepositoryList());
        $this->assertCount(3, $user3->getRepositoryList());

        $repo1->revokeTeamAccess($team2);

        $this->assertCount(2, $repo1->getTeamAccessList());

        $repo1->revokeTeamAccess($team3);

        $this->assertCount(1, $repo1->getTeamAccessList());

        $this->assertCount(1, $team2->getRepositoryList());
        $this->assertCount(1, $team3->getRepositoryList());

        $this->assertCount(2, $user2->getRepositoryList());
        $this->assertCount(2, $user3->getRepositoryList());

        $file = $repo1->getFile('readme.md', 'master', true);
        $file->update('test update', ['contents' => '*** README ***']);

        $file->rename('rename file', 'moved/readme.md');

        $file->remove('test remove file');

        $this->expectException(GitException::class);
        $repo1->revokeTeamAccess($team1);

        $this->expectException(GitException::class);
        $this->expectExceptionCode(404);
        $repo1->getFile('readme.md', 'master', false);
    }
}
