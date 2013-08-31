<?php
class WikiTools {
	
	const ST_FULL = 0;		// Pfad mit Storageangabe, mit Dateinamen, mit Erweiterung
	const ST_SHORT = 1;	 	// Pfad mit Storageangabe, ohne Dateinamen, ohne Erweiterung
	const ST_NOEXT = 2;	 	// Pfad mit Storageangabe, mit Dateinamen, ohne Erweiterung
	const FULL = 3;			// Pfad ohne Storageangabe, mit Dateinamen, mit Erweiterung
	const SHORT = 4;		// Pfad ohne Storageangabe, ohne Dateinamen, ohne Erweiterung
	const NOEXT = 5;		// Pfad ohne Storageangabe, mit Dateinamen, ohne Erweiterung
	const FNAME = 6;		// Nur Dateiname mit Erweiterung
	const FNAME_NOEXT = 7;	// Nur Dateiname ohne Erweiterung
	const DNAME = 8;		// Innerstes Verzeichnis

	public static function path($id, $type = self::SHORT, $extension, $storage) {
		$id = pw_url2u($id);
		$isdir = pw_wiki_isns($id);
		$pg = self::pg($id);
		$id = str_replace(":", "/", $id);
		$storage = utf8_rtrim($storage, '/').'/';

		if (!pw_checkfilename($id) || !pw_checkfilename($storage)) {
			return false;
		}

		$ext = "";
		if (!$isdir) {
			$ext = $extension;
		}

		switch ($type) {
			case ST_FULL:
				$out = $storage.pw_dirname($id).$pg.$ext;
			break;
			case ST_SHORT:
				$out = $storage.pw_dirname($id);
			break;
			case ST_NOEXT:
				$out = $storage.pw_dirname($id).$pg;
			break;
			case FULL:
				$out = pw_dirname($id).$pg.$ext;
			break;
			case SHORT:
				$out = pw_dirname($id) == '.' ? '' : pw_dirname($id);
				break;
			case NOEXT:
				$out = pw_dirname($id).$pg;
			break;
			case FNAME_NOEXT:
				$out = $pg;
			break;
			case FNAME:
				$out = $pg.$ext;
			break;
			case DNAME:
				$out = pw_dirname($id, true);
			break;

		}

		$out = str_replace('//', '/', $out);

		return pw_u2t($out);
	}
}

?>