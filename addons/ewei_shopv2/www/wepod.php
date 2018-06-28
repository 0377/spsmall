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
date_default_timezone_set("Asia/Shanghai");
$con = mysql_connect('localhost','root','12345678');

if(!$con){
    var_dump('连接失败');
    exit();
}
mysql_select_db('we7173',$con);

do{


    $sql= 'select * from ims_ewei_shop_healthy';

    $esul = mysql_query($sql);

    while($row = mysql_fetch_array($esul))
    {
        $us_healthy[] = $row;
    }

    foreach ($us_healthy as $hy){

        if($hy['healthy_integral'] >= 1.5  ){
            $h_y['healthy_integral'] = $hy['healthy_integral']-1.5;
            $h_y['healthy_money'] = $hy['healthy_money']+1.5;
            $sql = "update ims_ewei_shop_healthy set `healthy_integral`='".$h_y['healthy_integral']."',`healthy_money`='".$h_y['healthy_money']."'where `id`='".$hy['id']."' and `status`=1";
            @mysql_query($sql);

            $datetime = date('Y-m-d H:i:s',time());
            $sql = "insert into ims_ewei_shop_healthy_log (`user_id`,`add_integral`,`integral`,`datetime`,`type`,`status`) VALUES ('".$hy['user_id']."','1.5','".$h_y['healthy_money']."','".$datetime."','2','1')";
            @mysql_query($sql);

        }

    }

    sleep($interval); // 休眠半小时
}while(true);

mysql_close($con);