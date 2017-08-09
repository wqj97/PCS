<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/24
 * Time: 10:33
 */

namespace app\queryer\model;


use think\Db;
use think\Log;

class Tm extends Spider
{
    protected $header = [
        "Host" => 'chaoshi.tmall.com',
        "Origin" => "https://chaoshi.tmall.com",
        "Referer" => "https://chaoshi.tmall.com",
        "User-Agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
        "Accept" => "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
    ];

    /**
     * 通过名称搜索京东到家对应商品
     * @param string $product_name 商品名称
     * @param $product_id int 商品Id (optional)
     * @param string $city
     * @return mixed
     */
    public function search ($product_name, $product_id = 0, $city = 'Shengzheng')
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
            $this->save_into_db($result, $product_id, '', $city);
        } else {
            $this->save_into_db($result, 0, $product_name, $city);
        }

        return $result;
    }

    /**
     * 发送请求
     * @param string $product_name 商品名称
     * @param string $city 在城市中搜索
     * @return mixed
     */
    protected function make_request ($product_name = '', $city)
    {
        $data = [
            "q" => iconv('UTF-8', "GBK", $product_name)
        ];

        return $this->request($data, $this->config['Tm']->queryUrl, 'true');

    }

    /**
     * 发送请求
     * @param $data mixed post 内容
     * @param $url string post url
     * @param $cordition_str string 判断请求成功的表达式: $response->code == 0
     * @return mixed
     */
    protected function request ($data, $url, $cordition_str)
    {
        if ($cache = cache(sha1(json_encode($data)))) {
            return $cache;
        }
        for ($i = 0; $i < $this->config['retry_times']; $i++) {
            $option = $this->create_option();
//            try {
            $session = new \Requests_Session($this->config['Tm']->sessionUrl, $this->header, null, $option);
            $response = json_decode($session->get($url)->body);
            debug($response);
            exit();
//                if (eval($cordition_str)) {
//                    cache(sha1(json_encode($data)), $response);
//                    return $response;
//                } else {
//                    $this->proxy_pool->freeze_ip($option[1]);
//                }
//            } catch (\Exception $e) {
//                $this->proxy_pool->freeze_ip($option[1]);
//                Log::error($e->getMessage());
//            }
        }
        return false;
    }
}