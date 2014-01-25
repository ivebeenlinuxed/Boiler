$(document).ready(function() {
	TextWidgetFactory.Render($("body"));
	$("body").on("DOMSubtreeModified", function() {
		TextWidgetFactory.Render($("body"));
	});
});

TextWidgetFactory = new Object();
TextWidgetFactory.rendering = false;
TextWidgetFactory.Render = function(el) {
	if (TextWidgetFactory.rendering) {
		return;
	}
	TextWidgetFactory.rendering = true;
	$("[type='text'][data-table][data-id][data-field]", el).each(function() {
		if (!this.widget) {
			new TextWidget(this);
		}
	});	
	TextWidgetFactory.rendering = false;
}

TextWidget = function(el) {
	this.element = $(el);
	this.element.get(0).widget = this;
	this.table = this.element.attr("data-table");
	this.id = this.element.attr("data-id");
	this.field = this.element.attr("data-field");
	
	this.getResult = function() {
		return $(this.element).attr("data-result")? $(this.element).attr("data-result") : $(this.element).val();
	}
	
	this.keyup = function() {
		$.ajax({
			url: "/api/"+this.table+"/"+this.id+".json",
			type: "put",
			data: encodeURIComponent(this.field)+"="+encodeURIComponent(this.getResult())
		});
	};
	$(this.element).on("keyup", this.keyup.bind(this));
}