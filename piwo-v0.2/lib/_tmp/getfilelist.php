<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');
}

require_once INC_PATH."pwTools/string/utf8.php";
require_once INC_PATH."piwo-v0.2/lib/admin.php";
require_once INC_PATH."piwo-v0.2/lib/common.php";
require_once INC_PATH."piwo-v0.2/cfg/main.php";

$id = $_POST ['target'];

$files = pw_wiki_getfilelist ( $id );

$ns = pw_wiki_ns ( $id );

if ($files) {
	foreach ( $files as $i => $f ) {
		if ($f ['TYPE'] == 'DIR' && $f ['NAME'] != "..") {
			if (false !== stripos ( $ns . $f ['NAME'], $id )) {
				$match = preg_replace ( '/' . preg_quote ( $id ) . '/i', "<span>$0</span>", $ns . $f ['NAME'], 1 );
				$matches .= "<li>" . $match . ":</li>\n";
			}
		}
	}
	
	echo "<ul>\n" . $matches . "</ul>\n";
}

?>