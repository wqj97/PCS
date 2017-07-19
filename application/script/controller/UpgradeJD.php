<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 17:36
 */

namespace app\script\controller;


use app\queryer\controller\Jdquery;
use app\queryer\model\Jddj;
use app\SpiderSetting;
use think\Db;

class UpgradeJD extends SpiderSetting
{
    /**
     * 开始脚本
     */
    public function Start ()
    {
        ini_set('max_execution_time', 0);
        $queryer = new Jdquery();
//        $products = Db::query("SELECT id
//FROM t_product
//WHERE t_product.id NOT IN (SELECT P_Id
//                       FROM product_comparison
//                       WHERE unix_timestamp(now()) -unix_timestamp(P_last_update) > ?)",[$this->config['auto_update_frequency']]);
        $products = Db::query("SELECT id
FROM t_product
WHERE t_product.id NOT IN (SELECT P_Id
                       FROM product_comparison)");
        foreach ($products as $id) {
            $queryer->Search('', $id['id']);
            echo "{$id['id']} has updated<br />";
        }
    }
}