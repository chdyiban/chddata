/*关联sign.html*/
window.init = function() {
	map = null; //地图对象
	polygon = null; //地图多边形对象
	marker = null; //地图标记点对象
	map = new AMap.Map('map', {
		resizeEnable: true,
		zoom: 13
	});
	map.plugin(["AMap.ToolBar", "AMap.Scale", "AMap.Geolocation"], function() {
		map.addControl(new AMap.ToolBar());
		map.addControl(new AMap.Scale());
		geolocation = new AMap.Geolocation({
			enableHighAccuracy: true, //是否使用高精度定位，默认:true
			timeout: 10000, //超过10秒后停止定位，默认：无穷大
			maximumAge: 0, //定位结果缓存0毫秒，默认：0
			convert: true, //自动偏移坐标，偏移后的坐标为高德坐标，默认：true
			showButton: true, //显示定位按钮，默认：true
			buttonPosition: 'LB', //定位按钮停靠位置，默认：'LB'，左下角
			buttonOffset: new AMap.Pixel(10, 50), //定位按钮与设置的停靠位置的偏移量，默认：Pixel(10, 20)
			showMarker: true, //定位成功后在定位到的位置显示点标记，默认：true
			showCircle: true, //定位成功后用圆圈表示定位精度范围，默认：true
			panToLocation: true, //定位成功后将定位到的位置作为地图中心点，默认：true
			zoomToAccuracy: true //定位成功后调整地图视野范围使定位位置及精度范围视野内可见，默认：false
		});
		map.addControl(geolocation);
		$(".amap-geolocation-con").css("z-index", "9");
		//返回定位信息
		AMap.event.addListener(geolocation, 'complete', function(data) {
			var pos = {
				"longitude": data.position.L,
				"latitude": data.position.N
			}
			yibanhtml5location(JSON.stringify(pos));
		});
		//返回定位出错信息
		AMap.event.addListener(geolocation, 'error', function(data) {
			$("#geoError").show();
			$("#attend").data("ongeo", false);
			if(data.info == "NOT_SUPPORTED") {
				$.alert("请更换浏览器并请重试。", "当前浏览器不支持定位功能 !");
			} else {
				switch(data.message) {
					case "Get ipLocation failed.":
						$.alert("IP精确定位失败。", "定位失败！请重试。");
						break;
					case "sdk定位失败.":
						$.alert("请检查sdk的key是否设置好，以及webview的定位权限及应用和系统的定位权限是否开启。", "定位失败！请重试。");
						break;
					case "Browser not Support html5 geolocation.":
						$.alert("请更换浏览器并请重试。", "浏览器不支持原生定位接口!");
						break;
					case "Geolocation permission denied.":
						$.alert("设备和浏览器的定位权限可能被关闭了，或者浏览器禁止了非安全域的定位请求，请检查。", "定位失败！请重试。");
						break;
					case "Get geolocation time out.":
						$.alert("浏览器定位超时。", "定位失败！请重试。");
						break;
					case "Get geolocation failed.":
						$.alert("定位失败。", "定位失败！请重试。");
						break;
					case "Get geolocation time out.Get ipLocation failed.":
						$.toptip("尝试易班定位...", "warning");
						gethtml5location_fun();
						break;
					default:
						$.alert("定位失败。", "定位失败！请重试。");
						break;
				}
			}
		});
		$.toptip('正在定位...', 'warning');
		geolocation.getCurrentPosition();
		$("#attend").data("ongeo", true);
	});
	//绘制区域:长安大学渭水校区
	var path = [
		[108.89563, 34.371814],
		[108.906509, 34.375055],
		[108.909282, 34.37575],
		[108.911959, 34.376335],
		[108.914593, 34.370145],
		[108.89806, 34.365987],
		[108.897126, 34.367661],
		[108.897126, 34.367665],
		[108.8966, 34.368799],
		[108.896348, 34.369397],
		[108.896252, 34.369676],
		[108.8963, 34.36972],
		[108.896386, 34.369755]
	];
	polygon = new AMap.Polygon({
		map: map,
		path: path,
		strokeColor: "#95a0ff", //线颜色
		strokeOpacity: 1, //线透明度
		strokeWeight: 1.5, //线宽
		fillColor: "#65b5fb", //填充色
		fillOpacity: 0.15 //填充透明度
	});
	marker = new AMap.Marker({
		animation: "AMAP_ANIMATION_DROP"
	});
}
$(function() {
	//设置首页信息
	setIndexInfo();
	//签到
	$("#attend").on("click", function() {
		var _this = $(this);
		if(_this.hasClass("weui-btn_disabled") || _this.hasClass("weui-btn_loading")) {
			return;
		}
		if(_this.data("ongeo")) {
			$.toptip('定位中，请稍候', 'warning');
			return;
		}
		var pos = marker.getPosition();
		if(!pos) {
			$.toptip('未获取到定位信息', 'error');
			return;
		}
		_this.addClass("weui-btn_loading").text("正在签到……");
		if(!polygon.contains([pos.lng, pos.lat])) { //暂时改为false，用于测试
			$.confirm({
				title: '当前位置不在校园内!',
				text: '是否继续签到？',
				onOK: function() {
					//点击确认
					attend(pos.lng, pos.lat);
				},
				onCancel: function() {
					$("#attend").removeClass("weui-btn_loading").text("签到");
					return;
				}
			});
		} else {
			attend(pos.lng, pos.lat);
		}
	});
	//通知列表绑定点击事件
	$("#notice").children("a").on("click", function(e) {
		$(".noticeDetail h1.title").text($(this).find(".weui-media-box__title").text());
		$(".noticeDetail .modal-content").children("pre").html($(this).find(".weui-media-box__desc").text());
		$(".noticeDetail").popup();
	});
	//头像点击事件
	$("#avatarSign").on("click", function(e) {
		$(".weui-tabbar__item").eq(3).trigger("click");
	});
	//定位按钮事件
	$(document).on("click", "#geoError", function(e) {
		$.toptip('正在定位...', 'warning');
		geolocation.getCurrentPosition();
		$("#attend").data("ongeo", true);
	});
});
/*
 * 设置首页信息
 */
