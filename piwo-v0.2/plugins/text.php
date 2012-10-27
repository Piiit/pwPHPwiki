<?php

  function plugin_text($lexer, $node) {

    if (!is_object($lexer) or $lexer->checkNode($node) === false) {
      return;
    }

    $func = $node['CONFIG'][1];
    $func = utf8_strtolower($func);
    $text = $lexer->getText($node);

    $text = pw_e2u($text);

    switch ($func) {
      case "ucfirst":
        $out = utf8_ucfirst($text);
      break;
      case "ucwords":
        $out = utf8_ucwords($text);
      break;
      case "toupper":
        $out = utf8_strtoupper($text);
      break;
      case "tolower":
        $out = utf8_strtolower($text);
      break;
      case "comma":
        $out = str_replace(".", ",", $text);
      break;
      default: return nop("Funktion '$func' im Plugin 'TEXT' nicht vorhanden.", false);
    }

    $out = pw_s2e($out);
    return $out;
  }
?>