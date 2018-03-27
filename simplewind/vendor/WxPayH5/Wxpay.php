<?php

// require_once "WxpayAPI/WxPay.JsApiPay.php";
class Wxpay
{

    private $parameters; // cft 参数
    private $payment; // 配置信息

    /**
     * JSAPI 生成支付代码
     * @param   array $order 订单信息
     * @param   array $payment 支付方式信息
     */
    public function get_code($order, $payment)
    {
        $wx_return = 'http://www.onlmt.com/mobile/index.php?r=user';
        //include_once(BASE_PATH.'helpers/payment_helper.php');

        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        if (!preg_match('/micromessenger/', $ua)) {
            //return '<div style="text-align:center"><button class="btn btn-primary" type="button" disabled>请在微信中支付</button></div>';
            $result = $this->get_h5($order, $payment);
            if (empty($result)) {
                return '<div style="text-align:center"><button class="btn btn-primary" type="button" disabled>未得移动支付权限</button></div>';
            } else {
                $result = $result . '&redirect_url=' . $wx_return;
                return '<a href="' . $result . '" class="box-flex btn-submit" type="button">微信支付</a>';
            }
        }

        // 配置参数
        // $this->payment = $payment;

        // 网页授权获取用户openid
        $openid = '';
        //①、获取用户openid
        $tools = new JsApiPay();
        if (empty($_SESSION['wxopenid'])) {
            $openid = $tools->GetOpenid();
            $_SESSION['wxopenid'] = $openid;
        } else {
            $openid = $_SESSION['wxopenid'];
        }
        if (empty($openid)) {
            return '<div style="text-align:center"><button class="btn btn-primary" type="button" disabled>权限获取失败</button></div>';
        }

        //②、统一下单
        $order_amount = bcmul($order['order_amount'], 100, 0);
        $input        = new WxPayUnifiedOrder();
        $input->SetBody($order['order_sn']);
        $input->SetAttach("test");
        $input->SetOut_trade_no($order['order_sn'] . $order_amount . '-' . $order['log_id']);
        $input->SetTotal_fee($order_amount);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        //$input->SetGoods_tag("test");
        //$input->SetNotify_url("http://www.onlmt.com/notify.php");
        $input->SetNotify_url("http://www.onlmt.com/includes/modules/payment/WxpayAPI/notify.php");

        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openid);
        $order = WxPayApi::unifiedOrder($input);

        $jsApiParameters = $tools->GetJsApiParameters($order);

        //获取共享收货地址js函数参数
        // $editAddress= $tools->GetEditAddressParameters();
        // 设置必填参数
        $_SESSION['wx_jsparam'] = $jsApiParameters;

        $js =
        '<script language="javascript">
            function jsApiCall(){
                WeixinJSBridge.invoke("getBrandWCPayRequest",' . $jsApiParameters . ',
                function(res){
                    if(res.err_msg == "get_brand_wcpay_request:ok"){
                    location.href="' . $wx_return . '"}
                }
            )};
            function wxcallpay(){
                if (typeof WeixinJSBridge == "undefined"){
                    if( document.addEventListener ){
                        document.addEventListener("WeixinJSBridgeReady", jsApiCall, false);
                    }else if (document.attachEvent){
                        document.attachEvent("WeixinJSBridgeReady", jsApiCall);
                        document.attachEvent("onWeixinJSBridgeReady", jsApiCall);
                    }
                }else{
                    jsApiCall();
                }
            }
        </script>';

        $button = '<a class="box-flex btn-submit" type="button" onclick="wxcallpay()">微信支付</a>' . $js;

