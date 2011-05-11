<?php

require_once(dirname(__FILE__) . '/../TestHead.php');
require_once(PATH_TEMPLATE_MODIFIERS . '/template_modify_wiki.php');

class TestTemplate extends Test
{
    public function test_wikiHref(){
        $this->detail(true);
        
        equal($this->replace('http://example.com/', 'XXX'));
        equal($this->replace('http://good.com', 'XXX'));
        equal($this->replace('http://good.ru/', 'XXX'));
        equal($this->replace('http://good.ру', 'XXX'));
        equal($this->replace('http://лепрекон.ру', 'XXX'));
        equal($this->replace('http://user1.лепрекон.ру', 'XXX'));
        equal($this->replace('http://user3.лепрекон.ру', 'XXX'));
        equal($this->replace('http://home.user3.лепрекон.ру', 'XXX'));
        equal($this->replace('http://home.user3.лепрекон.ру/test', 'XXX'));
        equal($this->replace('http://home.user3.лепрекон.ру/test/', 'XXX'));
        equal($this->replace('http://home.user3.лепрекон.ру/test/more/', 'XXX'));
        equal($this->replace('http://home.user3.лепрекон.ру/test/more/fuck.html more text', 'XXX more text'));
        equal($this->replace('http://home.user3.лепрекон.ру/test/more/fuck.html?edit=1 more text', 'XXX more text'));
        equal($this->replace('http://home.user3.лепрекон.ру/test/more/fuck.html?edit=1&amp;page=2&amp;page=3 more text', 'XXX more text'));
        
        equal($this->replace('[http:example.com]', 'XXX'));
        equal($this->replace(' [http:example.com]', ' XXX'));
        equal($this->replace('[http:example.com/]', 'XXX'));
        equal($this->replace('[http:example.com/path]', 'XXX'));
        equal($this->replace('[http:example.com/path/]', 'XXX'));
        equal($this->replace('[http:example.com/path.html]', 'XXX'));
        equal($this->replace('[http:example.com/path.html?page]', 'XXX'));
        equal($this->replace('[http:example.com/path.html?page=4&sort=8]', 'XXX'));
        equal($this->replace('[http:example.com/path?page=4&sort=8]', 'XXX'));
        equal($this->replace('[http:example.com/path/?page=4&sort=8]', 'XXX'));
        equal($this->replace(' [http:topas.local/moderating/?someparam=true&more=false]', ' XXX'));
        equal($this->replace('[http:topas.local/moderating/?someparam=true&more=false админка4]', 'XXX'));
        equal($this->replace('http://www.jino-net.ru', 'XXX'));
        equal($this->replace(' http://www.jino-net.ru', ' XXX'));
        equal($this->replace('s http://www.jino-ne-t.ru', 's XXX'));
        equal($this->replace('s http://www.virtual-laser-keyboard.com/', 's XXX'));
        equal($this->replace('s http://www.virtual-laser-keyboard.com/?page-count=4 suffix', 's XXX suffix'));
        equal($this->replace('s http://www.virtual-laser-keyboard.com/?page-count=4 suffix', 's XXX suffix'));
        equal($this->replace('s http://www.jino-net.ru suffix', 's XXX suffix'));
        
        equal($this->replace(' http://home.user3.лепрекон.fucking s', ' XXX s'));
        equal($this->replace('http://home.user3.лепрекон.fucking', 'XXX'));
        equal($this->replace('http://fuck/home.user3.лепрекон.ру', 'http://fuck/home.user3.лепрекон.ру'));
        equal($this->replace('http://fuck s', 'http://fuck s'));
        equal($this->replace('http://л.ру', 'http://л.ру'));
        
        equal($this->replace(
            '[http:topas.local/moderating/?someparam=true&more=false http://topas.local/moderating/?someparam=true&more=false ]',
            '[http:topas.local/moderating/?someparam=true&more=false "http://topas.local/moderating/?someparam=true&more=false":topas.local ]',
            'wiki_replace_href_title'
        ));
        
        $this->result('Replace href', 'ok');
    }
    
    public function test_wikiManyHref(){
        $text = <<<TEXT
ticle

[http:topas.local/moderating/ админка]
[http:topas.local/moderating/?someparam=true админка2]
[http:topas.local/moderating/?someparam=true&false админка3]
[http:topas.local/moderating/?someparam=true&more=false админка4]
[http:topas.local/moderating/?more=false админка5]
[http:topas.local/moderating/?more=true админка6]
[http:topas.local/moderating/?some=true админка7]
[http:topas.local/moderating/?someparam=true админка7]

 [http:topas.local/moderating/?someparam=true админка2]


[http:topas.local/moderating/?someparam=true&more=false]

[http:topas.local/moderating/?someparam=true&more=false http://]
[http:topas.local/moderating/?someparam=true&more=false http]

http://topas.local/moderating/?someparam=true&more=false
sdfadsfadsf

http://topas.local/moderating/?someparam=true&more=false
TEXT;
        $orig   = $text;
        $text   = htmlspecialchars($text);
        wiki_href($text,  array($this, 'wiki_replace_href_title'));
        $expect = <<<TEXT
ticle

"http://topas.local/moderating/":админка
"http://topas.local/moderating/?someparam=true":админка2
"http://topas.local/moderating/?someparam=true&amp;false":админка3
"http://topas.local/moderating/?someparam=true&amp;more=false":админка4
"http://topas.local/moderating/?more=false":админка5
"http://topas.local/moderating/?more=true":админка6
"http://topas.local/moderating/?some=true":админка7
"http://topas.local/moderating/?someparam=true":админка7

 "http://topas.local/moderating/?someparam=true":админка2


"http://topas.local/moderating/?someparam=true&amp;more=false":topas.local

"http://topas.local/moderating/?someparam=true&amp;more=false":http://
"http://topas.local/moderating/?someparam=true&amp;more=false":http

"http://topas.local/moderating/?someparam=true&amp;more=false":topas.local
sdfadsfadsf

"http://topas.local/moderating/?someparam=true&amp;more=false":topas.local
TEXT;
        equal($text === $expect);
        $this->result('Replace many href', 'ok');
    }
    
    public function wiki_replace_href($matches){
        return 'XXX';
    }
    
    public function wiki_replace_href_title($matches){
        return '"http://' . $matches[1] . '":' . (isset($matches[9]) ? $matches[9] : $matches[2]);
    }
    
    private function replace($href, $expect, $event = 'wiki_replace_href'){
        $text = <<<TEXT
$href
        
TEXT;
        $expect = <<<TEXT
$expect
        
TEXT;
        wiki_href($text,  array($this, $event));
        return ($text === $expect) ? true : dump("<<<< Result\n" . $text . "\n----- Expect\n" . $expect . "\n>>>>\n", true);
    }
    
}

Test::create('TestTemplate')->complete();

?>