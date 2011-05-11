<?php

require_once PATH_CORE . '/Router.class.php';

class BrandtagRouter extends Router
{
    public function rule() {
        $this->rule    = array(
            ROUTER_ROOT         => array('DefaultController',   'blockDefault'),
            'twitter.com'       => array('TwitterController',   'requestAuth', null, array(
                'confirmation'      => array('TwitterController',   'blockConfirmation'),
                'disable'           => array('TwitterController',   'blockDisable'),
                'enable'            => array('TwitterController',   'blockEnable')
            )),
            
            'facebook.com'      => array('FacebookController',  'requestAuth', null, array(
                'confirmation'      => array('FacebookController',  'blockConfirmation'),
            )),
            
            'livejournal.com'   => array('LivejournalController',   'requestAuth', null, array(
                'approve'           => array('LivejournalController',   'approve', null)
            )),
            
            'brand'             => array('DefaultController',    'blockBrandList', null, array(
                '(\d)'              => array('DefaultController',   'blockBrandById', '%2', array(
                    'waiting'           => array('DefaultController',   'blockBrandWaiting', '%2'),
                    'approve'           => array('DefaultController',   'blockBrandApprove', '%2'),
                    'disapprove'        => array('DefaultController',   'blockBrandDisapprove', '%2'),
                    'edit'              => array('DefaultController',   'blockBrandEdit', '%2'),
                    'comment'           => array('DefaultController',   'blockBrandCommentById', '%2'),
            
                )),
                'dirty'             => array('DefaultController',   'blockBrandsDirty'),
                'upload'            => array('DefaultController',   'blockBrandUpload'),
                'cloud'             => array('DefaultController',   'blockBrandsCloud'),
            )),
            
            'tags'              => array('DefaultController',   'blockTagList', null, array(
                '(\d)'              => array('DefaultController',   'blockBrandListByTagId', '%2', array(
                    'merge'             => array('DefaultController',   'blockTagMerge', '%2'),
                    'remove'            => array('DefaultController',   'blockTagRemove', '%2'),
                    'approve'           => array('DefaultController',   'blockTagApprove', '%2'),
                )),
                'dirty'             => array('DefaultController',   'blockTagCensure'),
                'clear'             => array('DefaultController',   'blockTagClear'),
            )),
            
            'post'              => array('DefaultController',   'blockPostList', null, array(
                '(\d)'              => array('DefaultController',   'blockPostList', '%2', array(
                    'approve'           => array('DefaultController',   'blockPostApprove', '%2'),
                    'disapprove'        => array('DefaultController',   'blockPostDisapprove', '%2'),
                )),
                'approve'           => array('DefaultController',   'blockPostMassApprove'),
                'disapprove'        => array('DefaultController',   'blockPostMassDisapprove'),
            )),
            
            'banner'            => array('BannerController',    'blockBannerList', null, array(
                '(\d)'              => array('BannerController',    'blockBanner', '%2', array(
                    'conversion'        => array('BannerController',    'blockBannerConversion', '%2'),
                    'remove'            => array('BannerController',    'blockBannerRemove', '%2')
                )),
                'new'               => array('BannerController',    'blockBannerEdit', null)
            )),
            
            'profile'           => array('UserController',      'blockProfile'),
            'logout'            => array('UserController',      'logout'),
            
        );
   }
}

?>