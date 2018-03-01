/*
 * 获取项目类型
 * @obj：select对象
 */
function getType(obj) {
	var obj = $(obj);
	$.ajax({
		type: "get",
		url: SPORTS_LIST_JSON,
		async: true,
		success: function(d) {
			$.each(d.type, function(i, n) {
				var typeOption = $("<option></option>").text(n.typeName).val(n.id);
				if(obj.data("typeId") == n.id){
					typeOption.attr("selected","selected");
				}
				obj.append(typeOption);
			});
			if (obj.data("typeId")) {
				obj.trigger("change").prepend("<option value=''>---请选择---</option>");
			} else{				
				obj.prepend("<option value='' selected>---请选择---</option>");
			}
		},
		error: function(e) {
			console.log(e);
		}
	});
}

/*
 * 获取项目名称
 * @typeId:分类id
 * @group：分类所属组id
 */
function getEvents(typeId, group) {
	var eventsSelect = $(".eventsName[data-group=" + group + "]");
	if(!typeId) {
		eventsSelect.empty();
		$(".eventsDetail[data-group=" + group + "]").hide(300);
		//清空项目列表
		return;
	}
	$.ajax({
		type: "get",
		url: SPORTS_LIST_JSON,
		async: true,
		success: function(d) {
			eventsSelect.empty();
			$.each(d.events, function(i, n) {
				if(n.typeId == typeId) {
					var nameOption = $("<option></option>").text(n.eventsName).val(n.id);
					if(eventsSelect.data("nameId")==n.id){
						nameOption.attr("selected","selected");
					}
					eventsSelect.append(nameOption);
				}
			});
			if(eventsSelect.data("nameId")){
				eventsSelect.trigger("change").data("nameId","").prepend("<option value=''>---请选择---</option>");
			}else{
				eventsSelect.prepend("<option value='' selected='selected'>---请选择---</option>");
			}
		},
		error: function(e) {
			console.log(e);
		}
	});
}

/*
 * 获取项目详情
 * @eventId:项目id
 * @group：项目所属组id
 */
function getEventsDetail(eventId, group) {
	if(!eventId) {
		//清空项目列表
		$(".eventsDetail[data-group=" + group + "]").hide(300);
		return;
	}
	$.ajax({
		type: "get",
		url: SPORTS_LIST_JSON,
		async: true,
		success: function(d) {
			$.each(d.events, function(i, n) {
				if(n.id == eventId) {
					var wrap = $(".eventsDetail[data-group=" + group + "]");
					wrap.find(".method").text(n.detail.a);
					wrap.find(".rules").text(n.detail.b);
					wrap.find(".remark").text(n.detail.c);
					wrap.show(300);
				}
			});
		},
		error: function(e) {
			console.log(e);
		}
	});
}

/*
 * 创建项目组
 * jellykuma	2018.02.07
 * @i:当前项目组索引，从1开始
 * @btn:【添加】按钮对象
 * @n:已报名的项目
 */
function createGroup(i, btn, n) {
	//标题
	var title = $("<div/>", {
		"class": "weui-cells__title newEvent",
		"data-group": i
	}).append($("<span/>", {
		"text": "报名项目" + i,
	}));
	if(i>2){
		title.append($("<button/>", {
			"class": "weui-btn weui-btn_mini weui-btn_default btn-delete",
			"text": "删除-",
			"type": "button"
		}))
	}
	//包裹块
	var cells = $("<div/>", {
		"class": "weui-cells",
		"style": "display:none"
	});
	//分类包裹块
	var typeWrap = $("<div/>", {
		"class": "weui-cell weui-cell_select weui-cell_select-after"
	});

	//分类label
	$("<label/>", {
		"class": "weui-label",
		"text": "类别",
		"for": "type"
	}).appendTo($("<div/>", {
		"class": "weui-cell__hd"
	}).appendTo(typeWrap));

	//分类select
	var typeSelect = $("<select/>", {
		"class": "weui-select eventsType",
		"name": "type",
		"data-group": i
	}).data("typeId",n?n.type_id.toString():"").appendTo($("<div/>", {
		"class": "weui-cell__bd"
	}).appendTo(typeWrap)).on("DOMNodeInsertedIntoDocument", function(e) {
		getType(this);
	});
	//项目包裹块
	var eventsWrap = $("<div/>", {
		"class": "weui-cell weui-cell_select weui-cell_select-after"
	});

	//项目label
	$("<label/>", {
		"class": "weui-label",
		"text": "项目",
		"for": "events"
	}).appendTo($("<div/>", {
		"class": "weui-cell__hd"
	}).appendTo(eventsWrap));

	//项目select
	$("<select/>", {
		"class": "weui-select eventsName",
		"name": "events",
		"data-group": i
	}).attr("required","required").data("nameId",n?n.event_id.toString():"").appendTo($("<div/>", {
		"class": "weui-cell__bd"
	}).appendTo(eventsWrap));

	//详情包裹块
	var detailWrap = $("<div/>", {
		"class": "eventsDetail",
		"data-group": i
	});
	$("<div/>").append("<span>比赛方法</span>", "<span class='method'></span>").appendTo(detailWrap);
	$("<div/>").append("<span>竞赛规则</span>", "<span class='rules'></span>").appendTo(detailWrap);
	$("<div/>").append("<span>相关说明</span>", "<span class='remark'></span>").appendTo(detailWrap);;
	cells.append(typeWrap, eventsWrap, detailWrap);
	$(btn).before(title, cells);
	cells.show(300);
}

/*
 * 删除项目对象
 * @obj：删除按钮
 */
function deleteEvent(obj) {
	//				$(obj).parent(".weui-cells__title").nextAll(".newEvent").each(function(i){
	//					var group = $(this).data("group")-1;
	//					$(this).data("group",group).children("span").text("报名项目"+group);
	//				});
	//				$(obj).parent(".weui-cells__title").next(".weui-cells").remove().end().remove();
	$(obj).parent(".weui-cells__title").next(".weui-cells").hide(300, function() {
		$(this).remove();
	}).end().hide(300, function() {
		$(this).remove();
	});
}

/*
 *项目表单验证
 */
function eventsNameVerify(){
	var arr = [];
	var m;
	$(".eventsName").each(function(i){
		var eid = $(this).val();
		if (arr.indexOf(eid) > -1) {
			m = false;
			return false;
		}else{
			m = true;
		}
		if(eid){
			arr.push(eid);
		}
	});
	return m;
}