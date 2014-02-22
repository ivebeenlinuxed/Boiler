GanttChart = function(el) {
	this.el = $(el);
	this._dragElement = null;
	this._dragEvent = null;
	this._dragCalc = null;
	this.snapGrid = $(el).attr("data-snapseconds");
	this.minDrag = $(el).attr("data-mindrag");

	$(".gantt-row[data-table] .gantt-overlay[data-id]", this.el).on(
			"contextmenu", function(that) {
				return function(e) {
					e.preventDefault();
					table = $(this).closest(".gantt-row").attr("data-table");
					id = $(this).attr("data-id");

					$.ajax({
						url : "/api/" + table + "/" + id,
						type : "DELETE",
						success : function() {
							$(this).detach();
						},
						context : this
					});
				};
			}(this));

	$(".gantt-row[data-table][data-idfield][data-startfield][data-endfield]",
			this.el)
			.mousedown(
					function(that) {
						return function(e) {
							if (e.button != 0) {
								return;
							}
							that._dragCalc = new Object();
							that._dragCalc.table = $(this).attr("data-table");
							that._dragCalc.preset = $(this).attr("data-preset");
							e.preventDefault();
							$(this)
									.prepend(
											'<div class="gantt-overlay progress"><div class="progress-bar" style="width: 100%; background-image: linear-gradient(to bottom, #eded2f, #eaea81); color: black;"></div></div>');

							that._dragElement = $(this).children()[0];
							that._dragEvent = e;
						}
					}(this));

	$(".gantt-row[data-table][data-idfield][data-startfield][data-endfield]",
			this.el).mouseup(
			function(that) {
				return function(e) {
					if (!that._dragElement) {
						return;
					}

					start_field = $(this).attr("data-startfield");
					end_field = $(this).attr("data-endfield");
					id_field = $(this).attr("data-idfield");
					table = that._dragCalc.table;
					preset = that._dragCalc.preset;
					if (!preset) {
						post_data = new Object();
					} else {
						post_data = JSON.parse(preset);
					}

					post_data[start_field] = that._dragCalc.start;
					post_data[end_field] = that._dragCalc.end;

					that._dragCalc.id_field = id_field;

					$.ajax({
						url : "/api/" + table + ".json",
						type : "POST",
						dataType : "json",
						data : post_data,
						success : function(data) {
							$(this._dragElement).attr("data-id",
									data.data[this._dragCalc.id_field]);
							this._dragElement = null;
							this._dragEvent = null;
							this._dragCalc = null;
						},
						context : that
					});

				}
			}(this));

	$(".gantt-row[data-table][data-idfield][data-startfield][data-endfield]",
			this.el)
			.mousemove(
					function(that) {
						return function(e) {
							if (that._dragEvent == null) {
								return;
							}

							barstart_time = parseInt($(this).closest(
									".tab-pane").attr("data-timestart"));
							barend_time = parseInt($(this).closest(".tab-pane")
									.attr("data-timeend"));

							barrow_position = $(this).position();
							barrow_width = $(this).width();

							startoffset_pix = (that._dragEvent.clientX - barrow_position.left);
							endoffset_pix = (e.clientX - barrow_position.left);

							secPerPix = (barend_time - barstart_time)
									/ barrow_width;

							start_time = barstart_time
									+ (secPerPix * startoffset_pix);
							end_time = barstart_time
									+ (secPerPix * endoffset_pix);

							if (that.snapGrid != -1) {
								start_time = Math.round(start_time
										/ that.snapGrid)
										* that.snapGrid;
								end_time = Math.round(end_time / that.snapGrid)
										* that.snapGrid;
							}

							time_difference = end_time - start_time;

							if (time_difference >= 0
									&& time_difference < that.minDrag) {
								end_time = start_time + that.minDrag;
							}

							if (time_difference <= 0
									&& time_difference > 0 - that.minDrag) {
								end_time = start_time - that.minDrag;
							}

							if (time_difference < 0) {
								tmp = start_time;
								start_time = end_time;
								end_time = tmp;
								time_difference = time_difference * -1;
							}

							percPerSec = 100 / (barend_time - barstart_time);

							that._dragCalc.end = end_time;
							that._dragCalc.start = start_time;

							$(that._dragElement).css(
									"margin-left",
									(percPerSec * (start_time - barstart_time))
											+ "%").css("width",
									(percPerSec * time_difference) + "%");
						}
					}(this));
}

WidgetFactory.RegisterWidget(".gantt", GanttChart);

