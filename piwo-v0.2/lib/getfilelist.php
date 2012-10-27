<?php

require_once "pw_debug.php";
require_once "utf8.php";
require_once "admin.php";
require_once "common.php";
require_once "../cfg/main.php";

$id = $_POST['target'];
$files = pw_wiki_getfilelist($id);
$ns = pw_wiki_ns($id);

if ($files) {
foreach ($files as $i => $f) {
  if ($f['TYPE'] == 'DIR' && $f['NAME'] != "..") {
    if (false !== stripos($ns.$f['NAME'], $id)) {
      $match = preg_replace('/'.preg_quote($id).'/i', "<span>$0</span>", $ns.$f['NAME'], 1);
      $matches .= "<li>".$match.":</li>\n";
    }
  }
}
echo "<ul>\n".$matches."</ul>\n";
}

?>