
function pw_url_getparam(name, url) {
	var urlx = pw_url_split(url)

	for (var i = 1; i < urlx.length; i++) {

		curname = urlx[i][0]
		curvalue = urlx[i][1] ? urlx[i][1] : ""

		// Ohne curname ist der Eintrag kein Parameter, sondern ein #hash...
		if (curname != "#" && curname == name) {
			return curvalue
		}
	}

	return false
}

// @TODO: falls value = null, Parameter loeschen... (value = '', name erhalten, aber ohne '=')
function pw_url_setparam(name, value, url) {
	var urlx = pw_url_split(url)
	var out = urlx[0] + "?";
	var found = false

	for (var i = 1; i < urlx.length; i++) {

		curname = urlx[i][0]
		curvalue = urlx[i][1]

		// Ohne curname ist der Eintrag kein Parameter, sondern ein #hash...
		if (curname != "#") {
			if (curname == name) {
				curvalue = value
				found = true
			}

			if (curvalue) {
				out += curname + "=" + curvalue + "&"
			} else if (curvalue !== null) {
				out += curname + "&"
			}
		}

	}

	// Letztes & entfernen...
	if (out[out.length-1] == '&') {
		out = out.slice(0, -1)
	}

	// Parameter nicht gefunden => hinzufuegen...
	if (!found) {
		if (value === '') {
			out += "&" + name
		} else {
			out += "&" + name + "=" + value
		}
	}

	// #hash wieder einfügen, falls vorhanden...
	var hash = urlx.pop()
	if (hash && hash[0] == "#")
		out += hash

	return out;
}



function pw_url_split(url) {

	var out = new Array();
	var parout = new Array();

	url = url.split('?')
	adr = url[0]

	out.push(adr)

	if (! url[1]) {
		return out;
	}

	var jumptxt = ""
	var jump = url[1].split('#')
	for (var i in jump) {
		if (i > 0) {
			jumptxt += "#" + jump[i]
		}
	}

	var par = jump[0]
	par = par.split('&')

	for (var i in par) {
		namevalue = par[i].split('=')

		if (namevalue.length == 1) {
			name = (namevalue[0] == "") ? null : namevalue[0]
			value = null
		} else if (namevalue.length >= 2) {
			name = namevalue[0]
			value = ""
			for (var j = 1; j < namevalue.length; j++) {
				value += namevalue[j] + "="
			}
			if (value.substring(value.length-1, value.length) == "="){
				value = value.substring(0, value.length-1);
			}
		}

		if (name != null) {
			out.push( new Array (name, value) )
		}
	}

	if (jumptxt)
		out.push(jumptxt)

	return out;
}

function pw_url_oneisset(names, url) {
	var urlx = pw_url_split(url)

	for (var i in names) {
		if (pw_url_getparam(names[i], url)) {
			return true
		}
	}

	return false

}