<?php

/**
 * Вот такой код вылетает из под клавиатуры, когда приближается дедлайн.
 * Думаю что уже через неделю не смогу в нем разобраться или оптимизировать
 *
 * успею - перепишу
 */

require_once(dirname(__FILE__) . '/Template.class.php');
require_once(PATH_MODEL . '/file/FileException.class.php');

class TemplateXml extends Template
{
    private $sClass             = null;
    private $aCurrent           = array();

    private $templates          = array();

    static private $structure   = array();

    private function startElement($parser, $name, $attrs)
    {
        $this->aCurrent['is_content']   = ($name === 'CONTENT');

        if ($name === 'TEMPLATEXML'){
            return false;
        }

        if (isset($attrs['NAME'])){
            $this->aCurrent[$name] = $attrs['NAME'];
        }

        if ($this->sClass !== $this->aCurrent['AREA']){
            return false;
        }

        if ($name === 'TEMPLATE'){
            $this->templates[$attrs['NAME']] = array(
                'param'     => array(),
                'content'   => null,
                'functions' => array()
            );
        }

        if ($name === 'ITEM'){
            $temp_name  = $this->aCurrent['TEMPLATE'];
            $part_name  = strtolower($this->aCurrent['parent']);
            if (isset($attrs['MODIFIERS'])){
                $attrs['modifiers'] = $this->createModifiers($attrs['MODIFIERS']);
            }
            $this->templates[$temp_name][$part_name][$attrs['NAME']]    = $attrs;
        }

        if ($name === 'REFERENCE'){
            $temp_name  = $this->aCurrent['TEMPLATE'];
            $part_name  = strtolower($this->aCurrent['ITEM']);
            $item       = $this->aCurrent['ITEM'];
            $this->templates[$temp_name]['reference'][$item][$attrs['NAME']]   = array(
                'type'       => 'referens',
                'obj'        => $this->aCurrent['ITEM'],
                'field'      => $attrs['FIELD'],
                'modifiers'	 => !empty($attrs['MODIFIERS']) ? $this->createModifiers($attrs['MODIFIERS']) : false
            );
        }
        
        if ($name === 'DATA'){
            $temp_name  = $this->aCurrent['TEMPLATE'];
            $part_name  = strtolower($this->aCurrent['ITEM']);
            $item       = $this->aCurrent['ITEM'];
            $this->templates[$temp_name]['functions'][$part_name]['params'][$attrs['NAME']]   = array(
                'type'       => 'referens',
                'obj'        => $this->aCurrent['ITEM'],
                'field'      => $attrs['FIELD'],
                'modifiers'	 => isset($attrs['MODIFIERS']) ? $this->createModifiers($attrs['MODIFIERS']) : false
            );
        }
        
        $this->structure .= ' ';
        $this->structure .=  "$name: " . print_r($attrs, true) . "<br/>";

        if (($name !== 'ITEM') && ($name !== 'REFERENCE')){
            $this->aCurrent['parent']   = $name;
        }
    }

    private function getData($parser, $data){
        if (!isset($this->aCurrent['AREA']) || $this->sClass !== $this->aCurrent['AREA']){
            return false;
        }

        if ($this->aCurrent['is_content'] === true){
            $template   = &$this->templates[$this->aCurrent['TEMPLATE']];
            assert(!isset($template['content']));
            $template['content'] .= $data;
            unset($template);
        }
    }

    private function endElement($parser, $name)
    {
        unset($this->aCurrent[$name]);
        $this->aCurrent['is_content']   = false;
    }

    private function loadXml($source){
        $this->sClass   = get_class($this);
        FileException::isReadable($source);

        $xml_parser     = xml_parser_create();
        xml_set_element_handler($xml_parser, array($this, "startElement"), array($this, "endElement"));
        xml_set_character_data_handler($xml_parser, array($this, 'getData'));

        $this->aCurrent['is_content']   = false;

        xml_parse($xml_parser, file_get_contents($source));        
        xml_parser_free($xml_parser);
    }

    final protected function prepare($xmlFile){
        $str_name   = get_class($this);
        if (!isset(self::$structure[$str_name])){
            $this->loadXml($xmlFile);
            self::$structure[$str_name] = $this->templates;
        }
        else {
            $this->templates    = self::$structure[$str_name];
        }
        
        if (isset($this->templates['editCatalogCategory'])){
//            dump($this->templates['editCatalogCategory']['functions']);
        }
    }

