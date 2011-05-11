<?php

class Livejournal
{
    private $id;
    private $pwd;
    private $url;
    
    private $message;
    private $title;
    
    public function __construct($id, $pwd, $url){
        $this->id   = $id;
        $this->pwd  = $pwd;
        $this->url  = $url;
    }

    public function setMessage($message){
        $this->message = $message;
    }
    
    public function setTitle($title){
        $this->title = $title;
    }
    
    public function send(){
        
        $request = xmlrpc_encode_request("LJ.XMLRPC.getchallenge", array(
            'username'      => 'tttz',
            'auth_method'   => 'challenge',
            'lastupdate'    => time()
        ));
        $context = stream_context_create(array('http' => array(
            'method' => "POST",
            'header' => "Content-Type: text/xml",
            'content' => $request
        )));
        
        $file = file_get_contents($this->url, false, $context);
        $response = xmlrpc_decode($file);
        $this->validateChallenge($request, $response);
        
        $request = $this->getRequest($response);
        
        $context = stream_context_create(array('http' => array(
            'method'    => "POST",
            'header'    => "Content-Type: text/xml; charset=utf-8",
            'content'   => $request
        )));
        
        
        $file = file_get_contents($this->url, false, $context);
        
        $response = xmlrpc_decode($file);
        $this->validateChallenge($request, $response);
    }
    
    private function validateChallenge($request, $response){
        if ($response && xmlrpc_is_fault($response)) {
            exception_log( 
                new Exception(
                    "Не смог авторизоваться в Livejournal: \nRequest:\n" 
                    . print_r($request, true)
                    . "\nResponse:\n"
                    . print_r($response, true)
                )
            );
        }
    }
    
    private function getRequest($info){
        preg_match('/^http:\/\/([\w\d_]+)\./i', $this->id, $match);
        $username = $match[1];
        
        $title = $this->title; 
        $text = $this->message; 
        $year = date("Y"); 
        $month = date("m"); 
        $day = date("d"); 
        $hour = date("H"); 
        $min = date("i"); 
        
        return '<?xml version="1.0" encoding="utf-8"?> 
<methodCall> 
  <methodName>LJ.XMLRPC.postevent</methodName> 
  <params> 
    <param> 
      <value> 
        <struct> 
          <member> 
            <name>username</name> 
            <value> 
              <string>'.$username.'</string> 
            </value> 
          </member> 
          <member> 
            <name>auth_method</name> 
            <value> 
              <string>challenge</string> 
            </value> 
          </member> 
          <member> 
            <name>auth_challenge</name> 
            <value> 
              <string>' . $info['challenge'] . '</string> 
            </value> 
          </member> 
          <member> 
            <name>auth_response</name> 
            <value> 
              <string>' . md5($info['challenge'].md5($this->pwd)) . '</string> 
            </value> 
          </member>
          <member>
            <name>ver</name>
            <value>
              <string>1</string>
            </value>
          </member>   
          <member> 
            <name>event</name> 
            <value> 
              <string><![CDATA['.$text.']]></string> 
            </value> 
          </member> 
          <member> 
            <name>subject</name> 
            <value> 
              <string>'.$title.'</string> 
            </value> 
          </member> 
          <member> 
            <name>lineendings</name> 
            <value> 
              <string>pc</string> 
            </value> 
          </member> 
          <member> 
            <name>year</name> 
            <value> 
              <int>'.$year.'</int> 
            </value> 
          </member> 
          <member> 
            <name>mon</name> 
            <value> 
              <int>'.$month.'</int> 
            </value> 
          </member> 
          <member> 
            <name>day</name> 
            <value> 
              <int>'.$day.'</int> 
            </value> 
          </member> 
          <member> 
            <name>hour</name> 
            <value> 
              <int>'.$hour.'</int> 
            </value> 
          </member> 
          <member> 
            <name>min</name> 
            <value> 
              <int>'.$min.'</int> 
            </value> 
          </member> 
        </struct> 
      </value> 
    </param> 
  </params> 
</methodCall>';        
    }
}

?>