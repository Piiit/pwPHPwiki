<?php

if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../').'/lib/');
	#echo INC_PATH;
}
require_once INC_PATH.'FilePath.php';
require_once INC_PATH.'File.php';

#mkdir("001");
#mkdir("002");
#touch ("f01");


echo("<pre>");

try {
	File::setWorkingDirectory("001");
	$file = new File("f01");
	$file = new File("f02");
	#File::setWorkingDirectory("../002");
	#$file->rename("lola");
	#$file->rename("Lola");
	#$file->delete();
	echo($file->getWorkingDirectory());
	echo("\nREALPATH:");
	echo($file->getRealPath());
	echo("\nFILENAME:");
	echo($file->getFileName());
	echo("\nSIZE:");
	echo($file->getSize());
	echo("\nPERM:");
	echo($file->getPermissions());
	
	
} catch (Exception $e) {
	echo "EXCEPTION CATCHED: ".$e->getMessage();
}

echo("\nFILES in current folder:\n");
var_dump(glob("*"));
echo("</pre>");

