<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_CORE . '/SiteSkeleton.class.php');

class TestPage extends Test
{
    public function test_setUri(){
        $page   = new PageTest();

        if ($page->getUri() !== '/'){
            throw new Exception("Not insitialization uri in Page object");
        }

        $testMap    = array(
            '/test/'        => '/test/',
            '/test'         => '/test/',
            'test/'         => '/test/',
            'test'          => '/test/',
            '/test/user/'   => '/test/user/',
            '/test/user'    => '/test/user/',
            'test/user/'    => '/test/user/',
            'test/user'     => '/test/user/',
            't/u/r//d'      => '/t/u/r/d/',
            '/'             => '/',
            ''              => '/',
            '/test/?user=4' => '/test/?user=4',
            'test/?user=4'  => '/test/?user=4',
            '?user=4'       => '/?user=4',
            'a.php?user=4'  => '/a.php?user=4',
            '/?u=3&b=%s2'   => '/?u=3&b=%25s2', // XXX
            'index.php'     => '/index.php',
            'index.php?'    => '/index.php',
            'index.php?a=5' => '/index.php?a=5',
        );

        array_map(array($this, 'compareUri'), array_keys($testMap), array_values($testMap));

        $this->result("Test set Uri", 'ok');
    }

	public function initialization(){
	}

    public function test_MakeUrl(){
        $page   = new PageTest();

        $url    = '/';
//        if ((strpos($url, 'http://') !== 0) && (strpos($url, 'https://') !== 0)){
//            throw new Exception("Url site is not valid, please, check your config.php: URL_SITE = " . var_export(URL_SITE, true));
//        }

        if (($url !== $page->makeUrl())) {
            throw new Exception("Test empty makeUrl failed: ".$page->makeUrl());
        }

        $wrong  = false;
        try {
            $page->makeUrl(array('/s' => 'wrongParameter'));
            $wrong  = true;
        }
        catch (Exception $e){}
        if ($wrong) {
            throw new Exception("Test for wrong parameter failed");
        }

        $testMap    = array(
            $url                    => array(),
            $url                    => array('action' => '/'),
            $url                    => array('action' => ''),
            $url                    => array('action' => false),
            $url                    => array('action' => null),
            $url.'test/'            => array('action' => 'test'),
            $url.'test/'            => array('action' => '/test'),
            $url.'test/'            => array('action' => '/test/'),
            $url.'test/'            => array('action' => 'test/'),
            $url.'test/'            => array('action' => 'test///'),
            $url.'t///a/'           => array('action' => 't///a'),
            $url.'t/a/b/c/'         => array('action' => 't/a/b/c'),
            $url.'t/a/b/c/'         => array('action' => 't/a/b/c/'),
            $url.'?a=AA&b=BB'       => array('a' => 'AA', 'b' => 'BB'),
            $url.'?a=%2FA&b=BB'     => array('a' => '/A', 'b' => 'BB'),
        );
        array_map(array($this, 'compareUrl'), array_values($testMap), array_keys($testMap));



        $this->result("Test make Url", 'ok');
    }

    public function test_basePage(){
        $page   = new PageTest();

        if ($page->getBase() !== '/'){
            throw new Exception("Expected base '/' but not {$page->getBase()}");
        }
        
        $this->result('Base url', 'ok');
    }
    
    public function test_buildUrl(){
        $page   = new PageTest();
        $this->detail(true);
        
        $url    = $page->buildUrl('/');
        equal($url === '/');
        
        $url    = $page->buildUrl('/new/');
        equal($url === '/new/');

        $url    = $page->buildUrl('/user/guru/', array('auth_false' => 1));
        equal($url === '/user/guru/?auth_false=1', var_export($url, true));
        
        $url    = $page->buildUrl('/user/guru/?page=10', array());
        equal($url === '/user/guru/?page=10');
        
        $url    = $page->buildUrl('/user/guru/?auth_false=1', array('auth_false' => NULL));
        equal($url === '/user/guru/', var_export($url, true));
        
        $this->result('Build url', 'ok');
    }

    private function compareUri($set, $get){
        $page   = new PageTest();
        $page->setUri($set);
        if ($page->getUri() !== $get){
            throw new Exception("Expected value '$get' but not '{$page->getUri()}'");
        }
    }

    private function compareUrl($set, $get){
        $page   = new PageTest();
        $url    = $page->makeUrl($set);
        if ($url !== $get) {
            throw new Exception("Error make Url, expected value '$get' but not '$url'");
        }
    }

}

// XXX: Подозрение на неправильрую работу.
$test   = new TestPage();
$test->complete();