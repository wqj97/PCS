<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 11:03
 */

namespace app\queryer\model;

class Jddj extends Spider
{


    /**
     * 通过名称搜索京东到家对应商品
     * @param string $product_name 商品名称
     * @param $product_id int 商品Id (optional)
     * @param string $city
     * @return mixed
     */
    public function search ($product_name, $product_id = 0, $city = 'Shengzheng')
    {
        $response = $this->make_request($product_name, $city);

        # 当更换代理次数超过上限的时候
        if ($response == false) return false;

        # 当京东到家给出提示词的时候, 返回空列表
        if (!empty($response->result->promptWord)) return [null];

        # 讲结果转化为商店的形式
        $result = $this->parse($response->result->storeSkuList, $city);

        # 持久化储存
        $this->save_into_db($result, $product_id);

        return $result;
    }

    /**
     * 解析搜索结果
     * @param $list
     * @param $city
     * @return array
     */
    private function parse ($list, $city)
    {
        $stores = [];
        foreach ($list as $store) {
            $item = $store->skuList;
            $store = new Store($item[0]->storeName, $city);

            foreach ($item as $product) {
                $store->addProduct($product->skuName, $product->imgUrl, $product->realTimePrice);
            }
            $stores[] = $store;
        }
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

        $position = $this->getPosition($city);

        $body = [
            "longitude" => $position[0],
            "latitude" => $position[1],
            "type" => 2,
            "platCode" => "H5",
            "key" => $product_name,
            "page" => 1,
            "pageSize" => $this->config['Jddj']->pageSize,
            "sortType" => 1
        ];
        $data = [
            "functionId" => $this->config["Jddj"]->functionId,
            "lng" => $position[0],
            "lat" => $position[1],
            "city_id" => $position[2],
            "appVersion" => $this->config["Jddj"]->appVersion,
            "appName" => $this->config["Jddj"]->appName,
            "body" => json_encode($body)
        ];

        return parent::request($data, $this->config['Jddj']->url);

    }
}