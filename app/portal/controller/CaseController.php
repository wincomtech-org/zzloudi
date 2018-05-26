<?php

namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
 
class CaseController extends HomeBaseController
{
    private $m;
    private $cates;
     public function _initialize()
    {
        
        parent::_initialize();
        $this->assign('html_flag','case');
        $this->assign('html_title','案例');
        $this->m=DB::name('case');
       
    } 
    /* 列表页 */
    public function index()
    {
        $banners=DB::name('banner')->where('type','case')->order('sort asc,id asc')->select();
        $m=$this->m;
         
        $where=[];
        $where_cate=['type'=>['eq','case']];
        $data=$this->request->param();
        $m_cate=Db::name('cate');
        $cates1=$m_cate->where(['type'=>'case','fid'=>0])->order('sort asc,id asc')->select();
        //如果未选择一级分类
        if(empty($data['cid1'])){
            $data['cid1']=0; 
            $where_cate=['fid'=>['neq',$data['cid1']]]; 
        }else{
            $where_cate=['fid'=>['eq',$data['cid1']]]; 
        }
        //得到所有下属分类
        $cates2=$m_cate->where($where_cate)->order('sort asc,id asc')->select();
        //未选择二级分类
        if(empty($data['cid2'])){
            $data['cid2']=0; 
            //如果没选一级分类就显示所有，选择就显示分类下所有
            if(!empty($data['cid1'])){
                $ids=[$data['cid1']]; 
                foreach ($cates2  as $k=>$v){
                    $ids[]=$v['id'];
                }
                $where['cid']=['in',$ids];
            } 
        }else{
            $where['cid']=['eq',$data['cid2']];
        }
       
        $list=$m->where($where)->order('sort asc,browse desc,time desc')->paginate(8);
        // 获取分页显示
        $page = $list->appends($data)->render();
        
        $this->assign('banners',$banners);
        $this->assign('cates1',$cates1);
        $this->assign('cates2',$cates2);
        $this->assign('page',$page);
        $this->assign('list',$list);
        $this->assign('data',$data);
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
        $m_cate=Db::name('cate');
        $tmp=$m_cate->where('id',$info['cid'])->find();
        if(empty($tmp['fid'])){
           
            $info['cid1']=$tmp['id'];
            $info['cname1']=$tmp['name'];
           
        }else{
            $cate1=$m_cate->where('id',$tmp['fid'])->find();
             
            $info['cid1']=$cate1['id'];
            $info['cname1']=$cate1['name'];
            $info['cid2']=$tmp['id'];
            $info['cname2']=$tmp['name'];
             
        }
          zz_browse('case',$id);
        $this->assign('info',$info); 
        $this->assign('html_title',$info['name']);
        return $this->fetch();
    }
     
    
}
