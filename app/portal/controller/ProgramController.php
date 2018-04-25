<?php

namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
use Memcache;
class ProgramController extends HomeBaseController
{
    private $m;
    
     public function _initialize()
    {
        
        parent::_initialize();
        $this->assign('html_flag','program');
        $this->assign('html_title','小程序');
        $this->m=DB::name('program');
       
    } 
    /* 列表页 */
    public function index()
    {
        $banners=DB::name('banner')->where('type','program')->order('sort asc,id asc')->select();
        $m=$this->m;
        $list=$m->order('sort asc,id asc')->select();
        $cates=Db::name('cate')->where(['type'=>'case','fid'=>0])->order('sort asc,id asc')->select();
        
        $this->assign('banners',$banners);
        $this->assign('cates',$cates);
        
        $this->assign('list',$list);
       
        return $this->fetch();
    }
     
}
