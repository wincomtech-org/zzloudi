$(".nav-push").click(function () {
    $(".mobile-nav").show();
})
$("#close").click(function(){
    $(".mobile-nav").hide();
});
//信息；guyuan
var diqu,chengshi,diqu,xingming,shouji,qqhao,youxiang;
$("#pro-first").click(function(){
   //判断地区是否选择
	var xlzt=false;
	shengshi=$(".gy_ss_1").val();
	chengshi=$(".gy_ss_2").val();
	quji=$(".gy_ss_3").val();
	diqu=[shengshi,chengshi,quji];
   	if(shengshi!=0 && chengshi!=0 && quji!=0){xlzt=true}else{
		alert("地区有误！");
		return;
	}
	//判断申请人姓名是否填写是否符合姓名要求
	var namr_s=false;
	var re1 =/^[\u4E00-\u9FA5\uf900-\ufa2d·s]{2,20}$/;
	xingming=$(".gy_ss_y").val();
	if(re1.test(xingming)){namr_s=true}else{
		namr_s=false;
		alert("姓名填写错误！");
		return;							   
	}
	
	//判断手机号是否正确；
	shouji=$(".gy_ss_gg").val();
	if(!is_mobile(shouji)){alert("手机号填写错误");return false}
	//判断qq号码是否正确；
	var gy_t=false;
	var re3=/^[1-9]\d{4,10}$/;
	qqhao=$(".gy_t_i").val();
	if(re3.test(qqhao)){
		gy_t=true;
	}else{
		alert("QQ号码填写错误！");
		return;
	}
	
	//判断邮箱是否正确
	youxiang=$(".gy_suyty").val();
	if(!is_email(youxiang)){alert("邮箱填写错误");return false}
	//判断是否选择我已阅读，
	var ghyuj=$(".gy_sio").is(":checked");
	if(!ghyuj){alert("请阅读本站服务协议！");return}
	//验证所有信息是否填写
	if(xlzt===true&&namr_s===true&& is_mobile(shouji) ===true&&gy_t===true&& is_email(youxiang) ===true&&ghyuj===true){
		//执行ajax保存订单
		order_do1(); 
		if(ajax_result==1){ 
			ajax_result=0;
			$(".pro-box1").hide();
			$(this).hide();
	    	$(".pro-box2").show();
	    	$(".pro-tit-list li").removeClass("pro-tit-cur");
			$(".pro-tit-list li:nth-child(2)").addClass("pro-tit-cur");
		}
	   	
	   }
	
});
//选项点功能 guyuan
//数据；
var leixing=0;
$(".bugyalu>input").click(function(){
	var a=$(this).index()/2;
	leixing=a;
	xiangxi=$(this).next().html();
//	console.log(xiangxi);
	$($(".show_ss_1")[a]).css("display","block").siblings().css("display","none");
	//取消选项框
	$(".show_ss_1>div>input").attr("checked",false)
});
var jiage,xiangxi;
//记录所选服务
var sid=0;
$(".show_ss_1>div>input").click(function(){
	//记录所选服务
	sid=$(this).val();
	 
	var a=$(this).next().next().html();
	xiangxi=$(this).next().html()
	a=a.slice(1,-2);
	jiage=a;
//	console.log(a,xiangxi);
});
$("#pro-second1").click(function(){
   $("#pro-first").show();
	$(".pro-box1").show();
    $(".pro-box2").hide();
    $(".pro-tit-list li").removeClass("pro-tit-cur");
    $(".pro-tit-list li:nth-child(1)").addClass("pro-tit-cur");
});
$("#pro-second2").click(function(){
//	console.log(xiangxi);
    var a=$($($(".show_ss_1")[leixing]).children(".gy").children("input")).is(":checked");
	var b=$($($(".show_ss_1")[leixing]).children(".gu").children("input")).is(":checked");
	if(a==true || b==true){
		//执行ajax保存订单
		order_do2(); 
		if(ajax_result==1){ 
			ajax_result=0;
			$(".pro-box3").show();
	    	$(".pro-box2").hide();
	    	$(".pro-tit-list li").removeClass("pro-tit-cur");
	    	$(".pro-tit-list li:nth-child(3)").addClass("pro-tit-cur");
		}
		 
	}else{
		alert("请至少选择一个")
	}
});
//==========================================================>>>>>>>>>>>>>
var htm_jhg='<span class="gh_s"></span><span class="gh_x"></span>';
var width_ul=$(".show_shjy").width();
var width_li=$(".show_shjy>a>li").width();
var li_margin=parseFloat($(".show_shjy>a>li").css("margin-right"));
var length_s=$(".show_shjy>a>li").length;
var bar_div=$(".banner_div").width();
var marginL=width_li+li_margin;
var animation=500;//过度时长；
var scend=3000;//轮播间隔；
var timer_y=setInterval(auto_p_tt,scend);
if(bar_div>width_ul){
    $(".show_shjy").css({
        display:"flex",
        justifyContent: "center",
    });
    $(".show_shjy>a").css("margin","0.8%")
	clearInterval(timer_y);
}else{
    $(".banner_div").append(htm_jhg);
    $(".gh_s").click(gggt_s);
    $(".gh_x").click(gggt_x);
    $(".banner_zixun").mouseenter(function(){
        clearInterval(timer_y);
    }).mouseleave(function(){
        timer_y=setInterval(auto_p_tt,scend);
    });
}
function auto_p_tt(){
	$(".show_shjy").stop();
	$(".show_shjy").animate({
		marginLeft:"-"+marginL+"px",
	},animation);
	setTimeout(function(){
		$(".show_shjy").append($(".show_shjy>a:first-child")).animate({
			marginLeft:"0"
		},0).stop();
	},animation);
}
//左点击函数
function gggt_s(){
    $(".gh_s").off();
    $(".show_shjy").prepend($(".show_shjy>a:last-child")).animate({
        marginLeft:"-"+marginL+"px",
    },0).stop();
    $(".show_shjy").animate({
		marginLeft:"-0px",
	},animation);
    setTimeout(function(){
        $(".gh_s").on("click",gggt_s);
    },animation);
}
//右点击函数；
function gggt_x(){
    $(".show_shjy").stop();
    $(".gh_x").off();
	auto_p_tt();
    setTimeout(function(){
      $(".gh_x").on("click",gggt_x);
    },animation);
}
//================================================
//省市级联