/*
 * $(document).ready(function() {
 * 
 * 
 * 
 * $(".production-gantt-user-process").each(function() { p = new
 * ProductionGantt(this); p.addEventListener("periodAdded",
 * function(timestamp_start, timestamp_end, row_el, eventStart) { $.ajax({ url:
 * "/api/user_shift.json", type: "post", data: {user:
 * $(row_el).attr("data-user"), shift_job: $(row_el).attr("data-job"), start:
 * timestamp_start, end: timestamp_end}, dataType: "json", success:
 * function(data) { $(this).attr("data-id", data.id); }, context:
 * eventStart._newElement }); }); p.addEventListener("contextmenu", function(e) {
 * if ($(e.target).is(".progress-bar") && e.button == 2) { $.ajax({ url:
 * "/api/user_shift/"+$(e.target).parent().attr("data-id")+".json", type:
 * "DELETE" }); $(e.target).parent().detach(); } });
 * 
 * }); });
 * 
 * ProductionGantt = function(el) { this.el = $(el); this.dragType = -1;
 * this.dragEvent = false;
 * 
 * this.DRAG_PERIOD = 0x01;
 * 
 * this.minDrag = this.el.attr("data-min-drag"); this.snapGrid =
 * this.el.attr("data-snap-grid"); this.canEdit =
 * this.el.attr("data-edit")!="true"? false : true;
 * 
 * this.events = new Array();
 * 
 * 
 * 
 * this.addEventListener = function(event, callback) { if (!this.events[event]) {
 * this.events[event] = new Array(); }
 * 
 * this.events[event].push(callback); }
 * 
 * this.triggerEvent = function(event, args) { if (!this.events[event]) {
 * return; }
 * 
 * for (i in this.events[event]) { this.events[event][i].apply(null, args); } }
 * 
 * $("[data-type='schedule-dragger']", this.el).on("contextmenu", function(that) {
 * return function(e) { e.preventDefault(); that.triggerEvent("contextmenu",
 * [e]); } }(this));
 * 
 * $(this.el).on("mousedown", function(that) { return function(e) {
 * e.preventDefault(); if (!that.canEdit) { return; } if
 * (!$(e.target).parent().is("[data-type='schedule-dragger']")) { return; } el =
 * $(e.target).parent();
 * 
 * that.dragType = that.DRAG_PERIOD; //$("<div class='gantt-overlay progress'
 * style='margin-left: "+e.offsetX+"px; width: 50px;'></div>"); if
 * (that.snapGrid != -1) { start =
 * Math.round(e.offsetX/that.snapGrid)*that.snapGrid; }
 * 
 * $(e.target).prepend($("<div class='gantt-overlay progress'
 * style='margin-left: "+start+"px; width: 50px;'><div class='progress-bar'
 * role='progressbar' style='width: 100%; background-image: linear-gradient(to
 * bottom, #"+el.attr("data-colour-primary")+",
 * #"+el.attr("data-colour-gradient")+")'></div></div>")); e._newElement =
 * $(e.target).children()[0]; that.dragEvent = e; }; }(this));
 * 
 * 
 * $(this.el).on("mousemove", function(that) { return function(e) { if
 * (that.dragType == -1) { return; } start = that.dragEvent.offsetX;
 * requestWidth = e.screenX-that.dragEvent.screenX;
 * 
 * if (that.snapGrid != -1) { start =
 * Math.round(start/that.snapGrid)*that.snapGrid; requestWidth =
 * Math.round(requestWidth/that.snapGrid)*that.snapGrid; }
 * 
 * if (requestWidth >= 0 && requestWidth < that.minDrag) { requestWidth =
 * that.minDrag; }
 * 
 * if (requestWidth <= 0 && requestWidth > 0-that.minDrag) { requestWidth =
 * 0-that.minDrag; }
 * 
 * if (requestWidth < 0) { requestWidth = requestWidth*-1; start =
 * start-requestWidth; }
 * 
 * $(that.dragEvent._newElement).css("margin-left", start).css("width",
 * requestWidth+"px"); } }(this));
 * 
 * $(this.el).on("mouseup", function(that) { return function(e) { if
 * (that.dragType == that.DRAG_PERIOD) { el = $(that.dragEvent.target).parent();
 * drag_element = $(that.dragEvent._newElement); offset_gantt =
 * that.el.offset(); offset_bar = drag_element.offset();
 * 
 * gantt_offset = offset_bar.left-offset_gantt.left-200; if (gantt_offset <
 * that.el.attr("data-scale")/24) { gantt_offset -= 1; }
 * 
 * secperpx = 86400/that.el.attr("data-scale"); bar_starttime =
 * gantt_offset*secperpx;
 * console.log(gantt_offset+drag_element.width()*secperpx);
 * console.log(secperpx); bar_endtime =
 * ((gantt_offset+drag_element.width())*secperpx); console.log("----");
 * //console.log(gantt_offset); //console.log(pxpersec);
 * //console.log(bar_starttime); //console.log(bar_endtime);
 * //console.log(that.el.attr("data-timestamp-start"));
 * 
 * timestamp_start =
 * parseInt(that.el.attr("data-timestamp-start"))+bar_starttime; timestamp_end =
 * parseInt(that.el.attr("data-timestamp-start"))+bar_endtime;
 * //console.log(drag_element.width()); that.triggerEvent("periodAdded",
 * [timestamp_start, timestamp_end, el, that.dragEvent]); } that.dragType = -1; }
 * }(this)); }
 * 
 */