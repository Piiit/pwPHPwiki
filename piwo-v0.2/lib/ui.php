<?php

function pw_ui_printDialogWrap($dialog = -1) {
  if ($dialog == -1) {
    pw_ne ("</div>", END);
    return;
  }
  if ($dialog == "") {
    return false;
  }

  pw_ne ("<!-- MODALDIALOG START -->");
  pw_ne ('<div id="modal"><div class="overlay-decorator"></div><div class="overlay-wrap"><div class="overlay"><div class="dialog-wrap"><div class="dialog" id="dialog"><div class="dialog-decorator"><div id="ajax_dialog">', START);
  pw_ne ($dialog);
  pw_ne ('</div></div></div></div></div></div></div>', END);
  pw_ne ("<div style='position: absolute; top: 0; left: 0; width: 100%; display: block; height: 100%; overflow: hidden'>");
  pw_ne ("<!-- MODALDIALOG END -->", START);

  return true;
}

function pw_ui_getDialogInfo($title, $desc, $href, $method = "post") {
  $o  = pw_n();
  $o .= pw_n("<form method='$method' accept-charset='utf-8' id='form'>", START);
  $o .= pw_n("<h1>$title</h1>");
  $o .= pw_n("<div>$desc</div>");
  $o .= pw_n("<div>", START);
  $o .= pw_n("<a href='?$href' id='submit'>OK</a>");
  $o .= pw_n("</div>", END);
  $o .= pw_n("</form>", END);
  return $o;
}

function pw_ui_getDialogQuestion($title, $desc, $byesname, $byestext, $bno, $method = "post") {
  $o  = pw_n();
  $o .= pw_n("<form method='$method' accept-charset='utf-8' id='form'>", START);
  $o .= pw_n("<h1>$title</h1>");
  $o .= pw_n("<div>", START);
  $o .= $desc;
  $o .= pw_n("</div>", END);
  $o .= pw_n("<div>", START);
  $o .= pw_n("<input id='submit' type='submit' name='$byesname' value='$byestext' />");
  $o .= pw_n("<a href='?$bno'>Abbrechen</a>");
  $o .= pw_n("</div>", END);
  $o .= pw_n("</form>", END);
  return $o;
}

function pw_ui_getButton($name, $href, $shortcut = null) {
  $o  = pw_n("<span class='edit'>");
  $o .= pw_n("<a href='?$href'>");
  if ($shortcut !== null) {
    $o .= pw_n("<span class='shortcut'>$shortcut</span>");
  }
  $o .= pw_n("$name</a></span>");

  return $o;
}

?>