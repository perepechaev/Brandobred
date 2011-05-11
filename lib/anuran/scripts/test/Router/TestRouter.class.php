<?php

require_once(dirname(__FILE__) . '/../TestHead.php');

class TestRouter extends Test
{
    protected $detail   = false;

    public function __construct(){
        
    }
    
    public function test_instance(){
        
        $this->detail(true);

        // Не установлен каталог с клиентом
        try {
            Router::instance( 'TestRouterClient' );
            equal(false, 'RouterException::NOT_SET_PATH_TO_CLIENT не сработал');
        }
        catch ( RouterException $e){
            if ($e->getCode() !== RouterException::NOT_SET_PATH_TO_CLIENT) throw $e;
        }
        
        Router::setPathClient( dirname(__FILE__) );
        
        // Не установлен урл
        try {
            Router::instance( 'TestRouter' );
            equal(false, 'RouterException::NOT_FIND_CLIENT_FILE не сработал');
        }
        catch ( RouterException $e){
            if ($e->getCode() !== RouterException::NOT_SET_URL) throw $e;
        }
        
        Router::setUrl( '/test/uri/' );
        
        // Не найден файл с клиентом
        try {
            Router::instance( 'TestRouterClientNotFound' );
            equal(false, 'RouterException::NOT_FIND_CLIENT_FILE не сработал');
        }
        catch ( RouterException $e){
            if ($e->getCode() !== RouterException::NOT_FIND_CLIENT_FILE) throw $e;
        }
        
        // Не был выбран клиент
        try {
            Router::instance();
            equal(false, 'RouterException::NOT_SELECT_CLIENT не сработал');
        }
        catch ( RouterException $e){
            if ($e->getCode() !== RouterException::NOT_SELECT_CLIENT) throw $e;
        }
        
        Router::instance( 'TestRouterClient' );
        
        assert(Router::instance() instanceof Router);
        
        // Попытка переопредления клиента
        try {
            Router::instance( 'TestRouterClientNotFound' );
        }
        catch ( RouterException $e){
            if ($e->getCode() !== RouterException::CLIENT_ALREDY_SELECTED) throw $e;
        }
        
        assert(Router::instance() instanceof TestRouterClient);
        
        $this->result('Router Intstance', 'ok');
    }
    
