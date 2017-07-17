<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/14
 * Time: 23:54
 */

namespace app\queryer\model;


use app\SpiderSetting;
use Requests;
use think\Db;
use think\Exception;
use think\Log;

class Spider extends SpiderSetting
{
    protected $header = [
        "Host" => 'daojia.jd.com',
        "Origin" => "https://daojia.jd.com",
        "Referer" => "https://daojia.jd.com/html/index.html",
        "User-Agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
        "Accept" => "application/json",
        "Content-Type" => "application/x-www-form-urlencoded",
        "X-Requested-With" => "XMLHttpRequest"
    ];

    protected $proxy_pool;

    function __construct ()
    {
        parent::__construct();
        $this->proxy_pool = new proxy_pool();
    }

    /**
     * 获取坐标位置和城市代码
     * @param string $city 城市名
     * @return array 1.经度 2.纬度 3.城市Id
     */
    protected function getPosition ($city = 'Shengzheng')
    {
        return $this->config['Jddj']->city->$city;
    }

    /**
     * 构造一个用于 Requests 的 Option
     * @return array 1. option, 2. ip的Key, 用于在ip无效的时候, 冻结这个ip
     */
    protected function create_option ()
    {
        $random_ip = $this->proxy_pool->get_ip();
        $options = [];
        if (empty($random_ip[0])) {
            return [$options, $random_ip[1]];
        } else {
            $options['proxy'] = $random_ip[0];
            return [$options, $random_ip[1]];
        }
    }

    /**
     * 将爬取的结果存到数据库
     * @param $result array 爬取结果
     * @param $id int 本地商品Id
     * @param $keyWord string 关键词
     */
    protected function save_into_db ($result, $id = 0, $keyWord = '')
    {
        $encoded = json_encode($result);
        $from_id = $id != 0;
        if ($from_id) {
            $last_update_time = Db::query("SELECT unix_timestamp(P_last_update) AS 'last_update_time' FROM product_comparison WHERE P_Id = ?",
                [$id]);
        } else {
            $last_update_time = Db::query("SELECT unix_timestamp(P_last_update) AS 'last_update_time' FROM product_comparison WHERE P_keyWord = ?",
                [$keyWord]);
        }
        if (empty($last_update_time)) {
            $last_update_time = 0;
        } else {
            $last_update_time = $last_update_time[0]['last_update_time'];
        }
        if ($last_update_time + $this->config['auto_update_frequency'] > time()) {
            return;
        }
        if ($from_id) {
            Db::execute("INSERT INTO `product_comparison` (P_Id, P_Jddj_info) VALUES (?,?)", [$id, $encoded]);
        } else {
            Db::execute("INSERT INTO `product_comparison` (P_keyWord, P_Jddj_info) VALUES (?,?)", [$keyWord, $encoded]);
        }
    }

    /**
     * 发送请求
     * @param $data mixed post 内容
     * @param $url string post url
     * @return mixed
     * @throws Exception 当所有代理以及失效的时候, 抛出异常, 终止程序
     */
    protected function request ($data, $url)
    {
        if ($cache = cache($url)) {
            return $cache;
        }
        for ($i = 0; $i < $this->config['retry_times']; $i++) {
            $option = $this->create_option();
            try {
                $response = json_decode(Requests::post($url, $this->header, $data, $option[0])->body);
                if ($response->code == 0) {
                    cache($url, $response);
                    return $response;
                } else {
                    $this->proxy_pool->freeze_ip($option[1]);
                }
            } catch (\Exception $e) {
                $this->proxy_pool->freeze_ip($option[1]);
                Log::error($e->getMessage());
            }
        }
        throw new Exception('超过尝试次数');
    }

}