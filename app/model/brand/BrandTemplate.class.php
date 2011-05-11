<?php 

require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_link.php';
require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_href.php';
require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_select.php';

class BrandTemplate extends Template{

    
public function showTagForm($brand_id, User $user = null, $sent = '', $censured = false, $facebook = '', $livejournal = ''){ ?>
    <span style="font-size:1.6em; font-weight:700;">Добавьте вашу ассоциацию этому бренду:</span>
    <p><em>Это может быть просто слово или фраза. Не бойтесь, неправильных ответов не бывает ;-)</em></p>
    <form action="" id="addassociation" class="block" method="post" >
        <input type="hidden" name="id" value="<?= $brand_id ?>" />
        <input type="text" class="i-text" name="tag" value="" />
        <input type="image" src="/i/add_button.png" alt="Добавить" onclick="javascript: return this.form.submit();"/>
        <input type="submit" value="ok" style="display: none;" />
        <span>или <a href="/?skip=<?=$brand_id?>">пропустить этот бренд</a></span>
        <? if ($sent): ?>
          <div class="f-row">
            <span class="info">Ваше сообщение отправлено в twitter</span>
          </div>
        <? endif; ?>
        <? if ($facebook): ?>
          <div class="f-row">
            <span class="info">Ваше сообщение отправлено в facebook</span>
          </div>
        <? endif;?>
        <? if ($livejournal): ?>
          <div class="f-row">
            <span class="info">Ваше сообщение отправлено в livejournal</span>
          </div>
        <? endif;?>
        <? if ($censured): ?>
          <div class="f-row">
            <span class="info">Пожалуйста, без мата</span>
          </div>
        <? endif; ?>    
        <input type="checkbox" id="public_on_twitter" name="public_on_twitter" value="1" <?= $user && $user->isPublicOnTwitter() ? 'checked="checked"' : "" ?> style="display: none;" />
        <input type="checkbox" id="public_on_facebook" name="public_on_facebook" value="1" style="display: none;" />
        <input type="checkbox" id="public_on_livejournal" name="public_on_livejournal" value="1" style="display: none;" />
        
        <div class="f-row" style="display: none">
          <label for="textcomment" class="width-65">Комментарий:</label>
          <div class="width-65">
            <textarea name="comment" tabindex="3" class="i-text" rows="5" id="textcomment"></textarea>
          </div>
        </div>
        
    </form>
    
    <div id="publish">
      <script type="text/javascript">
        function swap_image(element, checkbox_id, front_url, back_url){
            var checkbox = document.getElementById(checkbox_id);
            if (checkbox.checked == true){
                element.style.cssText = 'background-image:url(' + back_url + ')';
                checkbox.checked = false;
            }
            else{
                element.style.cssText = 'background-image:url(' + front_url + ')';
                checkbox.checked = true;
            }
        }
        
        function button_click_facebook(element){
            swap_image(element, 'public_on_facebook', '/i/to_facebook.png', '/i/to_facebook_black.png'); 
            return false;
        }
        function button_click_livejournal(element){
            swap_image(element, 'public_on_livejournal', '/i/to_lj.png', '/i/to_lj_black.png'); 
            return false;
        }
      </script>
      <span style="float: left;">Опубликовать в:</span>  
      <? if ($user && UserSession::instance()->isTwitterAuthorize()): ?>
          <a class="twitter" 
            href="javasript:void(0);" 
            title="Твиттер" 
            <?= $user->isPublicOnTwitter() ? 'style="background-image:url(/i/to_twitter.png)"' : 'style="background-image:url(/i/to_twitter_black.png)"'; ?> 
            onclick="javascript: var el = document.getElementById('public_on_twitter'); if (el.checked == true) el.checked = false; else el.checked = true; if (el.checked == true) this.style.background = 'url(/i/to_twitter.png)'; else this.style.background = 'url(/i/to_twitter_black.png)'; return false;"
            ></a>
      <? else: ?>
          <a class="twitter" href="/twitter.com/" title="Твиттер" style="background-image:url(/i/to_twitter_black.png)"></a>
      <? endif; ?>
      
