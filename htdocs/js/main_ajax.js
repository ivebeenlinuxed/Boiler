

$(document).ready(function() {
	//$.pjax.defaults.timeout = 1000;


	$("body").delegate("[data-type='modal']", "click", function(e) {
		e.preventDefault();
		fireAPIModal($(this).attr("href"));
		return;
		$.ajax({
			url: $(this).attr("href"),
			dataType: "json",
			headers: {
				"X-Disposition":"modal"
			},
			success: function(json) {
				$("body").append(json.html);
				console.log($("#"+json.component_id));
				$("#"+json.component_id).modal('show');
			}
		});
	});
	
	$("body").delegate("#api-modal a:not([data-modal-result])", "click", function(e) {
		if ($(this).attr("href") != "#") {
			e.preventDefault();
			fireAPIModal($(this).attr("href"));
		}
		//$(document).pjax('a', '#pjax-container');
	});

	$("body").delegate("#api-modal [data-modal-result]", "click", function(e) {
		e.preventDefault();
		result = $(this).attr("data-modal-result");
		$("#api-modal").modal('hide');
		if (api_return) {
			$(api_return).val(result).trigger("change").trigger("keyup");
		}
	});
	
	//$(document).pjax("a:not([data-type='api-modal'])", '#main-container');
	
	//$(document).pjax(".side-menu a", '#main-container');
	var pjax;
	$("body").delegate("a:not([data-type='api-modal'])", "click", function(e) {
		e.preventDefault();
		if (pjax && pjax.state != 4) {
			pjax.abort();
		}
		pjax = $.ajax({
			url: $(this).attr("href"),
			timeout: 1000,
			success: function(data) {
				$("#main-container").html(data);
				window.history.replaceState({}, document.title, this.url);
			},
			error: function() {
				window.location = this.url;
			}
		});
		//$.pjax.click(e, {container: "#main-container"});
		//$(document).pjax('a', '#pjax-container');
	});
	
});

api_return = null;

$(document).ready(function() {
	$("body").delegate("[data-type='api-modal']", "click", function(e) {
		e.preventDefault();
		fireAPIModal($(this).attr("href"));
		if ($(this).attr("data-modal-return")) {
			api_return = $(this).attr("data-modal-return");
		} else {
			api_return = null;
		}
	});
});

function fireAPIModal(url) {
	$.ajax({
		url: url,
		dataType: "html",
		headers: {
			"X-Disposition":"modal"
		},
		success: function(html) {
			$("#api-modal").html(html).modal('show');
		}
	});
}



