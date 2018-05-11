<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
 
 
class ProgramController extends AdminbaseController {

    private $m;
    private $order;
   private $type_info;
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='sort asc,time desc';
        $this->m=Db::name('program');
        $cates=Db::name('cate')->where('type','program')->order('path asc')->select();
        $tmp=[];
        foreach($cates as $k=>$v){
            $tmp[$v['id']]=$v;
        }
        
        $this->assign('cates', $tmp);
        $this->assign('flag','小程序');
    }
    
    /**
     * 小程序列表
     * @adminMenu(
     *     'name'   => '小程序管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序管理',
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
     * 小程序编辑
     * @adminMenu(
     *     'name'   => '小程序编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序编辑',
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
     * 小程序编辑1
     * @adminMenu(
     *     'name'   => '小程序编辑1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序编辑1',
     *     'param'  => ''
     * )
     */
    function editPost(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
        $path=getcwd().'/upload/';
        if(!is_file($path.$data['pic0'])){
            $this->error('图片不存在');
        }
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $size=config('pic_program');
        $size0=config('pic_program_pic0');
        $pic='program/'.$data['id'].'.jpg';
        $pic0='program/pic0'.$data['id'].'.jpg';
        //文件为新上传
        if($data['pic0']!=$pic0){ 
            zz_set_image($data['pic0'], $pic0, $size0['width'], $size0['height'], 6);
            zz_set_image($data['pic0'], $pic, $size['width'], $size['height'], 6);
            unlink($path.$data['pic0']);
        }
        $data['pic0']=$pic0;
        $data['pic']=$pic;
        $data['time']=time();
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    /**
     * 小程序删除
     * @adminMenu(
     *     'name'   => '小程序删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序删除',
     *     'param'  => ''
     * )
     */
    function delete(){
        $m=$this->m;
         $id = $this->request->param('id', 0, 'intval');
        $row=$m->where('id',$id)->delete();
        if($row===1){ 
            $path=getcwd().'/upload/';
            $data['pic']='program/'.$id.'.jpg';
            if(is_file($path.$data['pic'])){
                unlink($path.$data['pic']);
            } 
            $data['pic0']='program/pic0'.$id.'.jpg';
            if(is_file($path.$data['pic0'])){
                unlink($path.$data['pic0']);
            } 
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        exit;
    }
   
    /**
     * 小程序添加
     * @adminMenu(
     *     'name'   => '小程序添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序添加',
     *     'param'  => ''
     * )
     */
    public function add(){
        
        return $this->fetch();
    }
    
    /**
     * 小程序添加1
     * @adminMenu(
     *     'name'   => '小程序添加1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '小程序添加1',
     *     'param'  => ''
     * )
     */
    public function addPost(){
        
        $m=$this->m; 
        $data= $this->request->param();
        $path=getcwd().'/upload/';
        if(!is_file($path.$data['pic0'])){
            $this->error('图片不存在');
        }
        $data['content']=empty($_POST['content'])?'':$_POST['content'];
        $data['time']=time();
        $insertId=$m->insertGetId($data);
        if($insertId>=1){
            $size=config('pic_program');
            $size0=config('pic_program_pic0');
            
            $pic='program/'.$insertId.'.jpg';
            $pic0='program/pic0'.$insertId.'.jpg';
            //文件为新上传
           
            zz_set_image($data['pic0'], $pic0, $size0['width'], $size0['height'], 6);
            zz_set_image($data['pic0'], $pic, $size['width'], $size['height'], 6);
            unlink($path.$data['pic0']);
            
            $result    = $m->where('id',$insertId)->update(['pic'=>$pic,'pic0'=>$pic0]); 
            if($result===1){ 
                $this->success('已成功添加',url('index'));
            }else{
                $this->error('图片更新失败');
            }
        }else{
            $this->error('添加失败');
        }
        exit;
    }
}

?>