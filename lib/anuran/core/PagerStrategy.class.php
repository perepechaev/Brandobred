<?php

require_once(PATH_CORE . '/Pager.class.php');

/**
 * Реализует наипростейший алгоритм
 * выбора страниц пейджера
 *
 */
class PagerStrategy
{
    /**
     * Возвращает список (массив) страниц, которые
     * необходимо отобразить в пейджере
     *
     * @param int $currentPage Текущая страница
     * @param int $countPage Количество страниц
     * @param call_user_function $item Шаблон для активных страниц
     * @param call_user_function $cur Шаблон для текущей страницы
     * @return array
     */
    public function complete($currentPage, $countPage, $item, $cur){
        if ($countPage === 1){
            return array();
        }
        $pages  = array_fill(1, $countPage, true );
        foreach ($pages as $page => &$val){
            $val    = ($currentPage == $page) ? $cur : $item;
        }
        return $pages;
    }
}


?>