      <? if ($user && UserSession::instance()->isFacebookAuthorize()): ?>
        <a title="Для бубликации сообщений в Facebook" href="javascript:void(0);" onclick="return button_click_facebook(this);" class="facebook" style="background-image:url(/i/to_facebook_black.png);"></a>
      <? else: ?>
          <a class="facebook" href="/facebook.com/" title="facebook" style="background-image:url(/i/to_facebook_black.png);"></a>
      <? endif; ?>    
      
      <? if ($user && UserSession::instance()->isLivejournalAuthorize() && UserSession::instance()->hasLivejournalPassword()): ?>
        <a title="Для бубликации сообщений в Livejournal" href="javascript:void(0);" onclick="return button_click_livejournal(this);" class="lj" style="background-image:url(/i/to_lj_black.png);"></a>
      <? else:?>
          <a class="lj" href="/livejournal.com/" title="Livejournal" style="background-image:url(/i/to_lj_black.png);"></a>
      <? endif; ?>    
    </div>
<?php }    
    
/**
 * Форма поиска брендов
 * 
 * @param $default_values string значения по умолчанию
 * @return void
 */
public function searchForm($default_values = "", $intag = 1){?>
  <div id="search">
    <form action="" class="block" method="get">
      <div class="f-row">
        <div class="corners corners-5">
          <input type="hidden" name="search" value="1" />
          <input name="brand" type="text" class="i-text" value="<?= html($default_values);?>" tabindex="1"/>
          <input type="image" class="imgbutton" src="/i/search_24.png" alt="Поиск" tabindex="2"/>
          <input style="display: none" type="checkbox" name="intag" <?= $intag ? 'checked="checked"' : '';?>  value="1"/>
          <em class="tl">&nbsp;</em><em class="tr">&nbsp;</em><em class="bl">&nbsp;</em><em class="br">&nbsp;</em>
        </div>
      </div>            
    </form>
  </div>
<?php }

/**
 * Форма загрузги бренда
 * 
 * @return void
 */
public function uploadForm($post){?>
  <? if (empty($post['filename'])): ?>
    <h2>Добавить новый Бренд:</h2>
  <? else: ?>
    <h2>Редактирование &bdquo;<?= html($post['title']);?>&rdquo;:</h2>
  <? endif; ?>    

  <? $industries = BrandIndustryMap::instance()->listAll(100, 'id'); ?>
  <? $isAdmin = UserController::create()->isAdmin();?>
  <form action="" method="post" enctype="multipart/form-data">
    <fieldset>
      <div class="f-row">
        <label for="org_name">Название:</label>
        <div class="width-70">
          <input type="text" id="org_name" class="i-text" tabindex="1" name="title" value="<?= html($post['title'])?>"/>
        </div>
      </div>
      <div class="f-row">
        <label for="org_name">Название компании:</label>
        <div class="width-70">
          <input type="text" id="org_name" class="i-text" tabindex="2" name="company" value="<?= html($post['company'])?>"/>
        </div>
      </div>
      <? /*?>
      <div class="f-row">
        <label for="org_services">Деятельность:</label>
        <div class="width-70">
          <textarea id="org_services" class="i-text" rows="2" tabindex="2"></textarea>
          <span class="helpinput">Несколько слов том, чем  занимается компания</span>
        </div>
      </div>
      <div class="f-row">
        <label for="org_about">Описание:</label>
        <div class="width-70">
          <textarea id="org_about" class="i-text" rows="3" tabindex="3"></textarea>
          <span class="helpinput">Описание компании.Ее история или любой другой компромат</span>
        </div>
      </div>
      <div class="f-row">
        <label for="org_bablo">Оборот:</label>
        <div class="width-70">
          <input type="text" id="org_bablo" class="i-text" tabindex="4"/>
          <span class="helpinput">Оборот компании за последний год, $</span>
        </div>
      </div>
      <div class="f-row">
        <label for="org_site">Сайт:</label>
        <div class="width-70">
          <input type="text" id="org_site" class="i-text" value="http://" tabindex="5"/>
        </div>
      </div>
      
      
      <? */ ?>
      <div class="f-row">
        <label for="industry_id">Отрасль:</label>
        <div class="width-70">
          <?= tselect($industries, $post['industry_id'], 'industry_id', 'industry_id') ?>
        </div>
      </div>
      <? if (UserSession::instance()->isAdmin()): ?>
      <div class="f-row">
        <label for="prioritet">Приоритет:</label>
        <div class="width-70">
          <input type="text" id="prioritet" class="i-text"  tabindex="3"  name="priority" value="<?= $post['priority'] ?>"/>
          <span class="helpinput">90%-100%&nbsp;частое&nbsp;появления; 70%-90%&nbsp;&mdash;&nbsp;нормальное; 50%-70%&nbsp;&mdash;&nbsp;редкое; 0%-50%&nbsp;&mdash;&nbsp;крайне редкое.</span>
        </div>
      </div>
      <? endif; ?>    
    </fieldset>
    
    <fieldset>
      <? if (!empty($post['filename'])): ?>
          <img src="/img/brands/<?= $post['filename']?>" width="176" height="132" alt="" />
      <? endif; ?>    
      <div class="f-row">
        <label for="userpic">Логотип:</label>
        <div class="width-65">
          <input type="file" id="userpic" class="i-text" tabindex="7" name="logo" />
          <span class="helpinput">Разрешенные форматы — jpeg, gif, png</span>
        </div>
      </div>
    </fieldset>
    <fieldset>
      <input type="hidden" name="upload" value="1" />
      <input type="image" src="/i/save.png" alt="Сохранить"  tabindex="8"/>
      <? if (!UserSession::instance()->isAdmin()): ?>
        <p class="helpinput">Все логотипы являются премодреруемыми.</p>
      <? endif;?>
    </fieldset>
  </form>
<?php }

