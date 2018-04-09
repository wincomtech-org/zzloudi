<?php

 
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\portal\controller\PayController;
 
class OrderController extends AdminbaseController {

    private $m;
    private $order;
    private $order_status;
    public function _initialize()
    {
        parent::_initialize(); 
        $this->order='time desc';
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
         $where=['is_delete'=>['eq',0]];
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
             $keywordComplex['id|oid|tel|qq|uname']    = ['eq', $keyword];
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
     * 订单回收站
     * @adminMenu(
     *     'name'   => '订单回收站',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单回收站',
     *     'param'  => ''
     * )
     */
    function old(){
        $m=$this->m;
        $data=$this->request->param();
        $where=['is_delete'=>['eq',1]];
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
            $keywordComplex['id|oid|tel|qq|uname']    = ['eq', $keyword];
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
        
        $data['time']=time();
        $order_status=$this->order_status;
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data['time'],
            'ip'=>get_client_ip(),
            'action'=>'对订单'.$info['oid'].'更改状态"'.$order_status[$info['status']].'"为"'.$order_status[$data['status']].'"',
            
        ];
        //如果改为已支付需要执行支付代码
        if($data['status']==2){
            //之类类别为3，buyer-id为管理员id，交易号为订单id
            $pay_data=[
                'total_fee'=>$info['money'],
                'trade_no'=>$info['id'],
                'buyer_id'=>$data_action['aid']
            ];
            $pay=new PayController();
            $result=trim($pay->pay_end($info['oid'], 3, $pay_data));
            
            if($result==='success' || $result==='end'){
                $row=1;
            }else{
                $row=0;
            }
        }else{
            $row=$m->where('id', $data['id'])->update($data);
        }
       
        if($row===1){
            Db::name('action')->insert($data_action);
            $this->success('修改成功',url('index')); 
        }else{
            $this->error('修改失败');
        }
        
    }
    /**
     * 订单回收
     * @adminMenu(
     *     'name'   => '订单回收',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单回收',
     *     'param'  => ''
     * )
     */
    function delete(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
        $info=$m->where('id',$data['id'])->find();
        if(empty($info)){
            $this->error('订单不存在');
        }
        if($info['status']==2){
            $this->error('已支付未完成的订单不能回收');
        }
         
        $data_order=['time'=>time(),'is_delete'=>1];
        
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data_order['time'],
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$info['oid'].'放入回收站', 
        ];
        
        $row=$m->where('id', $data['id'])->update($data_order);
        if($row===1){
            Db::name('action')->insert($data_action);
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        
    }
    /**
     * 订单批量回收
     * @adminMenu(
     *     'name'   => '订单批量回收',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单批量回收',
     *     'param'  => ''
     * )
     */
    function dels(){
        
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['ids'])){
            $this->error('未选中数据');
        }
        
        $idss=implode($data['ids'], ',');
       
        $data_order=['time'=>time(),'is_delete'=>1];
        
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data_order['time'],
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$idss.'批量放入回收站',
        ];
        
        $row=$m->where(['id'=>['in',$idss]])->update($data_order);
        if($row>0){
            Db::name('action')->insert($data_action);
            $this->success('回收成功');
        }else{
            $this->error('回收失败');
        }
        
    }
    /**
     * 订单批量还原
     * @adminMenu(
     *     'name'   => '订单批量还原',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单批量还原',
     *     'param'  => ''
     * )
     */
    function restores(){
        
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['ids'])){
            $this->error('未选中数据');
        }
        
        $idss=implode($data['ids'], ',');
        
        $data_order=['time'=>time(),'is_delete'=>0];
        
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data_order['time'],
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$idss.'批量还原',
        ];
        
        $row=$m->where(['id'=>['in',$idss]])->update($data_order);
        if($row>0){
            Db::name('action')->insert($data_action);
            $this->success('还原成功');
        }else{
            $this->error('还原失败');
        }
        
    }
    /**
     * 订单还原
     * @adminMenu(
     *     'name'   => '订单还原',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单还原',
     *     'param'  => ''
     * )
     */
    function restore(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
        $info=$m->where('id',$data['id'])->find();
        if(empty($info)){
            $this->error('订单不存在');
        }
        
        $data_order=['time'=>time(),'is_delete'=>0];
        
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>$data_order['time'],
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$info['oid'].'从回收站还原',
        ];
        
        $row=$m->where('id', $data['id'])->update($data_order);
        if($row===1){
            Db::name('action')->insert($data_action);
            $this->success('还原成功');
        }else{
            $this->error('还原失败');
        }
        
    }
    /**
     * 订单彻底删除
     * @adminMenu(
     *     'name'   => '订单彻底删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单彻底删除',
     *     'param'  => ''
     * )
     */
    function delete_true(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['id'])){
            $this->error('数据错误');
        }
        $info=$m->where('id',$data['id'])->find();
        if(empty($info)){
            $this->error('订单不存在');
        }
        if($info['status']==2){
            $this->error('已支付未完成的订单不能删除');
        }
         
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>time(),
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$info['oid'].'彻底删除',
        ];
        
        $row=$m->where('id', $data['id'])->delete();
        if($row===1){
            Db::name('action')->insert($data_action);
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        
    }
    /**
     * 订单批量删除
     * @adminMenu(
     *     'name'   => '订单批量删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10,
     *     'icon'   => '',
     *     'remark' => '订单批量删除',
     *     'param'  => ''
     * )
     */
    function dels_true(){
        $m=$this->m;
        $data= $this->request->param();
        if(empty($data['ids'])){
          
            $this->error('未选中数据');
        }
        
        $idss=implode($data['ids'], ',');
         
        $data_action=[
            'aid'=>session('ADMIN_ID'),
            'type'=>'order',
            'time'=>time(),
            'ip'=>get_client_ip(),
            'action'=>'将订单'.$idss.'批量删除',
        ];
        
        $row=$m->where(['id'=>['in',$idss]])->delete();
        if($row>0){
            Db::name('action')->insert($data_action);
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
        
    }
}

?>