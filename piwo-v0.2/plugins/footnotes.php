<?php

  function plugin_footnotes($lexer, $node) {
    global $footnotes;
    $o = '<div class="footnotes">';
    $o .= '<ol>';
    $i = 0;
    foreach($footnotes as $ftn) {
      $i++;
      $o .= '<li><a class="footnote_t" id="fn__'.$i.'" name="fn__'.$i.'" href="#fnt__'.$i.'">&uarr;</a> ';
      $footnote = $lexer->getNode($ftn);
      $o .= $lexer->getText($footnote);
      $o .= '</li>';
    }
    $o .= '</ol>';
    if ($i == 0) {
      $o .= 'In diesem Text kommen keine Fußnoten vor.';
    }
    $o .= '</div>';
    return $o;
  }

?>