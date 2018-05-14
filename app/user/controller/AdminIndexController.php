<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use think\Validate;
/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminIndexController extends AdminBaseController
{
    private $m;
    public function _initialize()
    {
        
        parent::_initialize();
        $this->assign('flag','本站用户');
        $this->m=DB::name('user');
        
    } 

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = ['user_type'=>2];
       
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        if (!empty($request['mobile'])) {
            $where['mobile'] = trim($request['mobile']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['user_login|user_nickname|user_email']    = ['like', "%$keyword%"];
        }
        $usersQuery = $this->m;

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $page = $list->appends($request)->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $m=$this->m;
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = $m->where(["id" => $id, "user_type" => 2])->setField('user_status', 0);
            if ($result) {
                $this->success("会员拉黑成功！", "adminIndex/index");
            } else {
                $this->error('会员拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $m=$this->m;
        $id = input('param.id', 0, 'intval');
        if ($id) {
            //$m->where(["id" => $id, "user_type" => 2])->setField('user_status', 1);
            //启用用户后失败登录清除
            $m->where(["id" => $id, "user_type" => 2])->update(['user_status'=>1,'fail_count'=>0,'fail_time'=>0]);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
    /**
     * VIP用户设置
     * @adminMenu(
     *     'name'   => 'VIP用户设置',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => 'VIP用户设置',
     *     'param'  => ''
     * )
     */
    public function vip()
    {
        $m=$this->m;
        $id = input('param.id', 0, 'intval');
        $vip = input('param.vip', 0, 'intval');
        if ($id && $vip) {
           
            $m->where(["id" => $id])->update(["rate" => $vip]);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
    /**
     * 删除用户 
     * @adminMenu(
     *     'name'   => '删除用户 ',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除用户 ',
     *     'param'  => ''
     * )
     */
    public function del()
    {
        $m=$this->m;
        $id = input('param.id', 0, 'intval'); 
        if ($id) { 
            $m->where(["id" => $id,"user_type" => 2])->delete();
            $this->success("删除成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
    /**
     * 添加用户
     * @adminMenu(
     *     'name'   => '添加用户 ',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加用户 ',
     *     'param'  => ''
     * )
     */
    public function add()
    {
      
       return $this->fetch();
    }
    /**
     * 添加用户执行
     * @adminMenu(
     *     'name'   => '添加用户执行 ',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加用户执行 ',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $m=$this->m;
       
        $data1=$this->request->param();
        $rules = [
            'user_nickname' => 'require|min:2|max:20',
            'user_pass' => 'require|min:6|max:20',
            'mobile'=>'require|number|length:11',
            'user_email'=>'require|email',
            
        ];
      
        $validate = new Validate($rules);
        $validate->message([
            'user_pass.require' => '密码不能为空',
            'user_pass.min'     => '密码为6-20位',
            'user_pass.max'     => '密码为6-20位',
            'user_nickname.require' => '用户姓名不能为空',
            'user_nickname.min'     => '用户姓名为2-20位',
            'user_nickname.max'     => '用户姓名为2-20位',
            'mobile.require' => '手机号码不能为空',
            'mobile.length'     => '手机号码格式错误',
            'user_email.require'     => '邮箱不能为空',
            'user_email.email'     => '邮箱格式错误',
            
        ]);
        $time=time();
        $data=[
           
            'user_nickname'=>$data1['name'],
            'user_pass'=>$data1['psw'],
            'mobile'=>$data1['mobile'],
            'user_email'=>$data1['email'],
            'last_login_ip'   => get_client_ip(0, true),
            'create_time'     => $time,
            'last_login_time' => $time,
            'user_status'     => 1,
            "user_type"       => 2,//会员
            'rate'=>2, //VIP
            
        ];
        
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if(preg_match(config('reg_mobile'), $data['mobile'])!=1){
            $this->error('手机号码错误');
        }
        $data['user_pass'] = cmf_password($data['user_pass']);
        
       
        $tmp=$m->where('mobile',$data['mobile'])->find();
        if(!empty($tmp)){
            $this->error('该手机号已被使用');
        }
        $tmp1=$m->where('user_email',$data['user_email'])->find();
        if(!empty($tmp1)){
            $this->error('邮箱已被使用');
        }
        
        $result  = $m->insertGetId($data);
        if ($result !== false) {
            
            $this->success("添加成功！",url('index'));
        } else {
            $this->error("添加失败！");
        }
    }
    /**
     * 编辑用户
     * @adminMenu(
     *     'name'   => '编辑用户 ',
     *     'parent' => 'index',
     *      'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑用户 ',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $m=$this->m;
        $id=$this->request->param('id',0,'intval');
        $info=$m->where('id',$id)->find();
        if(empty($info)){
            $this->error('该用户不存在',url('index'));
        }
        $this->assign('info',$info);
        return $this->fetch();
    }
    /**
     * 编辑用户执行
     * @adminMenu(
     *     'name'   => '编辑用户执行 ',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑用户执行 ',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $m=$this->m;
        
        $data1=$this->request->param();
        $rules = [
            'user_nickname' => 'require|min:2|max:20', 
            'mobile'=>'require|number|length:11',
            'user_email'=>'require|email',
            
        ];
        
        $validate = new Validate($rules);
        $validate->message([
            
            'user_nickname.require' => '用户姓名不能为空',
            'user_nickname.min'     => '用户姓名为2-20位',
            'user_nickname.max'     => '用户姓名为2-20位',
            'mobile.require' => '手机号码不能为空',
            'mobile.length'     => '手机号码格式错误',
            'user_email.require'     => '邮箱不能为空',
            'user_email.email'     => '邮箱格式错误',
            
        ]);
        $time=time();
        $data=[
            'id'=>$data1['id'],
            'user_nickname'=>$data1['name'],
            
            'mobile'=>$data1['mobile'],
            'user_email'=>$data1['email'],
           
        ];
        
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        if(preg_match(config('reg_mobile'), $data['mobile'])!=1){
            $this->error('手机号码错误');
        }
        if(!empty($data1['psw'])){
            if(strlen($data1['psw'])<6 || strlen($data1['psw'])>20){
                $this->error('密码在6-20位之间，数字或英文字母');
            }
            $data['user_pass']=$data1['psw'];
            $data['user_pass'] = cmf_password($data['user_pass']);
        }
        $info=$m->where('id',$data['id'])->find();
        
        if($info['mobile']!=$data['mobile']){
            $tmp=$m->where('mobile',$data['mobile'])->find();
            if(!empty($tmp)){
                $this->error('该手机号已被使用');
            }
        }
        if($info['user_email']!=$data['user_email']){
            $tmp1=$m->where('user_email',$data['user_email'])->find();
            if(!empty($tmp1)){
                $this->error('邮箱已被使用');
            }
        }
       
        
        $result  = $m->update($data);
        if ($result !== false) {
            
            $this->success("添加成功！",url('index'));
        } else {
            $this->error("添加失败！");
        }
    }
}