/**
 * Отображение бренда 
 * 
 * @param $brand
 * @return void
 */
public function brandShow(IBrand $brand){ ?>
    <div class="block brandshadow">
      <div class="brand" style="background:url(/img/brands/<?= $brand->filename; ?>) 50% 50% no-repeat;"></div>
    </div>
<?php }

public function pageBrand(IBrand $brand, $active = 'comment') { ?>
    <div class="block breadcrumbs">
      <span>&larr;&nbsp;<?= ahref('Список брендов', 'brand')?></span>
    </div>
    <h2><?= html($brand->title); ?></h2>
    
    <? $this->brandShow($brand); ?>
    
    <!--TABS-->
    <ul id="tabs">
      <li <?= $active === 'comment' ? 'class="active"' : '';?>><?= ahref('Комментарии', 'brand', $brand->id, 'comment');?></li>
      <li <?= $active !== 'comment' ? 'class="active"' : '';?>><?= ahref('Ассоциации', 'brand', $brand->id);?></li>
    </ul>
    <!--TAGS-->
<?php }

/**
 * Кнопики управления брендом
 * 
 * @param $brand
 * @return unknown_type
 */
public function brandControl(IBrand $brand){ ?> 

    <? if (UserController::create()->isAdmin()):?>
        <ul class="controls">
          <li><a href="<?= tlink('brand', $brand->id, 'edit');?>" title="Редактировать" class="edit"></a></li>
          <li><a href="<?= tlink('brand', $brand->id, 'approve');?>" title="Одобрить" class="approve"></a></li>
          <li><a href="<?= tlink('brand', $brand->id, 'disapprove');?>" title="Удалить" class="delete"></a></li>

          <li style="color: gray; font-size: 70%;"><sup><?= $brand->priority; ?>%</sup></li>
          
        </ul>
    <? endif;?>
<?php }

public function commentControl(UserPost $post){ ?> 

    <? if (UserController::create()->isAdmin()):?>
        <ul class="controls">
          <li><a href="<?= tlink('post', $post->id, 'approve');?>" title="Одобрить" class="approve"></a></li>
          <li><a href="<?= tlink('post', $post->id, 'disapprove');?>" title="Удалить" class="delete"></a></li>
        </ul>
    <? endif;?>
<?php }

public function commentControlMass(){ ?> 

    <? if (UserController::create()->isAdmin()):?>
        <ul class="">
          <li><a href="<?= tlink('post', 'approve');?>" title="Одобрить все" class="approve"><span>одобрить все</span></a></li>
          <li><a href="<?= tlink('post', 'disapprove');?>" title="Удалить все" class="delete"><span>удалить все</span></a></li>
        </ul>
    <? endif;?>
<?php }


/**
 * Отображение бренда в списке с ссылкой на страницу бренда
 * 
 * @param $brand
 * @return unknown_type
 */
