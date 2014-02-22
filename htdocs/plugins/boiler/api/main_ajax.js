var api_return;
$(document).ready(function() {
	var url = document.location.toString();
	if (url.match('#')) {
	    $('.nav-tabs a[href=#'+url.split('#')[1]+']').tab('show') ;
	} 
	
	$("body").delegate("[data-type='api-modal']", "click", function(e) {
		e.preventDefault();
		fireAPIModal($(this).attr("href"));
		if ($(this).attr("data-modal-return")) {
			api_return = $(this).attr("data-modal-return");
		} else {
			api_return = null;
		}
	});
	
	$("body").delegate("[data-type='api-modal-post']", "click", function(e) {
		e.preventDefault();
		$.ajax({
			url: $(this).attr("href"),
			data: $(this).attr("data-preset"),
			type: "POST",
			dataType: "html",
			headers: {
				"X-Disposition":"modal"
			},
			success: function(html) {
				$("#api-modal").html(html).modal('show');
			}
		});
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
	
	$("body").delegate("#api-modal a:not([data-modal-result], [href^='#'])", "click", function(e) {
		if ($(this).attr("href") != "#") {
			e.preventDefault();
			fireAPIModal($(this).attr("href"));
		}
	});
	
	$("body").delegate("#api-modal form", "submit", function(e) {
		e.preventDefault();
		
		fields = new Object();
		$("input, select, textarea, button", this).each(function() {
			if ($(this).attr("type") == "submit" && !$(e.target).is(this)) {
				return;
			}
			
			
			if ($(this).attr("name")) {
				fields[$(this).attr("name")] = $(this).val();
			}
		});
		
		$.ajax({
			url: $(this).attr("action"),
			type: $(this).attr("method"),
			data: build_http_query(fields),
			success: function(html) {
				$("#api-modal").html(html).modal('show');
			},
			headers: {
				"X-Disposition":"modal"
			}
		});
	});
	
	$("body").delegate("#main-container form:not([data-ajaxless]) :submit", "click", function(e) {
		e.preventDefault();
		form = $(this).closest("form");
		fields = new Object();
		$("input, select, textarea, button", form).each(function() {
			if ($(this).attr("type") == "submit" && !$(e.currentTarget).is(this)) {
				return;
			}
			
			
			if ($(this).attr("name")) {
				fields[$(this).attr("name")] = $(this).val();
			}
		});
		
		//console.log(fields);
		
		//return;
		$.ajax({
			url: $(form).attr("action"),
			type: $(form).attr("method"),
			data: build_http_query(fields),
			success: function(html) {
				$("#main-container").html(html);
			}
		});
	});

	$("body").delegate("#api-modal [data-modal-result]", "click", function(e) {
		e.preventDefault();
		result = $(this).attr("data-modal-result");
		$("#api-modal").modal('hide');
		if (api_return) {
			$(api_return).val(result).trigger("change").trigger("keyup");
		}
	});
	
	//$(document).pjax(".side-menu a", '#main-container');
	
	$("body").delegate(".side-menu a, #main-container a:not([data-type='api-modal-post'], [data-type='api-modal'], [data-type='api-notification'], [data-ajaxless])", "click", function(e) {
		if ($(this).attr("href").indexOf("#") === 0) {
			return;
		}
		
		e.preventDefault();
		loc = $(this).attr("href");
		window.history.pushState({page: loc}, "Mercian Portal", loc);
		ajax_navigate($(this).attr("href"));
		
		
		
		return;
		$.pjax.click(e, {container: "#main-container"});
		//$(document).pjax('a', '#pjax-container');
	});
	
	window.onpopstate = function(event) {
		  console.log(event);
		  if (event.state) {
			  ajax_navigate(document.location);
		  }
	};
	
	function ajax_navigate(url) {
		$.ajax({
			url: url,
			success: function(data) {
				$("#main-container").html(data);
			},
			timeout: 2000,
			error: function() {
				window.location.reload();
			}
		});
	}
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



