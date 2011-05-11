<?php
function template_modify_money($money, $currency = 1){
    if (is_object($money)){
        $money = $money->money;
    }
    return sprintf('%01.2f руб', $money);
}

?>