        return $button;

    }

    /*
     * H5支付
     * &nonce_str=$nonce_str¬ify_url=$notify_url
     */
    public function get_h5($order, $payment)
    {
        $money= bcmul($order['order_amount'], 100, 0);//充值金额   
        $userip = get_client_ip(); //获得用户设备IP 自己网上百度去  
        $appid = $payment['appid'];//微信给的  
        $mch_id = $payment['mchid'];//微信官方的  
        $key = $payment['key'];//自己设置的微信商家key  

        $out_trade_no     = $order['order_sn'];//平台内部订单号  
        $nonce_str        = MD5($out_trade_no);//随机字符串  
        $body             = "公众号开通服务";//内容  
        $total_fee        = $money; //金额  
        $spbill_create_ip = $userip; //IP  
        // $notify_url       = U('Home/Pay/notify','',true,true); //回调地址
        $notify_url       = U('Home/Pay/wxh5notify','',true,true); //回调地址
        $trade_type       = 'MWEB';//交易类型 具体看API 里面有详细介绍  
        $scene_info       = '{"h5_info":{"type":"Wap","wap_url":"http://www.baidu.com","wap_name":"支付"}}';//场景信息 必要参数  
        $signA            = "appid=$appid&body=$body&mch_id=$mch_id&nonce_str=$nonce_str&notify_url=$notify_url&out_trade_no=$out_trade_no&scene_info=$scene_info&spbill_create_ip=$spbill_create_ip&total_fee=$total_fee&trade_type=$trade_type";  
        $strSignTmp       = $signA."&key=$key"; //拼接字符串  注意顺序微信有个测试网址 顺序按照他的来 直接点下面的校正测试 包括下面XML  是否正确  
        $sign             = strtoupper(MD5($strSignTmp)); // MD5 后转换成大写  
        $post_data        = "<xml>  
            <appid>$appid</appid>  
            <body>$body</body>  
            <mch_id>$mch_id</mch_id>  
            <nonce_str>$nonce_str</nonce_str>  
            <notify_url>$notify_url</notify_url>  
            <out_trade_no>$out_trade_no</out_trade_no>  
            <scene_info>$scene_info</scene_info>  
            <spbill_create_ip>$spbill_create_ip</spbill_create_ip>  
            <total_fee>$total_fee</total_fee>  
            <trade_type>$trade_type</trade_type>  
            <sign>$sign</sign>  
        </xml>";//拼接成XML 格式  
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";//微信传参地址  

        $dataxml = $this->http_post($url,$post_data); //后台POST微信传参地址  同时取得微信返回的参数  POST 方法我写下面了  
        $objectxml = (array)simplexml_load_string($dataxml, 'SimpleXMLElement', LIBXML_NOCDATA); //将微信返回的XML 转换成数组
        if (empty($objectxml)) {
            return false;
        } else {
            return $objectxml['mweb_url'];
        }
    }

    public function http_post($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 同步通知
     * @param $data
     * @return mixed
     */
    public function callback($data)
    {
        return true;
    }

    /**
     * 异步通知
     * @param $data
     * @return mixed
     */
    public function notify($data)
    {
        include_once BASE_PATH . 'helpers/payment_helper.php';

        if (!empty($data['postStr'])) {
            $payment = get_payment($data['code']);
            $postdata = json_decode(json_encode(simplexml_load_string($data['postStr'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            /* 检查插件文件是否存在，如果存在则验证支付是否成功，否则则返回失败信息 */
            // 微信端签名
            $wxsign = $postdata['sign'];
            unset($postdata['sign']);
            foreach ($postdata as $k => $v) {
                $Parameters[$k] = $v;
            }

            // 签名步骤一：按字典序排序参数
            ksort($Parameters);
            $buff = "";
            foreach ($Parameters as $k => $v) {
                $buff .= $k . "=" . $v . "&";
            }

            $String = '';
            if (strlen($buff) > 0) {
                $String = substr($buff, 0, strlen($buff) - 1);
            }

            // 签名步骤二：在string后加入KEY
            $String = $String . "&key=" . $payment['wxpay_key'];

            // 签名步骤三：MD5加密
            $String = md5($String);

            // 签名步骤四：所有字符转为大写
            $sign = strtoupper($String);

            // 验证成功
            if ($wxsign == $sign) {
                // 交易成功
                if ($postdata['result_code'] == 'SUCCESS') {
                    // 获取log_id
                    $out_trade_no = explode('-', $postdata['out_trade_no']);
                    $order_sn = $out_trade_no[1]; // 订单号log_id
                    // 改变订单状态
                    order_paid($order_sn, 2);
                    // 修改订单信息(openid，tranid)
                    model()->table('pay_log')
                        ->data(array('openid' => $postdata['openid'], 'transid' => $postdata['transaction_id']))
                        ->where(array('log_id' => $order_sn))
                        ->update();

                    /*if(method_exists('WechatController', 'do_oauth')){
                        // 如果需要，微信通知 wanglu
                        $order_id = model('Base')->model->table('order_info')
                            ->field('order_id')
                            ->where('order_sn = "' . $out_trade_no[0] . '"')
                            ->getOne();
                        $order_url = __HOST__ . url('user/order_detail', array(
                            'order_id' => $order_id
                        ));
                        $order_url = urlencode(base64_encode($order_url));
                        send_wechat_message('pay_remind', '', $out_trade_no[0] . ' 订单已支付', $order_url, $out_trade_no[0]);
                    }*/

                }

                $returndata['return_code'] = 'SUCCESS';

            } else {

                $returndata['return_code'] = 'FAIL';
                $returndata['return_msg'] = '签名失败';
            }

        } else {

            $returndata['return_code'] = 'FAIL';
            $returndata['return_msg'] = '无数据返回';
        }

        // 数组转化为xml
        $xml = "<xml>";
        foreach ($returndata as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }

        $xml .= "</xml>";

        echo $xml;
        exit();
    }

    public function trimString($value)
    {
        $ret = null;

        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }

        return $ret;
    }

    /**
     * 作用：产生随机字符串，不长于32位
     */
    public function createNoncestr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";

        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    /**
     * 作用：设置请求参数
     */
    public function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }

        // 签名步骤一：按字典序排序参数
        ksort($Parameters);
        $buff = "";
        foreach ($Parameters as $k => $v) {
            $buff .= $k . "=" . $v . "&";
        }

        $String = '';

        if (strlen($buff) > 0) {

            $String = substr($buff, 0, strlen($buff) - 1);

        }

        // echo '【string1】'.$String.'</br>';

        // 签名步骤二：在string后加入KEY

        $String = $String . "&key=" . $this->payment['wxpay_key'];

        // echo "【string2】".$String."</br>";

        // 签名步骤三：MD5加密

        $String = md5($String);

        // echo "【string3】 ".$String."</br>";

        // 签名步骤四：所有字符转为大写

        $result_ = strtoupper($String);

        // echo "【result】 ".$result_."</br>";

        return $result_;

    }

    /**

     * 作用：以post方式提交xml到对应的接口url

     */

    public function postXmlCurl($xml, $url, $second = 30)
    {

        // 初始化curl

        $ch = curl_init();

        // 设置超时

        curl_setopt($ch, CURLOP_TIMEOUT, $second);

        // 这里设置代理，如果有的话

        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');

        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // 设置header

        curl_setopt($ch, CURLOPT_HEADER, false);

        // 要求结果为字符串且输出到屏幕上

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // post提交方式

        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        // 运行curl

        $data = curl_exec($ch);

        curl_close($ch);

        // 返回结果

        if ($data) {

            curl_close($ch);

            return $data;

        } else {

            $error = curl_errno($ch);

            echo "curl出错，错误码:$error" . "<br>";

            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";

            curl_close($ch);

            return false;

        }

    }

    /**

     * 获取prepay_id

     */

    public function getPrepayId()
    {

        // 设置接口链接

        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        try {

            // 检测必填参数

            if ($this->parameters["out_trade_no"] == null) {

                throw new Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");

            } elseif ($this->parameters["body"] == null) {

                throw new Exception("缺少统一支付接口必填参数body！" . "<br>");

            } elseif ($this->parameters["total_fee"] == null) {

                throw new Exception("缺少统一支付接口必填参数total_fee！" . "<br>");

            } elseif ($this->parameters["notify_url"] == null) {

                throw new Exception("缺少统一支付接口必填参数notify_url！" . "<br>");

            } elseif ($this->parameters["trade_type"] == null) {

                throw new Exception("缺少统一支付接口必填参数trade_type！" . "<br>");

            } elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == null) {

                throw new Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");

            }

            $this->parameters["appid"] = $this->payment['wxpay_appid']; // 公众账号ID

            $this->parameters["mch_id"] = $this->payment['wxpay_mchid']; // 商户号

            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip

            $this->parameters["nonce_str"] = $this->createNoncestr(); // 随机字符串

            $this->parameters["sign"] = $this->getSign($this->parameters); // 签名

            $xml = "<xml>";

            foreach ($this->parameters as $key => $val) {

                if (is_numeric($val)) {

                    $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

                } else {

                    $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";

                }

            }

            $xml .= "</xml>";

        } catch (Exception $e) {

            die($e->getMessage());

        }

        // $response = $this->postXmlCurl($xml, $url, 30);

        $response = \libraries\Http::curlPost($url, $xml, 30);

        $result = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        $prepay_id = $result["prepay_id"];

        return $prepay_id;

    }

    /**

     * 作用：设置jsapi的参数

     */

    public function getParameters($prepay_id)
    {

        $jsApiObj["appId"] = $this->payment['wxpay_appid'];

        $timeStamp = time();

        $jsApiObj["timeStamp"] = "$timeStamp";

        $jsApiObj["nonceStr"] = $this->createNoncestr();

        $jsApiObj["package"] = "prepay_id=$prepay_id";

        $jsApiObj["signType"] = "MD5";

        $jsApiObj["paySign"] = $this->getSign($jsApiObj);

        $this->parameters = json_encode($jsApiObj);

        return $this->parameters;

    }

    /**

     * 订单查询

     * @return mixed

     */

    public function query($order, $payment)
    {

    }

}