public function brandShowLink(IBrand $brand, $title){ ?>
      <img src="/img/brands/<?= $brand->filename; ?>" width="175" height="130" alt="" />
      <div class="brandname <?= UserSession::instance()->isAdmin() ? $brand->status : ''; ?>">
        <a href="<?= tlink('brand', $brand->id); ?>"><?= html($title) ?></a><sup><?= $brand->count; ?></sup>
        <? $this->brandControl($brand);?>
      </div>
<?php }

/**
 * Бренд ожидает модерацию 
 * 
 * @param $brand
 * @return void
 */
public function brandStatus(Brand $brand){ ?>
    <h2><?= BrandTerms::status($brand); ?></h2>
    <? $this->brandShow($brand); ?>
    <?= ahref('Добавить новый бренд', 'brand', 'upload'); ?>&nbsp;&rarr;
<?php }

/**
 * Бренд был удален из-за объективных или не очень причин
 * @return void
 */
public function brandDeleting(){ ?>
    <h3>Бренд был удален модератором</h3>
<?php }

/**
 * Список брендов
 * 
 * @param $brands - XXX: привести к типу BrandList
 * @return void
 */
public function brandList(MysqlList $brands, $field = "title"){ ?>

  <div id="brandlist" class="block">
    <? if (UserSession::instance()->isAdmin()): ?>
      <p class="legend">
        <span class="icon approve">&mdash;&nbsp;бренд одобрен</span>&nbsp;
        <span class="icon dirty">&mdash;&nbsp;бренд в ожидании</span>&nbsp;
        <span class="icon disapprove">&mdash;&nbsp;бренд отклонен</span>&nbsp;
      </p>
    <? endif; ?>    
    <? foreach ($brands as $brand):?>
      <div class="item container">
        <? $this->brandShowLink($brand, $brand->{$field})?>
      </div>
    <? endforeach; ?>
  </div>
  <? if (isset($brands->pager)): ?>
    <div class="pager block">
      <?= $brands->pager; ?>
    </div>
  <? endif?>
  <? if ($brands->count() === 0):?>
    <h3>ничего нет</h3>
  <? endif;?>
<?php }

/**
 * Нарисовать облако брендов
 * 
 * @param $brands
 * @return void
 */
public function cloudBrands(MysqlList $brands){ ?>
    <?php
        $max_count = 1;
        foreach ($brands as $brand){
            $max_count = max($max_count, $brand->count_tag);
        }
    ?>
    <div class="brand-cloud">
        <? foreach ($brands as $brand): ?>
            <span style="font-size: <?= round($brand->count_tag  * 250 /$max_count) + 50;?>%">
                <?= ahref(html($brand->title), 'brand', $brand->id)?>
            </span>
        <? endforeach;?>
    </div>
<?php }
    

/**
 * Облако тегов
 * 
 * @param $tags
 * @return unknown_type
 */
public function cloudTags(MysqlList $tags, $title = 'title'){ ?>
    <?php
        $max_count = 1;
        foreach ($tags as $tag){
            $max_count = max($max_count, $tag->count);
        }
    ?>
    <? if (UserSession::instance()->isAdmin()): ?>
        <div style="text-align: right; color: red; margin-bottom: 0.7em;"><?= ahref('Антимат', 'tags', 'dirty')?></div>
    <? endif; ?>    
    
    <div id="tags">
        <ul>
        <? foreach ($tags as $tag): ?>
            <li style="font-size: <?= round(pow($tag->count, 2) * 1000 / pow($max_count, 2) ) + 120;?>%">
        	   <a href="<?=tlink('tags', $tag->id); ?>" rel="tag" title="Связей: <?=$tag->count;?>" ><?= html($tag->{$title}); ?></a>
        	</li>
        <? endforeach;?>
        <? if (!$tags->count()): ?>
            <li>Ассоциаций еще нет</li>
        <? endif; ?>    
        </ul>
    </div>
<?php }

public function tagItem(ITag $tag){ ?>
    <?= ahref(html($tag->name), 'tags', $tag->tag_id);?>
<?php }

