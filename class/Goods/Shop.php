<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 06.09.2019
 * Time: 17:31
 */

namespace Avaks\Goods;


class Shop
{

    public $name;
    public $token;

    /**
     * Shop constructor.
     * @param $name
     * @param $token
     */
    public function __construct($name, $token)
    {
        $this->name = $name;
        $this->token = $token;
    }

}