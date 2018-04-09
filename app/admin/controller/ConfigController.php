<?php
 
namespace app\admin\controller;

 
use cmf\controller\AdminBaseController;
 
use think\Db;

 
 
class ConfigController extends AdminBaseController
{
    
    public function _initialize()
    {
        parent::_initialize();
        
    }
     
    /**
     * 网站配置
     * @adminMenu(
     *     'name'   => '网站配置',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 15,
     *     'icon'   => '',
     *     'remark' => '网站配置',
     *     'param'  => ''
     * )
     */
    public function index()
    { 
        
        $info=[
            
            'order_time'=>config('order_time')
        ];
        $this->assign('info',$info);
        
        return $this->fetch();
    }
    
    /**
     * 网站配置编辑1
     * @adminMenu(
     *     'name'   => '网站配置编辑1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '网站配置编辑1',
     *     'param'  => ''
     * )
     */
    function editPost(){
       
        $data= $this->request->param();
        
        $info=[
            'order_time'=>$data['order_time'],
           
        ];
      
        $result=cmf_set_dynamic_config($info);
        if(empty($result)){
            $this->error('修改失败'); 
        }else{ 
            $this->success('修改成功',url('index'));
        }
        
    }
     
     
}
