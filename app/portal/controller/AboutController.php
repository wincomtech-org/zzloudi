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
class AboutController extends HomeBaseController
{
     public function _initialize()
    {
        
        parent::_initialize();
        $this->assign('html_flag','about');
        
       
    } 
    public function index()
    { 
        $banners=DB::name('banner')->where('type','about')->order('sort asc,id asc')->select();
        //首页关于我们
        $use=DB::name('about')->where('type','use')->order('sort asc')->select();
        $team=DB::name('about')->where('type','team')->order('sort asc')->select();
        $use_title=DB::name('type_dsc')->where('type','use')->find();
        $team_title=DB::name('type_dsc')->where('type','team')->find();
        $title=[
            'use'=>$use_title['title'],
            'team'=>$team_title['title']
        ];
        $this->assign('banners',$banners);
        $this->assign('use',$use);
        $this->assign('team',$team);
        $this->assign('title',$title);
        return $this->fetch();
    }
    /* 退换货流程 */
    public function refund()
    {
        $this->assign('html_flag','refund');
        //首页关于我们
        $about=DB::name('about')->where('type','refund')->find();
         
        $this->assign('about',$about);
        
        return $this->fetch();
    }
    
}
