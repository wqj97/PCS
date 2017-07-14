<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/13
 * Time: 11:45
 */

namespace app\queryer\model;

use app\SpiderSetting;
use think\Cache;

class Listener extends SpiderSetting
{
    function __construct ()
    {
        parent::__construct();
        if ($this->config['auto_update'] == 0) {
            exception('自动更新已关闭');
        }
    }

    public function is_update ()
    {
        if (Cache::get('last_update') != false) {
            return true;
        } else {
            Cache::set('last_update', time(), $this->config['auto_update_frequency']);
            return false;
        }
    }
}