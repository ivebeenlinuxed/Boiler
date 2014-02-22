$("body").delegate("button[data-type='button-immediate'][data-table][data-field][data-id][data-value]", "click", function() {
	$.ajax({
		url: "/api/"+$(this).attr("data-table")+"/"+$(this).attr("data-id"),
		type: "put",
		data: encodeURIComponent($(this).attr("data-field"))+"="+encodeURIComponent($(this).attr("data-value"))
	});
});