public function showPosts(MysqlList $posts){ ?>

  <? foreach ($posts as $post):?>
    <div class="comment <?= UserSession::instance()->isAdmin() ? $post->status : "";?>">
      <div class="userpic">
        <?php // <img src="/i/contimg/userpics/userpic_1.png" width="30" height="30" alt="" />?>
        &nbsp;
      </div>
      <div class="text">
        <strong><?= html($post->name); ?></strong>
        <?= UserSession::instance()->isAdmin() ? '&nbsp;<i style="color: gray;">(' .html($post->email) . ")</i>" : '';?>
        <em><?= DateFormatted::humanDate($post->create, '%D% %gmonth% %YYYY%'); ?></em>
        <div class="entry">
          <?= html($post->comment); ?>
          <? $this->commentControl($post); ?>
        </div>
      </div>
    </div>
  <? endforeach;?>
    
<?php }

public function showPostForm(User $user, $post = array()){ ?>

  <div id="comments">
    <div class="block collapsform">
    <p><big><a href="javascript:void(0)" onclick="document.getElementById('post_form').className=''; this.className='hidden'; return false;" >Оставить комментарий:</a></big></p> 
      <form id="post_form" class="hidden" action="" method="post">
        <fieldset>
          <? if (!UserSession::instance()->isAuthorize()): ?>
          <div class="f-row">
            <label for="name">Ваше имя:</label>
            <div class="width-65">
              <input name="name" type="text" tabindex="1" class="i-text" id="name" value="<?= html($user->name); ?>"/>
            </div>
          </div>
          <div class="f-row">
            <label for="mail">Ваш e-mail:</label>
            <div class="width-65">
              <input name="email" type="text" tabindex="2" class="i-text" id="mail" value="<?= html($user->email);?>" />
            </div>
          </div>
          <? else: ?>
            <input name="name" type="hidden" tabindex="1" class="i-text" id="name" value="<?= html($user->name); ?>"/>
            <input name="email" type="hidden" tabindex="2" class="i-text" id="mail" value="<?= html($user->email);?>" />
          <? endif; ?>    
          <div class="f-row">
            <label for="textcomment" class="width-65">Комментарий:</label>
            <div class="width-65">
              <textarea name="comment" tabindex="3" class="i-text" rows="5" id="textcomment"><?= !empty($post['comment']) ? $post['comment'] : '';?></textarea>
            </div>
          </div>
          <? if (!UserSession::instance()->isOauthAuthorize()): ?>
            <div class="f-row">
              <div class="width-65">
                <p class="note">Комментарий будет проходить пост-модерацию</p>
              </div>
            </div>
          <? endif; ?>                                    
        </fieldset>
        <fieldset>
          <button class="button" type="submit" name="postbrand" >Ответить</button>
        </fieldset>
      </form>
    </div>
  </div>
  
<?php }

public function showTag(Tag $tag){ ?>
    <div class="user-comment">
        <h2>Тэг: <i><?= html($tag->name); ?></i></h2>
        <? if (UserSession::instance()->isAdmin()): ?>
            <div style="margin-bottom: 1em;" >
                <?= ahref('склеить', 'tags', $tag->id, 'merge'); ?>
                &nbsp;|&nbsp;
                <? if ($tag->status !== Status::APPROVE): ?>
                    <?= ahref('одобрить', 'tags', $tag->id, 'approve')?>
                    &nbsp;|&nbsp; 
                <? endif; ?>     
                <?= ahref('удалить', 'tags', $tag->id, 'remove')?>
            </div>
        <? endif; ?>
    </div>
<?php }

public function  showTagMergeForm(Tag $slave, MysqlList $choise){ ?>
    <h3>Склеивание тэга <i>&bdquo;<?= html($slave->name); ?>&rdquo;</i></h3>
    <div>Выберите из списка тэг, с которым будем склеивать:
        <form action="" method="post">
            <?= tselect($choise, !empty($_POST['tag']) ? $_POST['tag'] : false, 'choise_of_tag', 'tag', '', ''); ?>
            <input type="submit" name="merge_tags" value="склеить" />
        </form>
    </div>
<?php }

public function showBrandListMessage() { ?>
    <p>Первоначальный список брендов взят из списка <q>50 брендов-лидеров по обороту за 2009 год</q>, составленный журналом <b>Forbes Russia</b>.</p>
<?php }

/**
 * @return BrandTemplate
 */
static public function create(){
    return new self();
}

}
?>
