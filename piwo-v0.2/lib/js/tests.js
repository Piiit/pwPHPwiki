function test() {
	test5()
}

function test1() {
	var url = window.location.href
	document.write(url + "<br />")
	var x = pw_url_split(url)
	for (var i in x) {
		document.write(i + ": " + x[i] + "<br />")
	}
}

function test2() {
	var x = pw_url_setparam('mode', '7', window.location.href)
	document.write(x + "<br />")
}

function test3() {
	var x = pw_url_getparam('mode', window.location.href)
	document.write("'"+x + "': Length=" +x.length+"<br />")
}

function test4() {
	var arr = new Array("A", "BB", "CCC");
	var x = pw_array_find("Ds", arr);
	document.write(x + "<br />")
}

function test4() {
 	var arr = new Array("A", "BB", "CCC");
 	var x = pw_array_find("", arr);
 	document.write(x + "<br />")
}

function test5() {
 	var arr = new Array("A", "BB", "CCC");
 	var sarr = new Array("cleared");
 	var x = pw_array_oneinarray(sarr, arr);
 	document.write(x + "<br />")
}


function test6() {
	var url = window.location.href
	var arr = new Array("A", "mode2", "8");
	var x = pw_url_oneisset(arr, url);
 	document.write(x + "<br />")
}