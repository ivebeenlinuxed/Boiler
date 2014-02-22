$(document).ready(function() {
	
	WidgetFactory.Render($("body"));
	$("body").on("DOMSubtreeModified", function(e) {
		WidgetFactory.Render(e.target);
	});
});



WidgetFactory = new Object();
WidgetFactory.rendering = false;
WidgetFactory.widget_library = new Array();

WidgetFactory.registered_elements = new Array();

WidgetFactory.RegisterWidget = function(selector, cnstrct) {
	obj = new Object();
	obj.selector = selector;
	obj.constr = cnstrct;
	
	WidgetFactory.widget_library.push(obj);
}

WidgetFactory.Render = function(el) {
	if (WidgetFactory.rendering) {
		return;
	}
	WidgetFactory.rendering = true;
	for (i in WidgetFactory.widget_library)	{
		widget = WidgetFactory.widget_library[i];
		$(widget.selector, el).each(function() {
			if (!this.widget) {
				this.widget = new widget.constr(this);
				WidgetFactory.registered_elements.push(this);
			}
		});
	}
	new_reg = new Array();
	for (i in WidgetFactory.registered_elements) {
		reg = WidgetFactory.registered_elements[i];
		if (!$.contains(document, reg)) {
			if (reg.widget.Destroy) {
				reg.widget.Destroy();
			}
		} else {
			new_reg.push(reg);
		}
	}
	WidgetFactory.registered_elements = new_reg;
	
	WidgetFactory.rendering = false;
}


var lock_id = Math.round(Math.random()*Math.random()*10000);
Widget = function(el) {
	this.element = $(el);
	this.id = this.element.attr("data-id");
	this.table = this.element.attr("data-table");
	this.field = this.element.attr("data-field");
	$(this.element).on("change", this.Changed.bind(this));
	$(this.element).on("keyup", this.Changed.bind(this));
	$(this.element).on("focus", this._onfocus.bind(this));
	$(this.element).on("blur", this._onblur.bind(this));
	
	
	this.lockTimeout = null;
	this.relockTimeout = null;
	this.lock_rtc = null;
	
	waitForSocket(function(that) {
		return function(sock) {
			that.lock_rtc = sock;
			that.lock_rtc.subscribe("/lock/"+that.table+"/"+that.id+"/"+that.field, function(that) {
				return function(channel, response) {
					console.log(response);
					console.log("Got a lock: "+response.lock_id);
					if (response.lock_id != lock_id && response.lock_id != 0) {
						that.EnforceLock(response.user);
					} else {
						that.EnforceUnlock();
					}
				}
			}(that));
		}
	}(this));
}

Widget.prototype = {
		Destroy: function() {
			console.log("Destroyed widget");
		},
		Changed: function() {
			val = $(this.element).attr("data-result")? $(this.element).attr("data-result") : $(this.element).val();
			$.ajax({
				url: "/api/"+this.table+"/"+this.id+".json",
				type: "PUT",
				data: encodeURIComponent(this.field)+"="+encodeURIComponent(val)
			});
		},
		
		_onfocus: function() {
			this.Lock();
		},
		
		_onblur: function() {
			this.Unlock();
		},
		
		EnforceLock: function(user) {
			if (this.lockTimeout) {
				clearTimeout(this.lockTimeout);
			}
			this.lockTimeout = setTimeout(this.EnforceUnlock.bind(this), 5000);
			$(this.element).attr("disabled", true);
			if (user) {
				data = {animation: false, title: "Locked by "+user.firstname+" "+user.surname, trigger: "manual"};
				$(this.element).tooltip(data).tooltip("show");
			}
		},
		
		EnforceUnlock: function() {
			if (this.lockTimeout) {
				clearTimeout(this.lockTimeout);
			}
			$(this.element).attr("disabled", false);
			$(this.element).tooltip("hide");
		},
		
		Lock: function() {
			if (this.relockTimeout) {
				clearTimeout(this.relockTimeout);
			}
			this.relockTimeout = setTimeout(this.Lock.bind(this), 3000);
			this.lock_rtc.call("/lock/"+this.table+"/"+this.id+"/"+this.field, lock_id, current_user);
		},
		
		Unlock: function() {
			if (this.relockTimeout) {
				clearTimeout(this.relockTimeout);
			}
			this.lock_rtc.call("/lock/"+this.table+"/"+this.id+"/"+this.field, 0, current_user);
		},
		
}