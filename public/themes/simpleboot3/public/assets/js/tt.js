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
$(function(){
    //设置子的高度等于显示div的高度；
    var height=$("#super_citys>div").height();
    //设置动态生成的li高度等于显示框高度函数；下方动态生成li时调用；
    function auto_height(){$("#super_citys>div>ul>li").height(height).css("line-height",height+"px");}
    $("#super_citys>div>div").height(height).css("line-height",height+"px");
    $("#super_citys>div>ul").css("top",height+"px");
    var html1;
    for(var i in city1){
        html1='<li><input type="hidden" value="'+i+'"><span>'+city1[i]+'</span></li>';
        $("#super_citys>.provinces>ul").append(html1);
    }
    auto_height();
    //省
    var sheng,diqu;
    $("#super_citys>.provinces>ul>li").click(function(){
        $("#super_citys>.provinces>.input1").val($(this).children("input").val());
        $("#super_citys>.provinces>.input2").val($(this).children("span").html());
        $("#super_citys>.provinces>div").html($(this).children("span").html());
        var html2='<li><input type="hidden" value="0"><span>请选择城市</span></li>';
        var index_s=$(this).children("input").val();
        if(sheng!==index_s){
            $("#super_citys>.city>div").html("请选择城市");
            $("#super_citys>.city>.input1").val(0);
            $("#super_citys>.city>.input2").val(0);
            $("#super_citys>.area>div").html("请选择地区");
            $("#super_citys>.area>.input1").val(0);
            $("#super_citys>.area>.input2").val(0);
            $("#super_citys>.area>ul").html('<li><input type="hidden" value="0"><span>请选择地区</span></li>');
        }
        sheng=index_s;
        if(index_s==0){
            $("#super_citys>.provinces>.input2").val(0);
            $("#super_citys>.city>ul").html('<li><input type="hidden" value="0"><span>请选择城市</span></li>');
            $("#super_citys>.city>.input1").val(0);
            $("#super_citys>.city>.input2").val(0);
            $("#super_citys>.area>ul").html('<li><input type="hidden" value="0"><span>请选择地区</span></li>');
            $("#super_citys>.area>.input1").val(0);
            $("#super_citys>.area>.input2").val(0);
            
        }else{
            for(var i in city2[index_s]){
                html2+='<li><input type="hidden" value="'+i+'"><span>'+(city2[index_s])[i]+'</span></li>';
            }
            $("#super_citys>.city>ul").html(html2);
        }
        auto_height();
    });
    //市
    $("#super_citys>.city>ul").on("click","li",function(){
        $("#super_citys>.city>.input1").val($(this).children("input").val());
        $("#super_citys>.city>.input2").val($(this).children("span").html());
        $("#super_citys>.city>div").html($(this).children("span").html());
        var html3='<li><input type="hidden" value="0"><span>请选择地区</span></li>';
        var index_r=$(this).children("input").val();
        if(diqu!==index_r){
            $("#super_citys>.area>div").html("请选择地区");
            $("#super_citys>.area>.input1").val(0);
            $("#super_citys>.area>.input2").val(0);
        }
        diqu=index_r;
        if(index_r==0){
            $("#super_citys>.area>ul").html('<li><input type="hidden" value="0"><span>请选择地区</span></li>');
            $("#super_citys>.city>.input2").val(0);
            $("#super_citys>.area>.input2").val(0);
        }else{
            for(var i in city3[index_r]){
                html3+='<li><input type="hidden" value="'+i+'"><span>'+(city3[index_r])[i]+'</span></li>'; 
            }
            $("#super_citys>.area>ul").html(html3);
        }
        auto_height()
    });
    //区
    $("#super_citys>.area>ul").on("click","li",function(){
        var x_index=$(this).children("input").val();
        if(x_index==0){
            $("#super_citys>.area>.input2").val(0);
        }else{
            $("#super_citys>.area>.input2").val($(this).children("span").html());
        }
        $("#super_citys>.area>.input1").val($(this).children("input").val());
        $("#super_citys>.area>div").html($(this).children("span").html());
    });
    //点击显示框效果
    var height_shjiu,height_zong,height_li20;
    function guyuan(element){
         auto_height();
        //设置下拉框最多显示10个数据，
        height_zong=(element.children("ul").children("li").length)*height;
        height_li20=10*height;
        if(height_zong>height_li20){
            height_shjiu=height_li20;
        }else{
            height_shjiu=height_zong;
        }
        element.children("ul").css({
            "display":"block",
            "height":height_shjiu+"px"
        });
        element.siblings().children("ul").css({
            "display":"none",
            "height":"0px"
        });
    }
    //点击其他元素事件处理
    $(document).click(function(){
        $("#super_citys>div>ul").css("display","none");
        $("#super_citys>div").removeClass("shadow");
        sheng_is=true;
        shi_is=true;
        qu_is=true;
    });
    //子点击事件
    $("#super_citys>div>ul").on("click","li",function(e){
        e.stopPropagation();
        $(this).parent().parent().addClass("shadow").siblings().removeClass("shadow");
        $("#super_citys>div>ul").css("display","none");
        sheng_is=true;
        shi_is=true;
        qu_is=true;
    });
    //sheng
    var sheng_is=true;
    $("#super_citys>.provinces").click(function(e){
        if(sheng_is){
            e.stopPropagation();
        }
        $(this).addClass("shadow").siblings().removeClass("shadow");
        sheng_is=!sheng_is;
        guyuan($(this));
        shi_is=true;
        qu_is=true;
    });
    //shi
    var shi_is=true;
    $("#super_citys>.city").click(function(e){
        if(shi_is){
            e.stopPropagation();
        }
        $(this).addClass("shadow").siblings().removeClass("shadow");
        shi_is=!shi_is;
        guyuan($(this));
        sheng_is=true;
        qu_is=true;
    });
    //qu
    var qu_is=true;
    $("#super_citys>.area").click(function(e){
        if(qu_is){
            e.stopPropagation();
        }
        $(this).addClass("shadow").siblings().removeClass("shadow");
        qu_is=!qu_is;
        guyuan($(this));
        sheng_is=true;
        shi_is=true;
    });
    //提交按钮事件
    $(".btn").click(function(){
        var sheng=$(".provinces>.input1").val();
        var shi=$(".city>.input1").val();
        var qu=$(".area>.input1").val();
        var sheng_n=$(".provinces>.input2").val();
        var shi_n=$(".city>.input2").val();
        var qu_n=$(".area>.input2").val();
        if(sheng==0){alert("省份不能为空！"); return false}
        if(shi==0){alert("城市不能为空！"); return false}
        if(qu==0){alert("地区不能为空！"); return false}
        alert("您输入的地区代码为："+sheng+"-"+shi+"-"+qu);
        alert("您输入的地区名为："+sheng_n+"-"+shi_n+"-"+qu_n);
    });
});





