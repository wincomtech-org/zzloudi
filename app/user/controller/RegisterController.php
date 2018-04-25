<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use think\Validate;
use think\Db;
 

class RegisterController extends HomeBaseController
{
    
    /**
     * 前台用户注册
     */
    public function index()
    {
        
        if (cmf_is_user_login()) {
            $this->redirect(url('user/index/index'));
        } else {
            // return $this->fetch(":register");
            $this->redirect(url('register'));
        }
    }
    /**
     * 前台用户注册页面
     */
    public function register()
    {
        $this->redirect(url('portal/index/product'));
        $this->assign('verify_type',config('verify'));
        $this->assign('html_title','注册');
        return $this->fetch();
        
    }
    /**
     * 发送验证码
     */
    public function sendmsg()
    {
        $verify=session('verify');
        $time=time();
        if(!empty($verify) && ($time-$verify['time'])<60){
            $this->error('验证码发送频繁');
        }
        session('verify',null);
        $phone=$this->request->param('tel',0);
        $type=$this->request->param('type','reg'); 
        $email=$this->request->param('email','');
        $tmp1=Db::name('user')->where('user_email',$email)->find();
        $tmp=Db::name('user')->where('mobile',$phone)->find();
        switch ($type){
            //注册
            case 'reg':  
                if(!empty($tmp)){
                    $this->error('该手机号已被使用');
                } 
                 
                if(!empty($tmp1)){
                    $this->error('邮箱已被使用');
                }
                break;
                //找回密码
            case 'find':
                
                if(empty($tmp1)){
                    $this->error('该邮箱不存在');
                }
                
                break;
                //换手机号
            case 'mobile':
                if(!empty($tmp)){
                    $this->error('该手机号已被使用');
                }
                //判断密码
                $psw=$this->request->param('psw',0);
                $user=Db::name('user')->where('id',session('user.id'))->find();
                if(cmf_password($psw)!=$user['user_pass']){
                    $this->error('密码错误');
                }
                break;
           
            default:
                $this->error('未知操作');
                
        }
        
        $verify_type=config('verify');
        $num=rand(100000,999999);
       
        if($verify_type==1){ 
            $result = cmf_send_email($email, '邮箱验证码', $num);
            if($result['error']==0){
                session('verify',['time'=>$time,'code'=>$num,'mobile'=>$phone,'email'=>$email,'type'=>$type]);
            } 
            $this->error($result['message']);
        }else{
            
            $this->error('短信验证未接入');
        }
       
    }
    
    /**
     * 前台用户注册提交
     */
    public function ajaxRegister()
    {
        
        $time=time();
        $verify=session('verify');
        $data1 = $this->request->post();
        //验证码
          if(empty($verify) ||($time-$verify['time'])>600){
            $this->error('验证码不存在或已过期');
        }
        if($verify['code']!=$data1['code']){
            $this->error('验证码错误');
        }
        if($verify['mobile']!=$data1['tel'] || $verify['email']!=$data1['email'] ){
            $this->error('手机号码或邮箱不匹配');
        } 
        
        $rules = [
            'user_login' => 'require|min:2|max:20',
            'user_pass' => 'require|min:6|max:20',
            'mobile'=>'require|number|length:11',
            'user_email'=>'require|email',
             
        ];
        $redirect                = url('portal/index/index');
        $validate = new Validate($rules);
        $validate->message([
            'user_pass.require' => '密码不能为空',
            'user_pass.min'     => '密码为6-20位',
            'user_pass.max'     => '密码为6-20位',
            'user_login.require' => '用户名不能为空',
            'user_login.min'     => '用户名为2-20位',
            'user_login.max'     => '用户名为2-20位',
            'mobile.require' => '手机号码不能为空',
            'mobile.length'     => '手机号码格式错误',
            'user_email.require'     => '邮箱不能为空',
            'user_email.email'     => '邮箱格式错误',
            
        ]); 
        $data=[
            'user_login'=>$data1['username'],
            'user_pass'=>$data1['password'],
            'mobile'=>$data1['tel'],
            'user_email'=>$data1['email'],
            'last_login_ip'   => get_client_ip(0, true),
            'create_time'     => $time,
            'last_login_time' => $time,
            'user_status'     => 1,
            "user_type"       => 2,//会员
             
        ];
       
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if(preg_match(config('reg_mobile'), $data1['tel'])!=1){
            $this->error('手机号码错误');
        } 
        $data['user_pass'] = cmf_password($data['user_pass']);
         
        $m_user=Db::name('user');
        $tmp=$m_user->where('mobile',$data['mobile'])->find();
        if(!empty($tmp)){
            $this->error('该手机号已被使用');
        }
        $tmp1=Db::name('user')->where('user_email',$data['user_email'])->find();
        if(!empty($tmp1)){
            $this->error('邮箱已被使用');
        }
       
        $result  = $m_user->insertGetId($data);
        if ($result !== false) {
            $data   = Db::name("user")->where('id', $result)->find();
            cmf_update_current_user($data);
            session('verify',null);
            $this->success("注册成功！",$redirect);
        } else {
            $this->error("注册失败！");
        }
        
    }
    
}