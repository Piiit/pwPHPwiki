<?php
if (!defined('INC_PATH')) {
	define ('INC_PATH', realpath(dirname(__FILE__).'/../../').'/');
}
require_once INC_PATH.'pwTools/debug/TestingTools.php';
require_once INC_PATH.'pwTools/string/encoding.php';

TestingTools::outputOn();
TestingTools::logOn();
TestingTools::debugOn();

echo pw_s2e("ä"); 

