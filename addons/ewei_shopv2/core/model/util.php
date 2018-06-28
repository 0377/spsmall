<?php
class Util_EweiShopV2Model
{
	public function getExpressList($express, $expresssn)
	{
		global $_W;
		$express_set = $_W['shopset']['express'];
		$express = $express == 'jymwl' ? 'jiayunmeiwuliu' : $express;
		$express = $express == 'TTKD' ? 'tiantian' : $express;
		$express = $express == 'jjwl' ? 'jiajiwuliu' : $express;
		$express = $express == 'zhongtiekuaiyun' ? 'ztky' : $express;
		load()->func('communication');
		if (!empty($express_set['isopen']) && !empty($express_set['apikey'])) {
			if (!empty($express_set['cache']) && 0 < $express_set['cache']) {
				$cache_time = $express_set['cache'] * 60;
				$cache = pdo_fetch('SELECT * FROM' . tablename('ewei_shop_express_cache') . 'WHERE express=:express AND expresssn=:expresssn LIMIT 1', array('express' => $express, 'expresssn' => $expresssn));
				if (time() <= $cache['lasttime'] + $cache_time && !empty($cache['datas'])) {
					return iunserializer($cache['datas']);
				}
			}

			if ($express_set['isopen'] == 1) {
				$url = 'http://api.kuaidi100.com/api?id=' . $express_set['apikey'] . '&com=' . $express . '&nu=' . $expresssn;
				$params = array();
			}
			else {
				$url = 'http://poll.kuaidi100.com/poll/query.do';
				$params = array('customer' => $express_set['customer'], 'param' => json_encode(array('com' => $express, 'num' => $expresssn)));
				$params['sign'] = md5($params['param'] . $express_set['apikey'] . $params['customer']);
				$params['sign'] = strtoupper($params['sign']);
			}

			$response = ihttp_post($url, $params);
			$content = $response['content'];
			$info = json_decode($content, true);
		}

		if (!isset($info) || empty($info['data']) || !is_array($info['data'])) {
			$url = 'https://www.kuaidi100.com/query?type=' . $express . '&postid=' . $expresssn . '&id=1&valicode=&temp=';
			$response = ihttp_request($url);
			$content = $response['content'];
			$info = json_decode($content, true);
			$useapi = false;
		}
		else {
			$useapi = true;
		}

		$list = array();
		if (!empty($info['data']) && is_array($info['data'])) {
			foreach ($info['data'] as $index => $data) {
				$list[] = array('time' => trim($data['time']), 'step' => trim($data['context']));
			}
		}

		if ($useapi && 0 < $express_set['cache'] && !empty($list)) {
			if (empty($cache)) {
				pdo_insert('ewei_shop_express_cache', array('expresssn' => $expresssn, 'express' => $express, 'lasttime' => time(), 'datas' => iserializer($list)));
			}
			else {
				pdo_update('ewei_shop_express_cache', array('lasttime' => time(), 'datas' => iserializer($list)), array('id' => $cache['id']));
			}
		}

		return $list;
	}

	public function getIpAddress()
	{
		$ipContent = file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js');
		$jsonData = explode('=', $ipContent);
		$jsonAddress = substr($jsonData[1], 0, -1);
		return $jsonAddress;
	}

	public function checkRemoteFileExists($url)
	{
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		$result = curl_exec($curl);
		$found = false;

		if ($result !== false) {
			$statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if ($statusCode == 200) {
				$found = true;
			}
		}

		curl_close($curl);
		return $found;
	}

	/**
     * 计算两组经纬度坐标 之间的距离
     * params ：lat1 纬度1； lng1 经度1； lat2 纬度2； lng2 经度2； len_type （1:m or 2:km);
     * return m or km
     */
	public function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
	{
		$pi = 3.1415926000000001;
		$er = 6378.1369999999997;
		$radLat1 = $lat1 * $pi / 180;
		$radLat2 = $lat2 * $pi / 180;
		$a = $radLat1 - $radLat2;
		$b = $lng1 * $pi / 180 - $lng2 * $pi / 180;
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
		$s = $s * $er;
		$s = round($s * 1000);

		if (1 < $len_type) {
			$s /= 1000;
		}

		return round($s, $decimal);
	}

