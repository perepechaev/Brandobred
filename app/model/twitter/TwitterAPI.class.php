<?php

class TwitterApi
{
    private $oauth;
    
    private $user;
    
    public function __construct(OAuth $oauth){
        $this->oauth = $oauth;
    }
    
    public function statusUpdate($text){
        $params = array(
            'status' => $text 
        );
        try {
            $this->oauth->fetch("http://twitter.com/statuses/update.json", $params, OAUTH_HTTP_METHOD_POST);
        }
        catch (Exception $e){
            exception_log($e);
        }
    }
    
    private function getUser($twitter_id){
        if (!$this->user){
            $this->oauth->fetch("http://twitter.com/users/show.json?user_id=" . $twitter_id, null, OAUTH_HTTP_METHOD_GET);
            $this->user = json_decode($this->oauth->getLastResponse());
        }
        return $this->user;
    }
    
    public function getAvatarUrl($twitter_id){
        try {
            return str_replace('_normal','_bigger', $this->getUser($twitter_id)->profile_image_url);
        }
        catch (Exception $e){
            exception_log($e);
        }
    }
    
    public function getEmail($twitter_id){
        try {
            assert(false);
            return $this->user->profile_image_url;
        }
        catch (Exception $e){
            exception_log($e);
        }
    }
    
    public function getUserName($twitter_id){
        try {
            return $this->getUser($twitter_id)->name;
        }
        catch (Exception $e){
            exception_log($e);
        }
    }
}

?>
