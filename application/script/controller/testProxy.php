<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/14
 * Time: 17:08
 */

namespace app\script\controller;


use app\SpiderSetting;
use think\Db;

class testproxy extends SpiderSetting
{
    /**
     * 开始测试脚本
     */
    public function starTrsting ()
    {
        ini_set('max_execution_time', 0);
        $stable_ip = [];
        $header = [
            "User-Agent" => "Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1",
        ];
        foreach ($this->config['proxy_pool'] as $ip) {
            if ($ip == '') continue;
            echo "{$ip} -- ";
            try {
                $result = \Requests::post('http://ipecho.net/plain', $header, [], ['proxy' => $ip])->body;
                $true_ip = explode(':', $ip)[0];
                if ($result != $true_ip) {
                    throw new \Exception('bad proxy');
                }
            } catch (\Exception $e) {
                echo "<span style='color:red'>error</span><div style='display: inline-block;white-space: nowrap'>";
                print_r($e->getMessage());
                echo "</div><br />";
                continue;
            }
            echo "<span style='color: green'>ok</span><br />";
            $stable_ip[] = $ip;
        }
        $stable_ip = json_encode($stable_ip);
        Db::execute('UPDATE Spider_setting SET SS_val = ? WHERE SS_key = ?', [$stable_ip, 'proxy_pool']);
    }

    public function getIps ()
    {
        return json(Db::query("SELECT SS_val FROM Spider_setting WHERE SS_key = 'proxy_pool'")[0]['SS_val']);
    }
}