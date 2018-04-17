$(function(){
	var second_s=3000;	//几毫秒换张图
	var animate_s=500;	//几毫秒的动画效果
	var htm="<li>"+$(".slides_s>li:first-child").html()+"</li>";
	var width_l=$(".slides_s>li").width();
	var margin_l=width_l;
	$(".slides_s").append(htm);
	$(".slides_s").width($(".slides_s>li").width()*$(".slides_s>li").length);
	$("#slider_s>.slides_s>li").css("display","block");
	$(window).resize(function(){
		clearInterval(timer);
		width_l=$(".slides_s>li").width();
		margin_l=width_l;
		$(".slides_s").width($(".slides_s>li").width()*$(".slides_s>li").length);
		timer=setInterval(autoplay,second_s);
		
	})
	function autoplay(){
		if(margin_l>width_l*($(".slides_s>li").length-1)){
			margin_l=width_l;
			$(".slides_s").css("marginLeft","0");
		}
//		$(".slides_s").stop();
		$(".slides_s").animate({
			marginLeft:"-"+margin_l+"px"
		},animate_s);
		margin_l=margin_l+width_l;
	}
	var timer=setInterval(autoplay,second_s);
	$(".controls_s>li:nth-child(1)").click(function(){
		
		clearInterval(timer);
		$(".slides_s").stop();
		margin_l=margin_l-width_l;
		if(margin_l==0){
			$(".slides_s").css("marginLeft","-"+width_l*($(".slides_s>li").length-1)+"px");
			margin_l=width_l*($(".slides_s>li").length-1);
		}
		$(".slides_s").animate({
			marginLeft:"-"+(margin_l-width_l)+"px"
		},animate_s);
		timer=setInterval(autoplay,second_s);
	});
	$(".controls_s>li:nth-child(2)").click(function(){
		
		clearInterval(timer);
		$(".slides_s").stop();
		autoplay();
		timer=setInterval(autoplay,second_s);
	});
});