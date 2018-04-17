$(function () {
//数组动态改变
	var gus=$("#solutionList>li").length;
    var cArr = ["p0","p1", "p2", "p3","p4","p5","p6","p7","p8","p9","p10","p11","p12"];
	var arr1 = ["p0","p1", "p2", "p3","p4","p5","p6","p7","p8","p9","p10","p11","p12"];
	cArr=cArr.slice(0,gus);
    var index = 0;
    $("#solutionList>li>span").click(function () {
        $("#solutionList>li>span").removeClass("green-current-btn")
        $(this).addClass("green-current-btn")
    })
	var x;
	var arrgy=cArr.slice(0,gus);
	arrgy=arrgy.slice(1,arrgy.length).concat(arrgy.slice(0,1));
	var arr1=arrgy;
    $("#solutionList>li").click(function () {
		clearInterval(timer);
		index=$(this).index();
        arr1=arrgy.slice(arrgy.length-index).concat(arrgy.slice(0,arrgy.length-index));
		for(var i=0;i<arr1.length;i++){
			x=arr1[i];
			$($("#list>ul>li")[i]).attr("class",x);
		}
		timer=setInterval(nextimg,3000);
    })
    $(".next").click(
            function () {
                clearInterval(timer);
                $(".prev").css("background", "#f2f2f2");
                $(this).css("background", "#63b504");
                nextimg();
                timer = setInterval(nextimg, 3000);
            }
    )
    $(".prev").click(
            function () {
                clearInterval(timer);
                $(".next").css("background", "#f2f2f2");
                $(this).css("background", "#63b504");
                previmg();
                timer = setInterval(nextimg, 3000);
            }
    )
     function previmg() {
        arr1.push(arr1[0]);
        arr1.shift();
        var currentIndex = arr1.indexOf("p1");
        $("#solutionList>li>span").removeClass("green-current-btn");
        $("#solutionList>li").eq(currentIndex).children("span").addClass("green-current-btn");
        $(".list>ul>li").each(function (i, e) {
            $(e).removeClass().addClass(arr1[i]);
        })
    }

    function nextimg() {
		//改数组长度最后一个的数据
        arr1.unshift(arr1[gus-1]);
        arr1.pop();
        var currentIndex = arr1.indexOf("p1");
        $("#solutionList>li>span").removeClass("green-current-btn");
        $("#solutionList>li").eq(currentIndex).children("span").addClass("green-current-btn");
        $(".list>ul>li").each(function (i, e) {
            $(e).removeClass().addClass(arr1[i]);
        });
    };
    $(".list-box").mouseover(function () {
        clearInterval(timer);
    })

    //			鼠标移出box时开始定时器
    $(".list-box").mouseleave(function () {
        timer = setInterval(nextimg, 2000);
    })

    //			进入页面自动开始定时器
    timer = setInterval(nextimg, 3000);

    $(".case-qrcode-box").hover(function () {
        $(this).children(".item-case-qrcode").css("opacity", "1")
    }, function () {
        $(this).children(".item-case-qrcode").css("opacity", "0")
    });
})