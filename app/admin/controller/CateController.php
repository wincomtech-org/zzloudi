<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
 
 
class CateController extends AdminbaseController {

    private $m;
    private $order;
   private $type_info;
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='type asc,path asc';
        $this->m=Db::name('Cate');
        $this->type_info=['case'=>'客户案例','program'=>'小程序','service'=>'服务'];
        $this->assign('flag','分类');
        $this->assign('type_info',$this->type_info);
    }
    
    /**
     * 分类列表
     * @adminMenu(
     *     'name'   => '分类管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类管理',
     *     'param'  => ''
     * )
     */
    function index(){
        $m=$this->m;

         $list= $m->order($this->order)->paginate(10);
         // 获取分页显示
         $page = $list->render(); 
          
         $this->assign('page',$page);
         
         $this->assign('list',$list);
         
        return $this->fetch();
    }
    /**
     * 编辑分类
     * @adminMenu(
     *     'name'   => '分类编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类编辑',
     *     'param'  => ''
     * )
     */
    function edit(){
        $m=$this->m;
        $id=$this->request->param('id'); 
        $info=$m->where('id',$id)->find(); 
        
        $this->assign('info',$info);
        
        return $this->fetch();
    }
    /**
     * 分类编辑-执行
     * @adminMenu(
     *     'name'   => '分类编辑-执行',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类编辑-执行',
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
     * 分类删除
     * @adminMenu(
     *     'name'   => '分类删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类删除',
     *     'param'  => ''
     * )
     */
    function delete(){
        $m=$this->m;
        $id=$this->request->param('id');  
        
        $info=$m->where('id',$id)->find();
        if(empty($info)){
            $this->error('该分类不存在');
        }
        $count=$m->where('fid',$id)->count();
        if($count>0){
            $this->error('该分类下子类，不能删除');
        }
        $pro=Db::name($info['type']);
        $count=$pro->where('cid',$id)->count();
        if($count>0){
            $this->error('该分类下有产品，不能删除');
        }
        $row=$m->where('id',$id)->delete();
        if($row===1){ 
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
   
    /**
     * 分类添加
     * @adminMenu(
     *     'name'   => '分类添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类添加',
     *     'param'  => ''
     * )
     */
    public function add(){
        $m=$this->m;
        $cates=$m->where('fid',0)->order('type asc,sort asc')->select();
        $this->assign('cates',$cates);
        return $this->fetch();
    }
    
    /**
     * 分类添加-执行
     * @adminMenu(
     *     'name'   => '分类添加-执行',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '分类添加-执行',
     *     'param'  => ''
     * )
     */
    public function addPost(){
        
        $m=$this->m; 
        $data= $this->request->param();
        
        $data['time']=time();
        $insertId=$m->insertGetId($data);
        //还有2级分类
        if(empty($data['fid'])){
            $path='0-'.$insertId;
        }else{
            $path='0-'.$data['fid'].'-'.$insertId;
        }
        
        $m->where('id',$insertId)->update(['path'=>$path]);
        $this->success('已成功添加',url('index')); 
         
    }
}

?>