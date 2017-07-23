<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/14
 * Time: 12:03
 */

namespace app\queryer\model;


use app\SpiderSetting;
use think\Debug;

class proxy_pool extends SpiderSetting
{
    private $proxy_pool;
    private $freeze_ip;

    function __construct ()
    {
        parent::__construct();
        if (!\cache('proxy_pool') || !cache('freeze_ip')){
            $this->proxy_pool = $this->config['proxy_pool'];
            $this->freeze_ip = [];
            cache('proxy_pool', $this->proxy_pool);
            cache('freeze_ip', []);
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
        $ip = array_splice($this->proxy_pool, $key, 1)[0];
        cache('proxy_pool', $this->proxy_pool);
        $this->freeze_ip[] = $ip;
        cache('freeze_ip', $this->freeze_ip);
    }

    /**
     * 显示代理池状态
     */

    public static function get_proxy_pool_state ()
    {
        echo "正在使用的Ip: <br />";
        Debug::dump(\cache('proxy_pool'));
        echo "已经暂停使用的 Ip: <br />";
        Debug::dump(\cache('freeze_ip'));
        echo "<script>setTimeout(function() {
                 window.location.reload()
              }, 5000)</script>";
    }
}