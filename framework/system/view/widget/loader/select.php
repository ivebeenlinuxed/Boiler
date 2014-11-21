<script type="text/javascript">
var doc = this.document.currentScript.ownerDocument;
var SelectWidgetObj = function() {};
SelectWidgetObj = Object.create(Widget.prototype);
SelectWidgetObj.createdCallback = function() {
	Widget.call(this);
	$(this).addClass("form-control");
}

var selectWidget = document.registerElement('select-widget', {extends: 'input', prototype: SelectWidgetObj});
</script>