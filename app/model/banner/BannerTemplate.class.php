<?php

require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_link.php';
require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_href.php';
require_once PATH_TEMPLATE_MODIFIERS . '/template_modify_select.php';

class BannerTemplate extends Template{

    
public function showBannerForm(Banner $banner = null){ ?>
  <form action="" method="post" enctype="multipart/form-data">
    <fieldset>
      <? if ($banner->filename): ?>
          <? $this->showBannerItem($banner); ?>
      <? endif; ?>    
      <div class="f-row">
        <label for="remote_url">url:</label>
        <div class="width-70">
          <input type="text" id="org_name" class="i-text" tabindex="2" name="remote_url" value="<?= $banner->remote_url; ?>"/>
        </div>
      </div>
      <div class="f-row">
        <label for="place">Положение:</label>
        <div class="width-70">
          <?= tselect(BannerPlace::getAvailable(), $banner->place, 'place', 'place') ?>
        </div>
      </div>
      <div class="f-row">
        <label for="userpic">Баннер:</label>
        <div class="width-65">
          <input type="file" id="userpic" class="i-text" tabindex="7" name="banner" />
          <span class="helpinput">Разрешенные форматы — jpeg, gif, png, swf</span>
        </div>
      </div>
    </fieldset>
    <fieldset>
      <input type="hidden" name="banner_upload" value="1" />
      <input type="image" src="/i/save.png" alt="Сохранить"  tabindex="8"/>
    </fieldset>
    
  </form>
<?php }

public function controlBanner($banner_id = null){?>
  <?= ahref('Добавить баннер', 'banner', 'new'); ?>
  <? if ($banner_id): ?>
    <?= ahref('Удалить', 'banner', $banner_id, 'remove'); ?>
  <? endif; ?>    
<?php }

public function placeBottom(Banner $banner){ ?>
  <? if ($banner): ?>
    <a href="<?= tlink('banner', $banner->id, 'conversion');?>" style="text-decoration: none" >
      <div class="banner block" style="background-image: url(/b/<?= html($banner->filename); ?>)">
          &nbsp;
      </div>
    </a>
  <? endif; ?>    
<?php }
    
public function placeRight(Banner $banner){ ?>
  <? if ($banner): ?>
    <div class="block">
      <a href="<?= tlink('banner', $banner->id, 'conversion');?>" style="text-decoration: none" >
        <div class="banner block" style="background-image: url(/b/<?= html($banner->filename); ?>); height: 400px;">
          &nbsp;
        </div>
      </a>
    </div>
  <? endif; ?>    
<?php }


public function showBannerList(MysqlList $list) { ?>
    <? foreach ($list as $banner): ?>
        <? $this->showBannerItem($banner); ?>
    <? endforeach;?>
<?php }

private function  showBannerItem(Banner $banner ) { ?>
  <div>
    <a href="<?= tlink('banner', $banner->id); ?>">
      <img src="/b/<?= $banner->filename; ?>" />
    </a>
    <div>
      Показов: <?= $banner->hits; ?><br/>
      Переходов: <?= $banner->conversion; ?>
    </div>
  </div>
<?php }

}

?>