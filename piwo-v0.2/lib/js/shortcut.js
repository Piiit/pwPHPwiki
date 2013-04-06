/**
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/
 * Version : 2.01.B
 * By Binny V A
 * License : BSD
 */
shortcut = {
	'all_shortcuts':{},//All the shortcuts are stored in this array
	'add': function(shortcut_combination,callback,opt) {
		//Provide a set of default options
		var default_options = {
			'type':'keydown',
			'propagate':false,
			'disable_in_input':false,
			'target':document,
			'keycode':false
		};
		if(!opt) opt = default_options;
		else {
			for(var dfo in default_options) {
				if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
			}
		}

		var ele = opt.target;
		if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
		shortcut_combination = shortcut_combination.toLowerCase();

		//The function to be called at keypress
		var func = function(e) {
			e = e || window.event;

			if(opt['disable_in_input']) { //Don't enable shortcut keys in Input, Textarea fields
				element = null;
				if(e.target) element=e.target;
				else if(e.srcElement) element=e.srcElement;
				if(element.nodeType==3) element=element.parentNode;
				if(element.tagName == 'INPUT' || element.tagName == 'TEXTAREA') return;
			}

			//Find Which key is pressed
			if (e.keyCode) code = e.keyCode;
			else if (e.which) code = e.which;
			var character = String.fromCharCode(code).toLowerCase();

			if(code == 188) character=","; //If the user presses , when the type is onkeydown
			if(code == 190) character="."; //If the user presses , when the type is onkeydown

			var keys = shortcut_combination.split("+");
			//Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
			var kp = 0;

			//Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
			var shift_nums = {
				"`":"~",
				"1":"!",
				"2":"@",
				"3":"#",
				"4":"$",
				"5":"%",
				"6":"^",
				"7":"&",
				"8":"*",
				"9":"(",
				"0":")",
				"-":"_",
				"=":"+",
				";":":",
				"'":"\"",
				",":"<",
				".":">",
				"/":"?",
				"\\":"|"
			};
			//Special Keys - and their codes
			var special_keys = {
				'esc':27,
				'escape':27,
				'tab':9,
				'space':32,
				'return':13,
				'enter':13,
				'backspace':8,

				'scrolllock':145,
				'scroll_lock':145,
				'scroll':145,
				'capslock':20,
				'caps_lock':20,
				'caps':20,
				'numlock':144,
				'num_lock':144,
				'num':144,

				'pause':19,
				'break':19,

				'insert':45,
				'home':36,
				'delete':46,
				'end':35,

				'pageup':33,
				'page_up':33,
				'pu':33,

				'pagedown':34,
				'page_down':34,
				'pd':34,

				'left':37,
				'up':38,
				'right':39,
				'down':40,

				'f1':112,
				'f2':113,
				'f3':114,
				'f4':115,
				'f5':116,
				'f6':117,
				'f7':118,
				'f8':119,
				'f9':120,
				'f10':121,
				'f11':122,
				'f12':123
			};

			var modifiers = {
				shift: { wanted:false, pressed:false},
				ctrl : { wanted:false, pressed:false},
				alt  : { wanted:false, pressed:false},
				meta : { wanted:false, pressed:false}	//Meta is Mac specific
			};

			if(e.ctrlKey)	modifiers.ctrl.pressed = true;
			if(e.shiftKey)	modifiers.shift.pressed = true;
			if(e.altKey)	modifiers.alt.pressed = true;
			if(e.metaKey)   modifiers.meta.pressed = true;

			for(var i=0; k=keys[i],i<keys.length; i++) {
				//Modifiers
				if(k == 'ctrl' || k == 'control') {
					kp++;
					modifiers.ctrl.wanted = true;

				} else if(k == 'shift') {
					kp++;
					modifiers.shift.wanted = true;

				} else if(k == 'alt') {
					kp++;
					modifiers.alt.wanted = true;
				} else if(k == 'meta') {
					kp++;
					modifiers.meta.wanted = true;
				} else if(k.length > 1) { //If it is a special key
					if(special_keys[k] == code) kp++;

				} else if(opt['keycode']) {
					if(opt['keycode'] == code) kp++;

				} else { //The special keys did not match
					if(character == k) kp++;
					else {
						if(shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
							character = shift_nums[character];
							if(character == k) kp++;
						}
					}
				}
			}

			if(kp == keys.length &&
						modifiers.ctrl.pressed == modifiers.ctrl.wanted &&
						modifiers.shift.pressed == modifiers.shift.wanted &&
						modifiers.alt.pressed == modifiers.alt.wanted &&
						modifiers.meta.pressed == modifiers.meta.wanted) {
				callback(e);

				if(!opt['propagate']) { //Stop the event
					//e.cancelBubble is supported by IE - this will kill the bubbling process.
					e.cancelBubble = true;
					e.returnValue = false;

					//e.stopPropagation works in Firefox.
					if (e.stopPropagation) {
						e.stopPropagation();
						e.preventDefault();
					}
					return false;
				}
			}
		};
		this.all_shortcuts[shortcut_combination] = {
			'callback':func,
			'target':ele,
			'event': opt['type']
		};
		//Attach the function with the event
		if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
		else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
		else ele['on'+opt['type']] = func;
	},

	//Remove the shortcut - just specify the shortcut and I will remove the binding
	'remove':function(shortcut_combination) {
		shortcut_combination = shortcut_combination.toLowerCase();
		var binding = this.all_shortcuts[shortcut_combination];
		delete(this.all_shortcuts[shortcut_combination]);
		if(!binding) return;
		var type = binding['event'];
		var ele = binding['target'];
		var callback = binding['callback'];

		if(ele.detachEvent) ele.detachEvent('on'+type, callback);
		else if(ele.removeEventListener) ele.removeEventListener(type, callback, false);
		else ele['on'+type] = false;
	}
};





