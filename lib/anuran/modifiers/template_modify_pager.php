<?php
function template_modify_pager(MysqlList $list, $count = 10){
    return $list->slice(0, $count);
}
?>