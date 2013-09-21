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
	
	public static function dialogQuestion($title, $desc, $byesname, $byestext, $bnoname, $bnotext, $method = "post") {
		$o = "<div class='admin'>";
		$o .= "<form method='$method' accept-charset='utf-8' id='form'>";
		$o .= "<h1>$title</h1>";
		$o .= "<p>$desc</p>";
		$o .= "<button type='submit' name='$byesname'>$byestext</button>";
		$o .= "<button type='submit' name='$bnoname'>$bnotext</button>";
		$o .= "</form>";
		$o .= "</div>";
		return $o;
	}
	
	
}

?>