<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
    <title>BRANDOMET</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="imagetoolbar" content="no" />
    <link rel="shortcut icon" href="/i/favicon.ico" />
    <link rel="stylesheet" href="/css/cssf-base.css" type="text/css" media="screen" />
    <!--[if lte IE 6]><link rel="stylesheet" href="/css/cssf-ie6.css" type="text/css" media="screen" /><![endif]-->
    <!--[if IE 7]><link rel="stylesheet" href="/css/cssf-ie7.css" type="text/css" media="screen" /><![endif]-->
    <script type="text/javascript" src="/js/iepngfix_tilebg.js"></script>
    <script type="text/javascript" src="/js/css_browser_selector.js"></script>
</head>
<body>
    <? /*
    <div id="menu">
        <ul>
            <li style="float: right; display: block; margin-top: 0.5em;"><a href="/twitter.com/"><img width="151" height="24" alt="авторизоваться на twitter.com" src="/img/twitter.png"/></a></li>
            <li><a href="/">главная</a></li>
            <li><a href="/brand/">бренды</a></li>
            <li><a href="/tags/">тэги</a></li>
            <li><a href="/brand/cloud/">бренды в облаке</a></li>
            <li><a href="/profile/">профиль</a></li>
        </ul>
        
        <? if (UserController::create()->isAdmin()): ?>
            <ul class="admin" >
                <li class="right"><a href="<?= tlink('logout') ?>">выход</a></li>
                <li><a href="<?= tlink('brand', 'dirty') ?>" title="список новых брендов">новые</a></li>
                <li><a href="/brand/upload/">добавить бренд</a></li>
            </ul>
        <? endif;?>
        
    </div>
    <div id="content">
        <? content(); ?>
    </div>
    */ ?>
        <?php
    	    Mysql::instance()->query("SELECT count(id) FROM brand WHERE status = 'approve'");
    	    $list = Mysql::instance()->fetchArray();
    	    $brands_count = $list[0]['count(id)'];
    	    
    	    Mysql::instance()->query("SELECT count(id) FROM tag WHERE status = 'approve'");
    	    $list = Mysql::instance()->fetchArray();
    	    $tags_count = $list[0]['count(id)'];
	?>

