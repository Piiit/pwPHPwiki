<?php

class GuiTools {
	
	public static function checkbox($label, $name, $checked) {
		$checkedString = $checked ? " checked='checked' " : "";
		return "<label for='$name'>$label</label><input type='checkbox' name='$name'$checkedString />";
	}
	
}

?>