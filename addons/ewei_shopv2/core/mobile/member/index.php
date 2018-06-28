<?php
if (!(defined('IN_IA'))) {
	exit('Access Denied');
}

class Index_EweiShopV2Page extends MobileLoginPage
{
	public function main()
	{
		global $_W;
		global $_GPC;
		$member = m('member')->getMember($_W['openid'], true);
		$level = m('member')->getLevel($_W['openid']);

		if(empty($member['uuid']) or $member['uuid']<'10001688'){
            $one_mb = pdo_fetch('select * from '.tablename('ewei_number_id').' where id = :id limit 1',array(':id' => '1'));
            $mbid = $one_mb['number']+1;
            pdo_update('ewei_shop_member',array('uuid'=>$mbid),array('id'=>$member['id']));
            pdo_update('ewei_number_id',array('number'=>$mbid),array('id'=>'1'));
            $member = m('member')->getMember($_W['openid'], true);
            /*$mbid = $member['id']-1;
            for($i=$mbid;$i>=0;$i--){
                $one_mb = pdo_fetch('select * from '.tablename('ewei_shop_member').' where id = :id limit 1',array(':id' => $i));
                if($one_mb == true) break;
            }

            $uuid = $one_mb['uuid']+1;
            pdo_update('ewei_shop_member',array('uuid'=>$uuid),array('id'=>$member['id']));
            $member = m('member')->getMember($_W['openid'], true);*/
        }


        if (com('wxcard')) {
			$wxcardupdatetime = intval($member['wxcardupdatetime']);

			if (($wxcardupdatetime + 86400) < time()) {
				com_run('wxcard::updateMemberCardByOpenid', $_W['openid']);
				pdo_update('ewei_shop_member', array('wxcardupdatetime' => time()), array('openid' => $_W['openid']));
			}

		}


		$this->diypage('member');
		$open_creditshop = p('creditshop') && $_W['shopset']['creditshop']['centeropen'];
		$params = array(':uniacid' => $_W['uniacid'], ':openid' => $_W['openid']);
		$merch_plugin = p('merch');
		$merch_data = m('common')->getPluginset('merch');
		if ($merch_plugin && $merch_data['is_openmerch']) {
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and status=0 and (isparent=1 or (isparent=0 and parentid=0)) and paytype<>3 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params), 'favorite' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params));
		}
		 else {
			$statics = array('order_0' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and paytype<>3 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_1' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=1 or (status=0 and paytype=3)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_2' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and (status=2 or (status=1 and sendtype>0)) and isparent=0 and refundid=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'order_4' => pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and uniacid=:uniacid and istrade=0 and userdeleted=0', $params), 'cart' => pdo_fetchcolumn('select ifnull(sum(total),0) from ' . tablename('ewei_shop_member_cart') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and selected = 1', $params), 'favorite' => ($merch_plugin && $merch_data['is_openmerch'] ? pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0 and `type`=0', $params) : pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_member_favorite') . ' where uniacid=:uniacid and openid=:openid and deleted=0', $params)));
		}

		$newstore_plugin = p('newstore');

		if ($newstore_plugin) {
			$statics['norder_0'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=0 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_1'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=1 and isparent=0 and istrade=1 and refundid=0 and uniacid=:uniacid', $params);
			$statics['norder_3'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and status=3 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
			$statics['norder_4'] = pdo_fetchcolumn('select count(*) from ' . tablename('ewei_shop_order') . ' where openid=:openid and ismr=0 and refundstate=1 and isparent=0 and istrade=1 and uniacid=:uniacid', $params);
		}


		$hascoupon = false;
		$hascouponcenter = false;
		$plugin_coupon = com('coupon');

		if ($plugin_coupon) {
			$time = time();
			$sql = 'select count(*) from ' . tablename('ewei_shop_coupon_data') . ' d';
			$sql .= ' left join ' . tablename('ewei_shop_coupon') . ' c on d.couponid = c.id';
			$sql .= ' where d.openid=:openid and d.uniacid=:uniacid and  d.used=0 ';
			$sql .= ' and (   (c.timelimit = 0 and ( c.timedays=0 or c.timedays*86400 + d.gettime >=unix_timestamp() ) )  or  (c.timelimit =1 and c.timestart<=' . $time . ' && c.timeend>=' . $time . ')) order by d.gettime desc';
			$statics['coupon'] = pdo_fetchcolumn($sql, array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
			$pcset = $_W['shopset']['coupon'];

			if (empty($pcset['closemember'])) {
				$hascoupon = true;
			}


			if (empty($pcset['closecenter'])) {
				$hascouponcenter = true;
			}
			if ($hascoupon) {
				$couponnum = com('coupon')->getCanGetCouponNum($_W['merchid']);
			}

		}


		$hasglobonus = false;
		$plugin_globonus = p('globonus');

		if ($plugin_globonus) {
			$plugin_globonus_set = $plugin_globonus->getSet();
			$hasglobonus = !(empty($plugin_globonus_set['open'])) && !(empty($plugin_globonus_set['openmembercenter']));
		}


		$haslive = false;
		$haslive = p('live');

		if ($haslive) {
			$live_set = $haslive->getSet();
			$haslive = $live_set['ismember'];
		}


		$hasThreen = false;
		$hasThreen = p('threen');

		if ($hasThreen) {
			$plugin_threen_set = $hasThreen->getSet();
			$hasThreen = !(empty($plugin_threen_set['open'])) && !(empty($plugin_threen_set['threencenter']));
		}


		$hasauthor = false;
		$plugin_author = p('author');

		if ($plugin_author) {
			$plugin_author_set = $plugin_author->getSet();
			$hasauthor = !(empty($plugin_author_set['open'])) && !(empty($plugin_author_set['openmembercenter']));
		}


		$hasabonus = false;
		$plugin_abonus = p('abonus');

		if ($plugin_abonus) {
			$plugin_abonus_set = $plugin_abonus->getSet();
			$hasabonus = !(empty($plugin_abonus_set['open'])) && !(empty($plugin_abonus_set['openmembercenter']));
		}


		$card = m('common')->getSysset('membercard');
		$actionset = m('common')->getSysset('memberCardActivation');
		$haveverifygoods = m('verifygoods')->checkhaveverifygoods($_W['openid']);

		if (!(empty($haveverifygoods))) {
			$verifygoods = m('verifygoods')->getCanUseVerifygoods($_W['openid']);
		}


		$showcard = 0;
		if (!(empty($card))) {
			$membercardid = $member['membercardid'];

			if (!(empty($membercardid)) && ($card['card_id'] == $membercardid)) {
				$cardtag = '查看微信会员卡信息';
				$showcard = 1;
			}
			 else if (!(empty($actionset['centerget']))) {
				$showcard = 1;
				$cardtag = '领取微信会员卡';
			}

		}


		$hasqa = false;
		$plugin_qa = p('qa');

		if ($plugin_qa) {
			$plugin_qa_set = $plugin_qa->getSet();

			if (!(empty($plugin_qa_set['showmember']))) {
				$hasqa = true;
			}

		}


		$hassign = false;
		$com_sign = p('sign');

		if ($com_sign) {
			$com_sign_set = $com_sign->getSet();

			if (!(empty($com_sign_set['iscenter'])) && !(empty($com_sign_set['isopen']))) {
				$hassign = ((empty($_W['shopset']['trade']['credittext']) ? '积分' : $_W['shopset']['trade']['credittext']));
				$hassign .= ((empty($com_sign_set['textsign']) ? '签到' : $com_sign_set['textsign']));
			}

		}


		$hasLineUp = false;
		$lineUp = p('lineup');

		if ($lineUp) {
			$lineUpSet = $lineUp->getSet();

			if (!(empty($lineUpSet['isopen'])) && !(empty($lineUpSet['mobile_show']))) {
				$hasLineUp = true;
			}

		}


		$wapset = m('common')->getSysset('wap');
		$appset = m('common')->getSysset('app');
		$needbind = false;
		if (empty($member['mobileverify']) || empty($member['mobile'])) {
			if ((empty($_W['shopset']['app']['isclose']) && !(empty($_W['shopset']['app']['openbind']))) || !(empty($_W['shopset']['wap']['open'])) || $hasThreen) {
				$needbind = true;
			}

		}


		if (p('mmanage')) {
			$roleuser = pdo_fetch('SELECT id, uid, username, status FROM' . tablename('ewei_shop_perm_user') . 'WHERE openid=:openid AND uniacid=:uniacid AND status=1 LIMIT 1', array(':openid' => $_W['openid'], ':uniacid' => $_W['uniacid']));
		}


		$hasFullback = true;
		$ishidden = m('common')->getSysset('fullback');

		if ($ishidden['ishidden'] == true) {
			$hasFullback = false;
		}

		//jacky add
        $one_us = pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $member['id']));
        $one_sum = count($one_us);
        if(!$one_sum) $one_sum = 0;
        $k = 0;
        foreach ($one_us as $us){
            $two_sum = count(pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $us['id'])));
            if(!$two_sum) $one_sum = 0;
            $k = $k+$two_sum;
        }

        $healthy = pdo_fetch('select * from '.tablename('ewei_shop_healthy').'where user_id = :user_id limit 1',array(':user_id' => $member['id']));
        $hy = pdo_fetch("select * from ".tablename('ewei_shop_healthy_log')."where `user_id` = :user_id and `type` = :tp and `datetime` like '".date('Y-m-d')."%' and status = :status limit 1",array(':user_id' => $member['id'],':tp'=>'2',':status'=>'1'));
        if(!$hy){
            $l_a = date('Y-m');
            $l_m = date('Y-m',strtotime('-1 month'));
            $l_r = date('Y-m',strtotime('-2 month'));
            $l_e = date('Y-m',strtotime('-3 month'));
            $bv_list = pdo_fetchall("select * from ".tablename('ewei_shop_healthy_log')."where `user_id` = :user_id and (`datetime` like '".$l_m."%' or `datetime` like '".$l_r."%' or`datetime` like '".$l_e."%' or`datetime` like '".$l_a."%') and `type` = '4'",array(':user_id' => $member['id']));
            if($bv_list){
                if($healthy['healthy_integral'] >= 1.5  ){
                    $h_y['healthy_integral'] = $member['credit1']-1.5;
                    $h_y['healthy_money'] = $healthy['healthy_money']+1.5;
                    pdo_update('ewei_shop_healthy',$h_y,array('user_id' => $member['id']));
                    pdo_update('ewei_shop_member',array('credit1'=>$h_y['healthy_integral']),array('id' => $member['id']));
                    pdo_update('mc_members',array('credit1'=>$h_y['healthy_integral']),array('uid' => $member['uid']));
                    $datetime = date('Y-m-d H:i:s',time());
                    pdo_insert('ewei_shop_healthy_log',array('user_id'=>$member['id'],'add_integral'=>'1.5','integral'=>$h_y['healthy_money'],'datetime'=>$datetime,'type'=>'2','status'=>1));
                    pdo_insert('ewei_shop_healthy_log',array('user_id'=>$member['id'],'add_integral'=>'1.5','integral'=>$h_y['healthy_integral'],'datetime'=>$datetime,'type'=>'1','status'=>0));
                }
            }
        }
        if($healthy['healthy_money'] >=100){
            $h_y_t['healthy_money'] = $healthy['healthy_money']-100;
            $h_y_mb = $member['credit2']+100;
            pdo_update('ewei_shop_healthy',$h_y_t,array('user_id' => $member['id'],'status'=>'1'));
            pdo_update('ewei_shop_member',array('credit2'=>$h_y_mb),array('id' => $member['id']));
            pdo_update('mc_members',array('credit2'=>$h_y_mb),array('uid' => $member['uid']));
            $datetime = date('Y-m-d H:i:s',time());
            pdo_insert('ewei_shop_healthy_log',array('user_id'=>$member['id'],'add_integral'=>'100','integral'=>$h_y_t['healthy_money'],'datetime'=>$datetime,'type'=>'2','status'=>0));
        }
        if(date('d') == '09'){
            $l_a = date('Y-m');
            $l_m = date('Y-m',strtotime('-1 month'));
            $l_r = date('Y-m',strtotime('-2 month'));
            $l_e = date('Y-m',strtotime('-3 month'));
            $s_date = strtotime(date('Y-m-01 00:00:00',strtotime('-1 month')));
            $goods_list = pdo_fetchall("select * from ".tablename('ewei_shop_order_goods')."where `openid` = :openid and `createtime` > :createtime ",array(':openid' => $_W['openid'],':createtime' =>$s_date));
            $gds_mmy = 0;
            foreach ($goods_list as $g_l){
                $gds_mmy = $gds_mmy+$g_l['price'];
            }
            $bos_list = pdo_fetchall("select * from ".tablename('ewei_shop_bonus_log')."where `in_men` = :in_men and `datetime` like '".$l_m."%' and `type` = '1'",array(':in_men' => $member['id']));
            $bos_mmy = 0;
            foreach ($bos_list as $g_l){
                $bos_mmy = $bos_mmy+$g_l['out_men'];
            }
            $al_mmy = $gds_mmy+$bos_mmy;
            if($al_mmy<2999){
                pdo_update('ewei_shop_healthy',array('status'=>'0'),array('user_id'=>$member['id']));
            }else{
                $l_m = date('Y-m',strtotime('-1 month'));
                $bo_list = pdo_fetchall("select * from ".tablename('ewei_shop_bonus_log')."where `in_men` = :in_men and `datetime` like '".$l_m."%' and `type` = '3'",array(':in_men' => $member['id']));
                foreach ($bo_list as $blt){
                    $in_men_money = $member['credit2'] + $blt['in_men_money'];
                    $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $blt['in_men']));
                    $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $member['uid']));
                    if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                    }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $member['id']));
                    }
                }
            }
        }
        $hcjj=m('member')->getCredit($_W['openid']);
        $healthy = pdo_fetch('select * from '.tablename('ewei_shop_healthy').'where user_id = :user_id limit 1',array(':user_id' => $member['id']));
        $bonus_me = pdo_fetch('select sum(id) as sum_id from '.tablename('ewei_out_men').'where out_men = :out_men ',array(':out_men' => $member['id']));
        $bonus_one = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 1',array(':in_men' => $member['id']));
        $bonus_two = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 2',array(':in_men' => $member['id']));
        $bonus_thr = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 3',array(':in_men' => $member['id']));
        //var_dump($uuid);
        include $this->template();
	}

	public function wallet(){
        global $_W;
        global $_GPC;
        $member = m('member')->getMember($_W['openid'], true);
        $bonus_one = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 1',array(':in_men' => $member['id']));
        $bonus_two = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 2',array(':in_men' => $member['id']));
        $bonus_thr = pdo_fetch('select sum(money) as sum_money from '.tablename('ewei_shop_bonus_log').'where in_men = :in_men and type = 3',array(':in_men' => $member['id']));
        include $this->template('member/wallet');
    }

    public function contract(){
        global $_W;
        global $_GPC;
        if ($_W['ispost']) {
            /*$hh = pdo_fetch('select * from '.tablename('ewei_shop_goods').'where special = 2 limit 1');
            if($hh) {
                show_json(1,$hh['id']);
            }else {
                show_json(0,'签署失败，请检查网络');
            }*/
            $member = m('member')->getMember($_W['openid'], true);

            $hl = pdo_update('ewei_shop_healthy',array('status'=>'1'),array('user_id'=>$member['id']));
            if(!$hl){
                $hr = pdo_insert('ewei_shop_healthy',array('user_id'=>$member['id'],'datetime'=>date('Y-m-d H:i:s'),'status'=>'1'));
                if($hr) {
                    $hh = pdo_fetch('select * from '.tablename('ewei_shop_goods').'where special = 3 limit 1');
                    show_json(1,$hh['id']);
                }else {
                    show_json(0,'签署失败，请检查网络');
                }

            }else show_json(1,'签署成功');
            //show_json(0,$member['id']);
        }
        include $this->template('member/contract');
    }

    public function lineup(){
        global $_W;
        $member = m('member')->getMember($_W['openid'], true);
        $content_url = mobileUrl('commission/myshop', array('mid' => $member['id']), 1);
        $dirname = '../addons/ewei_shopv2/data/qrcode/user/';
        load()->func('file');
        mkdirs($dirname);
        require IA_ROOT . '/framework/library/qrcode/phpqrcode.php';
        QRcode::png($content_url, $dirname . '/' . $member['id'] . '.png', QR_ECLEVEL_L, 10, 3);
        $img = $_W['siteroot'] . 'addons/ewei_shopv2/data/qrcode/user/'. $member['id'] . '.png';
        include $this->template('member/lineup');
    }

    public function mymarket(){
        global $_W;
        $member = m('member')->getMember($_W['openid'], true);
        $one_us = pdo_fetchall('select * from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $member['id']));
        $one_sum = count($one_us);
        if(!$one_us) $one_sum = 0;
        $k = 0;
        $j = 0;
        foreach ($one_us as $us){
            $two_us = pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $us['id']));
            $two_sum = count($two_us);
            if(!$two_sum) $two_sum = 0;
            $k = $k+$two_sum;
            foreach ($two_us as $two){
                $thr_us = pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $two['id']));
                $thr_sum = count($thr_us);
                if(!$thr_sum) $thr_sum = 0;
                $j = $j+$thr_sum;
                foreach ($thr_us as $thr){
                    $for_us = pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $thr['id']));
                    $for_sum = count($for_us);
                    if(!$for_sum) $for_sum = 0;
                    $j = $j+$for_sum;
                    foreach ($for_us as $fir){
                        $six_us = pdo_fetchall('select id from '.tablename('ewei_shop_member').'where agentid = :agentid ',array(':agentid' => $fir['id']));
                        $six_sum = count($six_us);
                        if(!$six_sum) $six_sum = 0;
                        $j = $j+$six_sum;
                    }
                }
            }
        }
        //var_dump($k);
        include $this->template('member/mymarket');
    }
}


?>