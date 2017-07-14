<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 15:33
 */

namespace app;


use think\Db;

class SpiderSetting
{
    protected $config;

    /**
     * 读取数据库配置
     * Listener constructor.
     */
    function __construct ()
    {
        if ($config = cache('setting')) {
            $this->config = $config;
        } else {
            $settings = Db::query('SELECT * FROM Spider_setting');
            foreach ($settings as $val) {
                $this->config[$val['SS_key']] = json_decode($val['SS_val']);
            }
            cache('setting', $this->config);
        }
    }

    /**
     * 清空设置缓存
     */
    protected function flush_setting ()
    {
        cache('setting', null);
    }
}