	public function multi_array_sort($multi_array, $sort_key, $sort = SORT_ASC)
	{
		if (is_array($multi_array)) {
			foreach ($multi_array as $row_array) {
				if (is_array($row_array)) {
					$key_array[] = $row_array[$sort_key];
				}
				else {
					return false;
				}
			}
		}
		else {
			return false;
		}

		array_multisort($key_array, $sort, $multi_array);
		return $multi_array;
	}

	public function get_area_config_data($uniacid = 0)
	{
		global $_W;

		if (empty($uniacid)) {
			$uniacid = $_W['uniacid'];
		}

		$sql = 'select * from ' . tablename('ewei_shop_area_config') . ' where uniacid=:uniacid limit 1';
		$data = pdo_fetch($sql, array(':uniacid' => $uniacid));
		return $data;
	}

	public function get_area_config_set()
	{
		global $_W;
		$data = m('common')->getSysset('area_config');

		if (empty($data)) {
			$data = $this->get_area_config_data();
		}

		return $data;
	}

	public function pwd_encrypt($string, $operation, $key = 'key')
	{
		$key = md5($key);
		$key_length = strlen($key);
		$string = $operation == 'D' ? base64_decode($string) : substr(md5($string . $key), 0, 8) . $string;
		$string_length = strlen($string);
		$rndkey = $box = array();
		$result = '';
		$i = 0;

		while ($i <= 255) {
			$rndkey[$i] = ord($key[$i % $key_length]);
			$box[$i] = $i;
			++$i;
		}

		$j = $i = 0;

		while ($i < 256) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
			++$i;
		}

		$a = $j = $i = 0;

