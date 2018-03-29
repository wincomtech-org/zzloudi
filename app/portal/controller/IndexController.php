<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
class IndexController extends HomeBaseController
{
    private $m;
     public function _initialize()
    {
        $this->m=Db::name('order');
        parent::_initialize();
        
    } 
    public function index()
    {
        $this->assign('html_flag','index'); 
        
        //banner
        $banners=DB::name('banner')->where('type','index')->order('sort asc,id asc')->select();
        $types_tmp=Db::name('type_dsc')->select();
        $types=[];
        foreach($types_tmp as $k=>$v){
            $types[$v['tables']][$v['type']]=$v['title'];
        }
        
        //首页关于我们
        $m_about=DB::name('about');
        $about=$m_about->where('type','index')->find();
        $helps=$m_about->where('type','help')->order('sort asc')->select();
        $pros=$m_about->where('type','pro')->order('sort asc')->select();
         
        $m_link=DB::name('link');
        $links['pic']=$m_link->where('type','pic')->order('list_order asc')->select();
        $links['text']=$m_link->where('type','text')->order('list_order asc')->select();
        
        $this->assign('banners',$banners);
        $this->assign('types',$types);
        
        $this->assign('about',$about); 
        $this->assign('helps',$helps);
        $this->assign('pros',$pros);
       
        $this->assign('links',$links);
        
       
        return $this->fetch();
    }
    public function product(){
        
        $this->assign('html_flag','product'); 
        $this->assign('html_title','产品购买');
        //banner
        $banners=DB::name('banner')->where('type','product')->order('sort asc,id asc')->select();
        //获取city
        $m_city=Db::name('city');
        $city1=$m_city->where('type',1)->select();
        $city2=$m_city->where('type',2)->select();
        $city3=$m_city->where('type',3)->select();
        $cates=Db::name('cate')->where('type','service')->select();
        $m_service=Db::name('service');
        $services=[];
        foreach($cates as $k=>$v){
            $services[]=$m_service->where('cid',$v['id'])->order('sort asc')->select();
        }
        
        $this->assign('banners',$banners);
        $this->assign('city1',$city1);
        $this->assign('city2',$city2);
        $this->assign('city3',$city3);
        $this->assign('cates',$cates);
        $this->assign('services',$services);
        return $this->fetch();
    }
    /* 订单提交,废弃 */
    public function order_do0(){
        $this->error('废弃');
        $data0=$this->request->param();
        if(preg_match(config('reg_mobile'), $data0['tel'])!=1){
            $this->error('手机号码错误');
        } 
       /*  ["HTTP_HOST"] => string(10) "zzloudi.cc"
            ["REDIRECT_STATUS"] => string(3) "200"
                ["SERVER_NAME"] => string(10) "zzloudi.cc" */
        $data=[
            'uname'=>$data0['uname'],
            'city'=>$data0['city'],
            'tel'=>$data0['tel'],
            'qq'=>$data0['qq'],
            'email'=>$data0['email'], 
            'service_id'=>$data0['gy_w'],
            'wx_name'=>$data0['wx_name'],
            'wx_dsc'=>$data0['wx_dsc'], 
            'ip'=>get_client_ip(),
            'web_name'=>$_SERVER['HTTP_HOST'],
            'status'=>1,
            'oid'=>'kt'.cmf_get_order_sn(),
            'insert_time'=>time(),
            'time'=>time(),
        ];
       
        $uid=session('user.id');
        $m_user=Db::name('user');
        //没登录就根据手机和邮箱查找用户，手机优先级高
        if(empty($uid)){
            $uid=0;
            $tmp=$m_user->where('mobile',$data0['tel'])->find();
            if(!empty($tmp)){
                $uid=$tmp['id']; 
            }else{
                $tmp=$m_user->where('user_email',$data0['email'])->find();
                if(!empty($tmp)){
                    $uid=$tmp['id'];
                }
            }
        }
        $data['uid']=$uid;
        $service_name=Db::name('service')->where('id',$data['service_id'])->find();
        $data['service_name']=$service_name['name'];
        $data['money']=$service_name['price'];
        $id=Db::name('order')->insertGetId($data);
        $this->redirect(url('portal/index/order',['id'=>$id]));
    }
    /* 订单提交1 */
    public function order_do1(){
       
        $data0=$this->request->param();
        if(preg_match(config('reg_mobile'), $data0['tel'])!=1){
            $this->error('手机号码错误');
        }
        $data=[];
        $data['uname']=$data0['uname'];
        $data['city']=$data0['city'];
        $data['tel']=$data0['tel'];
        $data['qq']=$data0['qq'];
        $data['email']=$data0['email'];
        $data['time']=time();
        
        $m_order=$this->m;
        //没有提交过订单
        if(empty($data0['oid'])){
            
            $data['ip']=get_client_ip();
            $data['web_name']=$_SERVER['HTTP_HOST'];
            $data['status']=1;
            $data['oid']='kt'.cmf_get_order_sn();
            $data['insert_time']= $data['time'];
            $uid=session('user.id');
            $m_user=Db::name('user');
            //没登录就根据手机和邮箱查找用户，手机优先级高
            if(empty($uid)){
                $where_user=['user_type'=>2];
                $where_user['mobile']=$data['tel'];
                $tmp=$m_user->where($where_user)->find();
                 
                if(empty($tmp)){
                    unset($where_user['mobile']);
                    $where_user['user_email']=$data['email'];
                    $tmp=$m_user->where($where_user)->find();
                    if(empty($tmp)){
                        $uid=0;
                    }else{
                        $uid=$tmp['id'];
                    }
                    $uid=$tmp['id'];
                }else{
                    $uid=$tmp['id'];
                }
            }
            $data['uid']=$uid;
            $id=$m_order->insertGetId($data);
            $this->success('订单添加成功','',['oid'=>$data['oid']]);
        }else{ 
            $data['oid']=$data0['oid'];
            $row=$m_order->where(['oid'=>$data['oid'],'status'=>1])->update($data);
            if($row===1){
                $this->success('订单保存成功','',['oid'=>$data['oid']]);
            }else{
                $this->error('订单保存失败');
            }
        }
         
    }
    /* 订单提交1 */
    public function order_do2(){
        
        $data=$this->request->param();
        $service_name=Db::name('service')->where('id',$data['service_id'])->find();
        $data_order=[
            'service_id'=>$data['service_id'],
            'time'=>time(),
            'service_name'=>$service_name['name'],
            'money'=>$service_name['price'],
        ];
       
        $m_order=$this->m;
         
        $row=$m_order->where(['oid'=>$data['oid'],'status'=>1])->update($data_order);
        if($row===1){
            $this->success('订单保存成功');
        }else{
            $this->error('订单保存失败');
        }
         
        
    }
    /* 订单提交3 */
    public function order_do3(){
        
        $data=$this->request->param();
       
        $data_order=[
            'wx_name'=>$data['wx_name'],
            'wx_dsc'=>$data['wx_dsc'], 
            'time'=>time(),
        ];
        
        $m_order=$this->m;
        
        $row=$m_order->where(['oid'=>$data['oid'],'status'=>1])->update($data_order);
        if($row===1){
            $this->redirect(url('portal/index/order',['oid'=>$data['oid']]));
        }else{
            $this->error('订单保存失败');
        } 
    }
    /* 订单支付 */
    public function order(){
        $this->assign('html_title','订单支付');
        $oid=$this->request->param('oid');
        $order=Db::name('order')->where(['oid'=>$oid,'status'=>1])->find();
        if(empty($order)){
            $this->redirect(url('portal/index/product'));
        }
        $this->assign('html_flag','product');
        //banner
        $banners=DB::name('banner')->where('type','product')->order('sort asc,id asc')->select();
        $this->assign('banners',$banners);
        $this->assign('order',$order);
        return $this->fetch();
    }
   
    /* 订单支付结果 */
    public function pay_result(){
        $oid=$this->request->param('oid');
        $order['result']=$this->request->param('result','');
        $m_order=Db::name('order');
        $order=$m_order->where(['oid'=>$oid])->find();
        if(empty($order)){
            $this->redirect(url('portal/index/product'));
        }
        
        $this->assign('html_title','支付结果');
        $this->assign('html_flag','product');
        //banner
        $banners=DB::name('banner')->where('type','product')->order('sort asc,id asc')->select();
        $this->assign('banners',$banners);
        $this->assign('order',$order);
        return $this->fetch();
    }
    
}
