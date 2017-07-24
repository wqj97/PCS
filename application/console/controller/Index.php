<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/24
 * Time: 14:41
 */

namespace app\console\controller;


use app\queryer\model\proxy_pool;
use Linfo\Linfo;

class Index
{
    public function Index ()
    {
        $linfo = new Linfo();
        $parser = $linfo->getParser();
        $memory = round(memory_get_usage() / memory_get_peak_usage(),2);
        return json(["proxyPoolStatus" => proxy_pool::get_proxy_pool_state(), "memory" => $memory, "cpu" =>
            (float)$parser->getLoad()['5min']]);
    }

}