    public function test_setUrl(){
        Router::setUrl('/');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 1);
        equal($structure[0] === ROUTER_ROOT);
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'mainRoot' );
        equal( Router::instance()->getParams() === null, '$router->getParams !== false === ' . var_export(Router::instance()->getParams(), true));
                
        Router::setUrl($url = '/test');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 1);
        equal($structure[0] === 'test');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'main' );
        equal( Router::instance()->getParams() === null, '$router->getParams !== false === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 1);
        equal($structure[0] === 'test');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'main' );
        equal( Router::instance()->getParams() === null, '$router->getParams !== false === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 2);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRule' );
        equal( Router::instance()->getParams() === null, '$router->getParams !== false === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 2);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRule' );
        equal( Router::instance()->getParams() === null, '$router->getParams !== false === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/some.html');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal($structure[2] === 'some');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRuleHtml' );
        equal( Router::instance()->getParams() === 'some', '$router->getParams !== "some" === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/русскийурл.html');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal($structure[2] === 'русскийурл');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRuleHtml' );
        equal( Router::instance()->getParams() === 'русскийурл', '$router->getParams !== "русскийурл" === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/русский урл.html');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal($structure[2] === 'русский урл');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRuleHtml' );
        equal( Router::instance()->getParams() === 'русский урл', '$router->getParams !== "русский урл" === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/Русский урл.html');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3);
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal($structure[2] === 'Русский урл', '$structure[2] !== "Русский урл" === ' . var_export($structure[2], true));
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRuleHtml' );
        equal( Router::instance()->getParams() === 'Русский урл', '$router->getParams !== "Русский урл" === ' . var_export(Router::instance()->getParams(), true));
        
        Router::setUrl($url = '/test/rule/some.html?page=1');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3, '3 !== count($structure) === ' . count($structure) . "\n" . var_export($structure, true));
        equal($structure[0] === 'test');
        equal($structure[1] === 'rule');
        equal($structure[2] === 'some', "some !== {$structure[2]}");
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'testRuleHtml' );
        equal( Router::instance()->getParams() === 'some', '$router->getParams !== "some" === ' . var_export(Router::instance()->getParams(), true));

        Router::setUrl($url = '/catalog/машины/');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 2, '2 !== count($structure) === ' . count($structure) . "\n" . var_export($structure, true));
        equal($structure[0] === 'catalog');
        equal($structure[1] === 'машины');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'listGroupByTitle' );
        equal( Router::instance()->getParams() === 'машины', '$router->getParams !== "машины" === ' . var_export(Router::instance()->getParams(), true));

        Router::setUrl($url = '/catalog/телефоны/samsung.html');
        $structure    = Router::instance()->getUrlStructure();
        equal(is_array($structure));
        equal(count($structure) === 3, '3 !== count($structure) === ' . count($structure) . "\n" . var_export($structure, true));
        equal($structure[0] === 'catalog');
        equal($structure[1] === 'телефоны');
        equal($structure[2] === 'samsung');
        equal(get_class( Router::instance()->getController() ) === 'TestRouterController');
        equal( Router::instance()->getMethod() === 'getElementByTitle' );
        equal( Router::instance()->getParams() === 'samsung', '$router->getParams !== "samsung" === ' . var_export(Router::instance()->getParams(), true));
        
        try{
            Router::setUrl($url = '/catalog/машины/notfound');
            equal(false);
        }
        catch (RouterException $e){
            if ($e->getCode() !== RouterException::NOT_FOUND_METHOD) throw $e;
        }
        
        try{
            Router::setUrl($url = '/catalog/машины/notfound/');
            equal(false);
        }
        catch (RouterException $e){
            if ($e->getCode() !== RouterException::NOT_FOUND_METHOD) throw $e;
        }
        
        try{
            Router::setUrl('/article/Пингвины');
            equal(false);
        }
        catch (RouterException $e){
            if ($e->getCode() !== RouterException::NOT_FOUND_METHOD) throw $e;
        }
        
        Router::setUrl('/article/Пингвины.html');
        $router    = Router::instance();
        
        equal($router->getPageName() === 'article', "Неправильно выбрана страница: " . $router->getPageName());
        equal( get_class($router->getController()) === 'TestRouterController');
        
        $this->result('Router::setUrl()', 'ok');
    }
    
    public function test_parse(){
        Router::setUrl('/article/Пингвины.html');
        
        $router     = Router::instance();
        $page       = TestRouterPage::create();
        $controller = $router->getController();
        
        $page->setRouter($router);
        $page->prepare();
        $page->execute();
        
        equal($controller->state === 'getArticleByTitle', 'getArticleByTitle !== ' . $controller->state);
        equal($controller->params === 'Пингвины');
        
        Router::setUrl('/article/' . urlencode('Пингвины "что-то новое" <br/>') . '.html');
        
        $router     = Router::instance();
        $page       = TestRouterPage::create();
        $controller = $router->getController();
        
        $page->setRouter($router);
        $page->prepare();
        $page->execute();
        
        equal($controller->state === 'getArticleByTitle', 'getArticleByTitle !== ' . $controller->state);
        equal($controller->params === 'Пингвины "что-то новое" <br/>', var_export($controller->params, true));
        
        Router::setUrl('/catalog/телефоны/GPhone.html');
        $page->execute();
        equal($controller->state === 'getElementByTitle', 'getElementByTitle !== ' . $controller->state);
        equal($controller->params === array('GPhone'));
        
        Router::setUrl('/auction/lot/34/history.html');
        $page->execute();
        equal($controller->state === 'getLotHistory', 'getLotHistory !== ' . $controller->state);
        equal($controller->params === array('34'), 'array(34) !== ' . var_export($controller->params, true));
        
        $this->result('$router->parse()', 'ok');
    }
    
    public function test_generateTemplateLink(){
        require_once(PATH_TEMPLATE_MODIFIERS . '/template_modify_link.php');
        $link   = template_modify_link('faq?/', 'article', '{field}');
        equal($link === ($test = '/article/faq%3F.html'), var_export($link, true) . ' !== ' . var_export($test, true));
        
        Router::setUrl($link);
        
        $router     = Router::instance();
        $page       = TestRouterPage::create();
        $controller = $router->getController();
        
        $page->setRouter($router);
        $page->prepare();
        $page->execute();
                
        equal($controller->state === 'getArticleByTitle', 'getArticleByTitle !== ' . $controller->state);
        equal($controller->params === 'faq?', var_export($controller->params, true));
        
        $this->result('Generate link', 'ok');
    }
    
    private function testUrl($url){
        Router::setUrl($url);
        
        $router     = Router::instance();
        $page       = TestRouterPage::create();
        $controller = $router->getController();
        
        $page->setRouter($router);
        $page->prepare();
        $page->execute();
                
        return $controller->params;
    }
    
    public function test_parseWithPath(){
        
        Router::setUrlPath('/pathto/site/');
        Router::setUrl('/pathto/site/test/uri');
        Router::instance()->reparse();
        equal(get_class(Router::instance()->getController()) === 'TestRouterController');
        equal(Router::instance()->getMethod() === 'testUri');
        
        $this->result('$router->parse() with URL_PATH', 'ok');
        Router::setUrlPath('/');
    }

    public function test_getPageName(){
        Router::setUrl('/test');
        $router    = Router::instance();
        equal($router->getPageName() === 'test', "Неправильно выбрана страница: " . $router->getPageName());
        $this->result('Get page name', 'ok');
    }
    
    public function test_notFoundController(){
        Router::setUrl('/not_controller');
        $page       = TestRouterPage::create();
        $page->setRouter( Router::instance() );
        try {
            $page->prepare();
            $page->execute();
            equal(false, "Ожидается исключительная ситуация FileException::CANT_READ_FILE");
        } 
        catch (FileException $e){
            if ($e->getCode() !== FileException::CANT_READ_FILE) throw $e;
        }
        
        try {
            Router::setUrl('/test/uri/notfound/');
            equal(false);
        }
        catch (RouterException $e){
            if ($e->getCode() !== RouterException::NOT_FOUND_METHOD) throw $e;
        }
        
        $this->result("Not found controller", 'ok');
    }
    
    public function test_makeRootUrl(){
        $url = Router::instance()->makeUrl( '/' );
        equal($url === URL_PATH, '($url !== URL_PATH) ==('. $url . ' !== ' . URL_PATH . ')');
        
        $url = Router::instance()->makeUrl( 'index' );
        equal($url === URL_PATH . 'index/');

        $url = Router::instance()->makeUrl( array('index') );
        equal($url === URL_PATH . 'index/');
        
        $this->result('$router->makeUrl', 'ok');
    }
    
    public function test_makeHtmlUrl(){
        $url = Router::instance()->makeUrl(array('catalog', 'mobile', 'SiemensC60'));
        equal($url === '/catalog/mobile/SiemensC60.html', var_export($url, true));
        
        $this->result('Make html url', 'ok');
    }
    
    public function __destruct(){
        
    }

}

$test = new TestRouter();
$test->complete();

?>