    final public function get($templateName, $templateParam = array()){
        if (!isset($this->templates[$templateName])){
            equal(0, "Не найден шаблон: ".$templateName);
        }
        // XXX: Не найден шаблон
        $template   = $this->templates[$templateName];
        $content    = $template['content'];
        $callFunct  = array();

        if (!is_array($templateParam)){
            $key    = current(array_keys($this->templates[$templateName]['param']));
            $templateParam  = array($key => $templateParam);
        }
        
        foreach ($template['param'] as $name => $value) {
            $fieldName    = isset($template['param'][$name]['FIELD']) ? $template['param'][$name]['FIELD'] : $template['param'][$name]['NAME'];
            if (!isset($templateParam[$fieldName])){
                $value = null;
            }
            else{
                $value = $templateParam[$fieldName];
            }
            
            // Обработка объектов
            if (is_object($value) && ( 1 == 1 || !($value instanceof IteratorAggregate) )){
                $obj            = $value;

                // XXX: Поставить Exception: вызываемая функция не принимает значения как объекты
                if (!$name && isset($template['reference'])){
                    $reference  = current($template['reference']);
                }
                elseif (isset($template['reference'][$name])){
                    $reference  = $template['reference'][$name];
                }
                else{
                    continue;
                }
                $keys       = array_keys($reference);
                foreach ($keys as $key){
                    $field      = $reference[$key]['field'];
                    
                    if (($pos = strpos($field, '->')) !== false){
                        $ref    = explode('->', $field);

                        $val    = $obj;
                        for ($i = 0; $i < count($ref); $i++){
                            $method = $ref[$i];
                            if (is_object($val)){
                                $val    = $val->$method;
                            } 
                            else {
                                $val    = null;
                            }
                        }
                        $value      = $val;
                    }
                    else {
                        $value      = $obj->$field;
                    }
                    
                    if (($reference[$key]['modifiers'])){
                        equal(is_array($reference[$key]['modifiers']), var_export($reference[$key]['modifiers'], true));
                        foreach ($reference[$key]['modifiers'] as $function_modify) {
                            equal(function_exists($function_modify['function']), 'Не найдена функция-модификатор: '. var_export($function_modify['function'], true));
                            $function_modify['params'] = array_merge(array($value), $function_modify['params']);
                            $value = call_user_func_array($function_modify['function'], $function_modify['params']);
                            $templateParam['modify'][$key] = $value;
                        }
                    }
                    $search = '{@' . $key. '}';
                    if (!(is_object($value) && ($value instanceof IteratorAggregate))){
                        $content    = str_replace($search, $value, $content);
                    }
                    
                    $search = '{$' . $name . '->' . $field . '}';
                    if (mb_strpos($content, $search) !== false){
                        $content    = str_replace($search, $value, $content);
                    }
                }
            }
            else{
                if (isset($template['param'][$name]['modifiers'])){
                    foreach ($template['param'][$name]['modifiers'] as $function_modify) {
                        equal(function_exists($function_modify['function']), 'Не найдена функция-модификатор: '. var_export($function_modify['function'], true));
                        
                        if (!is_null($value)){
                            $function_modify['params'] = array_merge(array($value), $function_modify['params']);
                        }

                        $value = call_user_func_array($function_modify['function'], $function_modify['params']);
                    }
                }
                $key    = '{$'.$name.'}';
                if (strpos($content, $key) === false){
                    continue;
                }
                $content    = str_replace($key, $value, $content);
            }
            


            if (!isset($template['param'][$name])){
                assert(false);
                $field  = current($template['param']);
                $name   = $field['NAME'];
            }

            equal(isset($template['param'][$name]), "Параметр '$name' не найден для шаблона '$templateName'");
        }


        foreach ($template['functions'] as $name => &$param) {
            equal(isset($template['functions'][$name]), 'Такой функции не найдено' . __FILE__ . '(' . __LINE__ . ')');
            $funct  = $template['functions'][$name];
            $key    = '{'.$name.'()}';
            if ($funct['CLASS'] == 'loop'){
                $res    = '';
                
                $chain  = explode('->',trim($funct['DATA'], '$'));
                if (isset($templateParam['modify'][$funct['DATA']])){
                    $templateParam[$funct['DATA']] = $templateParam['modify'][$funct['DATA']];
                } 
                elseif (!isset($templateParam[$funct['DATA']])){
                    
                    $pos    = strpos($funct['DATA'], '->');
                    equal($pos !== false, "Нет переменной '{$funct['DATA']}' в шаблоне '$templateName'");
                    $ref    = explode('->', $funct['DATA']);

                    $method = $ref[0];
                    $val    = $templateParam[$method];
                    for ($i = 1; $i < count($ref); $i++){
                        $method = $ref[$i];
                        $val    = $val->$method;
                    }
                    $templateParam[$funct['DATA']]  = $val;
                }
                elseif (isset($template['param'][$chain[0]]) && isset($templateParam[$template['param'][$chain[0]]['NAME']])){
                    $variable = $templateParam[$template['param'][$chain[0]]['NAME']];
                    for ($i = 1; $i < count($chain); $i++){
                        $variable = $variable->{$template['reference'][$chain[0]][$chain[$i]]['field']};
                    }
                    $templateParam[$funct['DATA']] = $variable;
                }

                equal(is_object($templateParam[$funct['DATA']]) || is_array($templateParam[$funct['DATA']]), 'Могу итерировать лишь объекты и массивы');

                $list    = $templateParam[$funct['DATA']];
                foreach ($list as $data){
                    $data    = array('item' => $data);
                    if (isset($funct['params'])){
                        foreach ($funct['params'] as $func_key => $func_param){
                            $pos    = strpos($func_param['field'], '->');
                            equal($pos !== false, "Нет переменной '{$func_param['field']}' в шаблоне '$templateName'");
                            $ref    = explode('->', $func_param['field']);

                            $method = $ref[0];
                            $val    = $templateParam[$method];
                            for ($i = 1; $i < count($ref); $i++){
                                $method = $ref[$i];
                                $val    = $val->$method;
                            }
                            $data[$func_key] = $val;
                        }
                    }
                    $res    .= $this->get($funct['TEMPLATE'], $data);
                }
                
                if (is_object($list) && $list->count() === 0 && $funct['EMPTY']){
                    $res    .= $this->get($funct['EMPTY'], $templateParam);
                }
                
                $content    = str_replace($key, $res, $content);
            }
            elseif ($funct['CLASS'] === 'template'){
                $functData  = $funct['DATA'];
                if (isset($templateParam[ $functData ])){
                    $data   = $templateParam[ $functData ];
                }
                else{
                    $functData  = ltrim($functData, '$');
                    $ref        = explode('->', $functData);
                    $functData  = $ref[0];
                    $data       = $templateParam[ $functData ];
                    for ($i = 1; $i < count($ref); $i++){
                        $method = $ref[$i];
                        $data = $data->{$method};
                    }
                }
                $html       = $this->get($funct['TEMPLATE'], 
                    $data
                );
                $content    = str_replace($key, $html, $content);
            }
            elseif ($funct['CLASS'] === 'method'){
                $controller = clone $this->getController();
                
                equal(!empty($funct['METHOD']), 'Не указан метод контроллера');
                equal(!empty($funct['DATA']), 'Не переданы данные методу');
                equal(method_exists($controller, $funct['METHOD']), 'Метод ' . get_class($controller) . '::' . $funct['METHOD'] . '() не найден');
                
                call_user_func(array($controller, $funct['METHOD']), $templateParam[ ltrim($funct['DATA'], '$') ]);
                $content    = str_replace($key, $controller->getHtml(), $content);
            }
        }
        
        
        return $content;
    }
    
    private function createModifiers($modifiers){
        $result    = array();
        $modifiers = explode('|', trim($modifiers, '|'));
        foreach ($modifiers as $modify) {

            $modify      = str_replace('\\:', 'DOTA', $modify);
            $params      = explode(':', $modify);
            $modify      = current($params);
            unset($params[0]);

            $file     = PATH_TEMPLATE_MODIFIERS . '/template_modify_' . $modify . '.php';
            equal(is_readable($file), 'Не найден файл с модификатором: ' . $file);
            require_once $file;
            
            foreach ($params as &$param){
                $param = str_replace('DOTA', ':', $param);
            }

            $result[]    = array(
        		'function'    => 'template_modify_' . $modify,
        	    'params'      => $params
            );
        }
        return $result;
    }

    final protected function run($templateName, $param){
        echo $this->get($templateName, $param);
    }

    public function getAction($actionName, $param = null){
        TemplateException::templateMethodNotFound($this, $actionName);
        ob_start();
        $this->$actionName($param);
        $content    = ob_get_clean();
        return $content;
    }
}

?>