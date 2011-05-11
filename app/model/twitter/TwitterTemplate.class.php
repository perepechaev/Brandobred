<?php

class TwitterTemplate extends Template
{

/**
 * Подтверждение удачной авторизации
 * 
 * @return void
 */
public function authorizeConfirmation( User $user ){ ?>

    <div class="confirmation">
        <h3>Вы успешно авторизоваласись на twitter.com</h3>

        <?php if ( $user->public_on_twitter ): ?>
            Сообщения отправляются на twitter. Попрообуйте <a href="/twitter.com/disable/">выключить</a> если надоело
        <?php else:?>
            Теперь вы можете <a href="/twitter.com/enable/">включить</a> импорт сообщений в twitter.com
        <?php endif;?>        
        <div><a href="/">вернуться</a> к написанию тегов</div>
        
        <div><a href="/profile/">профиль</a></div>
    </div>
<?php }

}

?>