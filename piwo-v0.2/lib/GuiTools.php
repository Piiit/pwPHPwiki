<?php

class GuiTools {
	
	public static function checkbox($label, $name, $checked) {
		$checkedString = $checked ? " checked='checked' " : "";
		return "<label for='$name'>$label</label><input type='checkbox' name='$name'$checkedString />";
	}
	
	public static function textInput($label, $name, $default = "") {
		return "<label for='$name'>$label</label><input type='text' name='$name' />";
	}
	
	public static function passwordInput($label, $name) {
		return "<label for='$name'>$label</label><input type='password' name='$name' />";
	}
	
	public static function button($name, $href, $shortcut = null) {
		$o  = "<span class='button'><a href='?$href'>";
		if ($shortcut !== null) {
			$o .= "<span class='shortcut'>$shortcut</span>";
		}
		$o .= "$name</a></span>";
		return $o;
	}
	
}

?>