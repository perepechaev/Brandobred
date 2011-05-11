<?php

class PageException extends Exception
{
    const PAGE_REDIRECT                 = 302;
    const PAGE_NOT_FOUND                = 404;
    const PAGE_FORBIDDEN                = 403;
    const PAGE_INTERNAL_SERVER_ERROR    = 500;
    const PAGE_LINK_NOT_ACTIVE          = 410;
    const PAGE_BAD_REQUEST              = 400;
    
    const PAGE_ROUTER_NOT_SELECTED  = 1;
    
    protected $redirectUrl;
    
    public function handler(){
        switch ($this->getCode()){
            case PageException::PAGE_REDIRECT:
                $this->redirect($this->redirectUrl);
                return false;
                break;
                
            case PageException::PAGE_NOT_FOUND:
                header("HTTP/1.0 {$this->getCode()} Not Found");
                
                try{
                    Router::instance()->setUrl('/404/');
//                    $page = PageArticle::create();
//                    $page->setRouter( Router::instance() );
//                    $page->initialization();
//                    SiteSkeleton::instance()->setPage($page);
                    SiteSkeleton::instance()->execute();
                    SiteSkeleton::instance()->draw();
                    return false;
                }
                catch (Exception $e){
                    
                }
                
                break;
                
            case PageException::PAGE_FORBIDDEN:
                header("HTTP/1.0 {$this->getCode()} Forbidden");
                break;
                
            case PageException::PAGE_LINK_NOT_ACTIVE:
                header("HTTP/1.0 {$this->getCode()} Link no active");
                break;
                
            case PageException::PAGE_BAD_REQUEST:
                header("HTTP/1.0 {$this->getCode()} Bad Request");
                exception_log($this);
                break;
                
            case PageException::PAGE_INTERNAL_SERVER_ERROR:
            default:
                exception_log($this);
                header("HTTP/1.0 {$this->getCode()} Internal Server Error");
                break;
        }
        include PATH_TEMPLATE . "/page{$this->getCode()}.html";        
    }
    

    static public function pageNotFound(){
        throw new PageException("Страница не найдена", PageException::PAGE_NOT_FOUND);
    }
    
    static public function pageLinkNotActive(){
        throw new PageException("Ссылка не активна", self::PAGE_LINK_NOT_ACTIVE);
    }

    static public function pageRouterNotSelected(){
        throw new PageException("Page пытается получить доступ к Router, объект не найден", self::PAGE_ROUTER_NOT_SELECTED);
    }
    
    static public function pageForbidden(){
        throw new PageException("Доступ к странице запрещен", self::PAGE_FORBIDDEN);
    }
    
    static public function pageBadRequest(){
        throw new PageException("Ошибка в данных переданных пользователем", self::PAGE_BAD_REQUEST);
    }
    
    static public function pageInternalServerError(){
        throw new PageException("Внутрення ошибка сервера", self::PAGE_INTERNAL_SERVER_ERROR);
    }
    
    static public function pageRedirect($url){
        $e = new PageException("Страница временно перемещена на другой url: $url", self::PAGE_REDIRECT);
        $e->redirectUrl = $url;
        throw $e;
    }
    
    static public function pageBackRedirect(){
        $url = empty($_SERVER['HTTP_REFERER']) ? URL_PATH : $_SERVER['HTTP_REFERER'];
        $e = new PageException("Страница временно перемещена на другой url: ", self::PAGE_REDIRECT);
        $e->redirectUrl = $url;
        throw $e;
    }
    
    protected function redirect($url){
        header("HTTP/1.0 {$this->getCode()} Redirect Url");
        header("Location: {$this->redirectUrl}");
    }
    
}

?>