<div id="page">
  <div class="content box">
    <div id="header">
      <a href="/"><img src="/i/logo.png" width="203" height="51" alt="BRENDOMET" /></a>
      <span class="version">beta</span>
      <div class="forbes">Бренды&nbsp;&mdash; это то, что вы о них думаете. Лучше думать редко, но метко. Попадите своими метками в бренды, и посмотрите, сколько людей думает так же. Уже оставлено <?= $tags_count ?> меток к <?= $brands_count ?> брендам.</div>
    </div>
    <div id="container">
      <ul class="menu-h" style="margin:0 0 -2px 0;">
        <li <?= hactive('/', 'active');         ?>><?= ahref('Добавить ассоциацию', '/')?></li>
        <li <?= hactive('/brand/', 'active');   ?>><?= ahref('Бренды', 'brand')?></li>
        <li <?= hactive('/tags/', 'active');    ?>><?= ahref('Ассоциации', 'tags')?></li>
        <? if (!defined('DEBUG_HIDE_ODNAKNOPKA') || !DEBUG_HIDE_ODNAKNOPKA): ?>
          <li class="bookmark"><script src="http://odnaknopka.ru/ok2.js" type="text/javascript"></script></li>
        <? endif; ?>    
      </ul>
      <div id="content" class="block">
        <div class="roundtop">&nbsp;</div>
          <div class="inside">
            <? content(); ?>
          </div> 
         <div class="roundbottom">&nbsp;</div>   
      </div>
      
    <?= BannerController::create()->placeBottom(); ?>

    </div>
    <div id="sidebar">
      <div class="roundtop">&nbsp;</div>
        <div class="inside">
          <div class="block">
            <!--LOGIN-->
            <div id="login">
              <? if (UserSession::instance()->isAuthorize()): ?>
                <div class="a-center">
                  <strong>Здравствуйте:</strong><br />
                  <?= ahref( html(UserSession::instance()->getUser()->getName()), 'profile' );?>
                  <br />
                  <span><?= ahref('выйти', 'logout')?>&nbsp;&rarr;</span>
                </div>
                <? if (UserSession::instance()->havePossibleAuthhorize()): ?>
                  <div class="a-center">
                    <strong>Так же войти:</strong><br />
                    <? if (!UserSession::instance()->isTwitterAuthorize()): ?>
                      <a href="/twitter.com/"><img src="/i/to_twitter.png" width="16" height="16" alt="Твиттер" /></a>&nbsp;
                    <? endif; ?>    
                    <? if (!UserSession::instance()->isFacebookAuthorize()): ?>
                      <a href="/facebook.com/"><img src="/i/to_facebook.png" width="16" height="16" alt="facebook" /></a>&nbsp;
                    <? endif; ?>
                    <? if (!UserSession::instance()->isLivejournalAuthorize()): ?>
                      <a href="/livejournal.com/"><img src="/i/to_lj.png" width="16" height="16" alt="ЖЖ" /></a>&nbsp;
                    <? endif; ?>    
                  </div>    
                <? endif; ?>                 
              <? else: ?>
                <div class="a-center">
                    <strong>Войти с помошью:</strong><br />
                    <a href="/twitter.com/"><img src="/i/to_twitter.png" width="16" height="16" alt="Твиттер" /></a>&nbsp;
                    <a href="/facebook.com/"><img src="/i/to_facebook.png" width="16" height="16" alt="facebook" /></a>&nbsp;
                    <a href="/livejournal.com/"><img src="/i/to_lj.png" width="16" height="16" alt="ЖЖ" /></a>&nbsp;
                </div>
              <? endif; ?>    
            </div>
            
          <? if (UserSession::instance()->isAdmin()): ?>
            <em>Управление сайтом:</em>
            <div class="corners corners-5;" style="border-bottom: 1px solid #CCC; margin-bottom: 0.7em; padding-bottom: 0.7em;">
              <div>
                <?= ahref('Список новых брендов', 'brand', 'dirty');?>
              </div>
              <div style="margin-top: 0.5em;">
                <?= ahref('Добавить бренд', 'brand', 'upload');?>
              </div>
              <div style="margin-top: 0.5em;">
                <?= ahref('Антимат', 'tags', 'dirty');?>
              </div>
              <div style="margin-top: 0.5em;">
                <?= ahref('Комментарии', 'post');?>
              </div>
              <div style="margin-top: 0.5em;">
                <?= ahref('Баннеры', 'banner');?>
              </div>
            </div>
            
            <div class="roundbottom">&nbsp;</div>
          <? endif; ?>    

            
            <p class="description">Ваши ассоциации к брендам прокомментирует сам 
            <a href="http://www.trout.marketingsuccess.ru/">Джек Траут</a> с точки зрения позиционирования</p>
            <hr/>
            <div class="a-center"><a href="http://www.trout.marketingsuccess.ru/"><img src="/i/contimg/topclass.jpg" width="148" height="148" alt="" /></a></div>
            <div class="a-center">До конгресса Top Class International осталось:
            <span class="day"><?= _getDeltaTime() . '&nbsp;' .template_modify_numeric( _getDeltaTime() , 'день', 'дня', 'дней')?></span></div> 
            <div class="a-center" style="padding-bottom:2em;">
            
                            <form action="http://www.trout.marketingsuccess.ru/registration/index.htm">
                            
                                             <button style="padding:.4em;">Зарегистрироваться</button>
                                             
                                                             </form>
                                                             
                                                                           </div>
          </div>
          <?= BannerController::create()->placeRight();?>
        </div>
      <div class="roundbottom">&nbsp;</div>
      </div>
      <div id="footer"> 
            <a rel="license" href="http://creativecommons.org/licenses/by/3.0/" style="float: left; margin-right: 10px;"> 
                      <img src="http://i.creativecommons.org/l/by/3.0/88x31.png" alt="Creative Commons License" style="border:none;" height="31" width="88" /> 
            </a> 
            Проект агентства <a href="http://www.remarkable.ru/">&laquo;Редкая марка&raquo;</a>. Все метки добавлены обычными людьми и могут не совпадать с мнением владельца сайта или представителей торговой марки.
      </div> 
  </div><!-- content box -->
</div><!-- #page -->
    
    <!-- Yandex.Metrika -->
    <script src="//mc.yandex.ru/metrika/watch.js" type="text/javascript"></script>
    <script type="text/javascript">
    try { var yaCounter782863 = new Ya.Metrika(782863); } catch(e){}
    </script>
    <noscript><img src="//mc.yandex.ru/watch/782863" style="position:absolute" alt="" /></noscript>
    <!-- /Yandex.Metrika -->
    <script type="text/javascript"> var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www."); document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E")); </script> <script type="text/javascript"> try { var pageTracker = _gat._getTracker("UA-4007575-12"); pageTracker._setDomainName(".brandomet.ru"); pageTracker._trackPageview(); } catch(err) {}</script>
    
<?php

if (defined('DEBUG_SHOW_EXCEPTION') && DEBUG_SHOW_EXCEPTION){
    dump("Включён режим отображения ошибок");
}

if (defined('DEBUG_SIMPLE_AUTHORIZE') && DEBUG_SIMPLE_AUTHORIZE){
    dump("Включён режим простой авторизации");
}

if (defined('DEBUG_SHOW_SESSION') && DEBUG_SHOW_SESSION){
    isset($_SESSION) ? dump($_SESSION) : dump("empty");
    dump('$_GET', false, 'green');
    dump($_GET);
    dump('$_POST', false, 'green');
    dump($_POST);    
    dump('$_COOKIE', false, 'green');
    dump($_COOKIE);    
}

if (defined('DEBUG_SHOW_SQL_QUERY') && DEBUG_SHOW_SQL_QUERY ){
    dump(Mysql::instance());
}

?>    
</body>
</html>
