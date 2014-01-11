$("body").delegate(".search-control", "mouseover", function() {
	if (this.timeout) {
		clearTimeout(this.timeout);
	}
	$(".search-generator", this).css("display", "block");
});

$("body").delegate(".search-control", "mouseout", function() {
	this.timeout = setTimeout(function(that) {
		return function() {
			$(".search-generator", that).css("display", "none");
		}
	}(this), 5000);
});

$("body").delegate(".search-control .form-column", "change", function() {
	ctrl = $(this).closest(".search-control");
	
	$(".form-expression", ctrl).removeClass("active");
	console.log("#"+ctrl.attr("id")+"-column-"+$(this).val());
	$("#"+ctrl.attr("id")+"-column-"+$(this).val()).addClass("active");
});

$("body").delegate(".search-control button.form-btn-add", "click", function(e) {
	e.preventDefault();
	ctrl = $(this).closest(".search-control");
	expr = new Array();
	expr.push($(".form-column", ctrl).val());
	expr.push($(".form-expression.active .form-control.form-equality").val());
	formwidget = $(".form-expression.active .form-widget .form-control");
	controlval = formwidget.attr("data-result")? formwidget.attr("data-result") : formwidget.val();
	
	expr.push(controlval);
	console.log(expr);
	json = JSON.stringify(expr);
	$(".search-control .fake-control").append("<span data-json=\""+encodeURIComponent(json)+"\" class='label label-default'>"+expr[0]+"&nbsp;"+expr[1]+"&nbsp;"+expr[2]+" <a href='#'>&times;</a></span>&nbsp;");
	
});

$("body").delegate(".search-control .fake-control .label a", "click", function() {
		$(this).closest(".label").detach();
});

$("body").delegate(".search-control button.form-btn-search", "click", function(e) {
	e.preventDefault();
	ctrl = $(this).closest(".search-control");
	where_expr = new Array();
	$(".fake-control .label").each(function() {
		where_expr.push(JSON.parse(decodeURIComponent($(this).attr("data-json"))));
	});
	query = new Object();
	query.__where = JSON.stringify(where_expr);
	if ($(this).closest("#main_container").length > 0) {
		$.ajax({
			url: ctrl.attr("data-url")+"?"+build_http_query(query),
			success: function(html) {
				$("#main_container").html(html);
			}
		});
	} else {
		fireAPIModal(ctrl.attr("data-url")+"?"+build_http_query(query));
	}
	
});