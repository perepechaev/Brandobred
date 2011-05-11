<?php
function template_modify_date($date, $template = '%D% %gmonth% %YYYY%г'){
    
    return DateFormatted::humanDate($date, $template);
}
?>