function setIndexInfo() {
	var atdBtn = $("#attend");
	var mission = $("#mission");
	//任务状态
	var ts = publicInfo.task_status;
	//学生状态
	var ss = personalInfo.sign_status;
	/*
	 * 签到状态逻辑判断
	 * @ss:学生的签到状态，0——未签到，1——已签到，-1——无任务或所有任务已完成
	 * @ts:任务的状态，0——无任务或没有未完成的任务，1——有任务在进行，2——任务即将开始，3——补签任务
	 */
	if(ss == -1 && ts == 0) { //当前没有了签到任务
		mission.children(".weui-panel__hd").text(publicInfo.msg);
		atdBtn.text("当前无签到任务").addClass("weui-btn_disabled");
	} else if(ss == 2 && ts == 1) {
		atdBtn.text("重新签到");
		createMisson(mission, publicInfo.task_name, publicInfo.start_time, publicInfo.end_time, "已签到", "primary");
	} else if(ss == 1 && ts == 1) { //当前正在签到并且该学生完成签到
		atdBtn.text("已签到").addClass("weui-btn_disabled");
		createMisson(mission, publicInfo.task_name, publicInfo.start_time, publicInfo.end_time, "已签到", "primary");
	} else if(ss == 0 && ts == 1) { //当前正在签到并且该学生尚未签到
		atdBtn.text("签到");
		createMisson(mission, publicInfo.task_name, publicInfo.start_time, publicInfo.end_time, "未签到", "warning");
	} else if(ss == 0 && ts == 2) { //对任务进行预告，点名未开始
		mission.children(".weui-panel__hd").text("任务即将开始");
		atdBtn.text("签到任务未开始").addClass("weui-btn_disabled");
		createMisson(mission, publicInfo.task_name, publicInfo.start_time, publicInfo.end_time, "未开始", "primary");
	} else if((ss == 2 || ss == 0) && ts == 3) { //该学生尚未签到需要进行补签
		atdBtn.text("补签").addClass("weui-btn_warn");
		createMisson(mission, publicInfo.task_name, publicInfo.start_time, publicInfo.end_time, "待补签", "warning");
	} else {
		console.warn("获取状态失败");
		atdBtn.text("获取状态失败").addClass("weui-btn_disabled");
	}
	$("#avatarSignImg").attr("src", personalInfo.head_img);
	$("#avatarSignName").text(personalInfo.yb_realname || "username");
	//生成消息列表
	if(publicInfo.notice) {
		$.each(publicInfo.notice, function(i, n) {
			var box = $("<a href='javascript:;'></a>").addClass("weui-media-box weui-media-box_appmsg");
			var box_bd = $("<div></div>").addClass("weui-media-box__bd");
			var box_title = $("<h4></h4>").addClass("weui-media-box__title").text(n.title);
			var box_desc = $("<p></p>").addClass("weui-media-box__desc").text(n.notice);
			var box_info = $("<p></p>").addClass("weui-media-box__info").text(n.timestamp);
			$("#notice").append(box.append(box_bd.append(box_title, box_desc, box_info)));
		});
	} else {
		$("#noticeHd").text("暂无通知")
	}
}

/*
 * 2018-03-12 Wu Zhendong
 * 创建任务元素
 * @obj 父元素
 * @taskName 任务标题
 * @startTime 开始时间
 * @endTime 结束时间
 * @statusText 状态文本
 * @statusStyle 状态样式
 */
function createMisson(obj, taskName, startTime, endTime, statusText, statusStyle) {
	var bd = $("<div></div>").addClass("weui-panel__bd nosign-history");
	var box = $("<div></div>").addClass("weui-media-box weui-media-box_appmsg");
	var boxBd = $("<div></div>").addClass("weui-media-box__bd");
	var boxTitle = $("<h4></h4>").addClass("weui-media-box__title");
	boxTitle.text(taskName);
	var boxDesc = $("<p></p>").addClass("weui-media-box__desc").text("~");
	boxDesc.prepend("<span>签到时间：" + startTime + "</span>").append("<span>" + endTime + "</span>");
	var subscript = $("<div></div>").addClass("subscript").attr("id", "taskStatus");
	subscript.text(statusText).addClass(statusStyle);
	$(obj).append(bd.append(box.append(boxBd.append(boxTitle, boxDesc), subscript)));
}

/*
 * 签到
 * @noncestr:时间戳
 * @latitude:纬度
 * @longitude:经度
 */
function attend(lng, lat) {
	$.ajax({
		type: "POST",
		url: "http://www.chddata.com/index.php/yiban/sign/submit?appid=" + APP_ID + "&verify_request=" + vcode,
		async: true,
		cache: false,
		data: {
			noncestr: Date.parse(new Date()),
			latitude: lat,
			longitude: lng
		},
		success: function(d) {
			if(d.status == "success") {
				$.toast("签到成功！");
				$("#attend").removeClass("weui-btn_loading weui-btn_warn").text("签到成功").addClass("weui-btn_disabled");
				$("#taskStatus").text("已签").addClass("primary").removeClass("warning");
			} else {
				$.toast(d.info + "（错误代码：" + d.code + "）", "cancel", 2000);
				$("#attend").removeClass("weui-btn_loading").addClass("weui-btn_warn").text("重新签到");
			}
		},
		error: function(e) {
			$.toast(JSON.stringify(e), "forbidden");
		}
	});
}