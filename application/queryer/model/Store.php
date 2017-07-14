<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/15
 * Time: 00:36
 */

namespace app\queryer\model;


class Store
{
    public $store_name;
    public $products = [];
    public $city;

    function __construct ($store_name, $city)
    {
        $this->store_name = $store_name;
        $this->city = $city;
    }

    /**
     * 向商店中添加新商品
     * @param $product_name
     * @param $product_img
     * @param $product_price
     */
    public function addProduct ($product_name, $product_img, $product_price)
    {
        $this->products[] = [
            'product_name' => $product_name,
            'product_img' => $product_img,
            'product_price' => $product_price
        ];
    }
}