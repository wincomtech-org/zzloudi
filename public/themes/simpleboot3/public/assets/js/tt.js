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
	var gu_s=false;
	var re2=/^[1][3,4,5,7,8][0-9]{9}$/;
	shouji=$(".gy_ss_gg").val();
	if(re2.test(shouji)){gu_s=true}else{
		alert("手机号码错误！");
		return;
	}
	
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
	var br_s=false;
	var re4=/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/; 
	youxiang=$(".gy_suyty").val();
	if(re4.test(youxiang)){br_s=true}else{
		alert("邮箱填写错误！");
		return;
	}
	//判断是否选择我已阅读，
	var ghyuj=$(".gy_sio").is(":checked");
	if(!ghyuj){alert("请阅读本站服务协议！");return}
	//验证所有信息是否填写
	if(xlzt===true&&namr_s===true&&gu_s===true&&gy_t===true&&br_s===true&&ghyuj===true){
	   	$(".pro-box1").hide();
    	$(".pro-box2").show();
    	$(".pro-tit-list li").removeClass("pro-tit-cur");
		$(".pro-tit-list li:nth-child(2)").addClass("pro-tit-cur");
	   }
	
});
//选项点功能 guyuan
//数据；
var leixing=0;
$(".bugyalu>input").click(function(){
	var a=$(this).index()/2;
	leixing=a;
	xiangxi=$(this).next().html();
	console.log(xiangxi);
	$($(".show_ss_1")[a]).css("display","block").siblings().css("display","none");
	//取消选项框
	$(".show_ss_1>div>input").attr("checked",false)
});
var jiage,xiangxi;
$(".show_ss_1>div>input").click(function(){
	var a=$(this).next().next().html();
	xiangxi=$(this).next().html()
	a=a.slice(1,-2);
	jiage=a;
	console.log(a,xiangxi);
});
$("#pro-second1").click(function(){
    $(".pro-box1").show();
    $(".pro-box2").hide();
    $(".pro-tit-list li").removeClass("pro-tit-cur");
    $(".pro-tit-list li:nth-child(1)").addClass("pro-tit-cur");
});
$("#pro-second2").click(function(){
	console.log(xiangxi);
   var a=$($($(".show_ss_1")[leixing]).children(".gy").children("input")).is(":checked");
	var b=$($($(".show_ss_1")[leixing]).children(".gu").children("input")).is(":checked");
	if(a==true || b==true){
		$(".pro-box3").show();
    	$(".pro-box2").hide();
    	$(".pro-tit-list li").removeClass("pro-tit-cur");
    	$(".pro-tit-list li:nth-child(3)").addClass("pro-tit-cur");
	}else{
		alert("请至少选择一个")
	}
	console.log(leixing);
	console.log(a,b);
});
$("#pro-third1").click(function(){
    $(".pro-box2").show();
    $(".pro-box3").hide();
    $(".pro-tit-list li").removeClass("pro-tit-cur");
    $(".pro-tit-list li:nth-child(2)").addClass("pro-tit-cur");
});
var mingcheng;
$("#pro-third2").click(function(){
	var hgy=false;
	var hyg=false;
	var re5=new RegExp("\\s+");
	mingcheng=$(".guyuan_syj1").val();
	var neirong=$(".guyuan_syj2").val();
	var r=re5.test(mingcheng);
	var t=re5.test(neirong);
	console.log(r,t);
	if(r){alert("公众号名称不能含有空格！");return}else{hgy=true}
	if(mingcheng==""){hgy=false;alert("公众号名称必须填写！");return}
	if(t){alert("产品介绍不能含有空格！");return}else{hyg=true}
	if(neirong==""){hyg=false;alert("产品介绍必须填写!");return}
	if(hgy&&hyg){
		 $('#form_order').submit();
	}
  
});
$(".guyuanwudi").click(function(){
	location.href=""	//"./1.html"
})




