<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/20
 * Time: 13:54
 */

namespace app\queryer\controller;


use think\Db;

class Index
{
    public function suggest ()
    {
        $productName = input('productName');
        if (empty($productName)) {
            return Db::query("SELECT id, goods_name FROM t_product ORDER BY id DESC LIMIT 6", ["%{$productName}%"]);
        }
        return Db::query("SELECT id, goods_name FROM t_product WHERE goods_name LIKE ? LIMIT 6", ["%{$productName}%"]);
    }

    public function List ()
    {
        $product_name = input('get.productName', '');
        $product_id = input('get.productId', 0);
        $page = input('get.page', 1);
        $num_result = input('get.num_result', 6);
        $city = input('get.city', '深圳');
        $start = ($page - 1) * $num_result;
        if ($product_name != '') {
            return Db::query("SELECT P_last_update,goods_name,P_keyWord,P_Jddj_info FROM comparision WHERE P_city = ? AND comparision.P_keyWord LIKE ? LIMIT ?,?", [$city, "%{$product_name}%",
                $start, $num_result]);
        } else {
            return Db::query("SELECT P_last_update,goods_name,P_keyWord,P_Jddj_info FROM comparision WHERE P_city = ? AND P_Id = ? LIMIT ?,?", [$city, $product_id,
                $start, $num_result]);
        }
    }

    public function LocalInfo ()
    {
        $product_name = input('get.productName', '');
        $product_id = input('get.productId', 0);
        if ($product_name != '') {
            return Db::query("SELECT goods_name,price,logourl,weixin_shop_price,goods_inventory_original,goods_salenum FROM t_product WHERE 
goods_name LIKE ? AND goods_status = 1", ["%{$product_name}%"]);
        } else {
            return Db::query("SELECT goods_name,price,logourl,weixin_shop_price,goods_inventory_original,goods_salenum FROM t_product WHERE 
id = ? AND goods_status = 1", ["$product_id"]);
        }
    }
}