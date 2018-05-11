<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use app\admin\model\LinkModel;
use think\Db;

class LinkController extends AdminBaseController
{
    protected $targets = ["_blank" => "新标签页打开", "_self" => "本窗口打开"];

    /**
     * 友情链接管理
     * @adminMenu(
     *     'name'   => '友情链接',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 50,
     *     'icon'   => '',
     *     'remark' => '友情链接管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $linkModel = new LinkModel();
        $links     = $linkModel->select();
        $this->assign('links', $links);
        $types=Db::name('type_dsc')->where('table','link')->select();
        $tmp=[];
        foreach($types as $k=>$v){
            $tmp[$v['type']]=$v['title'];
        }
        
        $this->assign('types', $tmp);
        return $this->fetch();
    }

    /**
     * 添加友情链接
     * @adminMenu(
     *     'name'   => '添加友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $this->assign('targets', $this->targets);
        return $this->fetch();
    }

    /**
     * 添加友情链接提交保存
     * @adminMenu(
     *     'name'   => '添加友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '添加友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        $m=Db::name('link');
        $data= $this->request->param();
        $path=getcwd().'/upload/';
        if(is_file($path.$data['image'])){
            $data['type']='pic';
        }else{
            $data['type']='text';
        }
        //处理网址，补加http://
        $data['url']=zz_link($data['url']);
       
        $insertId=$m->insertGetId($data);
        if($insertId>=1){
            if($data['type']=='pic'){
                $size=config('pic_link');
                $pic='link/'.$insertId.'.jpg';
                zz_set_image($data['image'], $pic, $size['width'], $size['height'], 6);
                $result    = $m->where('id',$insertId)->update(['image'=>$pic]);
                if($result===1){
                    unlink($path.$data['image']);
                    $this->success('已成功添加',url('index'));
                }else{
                    $this->error('图片更新失败');
                }
            }
            $this->success('已成功添加',url('index'));
        }else{
            $this->error('添加失败');
        }
        exit;
    }

    /**
     * 编辑友情链接
     * @adminMenu(
     *     'name'   => '编辑友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $linkModel = LinkModel::get($id);
        $this->assign('targets', $this->targets);
        $this->assign('link', $linkModel);
        return $this->fetch();
    }

    /**
     * 编辑友情链接提交保存
     * @adminMenu(
     *     'name'   => '编辑友情链接提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '编辑友情链接提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        $data      = $this->request->param();
        //处理网址，补加http://
        $data['url']=zz_link($data['url']);
        $path=getcwd().'/upload/'; 
       
        if(is_file($path.$data['image'])){
            $size=config('pic_link');
            $pic='link/'.$data['id'].'.jpg';
            //文件为新上传
            if($data['image']!=$pic){  
                zz_set_image($data['image'], $pic, $size['width'], $size['height'], 6);
                unlink($path.$data['image']);
                $data['image']=$pic;
            }
            $data['type']='pic'; 
        }else{
            $data['type']='text';
        }
        $linkModel = new LinkModel();
        $result    = $linkModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($linkModel->getError());
        }

        $this->success("保存成功！", url("link/index"));
    }

    /**
     * 删除友情链接
     * @adminMenu(
     *     'name'   => '删除友情链接',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除友情链接',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');
        LinkModel::destroy($id);
        $path=getcwd().'/upload/';
        $data['pic']='link/'.$id.'.jpg';
        if(is_file($path.$data['pic'])){
            unlink($path.$data['pic']);
        } 
        $this->success("删除成功！", url("link/index"));
    }

    /**
     * 友情链接排序
     * @adminMenu(
     *     'name'   => '友情链接排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $linkModel = new  LinkModel();
        parent::listOrders($linkModel);
        $this->success("排序更新成功！");
    }

    /**
     * 友情链接显示隐藏
     * @adminMenu(
     *     'name'   => '友情链接显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '友情链接显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        $data      = $this->request->param();
        $linkModel = new LinkModel();

        if (isset($data['ids']) && !empty($data["display"])) {
            $ids = $this->request->param('ids/a');
            $linkModel->where(['id' => ['in', $ids]])->update(['status' => 1]);
            $this->success("更新成功！");
        }

        if (isset($data['ids']) && !empty($data["hide"])) {
            $ids = $this->request->param('ids/a');
            $linkModel->where(['id' => ['in', $ids]])->update(['status' => 0]);
            $this->success("更新成功！");
        }


    }

}