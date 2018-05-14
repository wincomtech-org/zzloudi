<?php


namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;


class AboutController extends AdminbaseController {
    
    private $m;
    private $order;
   
    public function _initialize()
    {
        parent::_initialize();
        $this->order='type asc,sort asc,id asc';
        $this->m=Db::name('about');
        $types=Db::name('type_dsc')->where('tables','about')->select();
        $tmp=[];
        foreach($types as $k=>$v){
            $tmp[$v['type']]=$v['dsc'].'--'.$v['title'];
        }
        
        $this->assign('types', $tmp);
        $this->assign('flag','关于我们');
    }
    
    /**
     * 关于我们列表
     * @adminMenu(
     *     'name'   => '关于我们管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们管理',
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
     * 关于我们编辑
     * @adminMenu(
     *     'name'   => '关于我们编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们编辑',
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
     * 关于我们编辑-执行
     * @adminMenu(
     *     'name'   => '关于我们编辑-执行',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们编辑-执行',
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
        $path='upload/';
        if(in_array($data['type'],['help','team'])){ 
            if(!is_file($path.$data['pic'])){
                $this->error('图片不存在');
            } 
        }
      
        //help,pro,team有图
        //index,use无图
        if(in_array($data['type'],['help','pro','team']) && is_file($path.$data['pic'])){
            $data['pic']=zz_picid($data['pic'],$info['pic'],'about_'.$data['type'],$info['id'],'about');
           
        }
        if(is_file($path.$data['bg'])){
            $data['bg']=zz_picid($data['bg'],$info['pic'],'about_bg',$info['id'],'about','bg');
            
        }
        
        $data['time']=time();
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            if($data['pic']!=$info['pic'] && is_file('upload/'.$info['pic'])){
                unlink('upload/'.$info['pic']);
            }
            if($data['bg']!=$info['bg'] && is_file('upload/'.$info['bg'])){
                unlink('upload/'.$info['bg']);
            }
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    /**
     * 关于我们添加
     * @adminMenu(
     *     'name'   => '关于我们添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们添加',
     *     'param'  => ''
     * )
     */
    public function add(){
       
        return $this->fetch();
    }
    
    /**
     * 关于我们添加1
     * @adminMenu(
     *     'name'   => '关于我们添加1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们添加1',
     *     'param'  => ''
     * )
     */
    public function addPost(){
        
        $m=$this->m;
        $data= $this->request->param();
        if(in_array($data['type'],['help','team'])){
            $path='upload/';
            if(!is_file($path.$data['pic'])){
                $this->error('图片不存在');
            }
            
        }
        $data['time']=time();
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $insertId=$m->insertGetId($data);
        if($insertId>=1){
            if(in_array($data['type'],['help','pro','team']) && is_file($path.$data['pic'])){
                $data['pic']=zz_picid($data['pic'],'','about_'.$data['type'],$insertId,'about');
                $data['bg']=zz_picid($data['bg'],'','about_bg',$insertId,'about','bg'); 
                
                $result    = $m->where('id',$insertId)->update(['pic'=>$data['pic'],'bg'=>$data['bg']]); 
            }
            
            $this->success('已成功添加',url('index'));
        }else{
            $this->error('添加失败');
        }
        
    }
    
    /**
     * 关于我们删除
     * @adminMenu(
     *     'name'   => '关于我们删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '关于我们删除',
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
            $path='upload/'; 
            if(is_file($path.$info['pic'])){
                unlink($path.$info['pic']);
            }
            if(is_file($path.$info['bg'])){
                unlink($path.$info['bg']);
            }
            
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
    
    
}

?>