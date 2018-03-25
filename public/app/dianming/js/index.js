/*关联index.html*/
var APP_ID = "d53201c6a67e6c8a"; //应用id
$(function() {
	//验证
	if(verify() == undefined || "" || false) {
		location.assign("https://openapi.yiban.cn/oauth/authorize?client_id=" + APP_ID + "&redirect_uri=http://f.yiban.cn/iapp130591&state=ff241");
		return;
	} else {
		//初始化
		initinfo();
	}
	//tabbar点击事件
	$(".weui-tabbar__item").off("click").on("click", function(e) {
		if($(".weui-popup__container").css("display") == "block") {
			$.closePopup();
		}
		$("#tab" + ($(this).index() + 1)).children(".weui-tab__panel:empty").load($(this).data("url"));
	});
});
/*
 * 初始化信息
 */
function initinfo() {
	//返回值
	//status:"true",
	//personal:"",//用户信息，包含学院，头像，学号，易班ID，签到状态（0未签，1已签，-1不存在任务）
	//public:"",//点名ID，开始时间，结束时间，状态，通知列表（json数组）
	$.ajax({
		type: "get",
		url: "http://www.chddata.com/index.php/yiban/sign/init?appid=" + APP_ID + "&verify_request=" + vcode,
		async: true,
		cache: false,
		success: function(data) {
			if(data.status == "error") {
				$.toptip(data.info, "error");
			} else if(data.status == "redirect") {
				$.toptip(data.info, "warning");
				location.assign("https://openapi.yiban.cn/oauth/authorize?client_id=" + APP_ID + "&redirect_uri=http://f.yiban.cn/iapp130591&state=ff241");
			} else {
				publicInfo = data.public;
				personalInfo = data.personal;
				//必须触发click事件，否则的话weui无法给对应的div赋予active类
				$(".weui-tabbar__item").eq(0).trigger("click");
			}
		},
		error: function(e) {
			$.toptip(JSON.stringify(e), "error");
		}
	});
}
/*
 * 验证用户授权
 */
function verify() {
	url = new LG.URL();
	vcode = url.get("verify_request");
	return vcode;
}