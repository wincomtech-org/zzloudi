<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
 
 
class ServiceController extends AdminbaseController {

    private $m;
    private $order;
   private $type_info;
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='sort asc,time desc';
        $this->m=Db::name('service');
        $cates=Db::name('cate')->where(['fid'=>0,'type'=>'service'])->order('path asc')->select();
        $tmp=[];
        foreach($cates as $k=>$v){
            $tmp[$v['id']]=$v;
        }
        
        $this->assign('cates', $tmp);
        $this->assign('flag','服务');
    }
    
    /**
     * 服务列表
     * @adminMenu(
     *     'name'   => '服务管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务管理',
     *     'param'  => ''
     * )
     */
    function index(){
        $m=$this->m;
         $data=$this->request->param();
         $where=[];
         if(empty($data['cid'])){
             $data['cid']=0;
         }else{
             $where=['cid'=>$data['cid']];
         }
         $list= $m->where($where)->order($this->order)->paginate(10);
         // 获取分页显示
         $page = $list->appends($data)->render(); 
          
         $this->assign('page',$page);
         $this->assign('data',$data);
         $this->assign('list',$list);
         
        return $this->fetch();
    }
    /**
     * 服务编辑
     * @adminMenu(
     *     'name'   => '服务编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务编辑',
     *     'param'  => ''
     * )
     */
    function edit(){
        $m=$this->m;
        $id=$this->request->param('id'); 
        $info=$m->where('id',$id)->find(); 
      
        $this->assign('info',$info);
       
        
        //不同类别到不同的页面
        return $this->fetch();
    }
    /**
     * 服务编辑1
     * @adminMenu(
     *     'name'   => '服务编辑1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务编辑1',
     *     'param'  => ''
     * )
     */
    function editPost(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
         
        $data['time']=time();
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    /**
     * 服务删除
     * @adminMenu(
     *     'name'   => '服务删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务删除',
     *     'param'  => ''
     * )
     */
    function delete(){
        $m=$this->m;
         $id = $this->request->param('id', 0, 'intval');
        $row=$m->where('id',$id)->delete();
        if($row===1){ 
            
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
   
    /**
     * 服务添加
     * @adminMenu(
     *     'name'   => '服务添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务添加',
     *     'param'  => ''
     * )
     */
    public function add(){
        
        return $this->fetch();
    }
    
    /**
     * 服务添加1
     * @adminMenu(
     *     'name'   => '服务添加1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '服务添加1',
     *     'param'  => ''
     * )
     */
    public function addPost(){
        
        $m=$this->m; 
        $data= $this->request->param();
         
        $data['time']=time();
        $insertId=$m->insertGetId($data);
        if($insertId>=1){
             $this->success('已成功添加',url('index'));
            
        }else{
            $this->error('添加失败');
        }
        exit;
    }
}

?>