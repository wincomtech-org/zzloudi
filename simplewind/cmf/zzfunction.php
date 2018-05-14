<?php
use think\Config;
use think\Db;
use think\Url;
use dir\Dir;
use think\Route;
use think\Loader;
use think\Request;
use cmf\lib\Storage;

// 应用公共文件

//设置插件入口路由
Route::any('plugin/[:_plugin]/[:_controller]/[:_action]', "\\cmf\\controller\\PluginController@index");
Route::get('captcha/new', "\\cmf\\controller\\CaptchaController@index");

/* 过滤HTML得到纯文本 */
function zz_get_content($list,$len=100,$field='content'){
    //过滤富文本
    $tmp=[];
    foreach ($list as $k=>$v){
        
        $content_01 = $v[$field];//从数据库获取富文本content
        $content_02 = htmlspecialchars_decode($content_01); //把一些预定义的 HTML 实体转换为字符
        $content_03 = str_replace("&nbsp;","",$content_02);//将空格替换成空
        $contents = strip_tags($content_03);//函数剥去字符串中的 HTML、XML 以及 PHP 的标签,获取纯文本内容
        $flag=(mb_strlen($contents,"utf-8")>$len)?'...':'';
        $con = mb_substr($contents, 0, $len,"utf-8");//返回字符串中的前100字符串长度的字符 
        $v[$field]=$con.$flag;
        $tmp[]=$v;
    }
    return $tmp;
}

/* 浏览量计算 */
function zz_browse($name,$id){
     
   $session=session('browse.'.$name);
   if(empty($session) || !in_array($id, $session)){
       $session[]=$id;
       //使用sql函数
       //Db::name($name)->where('id',$id)->update(['browse'=>['exp','browse+1']]);
       //字段自增,默认为1 
       Db::name($name)->where('id',$id)->setInc('browse');
       session('browse.'.$name,$session);
   } 
}

/*制作缩略图 
 * zz_set_image(原图名,新图名,新宽度,新高度,缩放类型)
 *  */
function zz_set_image($pic,$pic_new,$width,$height,$thump=6){
    /* 缩略图相关常量定义 */
//     const THUMB_SCALING   = 1; //常量，标识缩略图等比例缩放类型
//     const THUMB_FILLED    = 2; //常量，标识缩略图缩放后填充类型
//     const THUMB_CENTER    = 3; //常量，标识缩略图居中裁剪类型
//     const THUMB_NORTHWEST = 4; //常量，标识缩略图左上角裁剪类型
//     const THUMB_SOUTHEAST = 5; //常量，标识缩略图右下角裁剪类型
//     const THUMB_FIXED     = 6; //常量，标识缩略图固定尺寸缩放类型
    //         $water=INDEXIMG.'water.png';//水印图片
    //         $image->thumb(800, 800,1)->water($water,1,50)->save($imgSrc);//生成缩略图、删除原图以及添加水印
    // 1; //常量，标识缩略图等比例缩放类型
    //         6; //常量，标识缩略图固定尺寸缩放类型
    $path='upload/';
    if($pic_new==$pic){
        return $pic_new; 
    }
    //判断文件来源，已上传和未上传
    $imgSrc=(is_file($pic))?$pic:($path.$pic);
    $imgSrc1=$path.$pic_new;
    if(is_file($imgSrc)){
        $image = \think\Image::open($imgSrc); 
        $size=$image->size(); 
        //新旧文件名不同、文件尺寸不对都要重制
        if($imgSrc!=$imgSrc1 || $size!=[$width,$height] ){ 
            $image->thumb($width, $height,$thump)->save($imgSrc1);
             
        } 
    }
    return $pic_new; 
}
/* 一张原图指定生成一张图片*/
function zz_picid($pic,$pic_old,$type,$id,$dir='',$name=''){
    $path='upload/';
    //logo处理
    if(!is_file($path.$pic)){
        return '';
    }
    //文件未改变
    if($pic==$pic_old){
        return $pic;
    }
    $dir=(empty($dir))?$type:$dir; 
    $size=config('pic_'.$type);
    $pic_new=$dir.'/'.$name.$id.'-'.time().'.jpg';
    
    $image = \think\Image::open($path.$pic);
    $image->thumb($size['width'],  $size['height'],6)->save($path.$pic_new);
    unlink($path.$pic);
    Db::name('asset')->where('file_path', $pic)->delete();
    
    
    return $pic_new;
    
}
/*一张原图指定生成多张图片,不是图片0,无需修改返回1，完成后返回新数组*/
function zz_picids($pic,$types,$id,$dir='admin/'){
    $path='upload/';
    //不是图片
    if(!is_file($path.$pic)){
        return 0;
    }
    //或是已处理过的
    if(strpos($pic,$dir)===0){
        return 1;
    }
    $tmp=[];
    foreach($types as $v){
        $size=config('pic_'.$v);
        $tmp[$v]=$dir.'/'.$id.'-'.$v.'-'.time().'.jpg';
        
        $image = \think\Image::open($path.$pic);
        $image->thumb($size['width'],  $size['height'],6)->save($path.$tmp[$v]);
    }
     
    unlink($path.$pic);
    Db::name('asset')->where('file_path', $pic)->delete();
    
    return $tmp;
    
}
/* 为网址补加http:// */
function zz_link($link){
    //处理网址，补加http://
    $exp='/^(http|ftp|https):\/\//';
    if(preg_match($exp, $link)==0){
        $link='http://'.$link;
    }
    return $link;
}
  
  