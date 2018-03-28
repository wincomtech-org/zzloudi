<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
 
 
class OrderController extends AdminbaseController {

    private $m;
    private $order;
    private $order_status;
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='id desc';
        $this->m=Db::name('order');
        $this->order_status=config('order_status');
        //订单状态1初始，1已支付，3已完成，4已退款
        $this->assign('order_status',$this->order_status);
        //先不显示支付方式
//         $this->assign('pays',config('pays'));
        $this->assign('flag','订单');
    }
    
    /**
     * 订单列表
     * @adminMenu(
     *     'name'   => '订单管理',
     *     'parent' => '',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单管理',
     *     'param'  => ''
     * )
     */
    function index(){
        $m=$this->m;
         $data=$this->request->param();
         $where=[];
         if(empty($data['status'])){
             $data['status']=0;
         }else{
             $where['status']=['eq',$data['status']];
         }
         if(empty($data['uname'])){
             $data['uname']='';
         }else{
             $where['uname']=['like','%'.$data['uname'].'%'];
         }
         $keywordComplex = [];
         if (!empty($data['where'])) {
             $keyword = $data['where']; 
             $keywordComplex['tel|qq|uname']    = ['eq', $keyword];
         }else{
             $data['where']='';
         }
         $list= $m->whereOr($keywordComplex)->where($where)->order($this->order)->paginate(10);
         // 获取分页显示
         $page = $list->appends($data)->render(); 
          
         $this->assign('page',$page);
         $this->assign('data',$data);
         $this->assign('list',$list);
         
        return $this->fetch();
    }
    /**
     * 订单编辑
     * @adminMenu(
     *     'name'   => '订单编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单编辑',
     *     'param'  => ''
     * )
     */
    function edit(){
        $m=$this->m;
        $id=$this->request->param('id'); 
        $info=$m->where('id',$id)->find();
        
        $tmp=Db::name('city')->field('concat(c1.name,"-",c2.name,"-",c3.name) as city_name')
        ->alias('c3')
        ->join('cmf_city c2','c2.id=c3.fid')
        ->join('cmf_city c1','c1.id=c2.fid')
        ->where('c3.id',$info['city'])->find();
        
        $info['city_name']=empty($tmp['city_name'])?'地址错误':$tmp['city_name'];
        if($info['status']>1){
            $pay=Db::name('pay')->where('oid',$info['oid'])->find();
            $this->assign('pay',$pay);
            $this->assign('pays',config('pays'));
        }
        
        $this->assign('info',$info);
         
        return $this->fetch();
    }
    /**
     * 订单编辑1
     * @adminMenu(
     *     'name'   => '订单编辑1',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单编辑1',
     *     'param'  => ''
     * )
     */
    function editPost(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        } 
        $info=$m->where('id',$data['id'])->find();
        if(empty($info)){
            $this->error('订单不存在');
        }
        if($info['status']==$data['status']){
            $this->success('未修改订单',url('index')); 
        }
        if($data['status']!=3 || $data['status']!=4){
            $this->error('只能改为已完成和已退款');
        }
        $data['time']=time();
        $order_status=$this->order_status;
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data['time'],
            'ip'=>get_client_ip(),
            'action'=>'对订单'.$info['oid'].'更改状态"'.$order_status[$info['status']].'"为"'.$order_status[$data['status']].'"',
            
        ];
        
        $row=$m->where('id', $data['id'])->update($data);
        if($row===1){
            Db::name('action')->insert($data_action);
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    
}

?>