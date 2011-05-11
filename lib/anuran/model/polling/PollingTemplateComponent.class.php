<?php

class PollingTemplateComponent extends TemplateXml
{
    public function __construct(){
        $this->prepare( dirname(__FILE__) . '/PollingTemplateComponent.xml' );
    }

}

?>