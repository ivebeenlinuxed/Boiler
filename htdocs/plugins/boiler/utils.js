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

function parse_http_query(string) {
	out = new Object();
	
	e = string.split("&");
	for (i in e) {
		keyval = e[i].split("=");
		out[keyval[0]] = keyval[1];
	}
	
	return out;
}