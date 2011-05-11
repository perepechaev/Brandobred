<?php
function template_modify_datetime($date, $template = '%D% %gmonth% %YY% %H%:%i%'){

    return DateFormatted::humanDate($date, $template);
}

?>