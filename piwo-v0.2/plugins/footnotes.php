<?php

  function plugin_footnotes(TreeParser $parser, Node $node) {
  	
    $o = '<div class="footnotes">';
    $o .= '<ol>';
    $i = 0;
    foreach(Footnote::$footnoteList as $ftn) {
      $i++;
      $o .= '<li><a class="footnote_t" id="fn__'.$i.'" href="#fnt__'.$i.'">&uarr;</a> ';
      $o .= $ftn;
      $o .= '</li>';
    }
    $o .= '</ol>';
    if ($i == 0) {
      $o .= 'In diesem Text kommen keine Fu&szlig;noten vor.';
    }
    $o .= '</div>';
    return $o;
  }

?>