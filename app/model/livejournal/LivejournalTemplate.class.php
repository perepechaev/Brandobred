<?php
class LivejournalTemplate extends Template
{
public function requestLogin($login = ''){ ?>
<div id="search">
  <form action="<?= tlink('livejournal.com'); ?>" method="post" class="block" onsubmit="if (this.identify.value.substr(0, 7) != 'http://') this.identify.value = 'http://' + this.identify.value + '.livejournal.com'; return true;">
    <div class="f-row">
      <label for="org_name">Имя пользователя Livejournal:</label>
      <div class="corners corners-5">
        <input type="text" name="identify" value="<?= $login;?>" class="i-text" />
        <input type="submit" name="openid_login" value="ok" class="button" />
      </div>
    </div>
    <div class="f-row">
      <label for="lj_pwd">Пароль Livejournal:</label>
      <div class="corners corners-5">
        <input type="password" name="pwd" id="lj_pwd" value="" class="i-text" />
        <br />
        <span class="dimmed" style="font-size: 90%">Введите пароль, если хотите чтобы ваши сообщения были видны в Livejournal</span>
      </div>
    </div>
  </form>
  </div>
<?php }

public function message(Brand $brand, Tag $tag, User $user){ ?>
  <div>
    <img src="http://<?= URL_SITE; ?>/i/logo.png" />
  </div>

  <p>А у вас с чем бренд „<a href="http://<?= URL_SITE;?>/brand/<?= $brand->id?>/"><?= html($brand->title);?></a>” ассоциируется? У меня с „<?= html($tag->name); ?>”</p>
  
  <p>Метим другие бренды!</p>
<?php }
    
}

?>