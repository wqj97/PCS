<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 10:31
 */

namespace app\queryer\controller;

use app\queryer\model\Jddj;
use think\Db;

class Jdquery
{
    public function Search ($product_name = '', $product_id = 0)
    {
        $requester = new Jddj();

        if (empty($product_name)) {
            $product_name = input('productName', '');
        }

        if (empty($product_id)) {
            $product_id = input('productId', 0);
        }

        if (empty($product_name) && empty($product_id)) {
            return json_faile();
        }

        if ($product_id != 0) {
            $product_name = Db::query('SELECT goods_name FROM t_product WHERE id = ?', [$product_id])[0]['goods_name'];
        }

        if ($result = $requester->search($product_name, $product_id)) {
            return json($result);
        } else {
            return json_faile('500', '查询失败, 失败原因' . json_encode($result));
        }
    }

    public function List ()
    {
        $product_name = input('get.productName', '');
        $product_id = input('get.productId', 0);
        $page = input('get.page', 1);
        $num_result = input('get.num_result', 6);
        $start = ($page - 1) * $num_result;
        $is_from_name = $product_name == '';
        if ($is_from_name) {
            return Db::query("SELECT * FROM comparision WHERE goods_name LIKE ? LIMIT ?,?", ["%{$product_name}%",
                $start, $num_result]);
        } else {
            return Db::query("SELECT * FROM comparision WHERE P_Id = ? LIMIT ?,?", [$product_id, $start, $num_result]);
        }
    }
}