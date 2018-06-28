<?php
/**
 * Created by PhpStorm.
 * User: THINK
 * Date: 2018/5/22
 * Time: 17:39
 */

header("content-type:text/html; charset=utf-8");
date_default_timezone_set("Asia/Shanghai");

ignore_user_abort();
set_time_limit(0);

/*$con = mysql_connect('localhost','root','jdhr2015../');

mysql_select_db('spsmall',$con);

$sql= 'select * from ims_ewei_shop_member';

$resul = mysql_query($sql);

while($row = mysql_fetch_array($resul))
{
    $us[] = $row;
}

$sql = "select *,sum(price) as allprice from ims_ewei_shop_order_goods where openid='ou0Y606sqcLLkhJbUm9cGH5bI9F'";
$od = mysql_query($sql);

while($od_row = mysql_fetch_array($od)){$od_us[] = $od_row;}


var_dump($od_us);*/

//1.造一个mysqli对象,造连接对象
$db = new MySQLi("localhost","root","jdhr2015../","spsmall");
//2.准备一条SQL语句
$sql = "select *,sum(price) as allprice from ims_ewei_shop_order_goods where openid='ou0Y606sqcLLkhJbUm9cGH5bI9F'";
//3.执行SQL语句,如果是查询语句，成功返回结果集对象
$reslut = $db->query($sql);

var_dump($reslut);

foreach ($us as $u){
    //$sql = "select *,sum(price) as allprice from ims_ewei_shop_order_goods where openid='".$u['openid']."'";
    //$od = mysql_query($sql);
    //while($od_row = mysql_fetch_array($od)){$od_us[] = $od_row;}


    if($od_us[0]['allprice'] >= 2999 ){
        //var_dump($od_us[0]['allprice']);
        /*$sql = "select *,sum(money) as sumprice from ims_ewei_shop_bonus_log where in_men='".$u['id']."' and type=3 ";
        $hd = mysql_query($sql);
        while($hd_row = mysql_fetch_array($hd)){$hd_us[] = $hd_row['sumprice'];}
        $credit2 = $u['credit2']+$hd_us[0]['sumprice'];
        $sql = "update ims_ewei_shop_member set `credit2`='".$credit2."'";
        mysql_query($sql);*/
    }
}
