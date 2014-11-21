<script type="text/javascript">
var doc = this.document.currentScript.ownerDocument;
var textWidgetObj = function() {};
textWidgetObj = Object.create(Widget.prototype);
textWidgetObj.createdCallback = function() {
	Widget.call(this);
	$(this).addClass("form-control");
	this.type = "text";
}

var textWidget = document.registerElement('text-widget', {extends: 'input', prototype: textWidgetObj});
</script>