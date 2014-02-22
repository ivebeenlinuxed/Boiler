/*
$(document).ready(function() {
	$("body").on("DOMSubtreeModified", function() {
		$("[data-type='data-typeahead']").each(function() {
			if (this.uploader != null && this.uploader != undefined) {
				return;
			}
			AjaxTypeahead(this, $(this).attr("data-url"), $(this).attr("data-name"), $(this).attr("data-id"), $(this).attr("data-field"));
		});
	});
	
	
});
*/

$("body").delegate("[data-type='data-typeahead']", "keyup", function() {
	if (!this.uploader) {
		AjaxTypeahead(this, $(this).attr("data-url"), $(this).attr("data-name"), $(this).attr("data-id"), $(this).attr("data-field"));
	}
});

AjaxTypeahead = function(el, url, name, id, queryfield) {
	el.uploader = true;
	$(el).typeahead({
		name: name,
		value: id,
	    source: function(el, url, name, id, queryfield) {
	    	return function (query, process) {
		    	data = new Object();
		    	data[queryfield] = query;
		    	this.$element.removeClass("valid");
		        return $.ajax({
		        	url: url, 
		        	data: data,
		        	dataType: "json",
		        	context: this,
		        	success: function (process) {
		        		return function(data) {
		        			if (this.options.render) {
		        				this.render = this.options.render;
		        			}
		        			
		        			if (this.options.select) {
		        				this.select = this.options.select;
		        			}
		        			return process(data.data);
		        		};
		        	}(process)
		        });
		    }
	    }(el, url, name, id, queryfield),
	    
	    select: function() {
	    	var val = this.$menu.find('.active').attr('data-value');
	    	var text = this.$menu.find('.active').text();
	        this.$element
	          .attr("data-result", this.updater(val))
	          .val(text)
	          .change().trigger("typeahead-change")
	        this.$element.addClass("valid");
	        return this.hide()
	    },
	    
	    matcher: function(item) {
	    	return ~item[this.options.name].toLowerCase().indexOf(this.query.toLowerCase())
	    },
	    render: function (items) {
	        var that = this

	        items = $(items).map(function (i, item) {
	          i = $(that.options.item).attr('data-value', item[that.options.value])
	          i.find('a').html(that.highlighter(item[that.options.name]))
	          return i[0]
	        })

	        items.first().addClass('active')
	        this.$menu.html(items)
	        return this
	      },
	      sorter: function (items) {
	          var beginswith = []
	          , caseSensitive = []
	          , caseInsensitive = []
	          , item

	        while (item = items.shift()) {
	          if (!item[this.options.name].toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
	          else if (~item[this.options.name].indexOf(this.query)) caseSensitive.push(item)
	          else caseInsensitive.push(item)
	        }

	        return beginswith.concat(caseSensitive, caseInsensitive)
	      },
	      highlighter: function (item) {
	          var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
	          return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
	            return '<strong>' + match + '</strong>'
	          })
	        }

	      ,
	});
}

