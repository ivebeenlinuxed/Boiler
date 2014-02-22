$(document).ready(function() {
	$("body").delegate("input[data-type='value-switch']", "click", function() {
		if (!this.checked) {
			$.ajax({
				url: $(this).attr("data-url"),
				type: "PUT",
				data: encodeURIComponent($(this).attr("data-name"))+"="+encodeURIComponent($(this).attr("data-deselected"))
			});
		} else {
			$.ajax({
				url: $(this).attr("data-url"),
				type: "PUT",
				data: encodeURIComponent($(this).attr("data-name"))+"="+encodeURIComponent($(this).attr("data-selected"))
			});
		}
	});
});