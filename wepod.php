<?php
/**
 * Created by PhpStorm.
 * User: THINK
 * Date: 2018/5/21
 * Time: 11:41
 */
header("content-type:text/html; charset=utf-8");
ignore_user_abort(); // 后台运行
set_time_limit(0); // 取消脚本运行时间的超时上限
$interval=3;// 每隔半小时运行，这个间隔时间是可以随着 需要进行修改
do{
    $myfile = fopen("newfile.txt", "a+") or die("Unable to open file!");
    $txt = date('H:i:s')."\n";
    fwrite($myfile, $txt);
    fclose($myfile);
    sleep($interval); // 休眠半小时
}while(true);

mysql_close($con);