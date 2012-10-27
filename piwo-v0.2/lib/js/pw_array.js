 function pw_array_find(needle, stack) {
 	for (var i in stack) {
 		if (stack[i] == needle) {
 			return i
 		}
 	}

 	return false;
 }

 function pw_array_oneinarray(needles, stack) {
 	for (var i in needles) {
		var x = pw_array_find(needles[i], stack)
		if (x !== false) {
			return true
		}
 	}
 	return false
 }
