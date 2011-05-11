<?php
function template_modify_implode($value, $delimiter = ', ', $field = null ){
    if (is_array($value)){
        return implode(', ' , $value);
    }
    elseif (is_object($value) && ($value instanceof MysqlList)){
        $result = array();
        foreach ($value as $data){
            $result[] = $data->__get($field);
        }
        $delimiter = str_replace('\n', "\n", $delimiter);
        return implode($delimiter, $result);
    }
    equal(false);
}
?>