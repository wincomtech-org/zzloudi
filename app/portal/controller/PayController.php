<?php
 
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
class PayController extends HomeBaseController
{
    public $m;
    public function _initialize()
    {
        //引入WxPayPubHelper
        vendor('WxPayPubHelper.WxPayPubHelper');
        vendor('WxPayH5.Wxpay');
        vendor('Alipay.AlipayPay');
        $this->m=Db::name('order');
        parent::_initialize();
    }
     
    
    // 支付总入口
    public function createPay()
    {
        //["pay"] => string(1) "1"支付宝1，微信2
        //["oid"] => string(1) "4"订单号
        $data = $this->request->post();
        $m=$this->m;
        $order=$m->where(['id'=>$data['oid'],'status'=>1])->find();
        $url=url('portal/index/product');
        if(empty($order)){
            $this->error('订单已支付或不存在',$url);
        }
        $count=Db::name('pay')->where('oid',$order['oid'])->count();
        if($count>0){
            $this->error('订单已支付',$url);
        }
        $time=time();
         
        if (($time-$order['insert_time'])==300) {
            $this->error('订单已过期',$url);
        }
        // 支付动作
        //"1"支付宝1，微信2
        $mobile=cmf_is_mobile();
        $wx=cmf_is_wechat();
        $payment='alipc';
        if($data['pay']==1){
            if($mobile){
                $payment='aliwap';
            }
        }elseif($data['pay']==2){
            if($wx){
                $payment='wxjs';
            }elseif($mobile){
                $payment='wxh5';
            }else{
                $payment='wxnative';
            }
        }else{
            $this->error('错误信息',$url);
        }
        
        
        // 选择支付动作
        $wx_config = config('wx_config');
        $wx_oid=$order['oid'].'_'.time();
        switch($payment){
            case 'alipc':
                $order = array(
                'order_sn'      => $order['oid'],
                'order_amount'  => $order['money'],
                );
                $this->alipc($order);
                break;
           case 'aliwap': 
                $order = array(
                'order_sn'      => $order['oid'],
                'order_amount'  => $order['money'],
                );
               
                $this->aliwap($order);
                break;
          case 'wxnative': 
                $order = array(
                    'wfprice'       => $order['money'],
                    'wfproduct'     =>  $order['service_name'],
                    'out_trade_no'  => $wx_oid,
                ); 
                $this->wxnative($order);
                return $this->fetch('wxnative');
                break;
          case 'wxh5':
              $order = array(
              'order_amount'       => $order['money'], 
              'order_sn'  => $wx_oid,
              'wfproduct'     =>  $order['service_name'],
              );
              $this->wxh5($order, $wx_config);
             
              break;
              
          case 'wxjs':
              $order = array(
              'order_amount'       => $order['money'], 
              'order_sn'  => $wx_oid,
              'wfproduct'     =>  $order['service_name'],
              );
              $this->get_code($order, $wx_config);
              return $this->fetch('wxnative');
              break;
          default:
              break;
        }
       
    } 

/*以下为支付宝支付*/
    //PC访问 生成支付二维码
    public function alipc($order)
    {
        // $work = new \AlipayPay($order['order_sn'],$order['order_amount'],$order['order_id'],'aliwap');
        $work = new \AlipayPay(config('ali_config'),$order['order_sn'], $order['order_amount'],  'alipc');
       $work->work();
        
        // $work->QRcode();
    }

    // 移动端访问 WAP在线支付
    public function aliwap($order)
    {
        $work = new \AlipayPay(config('ali_config'),$order['order_sn'], $order['order_amount'],'aliwap');
        $work->work();
    }
    public function aliqr($order)
    {
        $work = new \AlipayPay(config('ali_config'),$order['order_sn'], $order['order_amount'], 'aliqr');
        $work->QRcode();
    }

