
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
	
	$("body").delegate("[data-type='modal']", "click", function(e) {
		//FIXME Depreciate
		e.preventDefault();
		fireAPIModal($(this).attr("href"));
		if ($(this).attr("data-modal-return")) {
			api_return = $(this).attr("data-modal-return");
		} else {
			api_return = null;
		}
	});
	
	$("body").delegate("[data-type='api-notification']", "click", function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr("href"),
			headers: {"X-Disposition": "notification"},
			dataType: "json",
			success: function(data) {
				for (n in data) {
					note = {iconClass: 'toast-error', message: data[n].message, title: data[n].title, timeout: data[n].timeout};
					if (data[n].priority == E_ERROR) {
						note.iconClass = 'toast-error';
						note.optionsOverride = {tapToDismiss: false};
					} else if (data[n].priority == E_WARNING) {
						note.iconClass = 'toast-warning';
					} else  if (data[n].priority == E_NOTICE) {
						note.iconClass = 'toast-info';
					}
					toastr.options.timeOut = data[n].timeout;
					note = toastr.notify(note);
				}
			}
		});
	});
	
	$("body").delegate("#api-modal a:not([data-modal-result])", "click", function(e) {
		if ($(this).attr("href") != "#") {
			e.preventDefault();
			fireAPIModal($(this).attr("href"));
		}
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
	
	$("body").delegate("#main-container a:not([data-type='api-modal'], [data-type='api-notification'])", "click", function(e) {
		e.preventDefault();
		$.pjax.click(e, {container: "#main-container"});
		//$(document).pjax('a', '#pjax-container');
	});
});

api_return = null;

$(document).ready(function() {
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



