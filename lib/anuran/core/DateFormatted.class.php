<?php

class DateFormatted
{
	const MONTH_SHORT		= 1;
	const MONTH_LONG		= 2;
	const MONTH_SHORT_NOM	= 3;
	const MONTH_GENETIVE	= 4;

	const DAY_OF_WEEK_S		= 1;
	const DAY_OF_WEEK_L		= 1;

    static public function getMonth($num, $flag = null){
		$mon	= self::getMonths($flag);
        assert(isset($mon[ $num - 1 ]));
        return $mon[ $num - 1 ];
    }

    static public function getMonths($flag = null){
    	$flag	= isset($flag) ? $flag : self::MONTH_SHORT;

        $mon[self::MONTH_SHORT]		= array('янв', 'фев', 'мар', "апр", "мая", "июня", "июля", "авг", "сен", "окт", "ноя", "дек");
        $mon[self::MONTH_SHORT_NOM]	= array('янв', 'фев', 'мар', "апр", "мая", "июнь", "июль", "авг", "сен", "окт", "ноя", "дек");
        $mon[self::MONTH_LONG]		= array('январь', 'февраль', 'март',  "апрель", "май", "июнь", "июль", "август",  "сентябрь", "октябрь", "ноябрь", "декабрь");
        $mon[self::MONTH_GENETIVE]	= array('января', 'февраля', 'марта', "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
        return $mon[$flag];
    }

    static public function getDaysWeek($flag = null){
    	$flag	= (isset($flag)) ? $flag : self::DAY_OF_WEEK_S;
    	$days[ self::DAY_OF_WEEK_L ]	= array( 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье');
    	$days[ self::DAY_OF_WEEK_S ]	= array( 'пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс');

    	assert(isset($days[$flag]));
    	return $days[$flag];
    }

    static public function getDayOfWeek($day, $flag = null){
        $days   = self::getDaysWeek($flag);
        return $days[$day];
    }

    static public function humanDate($date = null, $template = '%D% %mon% %YYYY%г'){
        if ($date === null) return null;
        preg_match('/^(\d{4})-(\d{2})-(\d{2})\s*((\d{2}):(\d{2}):(\d{2}))?$/', $date, $result);
        $template	= str_replace('%D%'       , $result[3], $template);
        $template	= str_replace('%mon%'     , self::getMonth($result[2], self::MONTH_SHORT ), $template);
        $template	= str_replace('%month%'   , self::getMonth($result[2], self::MONTH_LONG ), $template);
        $template	= str_replace('%gmonth%'  , self::getMonth($result[2], self::MONTH_GENETIVE ), $template);
        $template	= str_replace('%YYYY%'    , $result[1], $template);
        $template	= str_replace('%YY%'      , substr($result[1], 2), $template);
        if (isset($result[4])){
            $template	= str_replace('%time%'    , $result[4], $template);
            $template	= str_replace('%H%'       , $result[5], $template);
            $template	= str_replace('%i%'       , $result[6], $template);
            $template	= str_replace('%s%'       , $result[7], $template);
        }
        return $template;
    }
    //XXX: решено использовать для этих целей новое поле в новостях.
    // Поиск будет теперь не по дате, а по этому полю.  
    static public function machineDate($date, $template = '%D% %mon% %YYYY%г'){
        assert(false);
        $regular['%D%']         = "(\d{1,2})";
        $regular['%mon%']       = "([\wа-я]+)"; 
        $regular['%month%']     = "([\wа-я]+)"; 
        $regular['%gmonth%']    = "([\wа-я]+)"; 
        $regular['%YY%']        = "(\d{2})"; 
        $regular['%YYYY%']      = "(\d{4})";
        
        $template               = preg_quote($template);
        $expression             = str_replace(array_keys($regular), $regular, $template );
        if (!preg_match('/^' . $expression . '$/', $date, $mathces)){
            equal(false, 'Неверные входные параметры');
        }
        
        preg_match_all('/%[\w]+%/', $template, $items);
        
        $year   = false;
        $month  = false;
        $day    = false;
        foreach ($items as $key => $item){
            dump($item, true);
            switch ($item) {
            	case "%YY%":
            	    dump('fuck');
            	   $year = '20' . $mathces[ $key + 1 ];
            	break;
            	
            	case '%YYYY%':
            	    $year = $mathces[ $key + 1];
            	break;
            	
            	case '%mon%':
            	    $aMonth    = self::getMonth(self::MONTH_SHORT);
            	    $month     = array_search($mathces[ $key + 1 ], $aMonth) + 1;
            	break;
            	
            	case '%gmonth%':
            	    $aMonth    = self::getMonth(self::MONTH_GENETIVE);
            	    $month     = array_search($mathces[ $key + 1 ], $aMonth) + 1;
            	break;
            	
            	default:
            		;
            	break;
            }
        }
        
//        preg_match('/(\d{4})-(\d{2})-(\d{2})/', $date, $result);
//        $template   = str_replace('%D%'       , $result[3], $template);
//        $template   = str_replace('%mon%'     , self::getMonth($result[2], self::MONTH_SHORT ), $template);
//        $template   = str_replace('%month%'   , self::getMonth($result[2], self::MONTH_LONG ), $template);
//        $template   = str_replace('%gmonth%'  , self::getMonth($result[2], self::MONTH_GENETIVE ), $template);
//        $template   = str_replace('%YYYY%'    , $result[1], $template);
//        $template   = str_replace('%YY%'      , substr($result[1], 2), $template);
//        return $template;
    }

}

?>