<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/14
 * Time: 12:03
 */

namespace app\queryer\model;


use app\SpiderSetting;
use think\Cache;

class proxy_pool extends SpiderSetting
{
    private $proxy_pool;
    private $freeze_ip;

    function __construct ()
    {
        parent::__construct();
        if (!Cache::has('proxy_pool') || !Cache::has('freeze_ip')) {
            Cache::set('proxy_pool', $this->config['proxy_pool']);
        }
    }

    private function refresh_cache ()
    {
        $this->proxy_pool = cache('proxy_pool');
        if (!$this->freeze_ip = cache('freeze_ip')) {
            $this->freeze_ip = [];
        }
    }

    /**
     * 从代理池中取出一个ip
     * @return mixed
     */
    public function get_ip ()
    {
        if ($this->config['proxy_sleep'] != 0) {
            sleep($this->config['proxy_sleep']);
        }

        $this->refresh_cache();

        while (count($this->proxy_pool) < 10) {
            $this->proxy_pool[] = array_shift($this->freeze_ip);
        }

        $key = random_int(0, count($this->proxy_pool) - 1);

        return [$this->proxy_pool[$key], $key];
    }

    /**
     * 冻结一个ip
     * @param $key int
     */
    public function freeze_ip ($key)
    {
        $ip = $this->proxy_pool[$key];
        array_splice($this->proxy_pool, $key, 1);
        $this->freeze_ip[] = $ip;
        cache('freeze_ip', $this->freeze_ip);
    }
}