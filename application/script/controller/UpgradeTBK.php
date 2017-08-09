<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 17:36
 */

namespace app\script\controller;


use app\queryer\controller\Tbkquery;
use app\queryer\model\Tbk;
use app\SpiderSetting;
use think\Db;

class UpgradeTBK extends SpiderSetting
{
    /**
     * 开始脚本
     */
    public function Start ()
    {
        ini_set('max_execution_time', 0);
        $queryer = new Tbk();

        $products = Db::query("SELECT id
FROM t_product
WHERE t_product.id NOT IN (
SELECT P_Id
FROM product_comparison
WHERE unix_timestamp(now()) - unix_timestamp(P_last_update) < ?
GROUP BY P_Id
)",[$this->config['auto_update_frequency']]);

//        $products = Db::query("SELECT id
//FROM t_product
//WHERE t_product.id NOT IN (SELECT P_Id
//                       FROM product_comparison)");
        foreach ($products as $id) {
            $product_name = Db::query('SELECT goods_name FROM t_product WHERE id = ?', [$id['id']])[0]['goods_name'];
            $queryer->Search($product_name, $id['id']);
            echo "{$product_name}-{$id['id']} has updated<br />";
        }
    }
}