		while ($i < $string_length) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
			++$i;
		}

		if ($operation == 'D') {
			if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8)) {
				return substr($result, 8);
			}

			return '';
		}

		return str_replace('=', '', base64_encode($result));
	}

	public function location($lat, $lng)
	{
		$newstore_plugin = p('newstore');

		if ($newstore_plugin) {
			$newstore_data = m('common')->getPluginset('newstore');
			$key = $newstore_data['baidukey'];
		}

		if (empty($key)) {
			$key = 'ZQiFErjQB7inrGpx27M1GR5w3TxZ64k7';
		}

		$url = 'http://api.map.baidu.com/geocoder/v2/?callback=renderReverse&location=' . $lat . ',' . $lng . '&output=json&pois=1&ak=' . $key;
		$fileContents = file_get_contents($url);
		$contents = ltrim($fileContents, 'renderReverse&&renderReverse(');
		$contents = rtrim($contents, ')');
		$data = json_decode($contents, true);
		return $data;
	}

	public function geocode($address, $key = 0)
	{
		if (empty($key)) {
			$key = '7e56a024f468a18537829cb44354739f';
		}

		$address = str_replace(' ', '', $address);
		$url = 'http://restapi.amap.com/v3/geocode/geo?address=' . $address . '&key=' . $key;
		$contents = file_get_contents($url);
		$data = json_decode($contents, true);
		return $data;
	}

	//jacky add
	public function get_bonus($orderid,$member){
        $og_array = m('order')->checkOrderGoods($orderid);
        $item = pdo_fetchall('select * from ' . tablename('ewei_shop_order_goods') . '  where  orderid=:orderid', array(':orderid' => $orderid));
        foreach ($item as $it){
            $goods = pdo_fetch('select * from ' . tablename('ewei_shop_goods') . '  where  id=:id limit 1', array(':id' => $it['goodsid']));
            $healthy = pdo_fetch('select * from ' . tablename('ewei_shop_healthy') . '  where  user_id=:user_id limit 1', array(':user_id' => $og_array['user_id']));
            $mc_member = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $member['uid']));
            if($mc_member){
                $member['credit1'] = $mc_member['credit1'];
            }
            $goods_id = $it['goodsid'];
            $user_id = $og_array['user_id'];
            $money = $it['price'];
            $ordersn = $og_array['ordersn'];
            if($goods['special'] == 3){
                if($healthy){
                    $date_log = array(
                        'user_id' => $og_array['user_id'],
                        'add_integral'=> $it['price'],
                        'integral' => $member['credit1']+$it['price'],
                        'datetime' => date('Y-m-d H:i:s'),
                        'type' => '4',
                        'status' => '1'
                    );
                    pdo_insert('ewei_shop_healthy_log', $date_log);

                }
            }elseif($goods['special'] == 2 ){
                if(!$healthy) {
                    pdo_insert('ewei_shop_healthy',array('user_id'=>$member['id'],'datetime'=>date('Y-m-d H:i:s')));
                }
				$date = array(
					'healthy_integral'=> $member['credit1']+$it['price'],
					'datetime' => date('Y-m-d H:i:s')
				);
				pdo_update('ewei_shop_healthy',$date,array('user_id' => $og_array['user_id']));
				pdo_update('ewei_shop_member',array('credit1'=>$date['healthy_integral']),array('id' => $og_array['user_id']));
				if($mc_member) pdo_update('mc_members',array('credit1'=>$date['healthy_integral']),array('uid' => $member['uid']));
				$date_log = array(
					'user_id' => $og_array['user_id'],
					'add_integral'=> $it['price'],
					'integral' =>$member['credit1']+$it['price'],
					'datetime' => date('Y-m-d H:i:s'),
					'type' => '1',
					'status' => '1'
				);
				pdo_insert('ewei_shop_healthy_log', $date_log);
                $bv_list = pdo_fetchall("select * from ".tablename('ewei_shop_healthy_log')."where `user_id` = :user_id and `type` = '4'",array(':user_id' => $member['id']));
                if(!$bv_list){
                    $date_log = array(
                        'user_id' => $og_array['user_id'],
                        'add_integral'=> $it['price'],
                        'integral' => $member['credit1']+$it['price'],
                        'datetime' => date('Y-m-d H:i:s'),
                        'type' => '4',
                        'status' => '1'
                    );
                    pdo_insert('ewei_shop_healthy_log', $date_log);
                }
				$nb = m('bonus')->get_pay($user_id, $money, $ordersn, $goods_id);

                pdo_update('ewei_shop_member',array('credit0'=>$member['credit0']+$it['price']),array('id' => $member['id']));
            }else{
                $nb =m('bonus')->get_pay_one($user_id, $money, $ordersn, $goods_id);
            }

            return $nb;
        }
	}

    public function add_bonus($orderid,$member){
        $og_array = m('order')->checkOrderGoods($orderid);
        $item = pdo_fetchall('select * from ' . tablename('ewei_shop_order_goods') . '  where  orderid=:orderid', array(':orderid' => $orderid));
        $bonus_me = pdo_fetch('select * from '.tablename('ewei_out_men').'where out_men = :out_men ',array(':out_men' => $member['id']));
		if(!$bonus_me){
            pdo_insert('ewei_out_men',array('out_men' => $member['id']));
		}
        foreach ($item as $it){
            $goods = pdo_fetch('select * from ' . tablename('ewei_shop_goods') . '  where  id=:id limit 1', array(':id' => $it['goodsid']));
            $healthy = pdo_fetch('select * from ' . tablename('ewei_shop_healthy') . '  where  user_id=:user_id limit 1', array(':user_id' => $og_array['user_id']));
        	if($goods['special'] == 3){

			}elseif($goods['special'] == 2 ){
                if(!$healthy) {
                    pdo_insert('ewei_shop_healthy',array('user_id'=>$member['id'],'datetime'=>date('Y-m-d H:i:s')));
                }
            }
        }
    }
}

if (!defined('IN_IA')) {
	exit('Access Denied');
}

?>
