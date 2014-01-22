$(document).ready(function() {
	TooltipWidgetFactory.Render($("body"));
	$("body").on("DOMSubtreeModified", function() {
		TooltipWidgetFactory.Render($("body"));
	});
});

TooltipWidgetFactory = new Object();
TooltipWidgetFactory.rendering = false;
TooltipWidgetFactory.Render = function(el) {
	if (TooltipWidgetFactory.rendering) {
		return;
	}
	TooltipWidgetFactory.rendering = true;
	$("[data-toggle='tooltip']", el).each(function() {
		if (!this.widget) {
			this.widget = true;
			$(this).tooltip();
		}
	});	
	
	TooltipWidgetFactory.rendering = false;
}