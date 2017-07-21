<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/17
 * Time: 17:40
 */

namespace app\queryer\controller;


use app\SpiderSetting;
use think\Db;
class Setting extends SpiderSetting
{
    public function get ()
    {
        $key = input('get.key');
        if (isset($this->config[$key])) {
            return json($this->config[$key]);
        } else {
            return json_faile(403, 'param doesn\'t exist');
        }
    }

    public function set ()
    {
        $key = input('post.key');
        $val = input('post.val');
        Db::query("UPDATE Spider_setting SET SS_val = ? WHERE SS_key = ?", [$val, $key]);
        $this->flush_setting();
        return json_success();
    }

    public function list ()
    {
        return json($this->config);
    }

}