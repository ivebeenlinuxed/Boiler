

SelectWidget = function(el) {
	Widget.call(this, el);
}

SelectWidget.prototype = Object.create(Widget.prototype, {
	
});


WidgetFactory.RegisterWidget("select[data-type='select-immediate'][data-table][data-id][data-field]", SelectWidget);
