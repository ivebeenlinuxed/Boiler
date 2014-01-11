function build_http_query(parameters) {
	qs = "";
	for(var key in parameters) {
	    var value = parameters[key];
	    qs += encodeURIComponent(key) + "=" + encodeURIComponent(value) + "&";
	  }
	  if (qs.length > 0){
	    qs = qs.substring(0, qs.length-1); //chop off last "&"
	  }
	  return qs;
}