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
        $product_count = Db::query("SELECT count(0) FROM comparition")[0]['count(0)'];
        $product_per_week = 0;
        $analyze_list = Db::query("SELECT * FROM Annalyze_result ORDER BY A_date");
        $analyze_info = $analyze_list[0];
        return json(['productCount' => $product_count, 'productPerMonth' => $product_count,
            'productPerWeek' => $product_per_week, 'analyzeInfo' => $analyze_info, 'analyzeDataList' => $analyze_list]);
    }

    public function getData ()
    {
        $id = input('get.Id');
        $file_path = Db::query("select A_result from Annalyze_result WHERE Id = ?",[$id])[0]['A_result'];
        return file_get_contents($file_path);
    }
}