<?php
/**
 * Created by PhpStorm.
 * User: THINK
 * Date: 2018/5/2
 * Time: 11:28
 */

class Bonus_EweiShopV2Model
{
    public function pay_one($f_id){

        $f = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $f_id));
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $f['uid']));
        if($mc){
            $f['credit2'] = $mc['credit2'];
        }
        if(empty($f['agentid']) or $f['agentid'] == '0'){
            return false;//没有上级，自己就是A
            exit();
        }

        $e_id =$f['agentid'];
        $e = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $e_id));
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $e['uid']));
        if($mc){
            $e['credit2'] = $mc['credit2'];
        }
        if(empty($e['agentid'])){
            $ra[] = $e['id'];
            $rb = 1;//有一个上级A，自己是B。5
            $rc[] = $e['credit2'];
            return $r = array($ra,$rb,$rc);
            exit();
        }
        $ra[] = $e['id'];
        $rc[] = $e['credit2'];
        $d_id =$e['agentid'];
        $d = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $d_id));
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $d['uid']));
        if($mc){
            $d['credit2'] = $mc['credit2'];
        }
        if(empty($d['agentid'])){
            $ra[] = $d['id'];
            $rb = 2;//有两个上级A,B，自己是C。4
            $rc[] = $d['credit2'];
            return $r = array($ra,$rb,$rc);
            exit();
        }
        $ra[] = $d['id'];
        $rc[] = $d['credit2'];
        $c_id =$d['agentid'];
        $c = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $c_id));
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $c['uid']));
        if($mc){
            $c['credit2'] = $mc['credit2'];
        }
        if(empty($c['agentid'])) {
            $ra[] = $c['id'];
            $rb = 3;//有三个上级A,B,C，自己是D。3
            $rc[] = $c['credit2'];
            return $r = array($ra,$rb,$rc);
            exit();
        }
        $ra[] = $c['id'];
        $rc[] = $c['credit2'];
        $b_id =$c['agentid'];
        $b = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $b_id));
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $b['uid']));
        if($mc){
            $b['credit2'] = $mc['credit2'];
        }
        if(empty($b['agentid'])) {
            $ra[] = $b['id'];
            $rb = 4;//有四个上级A,B,C,D，自己是E。2
            $rc[] = $b['credit2'];
            return $r = array($ra,$rb,$rc);
            exit();
        }
        $ra[] = $b['id'];
        $rc[] = $b['credit2'];
        $a_id =$b['agentid'];
        $a = pdo_fetch('select * from ' . tablename('ewei_shop_member') . ' where id=:id limit 1', array(':id' => $a_id));
        $ra[] = $a['id'];
        $rb = 5;//有五个上级A,B,C,D,E，自己是F。
        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $a['uid']));
        if($mc){
            $a['credit2'] = $mc['credit2'];
        }
        $rc[] = $a['credit2'];
        return $r = array($ra,$rb,$rc);
        exit();

    }

    public function get_pay($d_id,$money,$ordersn,$goods_id,$openid){
        global $_W;
        if(empty($openid)) $openid = $_W['openid'];
        $member = m('member')->getMember($openid, true);
        $mc_member = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $member['uid']));
        if($mc_member){
            $money_all = $mc_member['credit2'];
        }

        //if($money_all >= $money){

            $d_n = $this->pay_one($d_id);
            if($d_n){
                $i = $d_n[1];
                switch ($i){

                    case 1://B
                        $in = $d_n[0][0];
                        $in_money = ($money*2)/10;
                        $in_men_money = $d_n[2][0];
                        $in_men_money = $in_men_money + $in_money;
                        $data = array('out_men' => $d_id,'goodsid' => $goods_id,'ordersn' => $ordersn,'out_men_money' => $money_all,'in_men' => $in,'in_men_money' => $in_men_money,'money' => $in_money,'datetime' => date('Y-m-d H:i:s'),'type' => '1','status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $in));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $in));
                        }

                        break;

                    case 2://C
                        //B享受20%的直接营销利益，
                        $b = $d_n[0][0];
                        $in_money = ($money*2)/10;
                        $in_men_money = $d_n[2][0];
                        $in_men_money = $in_men_money + $in_money;
                        $data_b = array('out_men' => $d_id,'goodsid' => $goods_id,'ordersn' => $ordersn,'out_men_money' => $money_all,'in_men' => $b,'in_men_money' => $in_men_money,'money' => $in_money,'datetime' => date('Y-m-d H:i:s'),'type' => '1','status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_b);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $b));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $b));
                        }

                        //A享受间接营销利益的15%
                        $a = $d_n[0][1];
                        $in_money = ($money*15)/100;
                        $in_men_money = $d_n[2][1];
                        $in_men_money = $in_men_money + $in_money;
                        $data_a = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $a, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '2', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_a);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $a));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $a));
                        }

                        break;

                    case 3://D
                        //C享受20%的直接营销利益，
                        $c = $d_n[0][0];
                        $in_money = ($money*2)/10;
                        $in_men_money = $d_n[2][0];
                        $in_men_money = $in_men_money + $in_money;
                        $data_c = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $c, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '1', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_c);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $c));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $c));
                        }

                        //B享受间接营销利益的15%
                        $b = $d_n[0][1];
                        $in_money = ($money*15)/100;
                        $in_men_money = $d_n[2][1];
                        $in_men_money = $in_men_money + $in_money;
                        $data_b = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $b, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '2', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_b);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $b));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $b));
                        }

                        //A享受2%的新增市场营销红利
                        $a = $d_n[0][2];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][2];
                        $in_men_money = $in_men_money + $in_money;
                        $data_a = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $a, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_a);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $a));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $a));
                        }*/

                        break;

                    case 4://E
                        //D享受20%的直接营销利益，
                        $d = $d_n[0][0];
                        $in_money = ($money*2)/10;
                        $in_men_money = $d_n[2][0];
                        $in_men_money = $in_men_money + $in_money;
                        $data_d = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $d, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '1', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_d);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $d));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $d));
                        }

                        //C享受间接营销利益的15%
                        $c = $d_n[0][1];
                        $in_money = ($money*15)/100;
                        $in_men_money = $d_n[2][1];
                        $in_men_money = $in_men_money + $in_money;
                        $data_c = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $c, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '2', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_c);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $c));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $c));
                        }

                        //B享受2%的新增市场营销红利
                        $b = $d_n[0][2];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][2];
                        $in_men_money = $in_men_money + $in_money;
                        $data_b = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $b, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_b);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $b));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $b));
                        }*/

                        //A享受2%的新增市场营销红利
                        $a = $d_n[0][3];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][3];
                        $in_men_money = $in_men_money + $in_money;
                        $data_a = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $a, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_a);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $a));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $a));
                        }*/

                        break;

                    case 5://F

                        $e = $d_n[0][0];
                        $in_money = ($money*2)/10;
                        $in_men_money = $d_n[2][0];
                        $in_men_money = $in_men_money + $in_money;
                        $data_e = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $e, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '1', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_e);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $e));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $e));
                        }

                        //B享受间接营销利益的15%
                        $d = $d_n[0][1];
                        $in_money = ($money*15)/100;
                        $in_men_money = $d_n[2][1];
                        $in_men_money = $in_men_money + $in_money;
                        $data_d = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $d, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '2', 'status' => '1');
                        pdo_insert('ewei_shop_bonus_log', $data_d);
                        $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $d));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $d));
                        }

                        //A享受2%的新增市场营销红利
                        $c = $d_n[0][2];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][2];
                        $in_men_money = $in_men_money + $in_money;
                        $data_c = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $c, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_c);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $c));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $c));
                        }*/

                        //A享受2%的新增市场营销红利
                        $b = $d_n[0][3];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][3];
                        $in_men_money = $in_men_money + $in_money;
                        $data_b = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $b, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_b);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $b));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $b));
                        }*/

                        //A享受2%的新增市场营销红利
                        $a = $d_n[0][4];
                        $in_money = ($money*2)/100;
                        $in_men_money = $d_n[2][4];
                        $in_men_money = $in_men_money + $in_money;
                        $data_a = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $a, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '3', 'status' => '0');
                        pdo_insert('ewei_shop_bonus_log', $data_a);
                        /*$mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $a));
                        $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                        if($mc) {
                            pdo_update('mc_members', array('credit2' => $in_men_money), array('id' => $mr['uid']));
                        }else {
                            pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $a));
                        }*/

                        break;

                }
            }
            return $d_n[1];
            exit();
        //}
    }

    public function get_pay_one($d_id,$money,$ordersn,$goods_id,$openid){
        global $_W;
        if(empty($openid)) $openid = $_W['openid'];
        $member = m('member')->getMember($openid, true);
        $mc_member = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $member['uid']));
        if($mc_member){
            $money_all = $mc_member['credit2'];
        }

        //if($money_all >= $money){

        $d_n = $this->pay_one($d_id);
        if($d_n){
            $i = $d_n[1];
            switch ($i){

                case 1://B
                    $in = $d_n[0][0];
                    $in_money = ($money*1)/10;
                    $in_men_money = $d_n[2][0];
                    $in_men_money = $in_men_money + $in_money;
                    $data = array('out_men' => $d_id,'goodsid' => $goods_id,'ordersn' => $ordersn,'out_men_money' => $money_all,'in_men' => $in,'in_men_money' => $in_men_money,'money' => $in_money,'datetime' => date('Y-m-d H:i:s'),'type' => '1','status' => '1');
                    pdo_insert('ewei_shop_bonus_log', $data);
                    $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $in));
                    $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                    if($mc) {
                        pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                    }else {
                        pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $in));
                    }

                    break;

                case 2://C
                    //B享受20%的直接营销利益，
                    $b = $d_n[0][0];
                    $in_money = ($money*1)/10;
                    $in_men_money = $d_n[2][0];
                    $in_men_money = $in_men_money + $in_money;
                    $data_b = array('out_men' => $d_id,'goodsid' => $goods_id,'ordersn' => $ordersn,'out_men_money' => $money_all,'in_men' => $b,'in_men_money' => $in_men_money,'money' => $in_money,'datetime' => date('Y-m-d H:i:s'),'type' => '1','status' => '1');
                    pdo_insert('ewei_shop_bonus_log', $data_b);
                    $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $b));
                    $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                    if($mc) {
                        pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                    }else {
                        pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $b));
                    }

                    //A享受间接营销利益的15%
                    $a = $d_n[0][1];
                    $in_money = ($money*5)/100;
                    $in_men_money = $d_n[2][1];
                    $in_men_money = $in_men_money + $in_money;
                    $data_a = array('out_men' => $d_id, 'goodsid' => $goods_id, 'ordersn' => $ordersn, 'out_men_money' => $money_all, 'in_men' => $a, 'in_men_money' => $in_men_money, 'money' => $in_money, 'datetime' => date('Y-m-d H:i:s'), 'type' => '2', 'status' => '1');
                    pdo_insert('ewei_shop_bonus_log', $data_a);
                    $mr = pdo_fetch('select * from ' . tablename('ewei_shop_member') . '  where  id=:id limit 1', array(':id' => $a));
                    $mc = pdo_fetch('select * from ' . tablename('mc_members') . '  where  uid=:uid limit 1', array(':uid' => $mr['uid']));
                    if($mc) {
                        pdo_update('mc_members', array('credit2' => $in_men_money), array('uid' => $mr['uid']));
                    }else {
                        pdo_update('ewei_shop_member', array('credit2' => $in_men_money), array('id' => $a));
                    }

                    break;


            }
        }
        return $d_n[1];
        exit();
        //}
    }
}