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
     
    public function changeOrder($param=[],$order_sn='')
    {
        // 查询订单是否已存在
        $order_id = ''; $findOrder = [];
        if (!empty(session('order_id'))) {
            $order_id = session('order_id');
            $where['id'] = $order_id;
            $findOrder = Db::name('order')->field('id,order_sn,pay')->where($where)->find();
        }

        if (empty($findOrder)) {
            $data = array(
                'wfproduct' => $param['wfproduct'],
                'wfproductb'=> $param['wfproductb'],
                'wfname'    => $param['wfname'],
                'wfmob'     => $param['wfmob'],
                'wfqq'      => $param['wfqq'],
                'wfaddress' => $param['wfaddress'],
                'wfprice'   => $param['wfprice'],
                'wfguest'   => $param['wfguest'],
                'payment'   => $param['wfpay'],
                'time'      => time(),
            );
            $data['order_sn'] = $order_sn;
            $order_id = Db::name('order')->data($data)->add();

        } elseif (isset($findOrder['pay']) && $findOrder['pay']=='0') {

            $data['payment'] = $param['wfpay'];
            $data['order_sn'] = $order_sn;
            Db::name('order')->where($where)->save($data);

        } elseif (isset($findOrder['pay']) && $findOrder['pay']=='1') {
            $this->error('请勿重复支付！',url('Index/pay'));
        }
        if (empty($order_id)) {
            $this->error('订单ID丢失',url('Index/pay'));
        }
        session('order_id', $order_id);
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
                    'out_trade_no'  =>  $order['oid'],
                ); 
                $this->wxnative($order);
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
        // 前置处理
        if (!empty($_POST)) {
            $method = 'post';
        } elseif (!empty($_GET)) {
            $method = 'get';
        } else{
            $method = 'null';
        }
        $jumpurl = url('portal/index/index');

        // 实例化
        $work = new \AlipayPay(config('ali_config'));

        // 获取数据
        if ($method=='get') {
            $orz = $work->getReturn();
        } elseif ($method=='post') {
            $orz = $work->getNotify();
        } else {
            $orz = false;
        }
        $m=$this->m;
        // 处理数据
        if (!empty($orz)) {
            $trade_status = $orz['trade_status'];//交易状态
            if($trade_status=='TRADE_FINISHED') {
                $statusCode = 2;//支付完成
            } elseif ($trade_status=='TRADE_SUCCESS') {
                $statusCode = 2;;//支付成功
            } else {
                $statusCode = 1;//支付失败
            }
            if (!empty($orz['out_trade_no'])) {
                $out_trade_no = $orz['out_trade_no'];
                // $payment = strstr($out_trade_no,'_',true);
                $where['oid'] = $out_trade_no;

                // 检查是否已支付过
                $findOrder = $m->where($where)->find();
                 
            } else {
                $this->error('订单号丢失',$jumpurl);
            } 
        } else {
            $this->error('数据获取失败',$jumpurl);
        }

        // 处理结果
        if ($statusCode==2 && $findOrder['status']==1) {
            // 修改订单状态
            $m->startTrans();
            $row=$m->where($where)->update(['status'=>2,'time'=>time()]);
            if($row===1){
               //保存支付信息
                $data=[
                    'type'=>1,
                    'oid'=>$findOrder['oid'],
                    'money'=>$orz['total_fee'],
                    'trade_no'=>$orz['trade_no'],
                    'buyer_id'=>$orz['buyer_id'],
                    'time'=>time(),
                ];
                $insert=Db::name('pay')->insertGetId($data);
                if($insert>0){
                    echo "success";
                    $m->commit();
                }else{
                    $m->rollback();
                } 
            }
        }
       
        $this->redirect(url('portal/index/pay_result',['id'=>$findOrder['id']]));
       
    }
    

/*以下是微信支付*/
    // 移动端访问 H5支付
    public function wxh5($order,$config)
    {
        $pay = new \Wxpay();
        $result = $pay->get_h5($order, $config);

        // $wx_return = U('Index/index',$order,true,true);
        // $wx_return = U('Pay/wxh5success',$order,true,true);
        $wx_return = U('Pay/wxh5return',$order,true,true);
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

        \Log::DEBUG("begin notify");
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
        $jiagee =0.01;
        // $jg = explode('.', $jiagee);

        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub(config('wx_config'));
        //设置统一支付接口参数
        //设置必填参数
        $unifiedOrder->setParameter("body", $order['wfproduct']); //商品描述
        //自定义订单号，此处仅作举例
        // $timeStamp    = time();
        // $out_trade_no = C('WxPay.pub.config.APPID') . "$timeStamp";
        $out_trade_no = $order['out_trade_no'].'_'.time();
        $unifiedOrder->setParameter("out_trade_no", $out_trade_no); //商户订单号
        $unifiedOrder->setParameter("total_fee", bcmul($jiagee , 100,0)); //总金额
        // $unifiedOrder->setParameter("notify_url", 'http://tp3.2_wx_cs.com/index.php/home/Pay/notify'); //通知地址
        $unifiedOrder->setParameter("notify_url", url('portal/pay/notify','',true,true)); //通知地址
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
        $log_name = __ROOT__ . "/Public/notify_url.log"; //log文件路径

        $this->log_result($log_name, "【接收到的notify通知】:\n" . $xml . "\n");

        if ($notify->checkSign() == true || 1) {
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name, "【通信出错】:\n" . $xml . "\n");
                $this->error("1");
            } elseif ($notify->data["result_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name, "【业务出错】:\n" . $xml . "\n");
                $this->error("失败2");
            } else {
                //此处应该更新一下订单状态，商户自行增删操作
                log_result($log_name, "【支付成功】:\n" . $xml . "\n");

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
    //生成二维码
    public function qrcode(){
        $url=$this->request->param('data','','trim');
        import('phpqrcode',EXTEND_PATH);
        $url = urldecode($url);
        \QRcode::png($url, false, QR_ECLEVEL_L,5, 2);
    }
}
?>