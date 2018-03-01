/**
 * Created by yiban on 16/5/23.
 *  author:liuchengbin
 *  desc:js<->oc js<->android
 */

/*
 函数名称：browser
 函数作用：判断访问终端
 参数说明：无
*/
var browser = {
	versions: function() {
		var u = navigator.userAgent,
			app = navigator.appVersion;
		return {
			mobile: !!u.match(/AppleWebKit.*Mobile.*/), //是否为移动终端
			ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端
			android: u.indexOf('Android') > -1 || u.indexOf('Adr') > -1, //android终端
			iPhone: u.indexOf('iPhone') > -1, //是否为iPhone或者QQHD浏览器
			iPad: u.indexOf('iPad') > -1, //是否iPad
		};
	}(),
	language: (navigator.browserLanguage || navigator.language).toLowerCase()
}

/*
 函数名称：getLocation
 函数作用：获取地理位置
 参数说明：无
 */
function gethtml5location_fun() {
	if(browser.versions.android) {
		//android 调用方式
		window.local_obj.yibanhtml5location();
	} else if(browser.versions.ios) {
		ios_yibanhtml5location();
	} else {
		onerror('该终端类型暂不支持使用');
	}
}

/*
 函数名称：yibanhtml5location
 函数作用：客户端获取地理位置，异步返回位置信息,html根据返回信息做界面内容处理
 参数说明：postion  格式:{"longitude": "","latitude": "","address": ""}
 */
function yibanhtml5location(postion) {
	var pos = JSON.parse(postion);
	marker = new AMap.Marker({
		position: [pos.longitude, pos.latitude],
		map: map,
		animation: "AMAP_ANIMATION_DROP"
	});
	map.setCenter([pos.longitude, pos.latitude]);
	map.setFitView().getCenter();
	if(f == 1) {
		if(polygon.contains([pos.longitude, pos.latitude])) {
			//客户端判断成功后再通过ajax提交给服务端
			//ajax待接口和服务端定义好后去完成
			$.ajax({
				type: "POST",
				url: "http://www.chddata.com/index.php/yiban/sign/submit?appid=" + APP_ID + "&verify_request=" + vcode,
				async: true,
				data: {
					noncestr: Date.parse(new Date()),
					latitude: pos.latitude,
					longitude: pos.longitude
				},
				success: function(d) {
					if(d.status == "success") {
						$("#attend").removeClass("weui-btn_loading").text("签到成功");
					} else {
						$.toast(d.info + "（错误代码：" + d.code + "）", "cancel",2000);
						$("#attend").removeClass("weui-btn_loading").addClass("weui-btn_warn").text("重新签到");
					}
				},
				error: function(e) {
					$.toast(JSON.stringify(e),"forbidden");
				}
			});
		} else {
			$("#attend").removeClass("weui-btn_loading").addClass("weui-btn_warn").text("签到失败");
			$.toast('签到失败，不在签到范围内', 'cancel');
		}
	}
}

/*
 函数名称：phone
 函数作用：拨打电话
 参数说明：电话号码
 */
function phone_fun(num) {
	var pre = /^1\d{10}$/;
	var tre = /^0\d{2,3}-?\d{7,8}$/;
	if(pre.test(num) || tre.test(num)) {
		if(browser.versions.android) {
			//android 调用方式
			window.local_obj.phone(num);
		} else if(browser.versions.ios) {
			phone(num);
		} else {
			onerror('该终端类型暂不支持使用');
		}
	} else {
		onerror('手机号格式错误');
	}
}

/*
 函数名称：mail
 函数作用：发邮件
 参数说明：email地址
 */
function mail_fun(email) {
	var re = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/
	if(re.test(email)) {
		if(browser.versions.android) {
			//android 调用方式
			window.local_obj.mail(email);
		} else if(browser.versions.ios) {
			mail(email);
		} else {
			onerror('该终端类型暂不支持使用');
		}
	} else {
		onerror('邮箱地址格式错误');
	}
}

/*
 函数名称：encode
 函数作用：扫一扫
 参数说明：content内容
 */
function encode_fun() {
	if(browser.versions.android) {
		//android 调用方式
		window.local_obj.encode();
	} else if(browser.versions.ios) {
		encode();
	} else {
		onerror('该终端类型暂不支持使用');
	}
}

/*
 函数名称：getScanResult
 函数作用：扫一扫结果返回
 参数说明：二维码中必须包含“yiban_scan_result”标识否则跳转新的页面
 */
function getScanResult(info) {
	document.getElementById("returnValue").value = info;
}

/*
 函数名称：back
 函数作用：返回app
 参数说明：content内容
 */
function back_fun() {
	if(browser.versions.android) {
		//android 调用方式
		window.local_obj.back();
	} else if(browser.versions.ios) {
		back();
	} else {
		onerror('该终端类型暂不支持使用');
	}
}

/*
 函数名称：download
 函数作用：下载
 参数说明：地址
 */
function download_fun(vurl) {
	if(browser.versions.android) {
		//android 调用方式
		window.local_obj.download(vurl);
	} else if(browser.versions.ios) {
		download(vurl);
	} else {
		onerror('该终端类型暂不支持使用');
	}
}

/*
 函数名称：onerror
 函数作用：非客户端的错误处理
 参数说明：errorInfo  错误信息   用户自定义格式
 */
function onerror(errorInfo) {
	$.toptip(errorInfo, 'error');
}