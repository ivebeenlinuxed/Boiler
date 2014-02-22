HTMLInstantCheckbox = function(el) {
	Widget.call(this, el);
	
}

HTMLInstantCheckbox.prototype = Object.create(Widget.prototype, {
	Changed: {value: function() {
		$.ajax({
			url: "/api/"+this.table+"/"+this.id+".json",
			type: "PUT",
			data: encodeURIComponent(this.field)+"="+($(this.element).is(":checked")? $(this.element).attr("data-selected") : $(this.element).attr("data-deselected"))
		});
	}}
});

WidgetFactory.RegisterWidget("[data-type='checkbox'][data-selected][data-deselected][data-table][data-id][data-field]", HTMLInstantCheckbox);