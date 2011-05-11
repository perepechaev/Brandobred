<?php 

class UserTemplate extends Template
{
    
public function profile(User $user){?>
    <? /* BrandTemplate::create()->brandList( $user->brands(), 'name' );  */?>
    <? $brands = $user->brands(); ?>
  <table id="profile" class="full null-table">
    <tr>
      <td class="useredit">
        <img src="<?= $user->avatar ?> " width="73" height="73" alt="Username" />
      </td>
      <td>
        <h2 class="name"><?= html($user->alias)?></h2>
        <!--MY BRANDS -->
        <div class="block mybrands">
          <em>Мои бренды и ассоциации:</em>
          <div class="corners corners-5">
            <? foreach ($brands as $brand):?>
              <div style="margin-top: 1em;">
                  <a href="<?= tlink('brand', $brand->brand_id); ?>"><?= html($brand->title) ?></a>
                  &mdash;
                  <a href="<?= tlink('tags', $brand->tag_id); ?>"><?= html($brand->name) ?></a>
              </div>
            <? endforeach; ?>
            
            <? if ($brands->count() === 0):?>
              <h3>ничего нет</h3>
            <? endif;?>
            <em class="tl">&nbsp;</em><em class="tr">&nbsp;</em><em class="bl">&nbsp;</em><em class="br">&nbsp;</em>  
          </div>
        </div>
        <div class="block mybrands">
          <? if (UserSession::instance()->isAdmin()): ?>
            <em>Управление сайтом:</em>
            <div class="corners corners-5">
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
              <em class="tl">&nbsp;</em><em class="tr">&nbsp;</em><em class="bl">&nbsp;</em><em class="br">&nbsp;</em>  
            </div>
            
          <? endif; ?>    
        </div>
      </td>
    </tr>
  </table>
    
<?php }    
    
}

?>