<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 11:03
 */

namespace app\queryer\model;

use function PHPSTORM_META\type;
use TbkItemGetRequest;
use think\Db;
use think\Debug;
use TopClient;

class Tbk extends Spider
{
    /**
     * 通过名称搜索京东到家对应商品
     * @param string $product_name 商品名称
     * @param $product_id int 商品Id (optional)
     * @param string $city
     * @return mixed
     */
    public function search ($product_name, $product_id = 0, $city = '深圳')
    {
        #优化关键词
        $product_name = $this->optimize_keyWords($product_name);
        $response = $this->make_request($product_name, $city);
        # 当更换代理次数超过上限的时候
        if ($response == false) return false;
        # 讲结果转化为商店类
        $result = $this->parse($response, $city);
        # 持久化储存
        if ($product_id != 0) {
            $this->save_into_db($result, $product_id, 'Tbk', '', $city);
        } else {
            $this->save_into_db($result, 0, 'Tbk', $product_name, $city);
        }

        return $result;
    }

    /**
     * 解析搜索结果
     * @param mixed $response request传来的结果
     * @param $city
     * @return array
     */
    protected function parse ($response, $city)
    {
        $stores = [];

        if (empty($response->results->n_tbk_item)) return [null];

        $response = $response->results->n_tbk_item;
        $store = new Store('淘宝客', '/static/timg.jpeg', '全国');

        foreach ($response as $item) {
            $store->addProduct($item->title, $item->pict_url, $item->zk_final_price, $item->item_url);
        }
        $stores[] = $store;

        if (empty($stores)) {
            return [null];
        }
        return $stores;
    }

    /**
     * 发送请求
     * @param string $product_name 商品名称
     * @param string $city 在城市中搜索
     * @return mixed
     */
    protected function make_request ($product_name = '', $city)
    {
        $product_hash = 'TBK_' . sha1($product_name);
        if ($resp = cache($product_hash)) {
            return $resp;
        }
        import('Tbk.TopSdk', EXTEND_PATH);
        $c = new TopClient;
        $c->format = 'json';
        $c->appkey = $this->config['Tbk']->AppKey;
        $c->secretKey = $this->config['Tbk']->AppSecret;
        $req = new TbkItemGetRequest;
        $req->setFields("num_iid,title,pict_url,small_images,reserve_price,zk_final_price,user_type,provcity,item_url,seller_id,volume,nick");
        $req->setQ($product_name);
        $req->setIsTmall("true");
        $req->setIsOverseas("false");
        $resp = $c->execute($req);
        cache($product_hash, $resp);
        return $resp;
    }
}