/**
 * PIWO: shortcuts...
 * @TODO: but them in a configfile.
 */

mode = pw_url_getparam('mode', window.location.href);

shortcut.add("Ctrl+s",function() {
	//alert("SAVE");
	//document.write("HALLO")
	te = document.getElementById("save");
	te.click();
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});



shortcut.add("Esc",function() {
	te = document.getElementById("exiteditor");
	if (te)
		window.location.href = te.href;
	else {
		// @TODO: location.href nur ändern, wenn ESC-Button im aktuellen Kontext erlaubt ist...
		url = window.location.href;

		mode = pw_url_getparam('mode', url);
		dialog = pw_url_getparam('dialog', url);

		notallowed = new Array('cleared');
		url = pw_url_setparam('dialog', null, url);

		if (!pw_array_find(mode, notallowed) || dialog)
			if (mode == 'showpages' && dialog) {
				window.location.href = pw_url_setparam('mode', 'showpages', url);
			} else {

				window.location.href = pw_url_setparam('mode', 'cleared', url);
			}
	}
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F6",function() {
	url = window.location.href;
	mode = pw_url_getparam('mode', url);
	if (mode == 'showpages') {
		node = document.getElementById("overview").getElementsByTagName("TR");

		for (i in node) {
			if (node[i].nodeName == "TR") {
				if (tr_focus == i) {
					tr_focus = 0;
					links = node[i].getElementsByTagName("A");
					if (links[1]) {
						window.location.href = links[1];
					}
				}
			}
		}
		return

	}
	window.location.href = pw_url_setparam('mode', 'editpage', url);
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F7",function() {
	url = window.location.href;
	mode = pw_url_getparam('mode', url);

	if (mode == 'showpages') {
		node = document.getElementById("overview").getElementsByTagName("TR");

		for (i in node) {
			if (node[i].nodeName == "TR") {
				if (tr_focus == i) {
					tr_focus = 0;
					links = node[i].getElementsByTagName("A");
					if (links[2]) {
						window.location.href = links[2];
					}
				}
			}
		}
		return

	}

	dialog = pw_url_getparam('dialog', url);
	if (dialog != 'delpage')
		window.location.href = pw_url_setparam('dialog', 'delpage', url);
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F8",function() {
	url = window.location.href;
	mode = pw_url_getparam('mode', url);

	if (mode == 'showpages') {
		node = document.getElementById("overview").getElementsByTagName("TR");

		for (i in node) {
			if (node[i].nodeName == "TR") {
				if (tr_focus == i) {
					tr_focus = 0;
					links = node[i].getElementsByTagName("A");
					if (links[3]) {
						window.location.href = links[3];
					}
				}
			}
		}
		return

	}

	dialog = pw_url_getparam('dialog', url);
	if (dialog != 'movepage')
		window.location.href = pw_url_setparam('dialog', 'movepage', url);

},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F9",function() {
	url = window.location.href;
	dialog = pw_url_getparam('dialog', url);
	if (dialog != 'newpage')
		window.location.href = pw_url_setparam('dialog', 'newpage', url);
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F10",function() {
	url = window.location.href;
	mode = pw_url_getparam('mode', url);
	if (mode != 'showpages')
		window.location.href = pw_url_setparam('mode', 'showpages', url);
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("F12",function() {
	url = window.location.href;
	dialog = pw_url_getparam('dialog', url);
	if (dialog != 'login')
		window.location.href = pw_url_setparam('dialog', 'login', url);
},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

tr_focus = 0;

shortcut.add("up",function() {

	node = document.getElementById("overview").getElementsByTagName("TR");

	tr_focus -= 1;

	if (tr_focus <= 0)
		tr_focus = node.length-1;

	for (i in node) {
		if (node[i].nodeName == "TR") {
			if (tr_focus == i) {
				node[i].style.background="#222222";

				// Position: tablerow
				y = findPosY(node[i]);
				x = findPosX(node[i]);

				// Position: scrolled page (bottom)
				px = f_scrollLeft();
				py = f_scrollTop()+f_clientHeight();

				if (py < y+20 || py > y) {
					window.scrollTo(px, y-40);
				}

			} else {
				node[i].style.background="#000000";
			}
		}
	}

},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("down",function() {

	node = document.getElementById("overview").getElementsByTagName("TR");

	tr_focus += 1;

	if (tr_focus > node.length-1)
		tr_focus = 1;

	for (i in node) {
		if (node[i].nodeName == "TR") {
			if (tr_focus == i) {
				node[i].style.background="#222222";

				// Position: tablerow
				y = findPosY(node[i]);
				x = findPosX(node[i]);

				// Position: scrolled page (bottom)
				px = f_scrollLeft();
				py = f_scrollTop()+f_clientHeight();

				if (py < y+20 || py > y) {
					window.scrollTo(px, y-40);
				}
			} else {
				node[i].style.background="#000000";
			}
		}
	}


},{
	'type':'keydown',
	'propagate':false,
	'target':document
});


shortcut.add("enter",function() {

	dialog = pw_url_getparam('dialog', window.location.href);
	ae = document.activeElement;
	if ((dialog != false || dialog != '')) {
		if ((dialog == 'login' && ae.name == 'password') || (ae.name == 'logout') || (dialog == 'newpage') || (dialog == 'config') || (dialog == 'movepage')) {
			node = document.getElementById('submit');
			node.click();
			return false;
		}
	}

	node = document.getElementById("overview").getElementsByTagName("TR");

	for (i in node) {
		if (node[i].nodeName == "TR") {
			if (tr_focus == i) {
				tr_focus = 0;
				links = node[i].getElementsByTagName("A");
				if (links[0])
					window.location.href = links[0];
			}
		}
	}

},{
	'type':'keydown',
	'propagate':false,
	'target':document
});

shortcut.add("delete",function() {
	node = document.getElementById("overview").getElementsByTagName("TR");

	for (i in node) {
		if (node[i].nodeName == "TR") {
			if (tr_focus == i) {
				links = node[i].getElementsByTagName("A");
				if (links[3]) {
					tr_focus = 0;
					window.location.href = links[3];
				}
			}
		}
	}


},{
	'type':'keydown',
	'propagate':false,
	'target':document
});