
$(document).ready(function() {
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
	
	$(document).pjax(".side-menu a", '#main-container');
	
	$("body").delegate("#main-container a:not([data-type='api-modal'])", "click", function(e) {
		e.preventDefault();
		$.pjax.click(e, {container: "#main-container"});
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



