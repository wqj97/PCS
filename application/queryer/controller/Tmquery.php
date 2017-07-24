<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/24
 * Time: 10:32
 */

namespace app\queryer\controller;


use app\queryer\model\Queryer;
use app\queryer\model\Tm;
use think\Db;

class Tmquery extends Queryer
{
    public function Search ($product_name = '', $product_id = 0)
    {
        $requester = new Tm();
        $city = input('post.city', '深圳');
        if (empty($product_name)) {
            $product_name = input('post.productName', '');
        }

        if (empty($product_id)) {
            $product_id = input('post.productId', 0);
        }

        if (empty($product_name) && empty($product_id)) {
            return json_fail();
        }

        if ($product_id != 0) {
            $product_name = Db::query('SELECT goods_name FROM t_product WHERE id = ?', [$product_id])[0]['goods_name'];
        }

        if ($result = $requester->search($product_name, $product_id, $city)) {
            return json($result);
        } else {
            return json_fail('500', '查询次数超过上线');
        }
    }
}