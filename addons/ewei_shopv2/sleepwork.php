<?php
/**
 * Created by PhpStorm.
 * User: THINK
 * Date: 2018/5/22
 * Time: 14:17
 */
$time=15;
$url="http://".$_SERVER['HTTP_HOST'].'/renrenshop/addons/ewei_shopv2/www/wepod.php';
/*
  function
*/
sleep($time);
file_get_contents($url);
