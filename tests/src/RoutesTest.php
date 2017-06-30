<?php

namespace ZeusTest;

class RoutesTest extends \PHPUnit_Framework_TestCase
{

    private $url;

    protected function setUp()
    {
        $this->url = json_decode(file_get_contents('test.json'))->url;
    }

    private function executeURL($url = '')
    {
        $ch = curl_init($this->url . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($ch);
    }

    public function testUpdateRoutes()
    {
        if (file_exists('routes.json')) {
            unlink('routes.json');
        }
        $ret = $this->executeURL('routes/update');
        $this->assertEquals('Routes updated.', $ret);
        $this->assertTrue(file_exists('routes.json'), 'Routes json not created.');
    }

    public function testIndex()
    {
        $ret = $this->executeURL();
        $this->assertEquals('Managing posts', $ret);
    }

    public function testManagePost()
    {
        $ret = $this->executeURL('post');
        $this->assertEquals('Managing posts', $ret);
    }

    public function testNewPost()
    {
        $ret = $this->executeURL('post/new');
        $this->assertEquals('New post', $ret);
    }

    public function testEditPost()
    {
        $ret = $this->executeURL('post/edit/1');
        $this->assertEquals('Editing post 1', $ret);
    }

    public function testEditFullPost()
    {
        $ret = $this->executeURL('post/edit/the_title/2');
        $this->assertEquals('Editing full post 2 with name the_title', $ret);
    }

    public function testShowPost()
    {
        $ret = $this->executeURL('post/show/3');
        $this->assertEquals('Showing post 3', $ret);
    }

}
