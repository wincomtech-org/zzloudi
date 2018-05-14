<?php

namespace app\user\controller;

use cmf\controller\UserBaseController;
use think\Db;
class VideoController extends UserBaseController
{
    private $m;
    
     public function _initialize()
    {
        
        parent::_initialize();
        $this->assign('html_flag','video');
        $this->m=DB::name('video');
       
    } 
    /* 列表页 */
    public function index()
    {
        $banners=DB::name('banner')->where('type','video')->order('sort asc,id asc')->select();
        $m=$this->m;
         
         
        $list=$m->order('sort asc,browse desc,time desc')->paginate(8);
        // 获取分页显示
        $page = $list->render();
        
        $this->assign('banners',$banners);
       
        $this->assign('page',$page);
        $this->assign('list',$list);
        
        return $this->fetch();
    }
    /* 详情页 */
    public function detail()
    {
        $id=$this->request->param('id',0,'intval'); 
        $m=$this->m; 
        $info=$m->where('id',$id)->find();
        $index=url('portal/index/index');
        if(empty($info)){
            $this->redirect($index);
        }
        $uid=session('user.id');
        
        $user=Db::name('user')->where('id',$uid)->find();
        if(empty($user['user_status'])){
            session('user',null);
            $this->error('用户信息错误',$index);
        }
        if($user['rate']!=2){
            $this->error('只有VIP用户才能观看视频',$index);
        }
        
          zz_browse('video',$id);
        $this->assign('info',$info); 
       
        return $this->fetch();
    }
     
    
}
