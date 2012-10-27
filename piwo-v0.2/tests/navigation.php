<?php

if (!defined('INC_PATH')) {
  define ('INC_PATH', '../');
}

require_once INC_PATH."bin/testing.php";

testing_html_header("Functional");

echo "<div id='testnavi'>";

// @TODO: per Ordner trennen... (Ordner = Kategorie, class, PHP-Datei; Datei = Funktionsname)
$libs = glob("functional/*");

foreach ($libs as $lib) {
  if (is_dir($lib)) {
    echo "<h2>".basename($lib)."</h2>";
    $functions = glob("$lib/*");
    echo "<ul>";
    foreach ($functions as $func) {
      $name = basename($func, ".php");
      echo "<li><a target='Daten' href='$func'>$name</a></li>";
    }
    echo "</ul>";
  }
}


echo "</div>";

testing_html_footer();

?>