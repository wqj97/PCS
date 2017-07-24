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
    public $store_pic;
    public $products = [];
    public $city;
    public $extraInfo = [];

    function __construct ($store_name,$store_pic, $city)
    {
        $this->store_name = $store_name;
        $this->store_pic = $store_pic;
        $this->city = $city;
    }

    /**
     * 向商店中添加新商品
     * @param $product_name
     * @param $product_img
     * @param $product_price
     * @param string $product_url
     */
    public function addProduct ($product_name, $product_img, $product_price, $product_url = '')
    {
        $this->products[] = [
            'product_name' => $product_name,
            'product_img' => $product_img,
            'product_price' => $product_price,
            'product_url' => $product_url
        ];
    }

    /**
     * 添加额外备注信息
     * @param $info_name string 信息名称
     * @param $info_content string 信息内容
     */
    public function addExtraInfo ($info_name, $info_content)
    {
        $this->extraInfo[] = [$info_name => $info_content];
    }
}