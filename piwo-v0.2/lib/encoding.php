<?php/** * ENCODING: utf8 based character encoding/decoding * * NAMES: pw_[FROM]2[TO] - FROM/TO can be: * s   : string (utf8 or ascii) * u   : utf8 string * e   : html entities (utf8) * t   : ascii - string * url : URL (utf8, urlencoded) * id  : Wiki-ID (lowercase, utf8, separator=':', stripslashes if allowed) */if (!defined('INC_PATH')) {	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/');}require_once INC_PATH."utf8.php";function pw_s2e($text) {	if (utf8_check($text)) {		$text = utf8_decode($text);	}	return utf8_encode(htmlentities($text));}function pw_s2u($text) {	if (utf8_check($text)) {		return $text;	}	return utf8_encode($text);}function pw_u2t($text) {	if (utf8_check($text)) {		return utf8_decode($text);	}	return $text;}function pw_e2u($text) {	if (utf8_check($text)) {		$text = utf8_decode($text);	}	$text = html_entity_decode($text);	$text = utf8_encode($text);	return $text;}function pw_s2url($text) {	if (utf8_check($text)) {		$text = utf8_decode($text);	}	$text = urlencode($text);	// encode URLs according to RFC 3986	$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');	$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");	$text = str_replace($entities, $replacements, $text);	$text = utf8_encode($text);	return $text;}function pw_url2u($text) {	if (utf8_check($text)) {		$text = utf8_decode($text);	}	$text = urldecode($text);	$text = utf8_encode($text);	return $text;}function pw_url2e($text) {	return pw_s2e(pw_url2u($text)); }function pw_e2url($text) {	return pw_s2url(pw_e2u($text));}function pw_url2t($text) {	return pw_u2t(pw_url2u($text));}function pw_stripslashes($text) {	if (pw_check_stripslashes()) {		return preg_replace(array('/\x5C(?!\x5C)/u', '/\x5C\x5C/u'), array('','\\'), $text);	}	return $text;}function pw_check_stripslashes() {	if ( (TRUE == function_exists("get_magic_quotes_gpc") && 1 == get_magic_quotes_gpc()) ||			(ini_get('magic_quotes_sybase') && (strtolower(ini_get('magic_quotes_sybase')) != "off")) ) {		return true;	}	return false;}function pw_stripslashes_deep($value){	$value = is_array($value) ?	array_map('pw_stripslashes_deep', $value) :	pw_stripslashes($value);	return $value;}function pw_stripslashes_array($data) {	if (pw_check_stripslashes()) {		$data = pw_stripslashes_deep($data);		return $data;	}	return $data;}function pw_s2hex($string) {	$hex='';	for ($i=0; $i < strlen($string); $i++) {		$hex .= ord($string[$i]); //."|$string[$i]<hr>";	}	return $hex;}function pw_formatbytes($bytes, $precision = 2, $showbytes = true) {	$units = array('B&nbsp;', 'KB', 'MB', 'GB', 'TB');	$bytes = max($bytes, 0);	if ($bytes < 1024 and !$showbytes) {		$pow = 1;	} else {		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));		$pow = min($pow, count($units) - 1);	}	$bytes /= pow(1024, $pow);	// Round up to 0.1 if bytes are not zero!	$fl = $bytes;	$bytes = round($bytes, $precision);	if ($bytes == 0 and $fl > 0) {		$bytes = "0.10"; // sprintf... add zeros for normalized output with given precision!	}	return str_replace(".", ",", $bytes).' '.$units[$pow];}function pw_s2e_whiteSpace($text, $break = false) {	$text = pw_s2e($text);	$text = str_replace("\r", '\r', $text);	if ($break) {		$text = str_replace("\n", '\n<br />', $text);	} else {
		$text = str_replace("\n", '\n', $text);	}
	$text = str_replace("\t", '\t', $text);
	return $text;
}define('TYPE_UNDEF', -1);define('TYPE_UNIX', 0);define('TYPE_WIN', 1);define('TYPE_MAC', 2);
function pw_normalizeLE(&$data) {	out(strpos($data,"\x"));	if (strpos($data,"\n") !== false and strpos($data,"\r") === false) {		return TYPE_UNIX;	}	if (($nr = strpos($data,"\n\r")) !== false or ($rn = strpos($data, "\r\n")) !== false) {		$sep = $nr ? "\n\r" : "\r\n";		$data = explode($sep, $data);		$data = implode("\n", $data);		return TYPE_WIN;	}	if(strpos($data,"\r") !== false and strpos($data, "\n")===false) {		$data = explode("\r", $data);		$data = implode("\n", $data);		return TYPE_MAC;	}	return TYPE_UNDEF;}?>