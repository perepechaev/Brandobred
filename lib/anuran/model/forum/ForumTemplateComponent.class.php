<?php

class ForumTemplateComponent extends TemplateXml
{
    public function __construct(){
        $this->prepare( dirname(__FILE__) . '/ForumTemplateComponent.xml' );
    }

}

?>