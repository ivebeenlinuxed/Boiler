$("body").delegate("input[type='password'][data-name][data-url]:not([data-type$='-typeahead']), input[type='text'][data-name][data-url]:not([data-type$='-typeahead']), textarea[data-name][data-url]", "keyup", function() {
	$.ajax({
		url: $(this).attr("data-url"),
		type: "put",
		data: encodeURIComponent($(this).attr("data-name"))+"="+($(this).attr("data-result")? encodeURIComponent($(this).attr("data-result")) : encodeURIComponent($(this).val()))
	});
});