
		//thx to www.digitalbonsai.com

			var xhr = new Array();				// ARRAY OF XML-HTTP REQUESTS
			var xi  = new Array();				// ARRAY OF XML-HTTP REQUEST INDEXES
			xi[0] = 1; 					// FIRST INDEX SET TO 1 MAKING IT AVAILABLE

			function pw_ajax_init() {

				var xhrsend = xi.length;		// xhrsend IS THE xi POSITION THAT GETS PASSED BACK. INITIALIZED TO THE LENGTH OF THE ARRAY(LAST POSITION + 1). IN CASE A FREE RESOURCE ISN'T FOUND IN THE LOOP

				for (var i=0; i<xi.length; i++) { 	// GO THROUGH AVAILABLE xi VALUES
					if (xi[i] == 1) { 				// IF IT'S 1 (AVAILABLE), ALLOCATE IT FOR USE AND BREAK
						xi[i] = 0;
						xhrsend = i;
						break;
					}
				}

				xi[xhrsend] = 0; 					// SET TO 0 SINCE IT'S NOW ALLOCATED FOR USE

				var ms_xml = new Array(
					"Microsoft.XMLHTTP",
					"MSXML2.XMLHTTP.6.0",
					"MSXML2.XMLHTTP",
					"MSXML2.XMLHTTP.5.0",
					"MSXML2.XMLHTTP.4.0",
					"MSXML2.XMLHTTP.3.0",
					"MSXML2.XMLHTTP.2.0"
				);


				if (window.XMLHttpRequest) {
					try {
						xhr[xhrsend] = new XMLHttpRequest();
					} catch (e) {}
				} else if (window.ActiveXObject) {
					for (var typ in ms_xml) {
						try {
							xhr[xhrsend] = new ActiveXObject(ms_xml[typ]);
							break;
						} catch(e) {}
					}
				} else {
					throw Exception("Ajax funktioniert auf diesem System nicht!");
					return false;
				}

				return (xhrsend);
			}

			function pw_ajax_call (adress, handler, method, param, htmlpos, append) {

				var xhri = pw_ajax_init();
				var func = null;
				var param_string = "";

				if (param != null) {
					for (var ele in param) {
						param_string += ele + "=" + param[ele] + "&";
					}
					param_string = param_string.substring(0, param_string.length-1);
				}

				if (method == "POST") {	func = param_string; }

				xhr[xhri].open(method, adress, true);
				xhr[xhri].setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

				xhr[xhri].onreadystatechange = function () {
					if (xhr[xhri].readyState == 4 && xhr[xhri].status == 200) { // Status: 200 = OK; State 4 = completed ajax response
						handler(xhr[xhri].responseText, htmlpos, append);
						if (xhr[xhri].responseText.length == 0)
							nocontent(htmlpos, adress, true);
						xi[xhri] = 1;
						xhr[xhri] = null;
					} else if (xhr[xhri].readyState == 4 && xhr[xhri].status == 404) {
						nocontent(htmlpos, adress, false);
						xi[xhri] = 1;
						xhr[xhri] = null;
					} else if (xhr[xhri].readyState == 1) {
						loading(htmlpos, adress);
					}

				};

				xhr[xhri].send(func);


			}
