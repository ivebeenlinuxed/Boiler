var lock_id = Math.round(Math.random()*Math.random()*10000);
Widget = function() {
	$this = $(this);
	console.log("Widget Created");
	if ($this.is("input, select, span, textarea")) {
		this.input = $this;
	} else {
		this.input = $this.find("input");
	}
	
	this.id = this.dataset.id;
	this.table = this.dataset.table;
	this.field = this.dataset.field;
	this.is_refreshing = false;
	
	if (this.input) {
		this.input.on("change", this.Changed);
		this.input.on("keyup", this.Changed);
		this.input.on("focus", this._onfocus);
		this.input.on("blur", this._onblur);
	}
	
	this.lockTimeout = null;
	this.relockTimeout = null;
	this.lock_rtc = null;
	this.updateTimeout = null;
	
	waitForSocket(function(that) {
		return function(sock) {
			that.lock_rtc = sock;
			that.lock_rtc.subscribe("/lock/"+that.table+"/"+that.id+"/"+that.field, function(that) {
				return function(args, kwargs) {
					response = kwargs;
					
					
					if (response.lock_id == 0) {
						that.EnforceUnlock();
					} else if (response.lock_id != lock_id) {
						that.EnforceLock(response.user);
					}
				}
			}(that));
		}
	}(this));
	
	waitForSocket(function(that) {
		return function(sock) {
			that.lock_rtc = sock;
			that.lock_rtc.subscribe("/model/"+that.table+"/"+that.id, function(that) {
				return function(channel, response) {
					if (response.old_data[that.field] != response.data[that.field]
						&& response.type == 1 && !$(that.input).is(":focus")
						&& response.target_module_table == response.module_table) {
						that.Refresh();
					}
				}
			}(that));
		}
	}(this));
}

Widget.prototype = Object.create(HTMLInputElement.prototype, {
		Destroy: {value: function() {
			console.log("Destroyed widget");
		}},
		Changed: {value: function() {
			if (this.updateTimeout) {
				clearTimeout(this.updateTimeout);
			}
			this.updateTimeout = setTimeout(this._doChanged.bind(this), 1000);
		}},
		
		_doChanged: {value: function() {
			savereg = StatusWidget.SaveRegister();
			this.updateTimeout = null;
			val = $(this.input).attr("data-result")? $(this.input).attr("data-result") : $(this.input).val();
			$.ajax({
				url: "/api/"+this.table+"/"+this.id+".json",
				type: "PUT",
				data: encodeURIComponent(this.field)+"="+encodeURIComponent(val),
				success: function(savereg) {
					return function() {
						StatusWidget.SaveFinish(savereg);
					}
				}(savereg),
				error: function(savereg) {
					return function() {
						StatusWidget.SaveError(savereg);
					}
				}(savereg)
			});
		}},
		
		_onfocus: {value: function() {
			this.Lock();
		}},
		
		_onblur: {value: function() {
			this.Unlock();
		}},
		
		Refresh: {value: function() {
			if (!$(this.element).attr("data-type")) {
				return;
			}
			this.is_refreshing = true;
			$.ajax({
				url: "/util/widget/render/"+$(this.element).attr("data-type")+"?"+build_http_query($(this.element).data()),
				success: function(result) {
					this.is_refreshing = false;
					e = $(result);
					$(this.element).replaceWith(e);
					this.element = e[0];
					console.log("REPLACED");
				},
				context: this
			});
		}},
		
		EnforceLock: {value: function(user) {
			if (this.unlockTimeout) {
				clearTimeout(this.unlockTimeout);
			}
			this.unlockTimeout = setTimeout(this.EnforceUnlock.bind(this), 15000);
			$(this.input).attr("disabled", true);
			if (user) {
				data = {animation: false, title: "Locked by "+user.firstname+" "+user.surname, trigger: "manual"};
				$(this.element).tooltip(data).tooltip("show");
			}
		}},
		
		EnforceUnlock: {value: function() {
			if (this.relockTimeout) {
				clearTimeout(this.relockTimeout);
			}
			if (this.lockTimeout) {
				clearTimeout(this.lockTimeout);
			}
			$(this.input).attr("disabled", false);
			$(this.element).tooltip("hide");
		}},
		
		Lock: {value: function() {
			if (this.relockTimeout) {
				clearTimeout(this.relockTimeout);
			}
			this.relockTimeout = setTimeout(this.Lock.bind(this), 10000);
			if (this.lock_rtc) {
				this.lock_rtc.publish("/lock/"+this.table+"/"+this.id+"/"+this.field, [], {"lock_id": lock_id});
			}
		}},
		
		Unlock: {value: function() {
			if (this.lock_rtc) {
				this.lock_rtc.publish("/lock/"+this.table+"/"+this.id+"/"+this.field, [], {"lock_id": 0});
			}
		}},
		
});