    public function alipayBack()
    {
        // 实例化
        $work = new \AlipayPay(config('ali_config'));
        // 前置处理
        $jumpurl = url('portal/index/index'); 
        if (!empty($_POST)) {
            $method = 'post';
            $orz = $work->getReturn();
        } elseif (!empty($_GET)) {
            $method = 'get';
            $orz = $work->getNotify();
        } else{
            $this->error('数据获取失败',$jumpurl);
        }
        
        // 处理数据
        if (empty($orz)) {
            $this->error('数据获取失败',$jumpurl); 
        } 
        $out_trade_no = $orz['out_trade_no'];
       //交易状态
        if($orz['trade_status']=='TRADE_FINISHED' || $orz['trade_status']=='TRADE_SUCCESS') { 
           
            $result=trim($this->pay_end($out_trade_no, 1, $orz));
            //post为notify，get为return的用户界面
            if ($method=='post') {
                if($result==='success' || $result==='end'){
                    exit('success');
                }else{
                    exit('fail');
                }
            }else{
                $this->redirect(url('portal/index/pay_result',['oid'=>$out_trade_no,'result'=>$result])); 
            }
        } else {
            $this->redirect(url('portal/index/pay_result',['oid'=>$out_trade_no]));
        } 
         
    }
    

/*以下是微信支付*/
    // 移动端访问 H5支付
    public function wxh5($order,$config)
    {
        $pay = new \Wxpay();
        $result = $pay->get_h5($order, $config);

        // $wx_return = url('Index/index',$order,true,true);
        // $wx_return = url('Pay/wxh5success',$order,true,true);
        $wx_return = url('Pay/wxh5return',$order,true,true);
        if (empty($result)) {
            echo '<div style="text-align:center"><button type="button" disabled>未获得移动支付权限</button></div>';exit;
        } else {
            // $url = $result;//如果不写则返回到之前的页面
            $url = $result . '&redirect_url='. $wx_return;//这个是指定用户操作后返回的页面
            // echo '<a href="'. $url .'" class="box-flex btn-submit" type="button">微信支付</a>';exit;
            $this->assign('wxh5Url',$url);
            $this->display('wxh5');
        }
    }
    // 支付中间页
    public function wxh5return()
    {
        // session(null);
        // echo date('Y-m-d H:i:s');
        // die;

        $order = I('get.');
        // $findOrder = Db::name('order')->field('id,order_sn,wfprice,pay,payment')->where(array('id',$order['log_id']))->find();
        $this->assign('order',$order);
        $this->display();
    }
    public function wxh5notify()
    {
        // dump($_REQUEST);die;
        vendor('WxPayH5.log');
        vendor('WxPayH5.notify');

        $data = I();
 
        $notify = new \PayNotifyCallBack();
        $notify->Handle(false);

        $result = $notify->NotifyProcess($data);

        if ($result===true) {
            $status = Db::name('order')->where(array('order_sn'=>$data['out_trade_no']))->setField('pay',1);
            if (!empty($status)) {
                exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
                echo true;exit();
            } else {
                echo false;exit();
            }
        } else {
            echo false;exit();
        }
    }
    public function wxh5OrderQuery($order_sn='')
    {
        $data = I();
        $out_trade_no = $data['order_sn'];
        $data_type = I('get.data_type');

        $orderModel = new OrderModel();
        $status = $orderModel->wxOrderQuery($out_trade_no);
        // dump($status);die;
        if ($status===true) {
            $result = Db::name('order')->where(array('order_sn'=>$out_trade_no))->setField('pay',1);  
        }
        if ($data_type=='html') {
            if (!empty($result)) {
                // $findOrder = Db::name('order')->field('id,order_sn,wfprice,pay,payment')->where(array('order_sn'=>$out_trade_no))->find();
                $order['wfprice'] = $data['wfprice'];
                session(null);
                $this->redirect('Pay/wxh5success',$order);
            } else {
                $this->redirect('Index/index');
            }
        } else {
            echo $status;exit();
        }
    }
    public function wxh5success()
    {
        $order = I('get.');
        // $wfprice = $order['order_amount'];
        // $wfprice = $order['wfprice'];
        if ($order['pay']==1) {
            session(null);
        }

        $this->assign('order',$order);
        // $this->assign('wfprice',$wfprice);
        $this->display();
    }


