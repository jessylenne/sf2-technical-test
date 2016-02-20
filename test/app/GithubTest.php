<?php

class GithubTest extends PHPUnit_Framework_TestCase
{
    public function testSearch()
    {
        $results = Github::searchAccounts('jessylenne');
        $this->assertTrue(Validate::isNonEmptyArray($results));
        $this->assertGreaterThanOrEqual(1, $results['total_count']);
    }

    public function testRetrieveProfile()
    {
        $profile = Github::getUser('jessylenne');
        $this->assertEquals('jessylenne', $profile['login']);
    }

    public function testRepositories()
    {
        $results = Github::getUserRepositories('jessylenne');
        $this->assertTrue(Validate::isNonEmptyArray($results));
        $this->assertGreaterThanOrEqual(1, sizeof($results));
    }
}