<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 10:31
 */

namespace app\queryer\controller;

use app\queryer\model\Jddj;
use app\queryer\model\Queryer;
use think\Db;

class Jdquery extends Queryer
{
    public function Search ($product_name = '', $product_id = 0)
    {
        $requester = new Jddj();
        $city = input('post.city', '深圳');
        if (empty($product_name)) {
            $product_name = input('post.productName', '');
        }

        if (empty($product_id)) {
            $product_id = input('post.productId', 0);
        }

        if (empty($product_name) && empty($product_id)) {
            return json_faile();
        }

        if ($product_id != 0) {
            $product_name = Db::query('SELECT goods_name FROM t_product WHERE id = ?', [$product_id])[0]['goods_name'];
        }

        if ($result = $requester->search($product_name, $product_id, $city)) {
            return json($result);
        } else {
            return json_faile('500', '查询失败, 失败原因' . json_encode($result));
        }
    }
}