    //PC访问 扫码支付
    // 生成二维码
    public function wxnative($order)
    {
        $jiagee = $order['wfprice']; 
         
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub(config('wx_config'));
        //设置统一支付接口参数
        //设置必填参数
        $unifiedOrder->setParameter("body", $order['wfproduct']); //商品描述
        //自定义订单号，此处仅作举例
        // $timeStamp    = time();
        // $out_trade_no = C('WxPay.pub.config.APPID') . "$timeStamp";
        $out_trade_no = $order['out_trade_no'];
        $unifiedOrder->setParameter("out_trade_no", $out_trade_no); //商户订单号
        $unifiedOrder->setParameter("total_fee", bcmul($jiagee , 100,0)); //总金额
        // $unifiedOrder->setParameter("notify_url", 'http://tp3.2_wx_cs.com/index.php/home/Pay/notify'); //通知地址
        $unifiedOrder->setParameter("notify_url", url('portal/pay/wx_notify','',true,true)); //通知地址
        $unifiedOrder->setParameter("trade_type", "NATIVE"); //交易类型
        $jumpurl=url('portal/index/product');
        //获取统一支付接口结果
        $unifiedOrderResult = $unifiedOrder->getResult();
        // var_dump($unifiedOrder);
        //商户根据实际情况设置相应的处理流程
        if ($unifiedOrderResult["return_code"] == "FAIL") {
            //商户自行增加处理流程
           $this->error("通信出错：" . $unifiedOrderResult['return_msg'],$jumpurl);
        } elseif ($unifiedOrderResult["result_code"] == "FAIL") {
            //商户自行增加处理流程
            echo "错误代码：" . $unifiedOrderResult['err_code'] . "<br>";
            echo "错误代码描述：" . $unifiedOrderResult['err_code_des'] . "<br>";
            $this->error($unifiedOrderResult['err_code'].$unifiedOrderResult['err_code_des'],$jumpurl);
        } elseif ($unifiedOrderResult["code_url"] != null) {
            //从统一支付接口获取到code_url
            $code_url = $unifiedOrderResult["code_url"];
            //商户自行增加处理流程
            //......
        } 
        $info=[
            'oid'=>$out_trade_no,
            'money'=>$order['wfprice'],
            'weixinUrl'=> urlencode($code_url),
            'query_url'=>url('Portal/Pay/orderQuery'),
        ];
        $this->assign('info',$info);  
        $this->assign('html_flag','product');
        //banner
        $banners=DB::name('banner')->where('type','product')->order('sort asc,id asc')->select();
        $this->assign('banners',$banners);
        
    }
    /* 微信页面 */
    public function wx_return(){
        $order_sn=$this->request->param('oid');
        $arr=explode('_', $order_sn);
        $oid=$arr[0];
        $info=DB::name('order')->where('oid',$oid)->find();
        $info['order_sn']=$order_sn;
        $this->assign('info',$info); 
        $this->assign('html_flag','product');
        return $this->fetch();
    }
    public function notify()
    {
        //使用通用通知接口
        $notify = new \Notify_pub(config('wx_config'));
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
         // var_dump($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if ($notify->checkSign() == false) {
            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
        }
        $returnXml = $notify->returnXml();

        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======

        //以log文件形式记录回调信息
        //         $log_ = new Log_();
        $log_name = "wx.log"; //log文件路径
 
