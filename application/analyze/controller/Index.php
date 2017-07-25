<?php
/**
 * Created by PhpStorm.
 * User: wanqianjun
 * Date: 2017/7/24
 * Time: 18:16
 */

namespace app\analyze\controller;


use think\Db;

class Index
{
    public function Index ()
    {
        $product_count = Db::query("SELECT count(0) FROM comparision")[0]['count(0)'];
        $product_per_month = 0;
        $product_per_week = 0;
        $analyze_info = Db::query("SELECT * FROM Annalyze_result ORDER BY A_date LIMIT 1")[0];
        return json(['productCount' => $product_count, 'productPerMonth' => $product_count,
            'productPerWeek' => $product_per_week, 'analyzeInfo' => $analyze_info]);
    }
}