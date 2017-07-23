<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/19
 * Time: 00:47
 */

namespace app\queryer\model;


use think\Db;

class Queryer
{
    public function decodeGBKurlEncode ()
    {
        $input = input('str');
        $decoded = urldecode($input);
        echo iconv('GB2312', 'UTF-8',$decoded);
    }
}