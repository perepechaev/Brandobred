<?php

require_once PATH_CORE . '/Router.class.php';


class TestRouterClient extends Router
{
    protected function rule(){
        $this->rule    = array(
            ROUTER_ROOT         => array('TestRouterController', 'mainRoot'),
            'test'				=> array('TestRouterController', 'main', false, array(
                'uri'				=> array('TestRouterController', 'testUri', false),
                'rule'				=> array('TestRouterController', 'testRule', false, array(
                    '(\html)'			=> array('TestRouterController', 'testRuleHtml', '%3')
                )),
            )),
            'article'           => array('TestRouterController', 	'listArticles', false, array(
                '(\html)'		    => array('TestRouterController',   'getArticleByTitle', '%2')
            )),
            'news'              => array('NewsController', 		'main'),
            'help'              => array('TestRouterController', 	'getMainHelp', false, array(
                '(\html)'		    => array('NewsController',      'getNewsByTitle', '%2')
            )),
            'catalog'           => array('TestRouterController',	'getCategoryList', false, array(
                '(\s)'   		    => array('TestRouterController',   'listGroupByTitle', '%2', array(
                    '(\html)'			=> array('TestRouterController',   'getElementByTitle', '%3')
                )),
            )),
            'not_controller'	=> array('NotFoundController',      'notMethod'),
            'auction'           => array('TestRouterController',	'listAuctions', false, array(
                'lot'				=> array('TestRouterController',   'listLotHistory', false, array(
                    '(\d)'				=> array('TestRouterController',   'getLotHistory', '%3', array(
                        'history.html'		=> array('TestRouterController',    'getLotHistory', '%3'),
                        'edit'  			=> array('TestRouterController',    'editLot',       '%3'),
                        'save'  			=> array('TestRouterController',    'saveLot',       '%3'),
                    )),
                )),
                'wins'			=> array('TestRouterController',   'listWins'),
            )),
            
            'manager'           => array('TestRouterControllerr',	'managerMain', false, array(
                'catalog'		 	=> array('TestRouterControllerr',    'managerCatalogMain', false, array(
                    '(\d)'				=> array('TestRouterControllerr',    'getCatalogElement',  '%2', array(
                        'edit'  			=> array('TestRouterControllerr',    'editCatalogElement',  '%2'),
                        'save'  			=> array('TestRouterControllerr',    'saveCatalogElement',	'%2'),
                    )),
                )), 
            )),
        );
    }
    
    public function reparse(){
        $this->__construct();
    }
}

class TestRouterController extends Controller
{
    public $state;
    public $params;
    
    
    public function __call($name, $params){
        $this->state  = $name;
        $this->params = $params;
    }
    
    public function getArticleByTitle($title){
        $this->state  = 'getArticleByTitle';
        $this->params = $title; 
    }
    
    static public function create(){
        return new TestRouterController();
    }
}

class TestRouterPage extends Page
{
    public function initialization(){
        
    }
    
    public function header(){
        
    }
    
    public function footer(){}
    
    /**
     * @return TestRouterPage
     */
    static public function create(){
        return new TestRouterPage();
    }
}

?>