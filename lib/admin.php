<?phpif (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'piwo-v0.2/lib/common.php';require_once INC_PATH.'pwTools/string/encoding.php';require_once INC_PATH.'pwTools/debug/TestingTools.php';require_once INC_PATH.'pwTools/file/FileTools.php';
require_once INC_PATH.'pwTools/gui/GuiTools.php';


function pw_wiki_update_cache($forced = false) {	$files = new RecursiveIteratorIterator(				new RecursiveDirectoryIterator(WIKISTORAGE)			 );
	foreach($files as $filename) {		if(substr($filename, (-1) * strlen(WIKIFILEEXT)) == WIKIFILEEXT) {			try {				pw_wiki_create_cached_page(WikiID::fromPath($filename, WIKISTORAGE, WIKIFILEEXT), $forced);			} catch (Exception $e) {				echo "<pre>Exception: Skipping file '$filename': $e\n</pre>";			}		}
	}}// function pw_wiki_create_cached_page(WikiID $id, $forced = false) {// 	$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;// 	$headerID = new WikiID(WIKITEMPLATESNS."header");// 	$footerID = new WikiID(WIKITEMPLATESNS."footer");// 	$headerFilename = WIKISTORAGE."/".$headerID->getPath().WIKIFILEEXT;// 	$footerFilename = WIKISTORAGE."/".$footerID->getPath().WIKIFILEEXT;	// 	if (!is_file($filename)) {// 		throw new Exception("File '$filename' does not exist!");
// 	}// 	if (!is_file($headerFilename)) {
// 		throw new Exception("File '$headerFilename' does not exist!"); 
// 	}// 	if (!is_file($footerFilename)) {
// 		throw new Exception("File '$footerFilename' does not exist!");
// 	}	// 	// If the cached file is still up-to-date do nothing! Except forced overwriting!// 	$cachedFilename = WIKICACHE."/".$id->getPath().WIKICACHEFILEEXT;// 	if(!$forced && is_file($cachedFilename)) {// 		$cachedMTime = filemtime($cachedFilename);// 		if($cachedMTime >= filemtime($filename) && $cachedMTime >= filemtime($headerFilename) && $cachedMTime >= filemtime($footerFilename)) {// 			$data = file_get_contents($cachedFilename);// 			if ($data === false) {// 				throw new Exception("Unable to read data file '$cachedFilename'!");// 			}// 			TestingTools::inform($cachedFilename);// 			return $data;// 		}// 	}	// 	$data = file_get_contents($filename);// 	if ($data === false) {
// 		throw new Exception("Unable to read data file '$filename'!");
// 	}
// 	$headerData = file_get_contents($headerFilename);
// 	if ($headerData === false) {
// 		throw new Exception("Unable to read template file '$headerFilename'!");
// 	}
// 	$footerData = file_get_contents($footerFilename);
// 	if ($footerFilename === false) {
// 		throw new Exception("Unable to read template file '$footerFilename'!");
// 	}	// 	$data = $headerData."\n".$data."\n".$footerData;// 	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));
	
// 	if (!utf8_check($data)) {
// 		throw new Exception("File '$filename' is not an UTF8-encoded file!");
// 	}
	
// 	$out = parse($data, pw_wiki_getcfg('debug'));	// 	FileTools::createFolderIfNotExist(dirname($cachedFilename));
// 	if (file_put_contents($cachedFilename, $out) === false) {// 		throw new Exception("Unable to write file '$cachedFilename'!");// 	}	// 	return $out;// }function pw_wiki_get_parsed_file(WikiID $id, $forcedCacheUpdate = false) {		$filename = WIKISTORAGE.$id->getPath().WIKIFILEEXT;	$headerID = new WikiID(WIKITEMPLATESNS."header");	$footerID = new WikiID(WIKITEMPLATESNS."footer");
	$headerFilename = WIKISTORAGE."/".$headerID->getPath().WIKIFILEEXT;;
	$footerFilename = WIKISTORAGE."/".$footerID->getPath().WIKIFILEEXT;;
	
	if (!is_file($filename)) {
		throw new Exception("File '$filename' does not exist!");
	}
	if (!is_file($headerFilename)) {
		throw new Exception("File '$headerFilename' does not exist!");
	}
	if (!is_file($footerFilename)) {
		throw new Exception("File '$footerFilename' does not exist!");
	}		/* 	 * This is only executed with configuration CACHE ENABLED!	 * If the cached file is still up-to-date do nothing, except forced 	 * overwrite is enabled.	 */	if(pw_wiki_getcfg('useCache') == true) {		$cachedFilename = WIKICACHE."/".$id->getPath().WIKICACHEFILEEXT;				if(! $forcedCacheUpdate && is_file($cachedFilename)) {						$cachedFileModTime = filemtime($cachedFilename);			if ($cachedFileModTime >= filemtime($filename) 				&& $cachedFileModTime >= filemtime($headerFilename) 				&& $cachedFileModTime >= filemtime($footerFilename)) {										$data = file_get_contents($cachedFilename);				if ($data === false) {					throw new Exception("Unable to read data file '$cachedFilename'!");				}				TestingTools::inform("Using cached file :".$cachedFilename);				return $data;			}		}	}		$data = file_get_contents($filename);
	if ($data === false) {
		throw new Exception("Unable to read data file '$filename'!");
	}
	$headerData = file_get_contents($headerFilename);
	if ($headerData === false) {
		throw new Exception("Unable to read template file '$headerFilename'!");
	}
	$footerData = file_get_contents($footerFilename);
	if ($footerFilename === false) {
		throw new Exception("Unable to read template file '$footerFilename'!");
	}		$data = $headerData."\n".$data."\n".$footerData;	$data = FileTools::setTextFileFormat($data, new TextFileFormat(TextFileFormat::UNIX));		if (!utf8_check($data)) {		throw new Exception("File '$filename' is not an UTF8-encoded file!");	}		$out = parse($data, pw_wiki_getcfg('debug'));		/*	 * Write parser results to a file if CACHING is enabled. 	 */	if (pw_wiki_getcfg("useCache") == true) {		FileTools::createFolderIfNotExist(dirname($cachedFilename));		if (file_put_contents($cachedFilename, $out) === false) {			throw new Exception("Unable to write file '$cachedFilename'!");		}	}		return $out;}
function pw_wiki_showcontent(WikiID $id) {// 	if(!isset($_SESSION['pw_wiki']['useCache']) || $_SESSION['pw_wiki']['useCache'] == false) {// 		return pw_wiki_get_parsed_file($id);// 	}// 	return pw_wiki_create_cached_page($id);	return pw_wiki_get_parsed_file($id);
}


?>