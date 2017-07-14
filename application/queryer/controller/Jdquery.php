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
            $product_name = input('productName');
        }
        if (empty($product_id)) {
            $product_id = input('product_id', 0);
        }
        if (empty($product_name) && empty($product_id)) {
            return json_faile();
        }
        if ($product_id != 0) {
            $product_name = Db::query('SELECT name FROM t_product WHERE id = ?', [$product_id])[0]['name'];
        }

        if ($result = $requester->search($product_name, $product_id)) {
            return json($result);
        } else {
            return json_faile('500', '查询失败, 失败原因' . json_encode($result));
        }
    }
}