        cmf_log( "接收到的notify通知:\r\n" . $xml . "\r\n",$log_name);
        if ($notify->checkSign() == true || 1) {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
               
                cmf_log( "通信出错通信出错:\r\n" . $xml . "\r\n",$log_name);
                $this->error("1");
            } elseif ($notify->data["result_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
               
                cmf_log( "业务出错:\r\n" . $xml . "\r\n",$log_name);
                $this->error("失败2");
            } else {
                //此处应该更新一下订单状态，商户自行增删操作
                cmf_log( "支付成功:\r\n" . $xml . "\r\n",$log_name);

            }

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息

        }
    }

     

    //查询订单
    public function orderQuery($order_sn='')
    {
        // out_trade_no='+$('out_trade_no').value,
        //退款的订单号
       
        if (!isset($_POST["out_trade_no"]) && empty($order_sn)) {
            $out_trade_no = '';
        } else {
            $out_trade_no = empty($order_sn) ? $_POST["out_trade_no"] : $order_sn;
          
            //使用订单查询接口
            
            $orderQuery = new \OrderQuery_pub(config('wx_config'));
           
            $orderQuery->setParameter("out_trade_no", $out_trade_no); //商户订单号
           
            //获取订单查询结果
            $orderQueryResult = $orderQuery->getResult();

            //商户根据实际情况设置相应的处理流程,此处仅作举例
            if ($orderQueryResult["return_code"] == "FAIL") {
                $this->error($out_trade_no);
            } elseif ($orderQueryResult["result_code"] == "FAIL") {
                // $this->ajaxReturn('','支付失败！',0);
                $this->error($out_trade_no);
            } else {
                error_log($orderQueryResult["trade_state"]."\r\n",3,'zz.log');
                //判断交易状态
                switch ($orderQueryResult["trade_state"]) {
                    case 'SUCCESS':
                        //Db::name('order')->where(array('id' => session('order_id')))->save(array('pay' => '1'));
                        $arr=explode('_', $out_trade_no);
                        $oid=$arr[0];
                        // 修改订单状态
                        $m=Db::name('order');
                      
                        $order=$m->where(['oid'=>$oid])->find();
                        if($order['status']==1){
                            $m->startTrans();
                            $m->where('id',$order['id'])->update(['status'=>2,'time'=>time()]);
                            //保存支付信息
                            $data=[
                                'type'=>2,
                                'oid'=>$oid,
                                'money'=>bcdiv($orderQueryResult['total_fee'],100,2),
                                'trade_no'=>$orderQueryResult['transaction_id'],
                                'buyer_id'=>$orderQueryResult['openid'],
                                'time'=>time(),
                            ];
                            $insert=Db::name('pay')->insertGetId($data);
                            if($insert>0){ 
                                
                                $m->commit();
                                $this->success("支付成功！");
                            }else{
                                $m->rollback();
                                $this->error("已支付但订单处理失败，请联系客服");
                            }
                        }
                       
                        break;
                    case 'REFUND':
                        $this->error("超时关闭订单：" );
                        break;
                    case 'NOTPAY':
                        $this->error("超时关闭订单：" );
                          // $this->ajaxReturn($orderQueryResult["trade_state"], "支付成功", 1);
                        break;
                    case 'CLOSED':
                        $this->error("超时关闭订单：" );
                        break;
                    case 'PAYERROR':
                        $this->error("支付失败" . $orderQueryResult["trade_state"]);
                        break;
                    default:
                        $this->error("未知失败" . $orderQueryResult["trade_state"]);
                        break;
                }
            }
        }
    }
    //查询订单
    public function wx_notify()
    {
        $data = file_get_contents("php://input");
        $postObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
        $out_trade_no= $postObj->out_trade_no;
          
        //使用订单查询接口 
        $orderQuery = new \OrderQuery_pub(config('wx_config')); 
        $orderQuery->setParameter("out_trade_no", $out_trade_no); //商户订单号 
        //获取订单查询结果
        $orderQueryResult = $orderQuery->getResult();
        if(isset($orderQueryResult["return_code"])
            && isset($orderQueryResult["result_code"])
            && $orderQueryResult["return_code"] == "SUCCESS"
            && $orderQueryResult['result_code']=='SUCCESS'
            && $orderQueryResult['trade_state']=='SUCCESS')
        {  
            //Db::name('order')->where(array('id' => session('order_id')))->save(array('pay' => '1'));
            $arr=explode('_', $out_trade_no);
            $oid=$arr[0];
            // 修改订单状态
            $m=Db::name('order');
            
            $order=$m->where(['oid'=>$oid])->find();
            if($order['status']==1){
                $m->startTrans();
                $m->where('id',$order['id'])->update(['status'=>2,'time'=>time()]);
                //保存支付信息
                $data=[
                    'type'=>2,
                    'oid'=>$oid,
                    'money'=>bcdiv($orderQueryResult['total_fee'],100,2),
                    'trade_no'=>$orderQueryResult['transaction_id'],
                    'buyer_id'=>$orderQueryResult['openid'],
                    'time'=>time(),
                ];
                $insert=Db::name('pay')->insertGetId($data);
                if($insert>0){ 
                    $m->commit();
                    exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
                    
                }else{
                    $m->rollback(); 
                }
            } 
        }
        exit(); 
    }
    //生成二维码
    public function qrcode(){
        $url=$this->request->param('data','','trim');
        import('phpqrcode',EXTEND_PATH);
        $url = urldecode($url);
        \QRcode::png($url, false, QR_ECLEVEL_L,5, 2);
    }
    
    /* 处理支付完成订单检查 */
    public function pay_end($oid,$pay_type,$pay_data){
        $log='pay.text';
//         $pay_type支付宝1，微信2
        if(empty($oid) || empty($pay_type)){
            return '数据错误';
        }
        // 修改订单状态
        $m_order=  $this->m;
       
        $order=$m_order->where(['oid'=>$oid])->find();
        if(empty($order)){
            return '数据错误';
        }
        if($order['status']!=1){
            return 'end';
        }
        $m_user=Db::name('user');
        $m_order->startTrans();
        //订单修改数据
        $data_order=['time'=>time()];
        //保存支付信息
        $data_pay=[
            'type'=>$pay_type,
            'oid'=>$oid,  
            'time'=>$data_order['time'],
        ];
        if($pay_type==1){ 
            $data_pay['money']=$pay_data['total_fee'];
            $data_pay['trade_no']=$pay_data['trade_no'];
            $data_pay['buyer_id']=$pay_data['buyer_id']; 
        }elseif($pay_type==2){
            $data_pay['money']=bcdiv($pay_data['total_fee'],100,2);
            $data_pay['trade_no']=$pay_data['transaction_id'];
            $data_pay['buyer_id']=$pay_data['openid'];
        }else{
            return '数据错误';
        }
       
        //判断用户是否在订单中，不在则添加
        if($order['uid']==0){
            //查找用户
            $user=$m_user->where('mobile',$order['tel'])->find();
            if(empty($user)){
                $user=$m_user->where('user_email',$order['email'])->find();
            }
            if(empty($user)){
                //创建用户
                $user_data=[
                    'user_nickname'=>$order['uname'],
                    'user_pass'=>cmf_password($order['tel']),
                    'mobile'=>$order['tel'],
                    'user_email'=>$order['email'],
                    'qq'=>$order['qq'],
                    'city'=>$order['city'],
                    'last_login_ip'   => get_client_ip(0, true),
                    'create_time'     =>$data_order['time'],
                    'last_login_time' =>$data_order['time'],
                    'user_status'     => 1,
                    'user_type'       => 2,//会员
                ];
                try {
                    $result  = $m_user->insertGetId($user_data);
                } catch (\Exception $e) { 
                    cmf_log($data_pay['type'].'支付交易号'.$data_pay['trade_no'].'支付成功后添加用户'.$order['tel'].'出错'.($e->getMessage()),$log);
                    $m_order->rollback();
                    return '支付成功但创建用户失败，请联系客服确保权益';
                }
                
                $user=$m_user->where('id',$result)->find(); 
            } 
            $data_order['uid']=$user['id'];
        }
        
        try {
            $m_order->where('id',$order['id'])->update($data_order);
            $insert=Db::name('pay')->insertGetId($data_pay);
        } catch (\Exception $e) { 
            $m_order->rollback();
            cmf_log($data_pay['type'].'支付交易号'.$data_pay['trade_no'].'支付成功后更改订单'.$order['oid'].'出错'.($e->getMessage()),$log);
            return '支付成功但更改订单，请联系客服确保权益';
        }
        
        $m_order->commit();
        //成功处理
       return 'success';   
    }
}
?>