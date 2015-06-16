<?php

function pw_wiki_getmenu($id, $mode, Collection $modules) {
	$o = "";
	foreach ($modules->getArray() as $module) {
		if($module instanceof MenuItemProvider && $module->getMenuAvailability()) {
			if(!($module instanceof PermissionProvider) || $module instanceof PermissionProvider && $module->permissionGranted()) {
				$o .= GuiTools::textButton($module->getMenuText(), "id=".$id->getIDAsUrl()."&mode=".$module->getName());
				$o .= " | ";
			}
		}
	}

	return $o;
}

function pw_wiki_file2editor($data) {
	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	$data = pw_s2e($data);
	return $data;
}

function pw_wiki_trace(WikiID $id, $sep = "&raquo;") {
	
	$ns = $id->getFullNS();

	$sep = ' '.$sep.' ';

	$ns = preg_split("#:#", $ns, null, PREG_SPLIT_NO_EMPTY);
	$o = "<a href='?mode=showpages&id='>Home</a>";

	$p = "";
	foreach ($ns as $i) {
		$p .= pw_s2url($i).":";
		$i = pw_s2e(utf8_trim($i));
		$i = utf8_ucfirst($i);
		$o .= $sep."<a href='?mode=showpages&id=".$p."'>".$i."</a>";
	}

	return $o;

}

function pw_wiki_syntaxerr($text, $line, $errtxt, $header = 0, $footer = 0) {
	$texp = explode("\n", $text);
	array_pop($texp);
	array_shift($texp);

	$footer = count($texp)-$footer+1;

	$out = "";
	$lnr = 0;
	foreach ($texp as $ltxt) {
		$lnr++;
		if ($lnr == $line) {
			$out .= sprintf("<span style='color: red'>%6s| %s</span><span class='syntaxerror'>&lt; %s</span>\n", $lnr, htmlentities($ltxt), $errtxt);
		} else {
			if ($lnr <= $header or $lnr >= $footer) {
				$out .= sprintf("<span style='color: gray'>%6s| %s</span>\n", $lnr, htmlentities($ltxt));
			} else {
				$out .= sprintf("%6s| %s\n", $lnr, htmlentities($ltxt));
			}

		}
	}
	return $out;
}

function nop($txt) {
	$out = "<span style='color: yellow'>[WARNING: ";
	$out .= $txt;
	$out .= "]</span>";

	return $out;

}


?>