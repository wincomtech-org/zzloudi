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
        //$("#solutionList>li>span").removeClass("solution-current-btn")
        //$(this).addClass("solution-current-btn")
		//		[0,1,2,3,4,5]
		//   0	[1,2,3,4,5,0]	1
		//   1  [0,1,2,3,4,5]	0
		//	 2	[5,0,1,2,3,4]	5
		//	 3	[4,5,0,1,2,3]	4
		//	 4	[3,4,5,0,1,2]	3
		//	 5	[2,3,4,5,0,1]	2
		

    })
	var x;
	var arrgy=cArr.slice(0,gus);
	arrgy=arrgy.slice(1,arrgy.length).concat(arrgy.slice(0,1));
	var arr1=arrgy;
	console.log(arr1);
    $("#solutionList>li").click(function () {
		clearInterval(timer);
		//console.log(arrgy);
		index=$(this).index();
        arr1=arrgy.slice(arrgy.length-index).concat(arrgy.slice(0,arrgy.length-index))
		console.log(arr1);
		for(var i=0;i<arr1.length;i++){
			x=arr1[i];
			
			$($("#list>ul>li")[i]).attr("class",`${x}`);
			
		}
		timer=setInterval(nextimg,3000);
    })

    $(".next").click(
            function () {
                clearInterval(timer);
                $(".prev").css("background", "#f2f2f2")
                $(this).css("background", "#63b504")
                //$(this).css("background", "#3296fa")
                nextimg();
                timer = setInterval(nextimg, 3000);
            }
    )
    $(".prev").click(
            function () {
                clearInterval(timer);
                $(".next").css("background", "#f2f2f2")
                $(this).css("background", "#63b504")
                //$(this).css("background", "#3296fa")
                previmg();
                timer = setInterval(nextimg, 3000);
            }
    )
//    function jumpToItem(targetItemIndex) {
//        clearInterval(timer);
//
//        //var targetItem = cArr.splice(targetItemIndex)
//        //console.log(targetItem)
//        //cArr = targetItem.concat(cArr)
//        //console.log(cArr)
//        var num = cArr.length - targetItemIndex;
//		console.log(num);
//        var targentItem = cArr.splice(0, num-1);
//		console.log(targentItem)
//        cArr = cArr.concat(targentItem)
//		console.log(cArr)
//		
//        
//		$(".list>ul>li").each(function (i, e) {
//            $(e).removeClass().addClass(cArr[i]);
//        })
//        timer = setInterval(nextimg, 3000);

  //  }

    function previmg() {
        arr1.push(arr1[0]);
        arr1.shift();
        var currentIndex = arr1.indexOf("p1");
        $("#solutionList>li>span").removeClass("green-current-btn")
        $("#solutionList>li").eq(currentIndex).children("span").addClass("green-current-btn")
        //$("#solutionList>li>span").removeClass("solution-current-btn")
        //$("#solutionList>li").eq(currentIndex).children("span").addClass("solution-current-btn")
        $(".list>ul>li").each(function (i, e) {
            $(e).removeClass().addClass(arr1[i]);
        })
    }

    function nextimg() {
		//改数组长度最后一个的数据
        arr1.unshift(arr1[gus-1]);
        arr1.pop();
        var currentIndex = arr1.indexOf("p1");
        $("#solutionList>li>span").removeClass("green-current-btn")
        $("#solutionList>li").eq(currentIndex).children("span").addClass("green-current-btn")

        //$("#solutionList>li>span").removeClass("solution-current-btn")
        //$("#solutionList>li").eq(currentIndex).children("span").addClass("solution-current-btn")
        $(".list>ul>li").each(function (i, e) {
            $(e).removeClass().addClass(arr1[i]);
        })
    }

    //点击class为p2的元素触发上一张照片的函数
    //$(document).on("click", ".p12", function () {
    //    previmg();
    //    return false;//返回一个false值，让a标签不跳转
    //});

    ////点击class为p4的元素触发下一张照片的函数
    //$(document).on("click", ".p2", function () {
    //    nextimg();
    //    return false;
    //});

    //			鼠标移入box时清除定时器
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
    })

    // //单页版：4，企业版：2，电商版：6，餐饮版：18，行业版：27，专业版：37
    // getCaseImgData("4,2,6,18,27,37", 3);
    // function getCaseImgData(id, pgSize) {
    //     var isCaseImgLoad = false;
    //     $.ajax({
    //         type: "POST",
    //         url: "/dzhome/GetCaseListByTemplate",
    //         data: { tids: id, pageSize: pgSize },
    //         success: function (data) {
    //             //console.log(data)
    //             var itemIndex = 0;
    //             var casesItem;
    //             if (data.isok) {
    //                 for (var i = 0; i < data.list.length; i++) {
    //                     casesItem = data.list[i]
    //                     itemIndex = i * 3;
    //                     for (var j = 0; j < casesItem.clist.length; j++) {
    //                         $("#itemCaseImgBox>img").eq(itemIndex).attr({"class":"lazy","data-original":casesItem.clist[j].coverPath})
    //                         $("#itemCaseQrcode>img").eq(itemIndex).attr({ "class": "lazy", "data-original": casesItem.clist[j].QrcodePath })
    //                         itemIndex++;
    //                     }
    //                     isCaseImgLoad = true;
    //                 }
    //                 if (isCaseImgLoad) {
    //                     var imgs = $(".template-item img")
    //                     for (var i = 0; i < imgs.length; i++) {
    //                         var src = imgs[i].data - src;
    //                         var href1 = window.location.href.substring(0, location.href.lastIndexOf('/') + 1);//IE
    //                         var href2 = window.location.href;//非IE
    //                         if (src == "" || src == href1 || src == href2) {
    //                             imgs[i].parentNode.removeChild(imgs[i]);
    //                         };
    //                     }
    //                 }
    //                 $("img.lazy").lazyload();
    //             }
    //         },
    //         error: function () {
    //             console.log("error");
    //         }
    //     })

    // }
})