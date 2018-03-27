<?php


namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;


class TypeDscController extends AdminbaseController {
    
    private $m;
    private $order;
    
    public function _initialize()
    {
        parent::_initialize();
        $this->order='tables asc';
        $this->m=Db::name('type_dsc');
        $this->assign('flag','页面说明');
    }
    
    /**
     * 页面说明列表
     * @adminMenu(
     *     'name'   => '页面说明管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '页面说明管理',
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
     * 页面说明编辑
     * @adminMenu(
     *     'name'   => '页面说明编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '页面说明编辑',
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
     * 页面说明编辑-执行
     * @adminMenu(
     *     'name'   => '页面说明编辑-执行',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '页面说明编辑-执行',
     *     'param'  => ''
     * )
     */
    function editPost(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
         
        $data= $this->request->param();
       
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    
    
}

?>