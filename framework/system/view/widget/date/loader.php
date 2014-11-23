<script type="text/javascript">
var doc = this.document.currentScript.ownerDocument;
var DateWidgetObj = function() {};
DateWidgetObj = Object.create(Widget.prototype, {
	Refresh: {
		value: function(response) {
			dt = new Date(response.data[this.dataset.field]*1000);
			$(this).attr("data-result", response.data[this.dataset.field]);
			this.value = ""+dt.getDate()+"/"+dt.getMonth()+"/"+dt.getFullYear();
		}
	}
});
DateWidgetObj.createdCallback = function() {
	//this.value = "TEST";
	Widget.call(this);
	$(this).addClass("form-control");
	$(this).datepicker({
		onSelect: function() {
			e = $(this).val().split("/");
			d = Math.round((new Date(e[2], e[1], e[0])).getTime()/1000);
			$(this).attr("data-result", d);
			$(this).trigger("change");
		},
		onClose: function() {
			if ($(this).val() == "") {
				$(this).attr("data-result", "null");
			}
		}
	});
	//console.log("Date Widget", this);
}

var DateWidget = document.registerElement('date-widget', {extends: 'input', prototype: DateWidgetObj});
</script>