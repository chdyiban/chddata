/*关联notice.html*/
$(function() {
	var p = 1; //获取页数
	//通知列表绑定点击事件
	$("#notice-list").on("click", "a", function(e) {
		$(".noticeDetail h1.title").text($(this).find(".weui-media-box__title").text());
		$(".noticeDetail .modal-content").children("pre").html($(this).find(".weui-media-box__desc").text());
		$(".noticeDetail").popup();
	}).parents(".weui-tab__panel").on("pull-to-refresh", function() {
		getNotice(p);
	}).pullToRefresh("triggerPullToRefresh");

	//上一页
	$("#prev").on("touchstart", function() {
		if($(this).is(".weui-btn_disabled")) {
			return;
		}
		p = $("#pageNum").val() || p;
		p--;
		getNotice(p);
	});

	//下一页
	$("#next").on("touchstart", function() {
		if($(this).is(".weui-btn_disabled")) {
			return;
		}
		p = $("#pageNum").val() || p;
		p++;
		getNotice(p);
	});

	//手动跳转
	$(".pageNumWrap").on("submit", function(e) {
		e.preventDefault();
		p = $("#pageNum").val() || p;
		getNotice(p);
	})

})

/*
 * 生成通知列表，ajax
 * 传入列表页数
 */
function getNotice(p) {
	$.ajax({
		type: "get", //正式接入接口可能要改为post，毕竟要传输页码的数值
		url: "http://www.chddata.com/index.php/yiban/sign/notice?appid=" + APP_ID + "&verify_request=" + vcode,
		//			url:"notice.json",
		async: true,
		cache: false,
		data: {
			p: p
		},
		complete: function(xhr, st) {
			$("#notice-list").parents(".weui-tab__panel").pullToRefreshDone();
		},
		success: function(data) {
			if(data.status) {
				$("#notice-list").empty();
				$.each(data.info.notice, function(i, n) {
					var box = $("<a href='javascript:;'></a>").addClass("weui-media-box weui-media-box_appmsg").data("id", n.id);
					var box_bd = $("<div></div>").addClass("weui-media-box__bd");
					var box_title = $("<h4></h4>").addClass("weui-media-box__title").text(n.title);
					var box_desc = $("<p></p>").addClass("weui-media-box__desc").text(n.notice);
					var box_info = $("<p></p>").addClass("weui-media-box__info").text(n.timestamp);
					$("#notice-list").append(box.append(box_bd.append(box_title, box_desc, box_info)));
				});
				$("#pageNum").val(data.info.now_page).attr("max", data.info.total_page); //设置页码，限制最大输入数
				$("#totalPage").text(data.info.total_page) //设置总页数
				//向前按钮判断
				if(data.info.now_page <= 1) {
					$("#prev").addClass("weui-btn_disabled");
				} else {
					$("#prev").removeClass("weui-btn_disabled");
				}
				//向后按钮判断
				if(data.info.now_page >= data.info.total_page) {
					$("#next").addClass("weui-btn_disabled");
				} else {
					$("#next").removeClass("weui-btn_disabled");
				}
				$("#notice-list").parents(".weui-tab__panel").animate({
					scrollTop: 0
				}, 300);
			} else {
				$.toast("暂无任何通知", "forbidden");
			}
		},
		error: function(e) {
			$.toptip(JSON.stringify(e), "error");
		}
	});
}