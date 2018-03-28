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

use cmf\controller\UserBaseController;
use think\Db;

class InfoController extends UserBaseController
{
    
    /**
     * 我的订单
     */
    public function index()
    {
       
        $this->assign('html_flag','product');
        $this->assign('html_title','个人中心');
        $uid   =session('user.id');
        $where=['uid'=>$uid,'is_delete'=>0];
        $orders = Db::name("order")->where($where)->paginate(2);
       
        // 获取分页显示
        $page = $orders->render();
        //banner
        $banners=DB::name('banner')->where('type','personal')->order('sort asc,id asc')->select();
       
        $this->assign('orders',$orders);
        $this->assign('page',$page);
        $this->assign('banners',$banners);
        $this->assign('order_status',config('order_status'));
        return $this->fetch();

    }
    /**
     * 删除订单
     */
    public function delete()
    {
        $uid   =session('user.id');
        $ids=$this->request->param('ids');
        $where=[
            'uid'=>['eq',$uid],
            'id'=>['in',$ids], 
        ];
        $row = Db::name("order")->where($where)->update(['is_delete'=>1,'time'=>time()]);
        if($row>0){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 前台ajax 判断用户登录状态接口
     */
    function isLogin()
    {
        if (cmf_is_user_login()) {
            $this->success("用户已登录",null,['user'=>cmf_get_current_user()]);
        } else {
            $this->error("此用户未登录!");
        }
    }

    /**
     * 退出登录
    */
    public function logout()
    {
        session("user", null);//只有前台用户退出
        return redirect($this->request->root() . "/");
    }

}
