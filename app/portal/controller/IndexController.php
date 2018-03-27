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
     public function _initialize()
    {
        
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
    /* 订单提交 */
    public function order_do(){
        $data0=$this->request->param();
        
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
        ];
        
        $uid=session('user.id');
        $data['uid']=empty($uid)?0:$uid;
        $service_name=Db::name('service')->where('id',$data['service_id'])->find();
        $data['service_name']=$service_name['name'];
        $data['money']=$service_name['price'];
        $id=Db::name('order')->insertGetId($data);
        $this->redirect(url('portal/index/order',['id'=>$id]));
    }
    /* 订单支付 */
    public function order(){
        $id=$this->request->param('id','intval',0);
        $order=Db::name('order')->where(['id'=>$id,'status'=>1])->find();
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
        $id=$this->request->param('id','intval',0);
        $order=Db::name('order')->where(['id'=>$id])->find();
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
    
}
