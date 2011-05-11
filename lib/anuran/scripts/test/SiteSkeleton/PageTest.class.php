<?php

class PageTestDefaultWrapper extends Page
{
    public function header(){}
    public function footer(){}
	public function initialization(){}
}

class PageTestSelectedWrapper extends Page
{
    protected $wrapperName  = 'test_wrapper.php';

    public function header(){}
    public function footer(){}
	public function initialization(){}
}

class PageTestNotFoundWrapper extends Page
{
    protected $wrapperName  = 'not_found_wrapper_random41';

    public function header(){}
    public function footer(){}
	public function initialization(){}
}


?>