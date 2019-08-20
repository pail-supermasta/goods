<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12.08.2019
 * Time: 16:39
 */

namespace Avaks\Goods;
use Avaks\MS\CurlMoiSklad;

/**
 * Класс Доставка сформирована Лотами для заказа
 */

class Cargo
{
    public $items;
    public $count;

    public function __construct($items)
    {
        $this->items = $items;
        return $this->items;
    }

    /**
     * Комплектация подразумевает распределение Лотов, входящих в
     * Отправление по Грузовым местам(запрос order/packing
     */

    public function setCargo()
    {
        $sticker = new Sticker();
        $sticker->glue();
    }

    /**
     * проверяет, какие Лоты в Отправлении он готов отгрузить, а какие не готов
     */

    public function validateCargo()
    {
        /*проверить каждый лот в МС*/

        $link = "/entity/customerorder/?search=705388479368808";
        $curlMS = new CurlMoiSklad;


        /*отправить запрос для каждого item*/
        foreach ($this->items as $item){
            $this->count++;

            /*отправить запрос*/
            $res = $curlMS->curlMS($link, false);
            echo $res;
        }
        return $this->count;
    }
}