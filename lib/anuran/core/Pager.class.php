<?php

require_once(PATH_CORE . '/Template.class.php');
require_once(PATH_CORE . '/PagerStrategy.class.php');

interface IPager{
    public function draw();
    
    public function setCountPage($count);
    public function isVisible();
}

/**
 * Класс-листалка, пейджер
 *
 * @uses Template
 * @uses PagerStrategy
 */
class Pager implements IPager
{
    private $itemT      = array('Pager', 'drawItem');
    private $separatorT = array('Pager', 'drawSeparator');
    private $currentT   = array('Pager', 'drawCurrentItem');
    private $currentP   = 1;
    /**
     * Количество страниц
     *
     * @var int
     */
    private $countP     = 1;

    /**
     * @var PagerStrategy
     */
    private $strategy   = null;

    final public function draw(){
        if (!isset($this->strategy)) {
            $this->setStrategy( new PagerStrategy() );
        }

        $a = $this->strategy->complete($this->currentP, $this->countP, $this->itemT, $this->currentT);
        $s = call_user_func($this->separatorT);
        foreach ($a as $page => &$t){
            $t = call_user_func($t, $page, Uri::add(array('page'=>$page)));
        }
        return implode($s, $a);
    }

    /**
     * Установить шаблон для отображения элементов
     * пейджера (страница 1, 2)
     *
     * @param call_user_function $user_function
     */
    public function setItemTemplate($user_function){
        $this->itemT        = $user_function;
    }

    /**
     * Установить шаблон для элемента текущей страницы
     *
     * @param call_user_function $user_function
     */
    public function setCurrentTemplate($user_function){
        $this->currentT     = $user_function;
    }

    /**
     * Шаблон разделителя между элементами
     *
     * @param call_user_function $user_function
     */
    public function setSeparatorTemplate($user_function){
        $this->separatorT   = $user_function;
    }

    /**
     * Установить страницу $page текущей
     *
     * @param int $page
     */
    public function setCurrentPage($page){
        $this->currentP = $page;
    }

    /**
     * Установить количество страниц для
     * пейджера
     *
     * @param int $count
     */
    public function setCountPage($count){
        $this->count    = $count;
        $this->countP   = $count;
    }

    /**
     * Выбрать стратегию отображения пейджера
     *
     * По умолчанию класс Pager использует
     * PagerStrategy, который в свою очередь
     * может не удоволетворять всем требованиям.
     * Например, если необходимо отображать только
     * ссылкы на первую, последнюю и текущею страницы,
     * то потребуется создать новый класс-наслледник
     * от PagerStrategy, который будет реализовывать
     * только логику представлений.
     *
     * @see PagerStrategy
     * @param PagerStrategy $strategy
     */
    public function setStrategy(PagerStrategy $strategy){
        $this->strategy = $strategy;
    }

    /**
     * Стандартный шаблон для элемента пейджера
     *
     * Конечно, ему тут не место, но, думаю, пока
     * и здесь немного поживет :)
     *
     * @param int $page
     * @param string $url
     * @return string
     */
    static private function drawItem($page, $url){
        return "<a href=\"". htmlspecialchars($url) ."\">$page</a>";
    }

    /**
     * Стандартный шаблон для элемента текущей страницы
     *
     * @param int $page
     * @param string $url
     * @return string
     */
    static private function drawCurrentItem($page, $url){
        return "<a href=\"". htmlspecialchars($url) ."\" class='active'>$page</a>";
    }

    /**
     * Стандартный шаблон разделителя между
     * элементами пейджера
     *
     * @return string
     */
    static private function drawSeparator(){
        return ", ";
    }
    
    public function isVisible(){
        return false;
    }

    /**
     * @return Pager
     */
    static public function create($page = null, $count = null, $countPage = null){
        return new Pager();
    }

    public function __toString(){
        return $this->draw();
    }

}

?>