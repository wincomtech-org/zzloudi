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
class NewsController extends HomeBaseController
{
     public function _initialize()
    { 
        parent::_initialize();
        $this->assign('html_flag','news'); 
        $this->assign('html_title','行业资讯');
    } 
    public function index()
    { 
        $m_news=Db::name('news');
        $list=$m_news->order('sort asc,insert_time desc')->paginate(10);
       
        $page=$list->render();
        $list=zz_get_content($list);
        $this->assign('list',$list);
        $this->assign('page',$page);
        return $this->fetch();
    }
    
    /* 新闻详情页*/
    public function detail()
    {
        $m_news=Db::name('news');
        $id=$this->request->param('id',0,'intval');
        
        $info=$m_news->where('id',$id)->find();
         if(empty($info)){
             $this->redirect(url('index'));
         }
         $this->assign('info',$info);
         $this->assign('html_title',$info['name']);
        return $this->fetch();
    }
    
}
