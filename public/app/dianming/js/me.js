/*关联notice.html*/
$(function() {
	//初始化加载我的信息
	$(".myprofile").parent(".weui-tab__panel").on("pull-to-refresh", function() {
		meData();
	}).pullToRefresh("triggerPullToRefresh");
});
//获取我的信息
function meData() {
	$.ajax({
		type: "get",
		url: "http://www.chddata.com/index.php/yiban/sign/me?appid=" + APP_ID + "&verify_request=" + vcode,
		async: true,
		global: false,
		success: function(d) {
			$("#college").text(d.college);
			//				$("#avatar").attr("src", d.head_img);
			$("#name").text(d.name);
			$("#stu_id").text(d.stu_id);
			$("#yb_money").text(d.yb_money);
			$("#major").text(d.major);
			$("#total_sign_count").text(d.total_sign_count);
			$("#total_sign_rate").text(d.total_sign_rate);
			$("#total_sign_rank").text(d.total_sign_rank);
			$("#nosign-history").empty();
			$(".myprofile").parent(".weui-tab__panel").pullToRefreshDone();
			$.each(d.not_sign_list, function(i, n) {
				var box = $("<a href='javascript:;'></a>").addClass("weui-media-box weui-media-box_appmsg");
				var box_bd = $("<div></div>").addClass("weui-media-box__bd");
				var box_title = $("<h4></h4>").addClass("weui-media-box__title").text(n.task_title);
				var box_desc = $("<p></p>").addClass("weui-media-box__desc").text(n.task_time);
				$("#nosign-history").append(box.append(box_bd.append(box_title, box_desc)));
			});
		},
		error: function(e) {
			$.toptip(JSON.stringify(e), "error");
		}
	});
}