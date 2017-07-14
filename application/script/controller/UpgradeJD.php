<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 17:36
 */

namespace app\script\controller;


use app\queryer\model\Jddj;
use app\SpiderSetting;
use think\Db;

class UpgradeJD extends SpiderSetting
{
    /**
     * 开始脚本
     */
    public function index ()
    {

        $product_list = Db::query("SELECT id,name FROM t_product");

        $scrated_id = $this->get_scrated_id();
        $queryer = new Jddj();

        foreach ($product_list as $product) {
            # 跳过不需要更新的商品
            if (in_array($product['id'], $scrated_id)) {
                $last_update = Db::query("SELECT UNIX_TIMESTAMP(P_last_update) AS 'last_update_time' FROM `Product_comparison` WHERE P_Id = ?",
                    [$product['id']])[0]['last_update_time'];
                if ($last_update + $this->config['auto_update_frequency'] > time()) {
                    continue;
                } else {
                    $result = $queryer->Search($product['name']);
                    if ($result) {
                        $this->save_into_db($queryer->Search($product['name'], false), $product['id'], true);
                    } else {
                        echo "爬取: {$product['id']} 失败\r\n";
                        continue;
                    }
                }
            }
            $this->save_into_db($queryer->Search($product['name'], false), $product['id']);
        }
    }

    /**
     * 将爬取的结果存到数据库
     * @param $result object 爬取结果
     * @param $id int 本地商品Id
     * @param bool $update 是否使用更新模式
     */
    private function save_into_db ($result, $id, $update = false)
    {
        echo "爬取: {$id} 成功\r\n";
        $result = $this->parse($result);
        $encoded = json_encode($result);
        if ($update) {
            Db::execute("UPDATE `Product_comparison` SET P_Jddj_info = ? WHERE P_Id = ?", [$encoded, $id]);
        } else {
            Db::execute("INSERT INTO `Product_comparison` (P_Id, P_Jddj_info) VALUES (?,?)", [$id, $encoded]);
        }
    }

    /**
     * 解析搜索结果
     * @param $list
     * @return array
     */
    private function parse ($list)
    {
        $stores = [];
        foreach ($list as $store) {
            $item = $store->skuList;
            $store = [
                "name" => $item[0]->storeName,
                "product" => []
            ];
            foreach ($item as $product) {
                $product_each = [
                    "product_name" => $product->skuName,
                    "product_img" => $product->imgUrl,
                    "product_price" => $product->realTimePrice
                ];
                $store["product"][] = $product_each;
            }
            $stores[] = $store;
        }
        if (empty($stores)) {
            return [null];
        }
        return $stores;
    }

    /**
     * 将所有已经爬取过的id存下来, 如果已经存在 且超过更新时间, 才进行爬取
     * @return array
     */
    private function get_scrated_id ()
    {

        $db_list = Db::query("SELECT P_Id FROM `Product_comparison`");
        $ids = [];

        foreach ($db_list as $id) {
            $ids[] = $id['P_Id'];
        }

        return $ids;
    }
}