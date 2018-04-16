/*关联data.html*/
$(function() {
	//初始化信息
	$("#cls-sign-info").parent(".weui-tab__panel").on("pull-to-refresh", function() {
		getData();
	}).pullToRefresh("triggerPullToRefresh");
});

function getData() {
	var stu_id = personalInfo.stu_id; //个人信息
	//未签到名单
	$.ajax({
		type: "get",
		url: "http://www.chddata.com/index.php/yiban/sign/getNotSignList?appid=" + APP_ID + "&stu_id=" + stu_id,
		async: true,
		global: false,
		complete: function(xhr, st) {
			$("#cls-sign-info").parent(".weui-tab__panel").pullToRefreshDone();
		},
		success: function(d) {
			console.table(d);
			if(d.status == false) {
				$.toast(d.msg, "forbidden");
			} else {
				$("#classId").text(d.class_id);
				$("#sign_count").text(d.sign_count);
				$("#sign_rate").text(d.sign_rate);
				$("#not_sign_count").text(d.not_sign_count);
				$("#class_stu_num").text(d.class_stu_num);
				$("#nosigh_list").empty();
				$.each(d.not_sign_list, function(i, n) {
					var cell = $("<div href='javascript:;'></div>").addClass("weui-cell");
					var cell_hd = $("<div></div>").addClass("weui-cell__hd").append('<img src="img/default_avatar_128_128.jpg" alt="" style="width:20px;border-radius:10px;">');
					var cell_bd = $("<div></div>").addClass("weui-cell__bd").append("<p>" + n.name + "</p>");
					var cell_ft = $("<div></div>").addClass("weui-cell__ft").append("<i class='iconfont icon-bianhao1'></i> " + n.number);
					$("#nosigh_list").append(cell.append(cell_hd, cell_bd, cell_ft));
				});
			}
		},
		error: function(e) {
			$.toptip(JSON.stringify(e), "error");
		}
	});
}