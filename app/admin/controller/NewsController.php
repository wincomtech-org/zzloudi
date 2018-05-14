<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
 
 
class NewsController extends AdminbaseController {

    private $m;
    private $order;
  
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='sort asc,insert_time desc';
        $this->m=Db::name('news');
      
        $this->assign('flag','资讯');
    }
    
    /**
     * 资讯列表
     * @adminMenu(
     *     'name'   => '资讯管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯管理',
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
     * 资讯编辑
     * @adminMenu(
     *     'name'   => '资讯编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯编辑',
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
     * 资讯编辑1
     * @adminMenu(
     *     'name'   => '资讯编辑1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯编辑1',
     *     'param'  => ''
     * )
     */
    function editPost(){
        $m=$this->m;
        $data= $this->request->param(); 
        $info=$m->where('id',$data['id'])->find();
        if(empty($info)){
            $this->error('数据错误');
        }
         
        $data['pic']=zz_picid($data['pic'],$info['pic'],'news',$info['id']);
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $data['time']=time();
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            if($data['pic']!=$info['pic'] && is_file('upload/'.$info['pic'])){
                unlink('upload/'.$info['pic']);
            }
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    /**
     * 资讯删除
     * @adminMenu(
     *     'name'   => '资讯删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯删除',
     *     'param'  => ''
     * )
     */
    function delete(){
        $m=$this->m;
         $id = $this->request->param('id', 0, 'intval');
         $info=$m->where('id',$id)->find();
         if(empty($info)){
             $this->error('数据错误');
         }
        $row=$m->where('id',$id)->delete();
        if($row===1){ 
            $path=getcwd().'/upload/';
          
            if(is_file($path.$info['pic'])){
                unlink($path.$info['pic']);
            } 
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
   
    /**
     * 资讯添加
     * @adminMenu(
     *     'name'   => '资讯添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯添加',
     *     'param'  => ''
     * )
     */
    public function add(){
        
        return $this->fetch();
    }
    
    /**
     * 资讯添加1
     * @adminMenu(
     *     'name'   => '资讯添加1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '资讯添加1',
     *     'param'  => ''
     * )
     */
    public function addPost(){
        
        $m=$this->m; 
        $data= $this->request->param();
        
        $data['time']=time();
        $data['insert_time']=$data['time'];
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $insertId=$m->insertGetId($data);
       
        $pic=zz_picid($data['pic'],'','news',$insertId);
        if(!empty($pic)){
            $result    = $m->where('id',$insertId)->update(['pic'=>$pic]); 
        }
        $result    = $m->where('id',$insertId)->update(['pic'=>$pic]); 
        $this->success('已成功添加',url('index'));
            
    }
}

?>