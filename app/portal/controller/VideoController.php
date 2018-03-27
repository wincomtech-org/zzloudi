<?php

namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
use Memcache;
class VideoController extends HomeBaseController
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
         
         
        $list=$m->order('sort asc,browse desc,time desc')->paginate(2);
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
        if(empty($info)){
            $this->redirect(url('portal/index/index'));
        }
        
          zz_browse('video',$id);
        $this->assign('info',$info); 
       
        return $this->fetch();
    }
     
    
}
